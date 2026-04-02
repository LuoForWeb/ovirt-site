<?php include_once '../../tpl/permission.php';?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>

<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link rel="stylesheet" type="text/css" href="./css/fs/filetype-small.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<link rel="stylesheet" type="text/css" href="./scripts/plugins/popModal/popModal.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/daterangepicker/daterangepicker.css"/>
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
		<!-- Begin: life time stats -->
		<div class="portlet box blue-hoki">
			<div class="portlet-title">
				<div class="caption">
					<i class="levelchild viconfont vicon-vmdata"></i><?php echo $LANG['UI_NAS_BAK_DATA']?>
				</div>
			</div>
			<div class="portlet-body mlr10">
			     <div class="row">
			         <div class="col-md-4">
			             <div class="portlet" id="timepointdiv">
			                <div class="portlet-title">
                				<div class="caption">
                					<i class="viconfont vicon-ge_time_point"></i><?php echo $LANG['UI_NAS_BACKUP_POINT']?>
                				</div>
                			</div>
                			<div class="portlet-body batchDeleteDiv">
								<div class="btn-group">
								    <button type="button" id="allDelete" class="btn btn-sm green-haze display-none" style="margin-top: 3px;">
								        <i class="viconfont vicon-ge_delete"></i> <?php echo $LANG['UI_PUBLIC_DELETE']?>
								    </button>
							    </div>
                    		    
								<div id="nodeCheck" style="margin-top:10px;margin-bottom:10px;">
                    			    <select class="bs-select width50p dataBorder" style="height:34px;padding-left: 12px" data-show-subtext="true" id="storageSelect" >
                                    </select>
                                    <div class="input-icon right width45p" style="float: right;">
                    				    <input style="display:none" type="text" maxlength="128" placeholder="<?php echo $LANG['UI_COPY_SEARCH_AS_KEYWORD']?>" class="form-control " />
										<button type="button" id="filterSearch" class="btn btn-sm green-haze" style="margin-top: 3px;">
								        	<i class="viconfont vicon-ge_filter"></i> <?php echo $LANG['UI_DATA_SCREEN']?>
								   		</button>                  		        
                    		        </div>
                    		        	                   	
                                </div>
                			    <div class="vcenter-tree">
									<p id="markStr"></p>
        							<div class="alert alert-block alert-info fade in display-hide" id="nopointtips">
									    <button type="button" class="close" data-dismiss="alert"></button>
										<ul class="alert-ul">
											<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS']?>:</strong>
											<li>
												<?php echo $LANG['UI_DATA_NODATA_TIPS']?>
											</li>
										</ul>
									</div>
									<div class="alert alert-block alert-info fade in display-hide" id="nosearchtips">
									    <button type="button" class="close" data-dismiss="alert"></button>
										<ul class="alert-ul">
											<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS']?>:</strong>
											<li>
												<?php echo $LANG['UI_DATA_NOSEARCH_TIPS']?>
											</li>
										</ul>
									</div>
        							<ul id="filetimepointtree" class="ztree"></ul>
								</div>
                			</div>
							<!-- BEGIN MODAL -->
							<div id="searchmodal" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
							    <input id="vcenteruuid" class="display-none"></input>
								<div class="modal-header ">
									<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
									<h4 class="modal-title"><i class="fa fa-search"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH']?>
									</h4>
								</div>
								<div class="modal-body">
    								<div class="portlet-body">
					
    									<!-- 时间点范围 -->
    								    <div class="list-option" id="startTimeDiv">
    										<div class="row">
    											<label class="control-label col-md-4"><?php echo $LANG['UI_DATA_TIMEPOINT_RANGE']?> ：
    											</label>
    											<div class="col-md-6" >
    											    <div class="daterangepickerdiv">
        					                            <input type="text" id="daterangepicker" class="form-control" autocomplete="off">
        					                            <i class="viconfont vicon-ge_calendar"></i>
        					                   	 	</div>
    											</div>
    										</div>
    									</div>
					
    								    <div class="list-option">
    										<div class="row">
    											<!-- 时间点类型 -->
    											<label class="control-label col-md-4"><?php echo $LANG['UI_PUBLIC_TYPE']?> ：
												</label>
												<div class="col-md-4">
													<select class="form-control select2me" id="timepointType">
														<option value="0"><?php echo $LANG['UI_PUBLIC_ALL']?></option>
														<option value="1"><?php echo $LANG['UI_BACKUP_FULL']?></option>
														<option value="2"><?php echo $LANG['UI_BACKUP_INCREMENT']?></option>
														<option value="3"><?php echo $LANG['UI_BACKUP_DIFFERENCE']?></option>
													</select>
												</div>
												
    										</div>
    									</div>
					
					
    									  <div class="list-option">
    										<div class="row">
    											<!-- 时间点类型 -->
    											<label class="control-label col-md-4"><?php echo $LANG['UI_BACKUP_MARKING']?> ：
												</label>
												<div class="col-md-4">
													<label style="padding-right: 20px;">
        					    						<input type="checkbox" name="foreverCheck1" class="icheck">
        					    						<i class="viconfont vicon-remark-forever"></i>
        					    						<?php echo $LANG['WEB_VM_GFS_FOREVER_POINT']?>
        											</label>
												</div>
											</div>
    									</div>
					
									</div>
								</div>
								
								<!-- modal-footer -->
								<div class="modal-footer">
									<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
									<button type="button"class="btn btn-primary" id="serach_submit"><?php echo $LANG['UI_PUBLIC_YES']?></button>
								</div>
							</div>	
							<!-- END MODAL -->
							<!-- BEGIN MODAL -->
							<div id="setAllMark" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
								<input id="Marktimepoint_uuid" class="display-none"></input>
								<input id="MarktimepointNodeUUID" class="display-none"></input>
								<input id="Marksubmode_type" class="display-none"></input>
								<div class="modal-header ">
									<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
									<h4 class="modal-title"><i class="viconfont vicon-ge_sign"></i> <?php echo $LANG['WEB_PT_OP_GFS_FLAG']?>
									</h4>
								</div>
								<div class="modal-body">
    								<div class="portlet-body">
    									<div style="margin-top: 10px;">
    										<div class="row">
    											<div class="col-md-2"></div>
        										<div class="col-md-10">
        											<label style="padding-right: 20px;">
        					    						<input type="checkbox" name="foreverCheck" class="icheck">
        					    						<i class="viconfont vicon-remark-forever"></i>
        					    						<?php echo $LANG['WEB_VM_GFS_FOREVER_POINT']?>
        											</label>
    											</div>
    										</div>
    									</div>
					
					
					
									</div>
								</div>
								
								<!-- modal-footer -->
								<div class="modal-footer">
									<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
									<button type="button"class="btn btn-primary" id="mark_submit"><?php echo $LANG['UI_PUBLIC_YES']?></button>
								</div>
							</div>	
							<!-- END MODAL -->
							<!-- popover stard -->
							<!-- PopoverX content -->
							<div id="filter-content" class="display-none">
        						<div class="modal-body">
            						<div class="portlet-body">
            							<div style="margin-top: 10px;">
            								<div class="row">
            				    				<div class="col-md-12">
            				    					<label style="padding-right: 20px;">
            				    						<input type="checkbox" name="foreverFilter" class="icheck">
            				    						<i class="viconfont vicon-remark-forever"></i>
            				    						<?php echo $LANG['WEB_VM_GFS_FOREVER_POINT']?>
            				    					</label>
            									</div>
            								</div>
            							</div>
            							<div style="margin-top: 10px;">
            								<input type="text" maxlength="128" placeholder="<?php echo $LANG['UI_COPY_SEARCH_AS_KEYWORD']?>" class="form-control " id="searchfs">
        								</div>
        							</div>
        						</div>
            				    <div class="popModal_footer">
                                    <button type="button" data-popModalBut="cancel" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_CANCEL']?></button>
            				    	<button type="button" data-popModalBut="ok"  class="btn btn-primary"><?php echo $LANG['UI_PUBLIC_CONFIRM']?></button>
            				    </div>
            				 </div>
							<!-- popover end -->
			             </div>
			         </div>
			         <div class="col-md-8">
			             <div class="portlet ">
			                <div class="portlet-title">
                				<div class="caption">
                					<i class="viconfont vicon-vmcopy"></i><?php echo $LANG['UI_MICROSOFT365_POINT']?>
                					<span style="margin-left: 10px;color:#4ad1cd;font-size:14px;" id="fsdataUrl"></span>
                				</div>
                			</div>
							<div class="alert alert-block alert-info fade in" id="tabletips">
								<button type="button" class="close" data-dismiss="alert"></button>
								<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS']?></strong></h4>
								<ol class = "alert-ol">
									<li><?php echo $LANG['UI_NAS_TIMEPOINT_MANAGER_ONE']?></li>
									<li><?php echo $LANG['UI_NAS_TIMEPOINT_MANAGER_TWO']?></li>
									<li><?php echo $LANG['UI_DATA_VM_TIPS3']?></li>
									<li><?php echo $LANG['UI_DATA_VM_TIPS4']?></li>
								</ol>
							</div>
                			<div class="portlet-body display-none" id="nastablediv">
								<!-- search -->
								<div class="table-toolbar">
									<div class="row">
										<div class="col-md-8">
										</div>
										<div class="col-md-4">
											 <div class="table-actions-wrapper page-right">
												<span>
												</span>
												<button type="button" id="searchAll" class="btn btn-sm green-haze">
												<i class="fa fa-search"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH']?>
												</button>
											</div>
										</div>
										<div class="col-md-12">
											<div id="searchDiv" class="searchDiv display-none"><?php echo $LANG['UI_PUBLIC_SEARCH_CONDITION']?> <span class="searchContent"></span><span class="clearSearch"><?php echo $LANG['UI_SEARCH_CLEAR']?></span> </div>
										</div>
									</div>
								</div>
                			    <div class="table-container">
                					<table class="table table-striped table-bordered table-hover lh2" id="datatable">
                						<thead>
                							<tr role="row" class="heading">
                								<th width="15%">
                									<?php echo $LANG['UI_DATA_TIMEPOINT']?>
                								</th>
                								<th width="10%">
                									<?php echo $LANG['UI_PUBLIC_TYPE']?>
                								</th>
                								<th width="10%">
                									<?php echo $LANG['UI_DATA_SIZE']?>
                								</th>
                								<th width="10%">
                									<?php echo $LANG['UI_JOB_REAL_SIZE']?>
                								</th>
                								<th width="20%">
                									<?php echo $LANG['UI_DATA_OF_STORAGE']?>
                								</th>
                                                <th width="10%">
                                                    <?php echo $LANG['UI_CLIENT_OWNER']?>
                                                </th>
                								<th width="10%">
                									<?php echo $LANG['UI_PUBLIC_OPERATION']?>
                								</th>
                							</tr>
                						</thead>
                						<tbody></tbody>
                					</table>
                				</div>
                			</div>
                			<div class="alert alert-block alert-info fade in display-none mt15" id="marktips">
                                <button type="button" class="close" data-dismiss="alert"></button>
                                <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS']?></strong></h4>
                                <ol class = "alert-ol">
                                    <li>
                                        <?php echo $LANG['WEB_OS_HOST_TIME_TIPS1']?>
                                    </li>
                                    <li>
                                        <?php echo $LANG['WEB_OS_HOST_TIME_TIPS2']?>
                                    </li>
                                </ol>
                            </div>
			             </div>
			         </div>
			     </div>
			</div>
		</div>
		<!-- End: life time stats -->
	</div>
</div>
<!-- END PAGE CONTENT-->
	

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/plugins/popModal/popModal.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<script type="text/javascript" src="./scripts/nas/nasdata.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	