<?php include_once '../../tpl/permission.php';?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>

<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
		<!-- Begin: life time stats -->
		<div class="portlet box blue-hoki backupsetmanager">
			<div class="portlet-title">
				<div class="caption">
					<i class="levelchild viconfont vicon-vmdata"></i><?php echo $LANG['UI_VOL_CDP_CLIENT_BAK_DATA']; ?>
				</div>
			</div>
			<div class="portlet-body mlr10">
			     <div class="row">
			         <div class="col-md-4">
			             <div class="portlet">
			                <div class="portlet-title">
                				<div class="caption">
                					<i class="viconfont vicon-ge_backup_client"></i><?php echo $LANG['UI_VOL_CDP_DATA_HOST']; ?>
                				</div>
                			</div>
                			<div class="portlet-body batchDeleteDiv">
                			    <div class="btn-group">
								    <button type="button" id="allDelete" class="btn btn-sm green-haze" style="margin-top: 3px;">
								        <i class="viconfont vicon-ge_delete"></i> <?php echo $LANG['UI_PUBLIC_DELETE']; ?>
								    </button>
							    </div>
							    
                    		    
                    			<div id="nodeCheck" style="margin-top:10px;margin-bottom:10px;">
                    			    <select class="bs-select width45p dataBorder floatl" style="height:34px;padding-left: 12px" data-show-subtext="true" id="nodeselect" >
                    			        
                                    </select>
                                    <div class="input-icon right float-left width35p floatl ml10" >
                    				    <input type="text" maxlength="128" placeholder="<?php echo $LANG['UI_COPY_SEARCH_AS_KEYWORD']; ?>" class="form-control " id="searchHost"/>
                    		        </div>	
                    		        <div class="btn-group ml5">
    								    <button type="button" id="searchAgent" class="btn btn-sm green-haze" style="margin-top: 3px;">
    								        <i class="fa fa-search"></i> <?php echo $LANG['UI_PUBLIC_SEARCH']; ?>
    								    </button>
    							    </div>                   	
                                </div>
                                
                                
                			    <div class="vcenter-tree " >
        							<div class="alert alert-block alert-info fade in display-hide"  id="nopointtips">
        								<button type="button" class="close" data-dismiss="alert"></button>
										<ul class="alert-ul">
                                            <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS']?>:</strong>
                                            <li>
                                               <?php echo $LANG['UI_DATA_NODATA_TIPS']?>
                                            </li>
                                        </ul>
        							</div>

									<div class="alert alert-block alert-info fade in display-hide"  id="nosearchtips">
        								<button type="button" class="close" data-dismiss="alert"></button>
										<ul class="alert-ul">
                                            <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS']?>:</strong>
                                            <li>
                                               <?php echo $LANG['UI_DATA_NOSEARCH_TIPS']?>
                                            </li>
                                        </ul>
        							</div>
        							<ul id=cdp_backup_set_tree class="ztree ztree-fa tree-scroll"></ul>
								</div>
                			</div>
			             </div>
			         </div>
			         <div class="col-md-8">
			             <div class="portlet ">
			                <div class="portlet-title">
                				<div class="caption">
                					<i class="viconfont vicon-vmdata"></i><?php echo $LANG['UI_VOL_CDP_DATA_SET']; ?>
                					<span style="margin-left: 10px;color:#4ad1cd;font-size:14px;" id="vmdataUrl"></span>
                				</div>
                			</div>
                			
                			
							<div class="alert alert-block alert-info fade in" id="tabletips">
                                <button type="button" class="close" data-dismiss="alert"></button>
                                <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS']?></strong></h4>
                                <ol class = "alert-ol">
                                    <li>
                                    <?php echo $LANG['UI_VOL_CDP_MANAGE_BAK_DATA_TIPSONE']; ?>
                                    </li>
                                    <li>
                                     <?php echo  $LANG['UI_VOL_CDP_MANAGE_BAK_DATA_TIPSTWO']; ?>
                                    </li>
                                    <li>
                                    <?php echo $LANG['UI_VOL_CDP_MANAGE_BAK_DATA_TIPSTHREE']; ?>
                                    </li>
                                    <li>
                                    <?php echo $LANG['UI_VOL_CDP_MANAGE_BAK_DATA_TIPSFOUR'] ;?>
                                    </li>
                                </ol>
                            </div>
							
							
                			<div class="portlet-body display-none" id="backupSetTable">
                				<!-- search -->
                				<div class="tabbable tabbable-custom" style="overflow: visible;">
                					<ul class="nav nav-tabs">
                						<li class="active">
                							<a href="#tab_backup_set" data-toggle="tab" aria-expanded="true">
                							<i class="fa fa-area-chart"></i><?php echo $LANG['UI_VOL_CDP_BAK_DATA']; ?> </a>
                						</li>
                						<li class="" id="task_plot_li">
                							<a href="#tab_backup_lable_point" data-toggle="tab" aria-expanded="false">
                							<i class="viconfont vicon-storage_manager"></i> <?php echo $LANG['UI_VOL_CDP_LABEL_POINTS']; ?> </a>
                						</li>
										<!-- 
										  -- 2024-08-13 20:52:15 因后台程序复杂的，导致监控数据不准确，暂时屏蔽
                						<li class="display-none" id="tab_event_li">
                							<a href="#tab_backup_event_info" data-toggle="tab" aria-expanded="false">
                							<i class="fa fa-flag"></i> <?php echo $LANG['UI_VOL_CDP_EVENT_INFO']; ?> </a>
                						</li>
										-->
                					</ul>
                				</div>
                				<div class="tab-content " >
            						<div class="tab-pane active" id="tab_backup_set">
            							<div class="table-toolbar">
        									<div class="row">
        										<div class="col-md-8">
        										</div>
        										<div class="col-md-4">
        											 <div class="table-actions-wrapper page-right">
        												<span>
        												</span>
        												<button type="button" id="searchAll" class="btn btn-sm green-haze">
        												<i class="fa fa-search"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH']; ?>
        												</button>
        											</div>
        										</div>
        										<div class="col-md-12">
        											<div id="searchDiv" class="searchDiv display-none"><?php echo $LANG['UI_PUBLIC_SEARCH_CONDITION']; ?> <span class="searchContent"></span><span class="clearSearch"><?php echo $LANG['UI_SEARCH_CLEAR']; ?></span> </div>
        										</div>
        									</div>
        								</div>
            							<div class="table-container">
                        					<table class="table table-striped table-bordered table-hover" id="datatable">
                            					<thead>
                                					<tr role="row" class="heading">
                                						<th width="14%">
                                							<?php echo $LANG['UI_VOL_CDP_START_TIME']; ?>
                                						</th>
                                						<th width="14%">
                                							<?php echo $LANG['UI_VOL_CDP_END_TIME']; ?>
                                						</th>
                                						<th width="7%">
                                							<?php echo $LANG['UI_VOL_CDP_DATA_ENCRYPT']; ?>
                                						</th>
                                						<th width="11%">
                                							<?php echo $LANG['UI_VOL_CDP_TASK']; ?>
                                						</th>
                                						<th width="12%">
                                							<?php echo $LANG['UI_VOL_CDP_DATA_MIRROR_DATA']; ?>
                                						</th>
                                						<th width="10%">
                                							<?php echo $LANG['UI_VOL_CDP_DATA_BACKOUT_DATA']; ?>
                                						</th>
                                						<th width="7%">
                                							<?php echo $LANG['UI_PUBLIC_STATUS']; ?>
                                						</th>
                                						<th width="13%">
                                							<?php echo $LANG['UI_DATA_OF_STORAGE']; ?>
                                						</th>
                                						<th width="8%">
                                							<?php echo $LANG['UI_PUBLIC_REMARK']; ?>
                                						</th>
                                						<th width="12%">
                                							<?php echo $LANG['UI_PUBLIC_OPERATION']; ?>
                                						</th>
<!--                                 						<th width="5%"> -->
                                							<?php //echo $LANG['UI_DATA_STAR']; ?>
<!--                                 						</th> -->
                                						
                                					</tr>
                            					</thead>
                            					<tbody>
                            					</tbody>
                        					</table>
                        				</div>
                        				<div class="alert alert-block alert-info mt15 fade in" id="step1tips">
                                        	<button type="button" class="close" data-dismiss="alert"></button>
                                        	<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS']?></strong></h4>
                                            <ol class = "alert-ol">
                                                <li><?php echo $LANG['UI_VOL_CDP_MANAGE_BAK_DATA_IMAGE_DATA_SIZE_TIPS']; ?></li>
                                                <li><?php echo $LANG['UI_VOL_CDP_MANAGE_BAK_DATA_ROLLBACK_DATA_SIZE_TIPS']; ?></li>
                                            </ol>
                                		</div>
                                   		
            						</div>
            						
            						<div class="tab-pane" id="tab_backup_lable_point"> 
            							<div class="table-toolbar">
        									<div class="row">
        										<div class="col-md-8">
        										</div>
        										<div class="col-md-4">
        											 <div class="table-actions-wrapper page-right">
        												<span>
        												</span>
        												<button type="button" id="searchAllTagPoint" class="btn btn-sm green-haze">
        												<i class="fa fa-search"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH']; ?>
        												</button>
        											</div>
        										</div>
        										<div class="col-md-12">
        											<div id="tagPointSearchDiv" class="searchDiv display-none"><?php echo $LANG['UI_PUBLIC_SEARCH_CONDITION']; ?> <span class="searchContent"></span><span class="clearSearch"><?php echo $LANG['UI_SEARCH_CLEAR']; ?></span> </div>
        										</div>
        									</div>
        								</div>
            							<div class="table-container">
                        					<table class="table table-striped table-bordered table-hover" style="word-break:break-all;" id="tagPointDatatable">
                        					<thead>
                        					<tr role="row" class="heading">
                        						<th width="5%">
                        							<?php echo $LANG['UI_PUBLIC_NUMBER']; ?>
                        						</th>
                        						<th width="15%">
                        							<?php echo $LANG['UI_VOL_CDP_GENERATE_TIME']; ?>
                        						</th>
                        						<th width="15%">
                        							<?php echo $LANG['UI_VOL_CDP_TASK']; ?>
                        						</th>
                        						<th width="25%">
                        							<?php echo $LANG['UI_DATA_OF_STORAGE']; ?>
                        						</th>
                        						<th width="25%">
                        							<?php echo $LANG['UI_PUBLIC_REMARK']; ?>
                        						</th>
                        						<th width="10%">
                        							<?php echo $LANG['UI_PUBLIC_OPERATION']; ?>
                        						</th>
                        					</tr>
                        					</thead>
                        					<tbody>
                        					</tbody>
                        					</table>
                        				</div>
            						</div>
            						
            						<div class="tab-pane display-none" id="tab_backup_event_info"> 
            							<div class="table-toolbar">
        									<div class="row">
        										<div class="col-md-8">
        										</div>
        										<div class="col-md-4">
        											 <div class="table-actions-wrapper page-right">
        												<span>
        												</span>
        												<button type="button" id="delCheckedEventInfo" class="btn btn-sm green-haze">
        												<i class="viconfont vicon-ge_delete"></i> <?php echo $LANG['UI_PUBLIC_DELETE']; ?>
        												</button>
        											</div>
        										</div>
        									</div>
        								</div>
        								
            							<div class="table-container">
                        					<table class="table table-striped table-bordered table-hover" id="eventPointDatatable">
                            					<thead>
                                					<tr role="row" class="heading">
        												<th width="5%">
        													<input type="checkbox" class="group-checkable" disabled>
        												</th>
        												<th width="25%" id="timePoint">
        													 <?php echo $LANG['UI_PUBLIC_TIME']; ?>
        												</th>
        												<th width="15%">
        													<?php echo $LANG['UI_PUBLIC_TYPE']; ?>
        												</th>
        												<th width="10%">
        													<?php echo $LANG['UI_VOL_CDP_EVENT_LEVEL']; ?>
        												</th>
        												<th width="45%">
        													<?php echo $LANG['UI_VOL_CDP_EVENT_DES']; ?>
        												</th>
        											</tr>
                            					</thead>
                        					<tbody>
                        					</tbody>
                        					</table>
                        				</div>
            						</div>
            						
            						
            					</div>
                			</div>
                			
                			
			             </div>
			         </div>
			     </div>
			</div>
		</div>
		<!-- End: life time stats -->
		
		<!-- BEGIN MODAL -->
		<div id="searchmodal" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
		    <input id="vcenteruuid" class="display-none"></input>
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="fa fa-search"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH']; ?>
				</h4>
			</div>
			<div class="modal-body">
    			<div class="portlet-body">
    				
    				<!-- 时间点范围 -->
    			    <div class="list-option" id="startTimeDiv">
    					<div class="row">
    						<label class="control-label col-md-3"><?php echo $LANG['UI_DATA_TIMEPOINT_RANGE']; ?> ：
    						</label>
    						<div class="col-md-4" style="width:35%">
    						    <div class="input-group date form_datetime">
    								<input type="text" size="16" readonly id="startTime" class="form-control">
    								<span class="input-group-btn">
    									<button class="btn default" id="resetStartTime" type="button"><i class="fa fa-times"></i></button>
	    								<button class="btn default date-set" type="button"><i class="viconfont vicon-ge_calendar"></i></button>
    								</span>
    							</div>
    						</div>
    						<div class="col-md-4" style="width:35%">
    						    <div class="input-group date form_datetime">
    								<input type="text" size="16" readonly id="endTime" class="form-control">
    								<span class="input-group-btn">
    									<button class="btn default" id="resetEndTime" type="button"><i class="fa fa-times"></i></button>
	    								<button class="btn default date-set" type="button"><i class="viconfont vicon-ge_calendar"></i></button>
    								</span>
    							</div>
    						</div>
    					</div>
    				</div>
    				
    			    <div class="list-option display-none">
    					<div class="row">
							
							<!-- 是否标星 -->
    						<label class="control-label col-md-3"> <?php echo $LANG['UI_DATA_STAR']; ?>：
							</label>
							<div class="col-md-4">
								<select class="form-control select2me" id="starFlag">
									<option value="0"><?php echo $LANG['UI_PUBLIC_ALL']; ?></option>
									<option value="1"><?php echo $LANG['UI_DATA_STAR_TAGGED']; ?></option>
									<option value="2"><?php echo $LANG['UI_DATA_STAR_UNTAGGED']; ?></option>
								</select>
							</div>
    					</div>
    				</div>
    				
    				
    				
				</div>
			</div>
			
			<!-- modal-footer -->
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']; ?></button>
				<button type="button"class="btn btn-primary" id="serach_submit"><?php echo $LANG['UI_PUBLIC_YES']; ?></button>
			</div>
		</div>	
		<!-- END MODAL -->
	</div>
</div>
<!-- END PAGE CONTENT-->
	

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>

<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/volcdp/backup_data_set.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	