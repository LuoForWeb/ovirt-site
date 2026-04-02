<?php include_once '../../../tpl/permission.php';?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>

<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<!-- END PAGE LEVEL STYLES -->
<?php session_start();session_commit();?>
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
<?php echo $LANG['UI_DRILLS_CREATE_TASK']?> <small><?php echo $LANG['UI_DRILLS_CREATE_TASK_TIPS']?></small>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
		<div class="portlet box blue-hoki" id="vmrecovercontent">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-gift"></i><?php echo $LANG['UI_BACKUP_NEW_TASK']?>
				</div>
			</div>
			<div class="portlet-body form">
				<form action="#" class="form-horizontal" id="submit_form" method="POST">
					<div class="form-wizard">
						<div class="form-body">
							<ul class="nav nav-pills nav-justified steps">
								<li>
									<a href="#tab1" data-toggle="tab" class="step">
									<span class="number">
									1 </span>
									<span class="desc">
									<i class="fa fa-check"></i> <?php echo $LANG['UI_DRILLS_SELECT_PLAN_AND_VM']?> </span>
									</a>
								</li>
								<li>
									<a href="#tab2" data-toggle="tab" class="step">
									<span class="number">
									2 </span>
									<span class="desc">
									<i class="fa fa-check"></i> <?php echo $LANG['UI_DRILLS_TARGET']?> </span>
									</a>
								</li>
								<li>
									<a href="#tab3" data-toggle="tab" class="step active">
									<span class="number">
									3 </span>
									<span class="desc">
									<i class="fa fa-check"></i> <?php echo $LANG['UI_DRILLS_STRATEGY']?> </span>
									</a>
								</li>
								<li>
									<a href="#tab4" data-toggle="tab" class="step">
									<span class="number">
									4 </span>
									<span class="desc">
									<i class="fa fa-check"></i> <?php echo $LANG['UI_BACKUP_CONFIRM_CONFIG']?> </span>
									</a>
								</li>
							</ul>
							<div id="bar" class="progress progress-striped" role="progressbar">
								<div class="progress-bar progress-bar-success">
								</div>
							</div>
							<div class="tab-content">
								<div class="tab-pane active" id="tab1">
									<h4 class="block"> <?php echo $LANG['UI_DRILLS_SELECT_PLAN_VM_TIMEPOINT']?> </h4>
									<div class="row">
                                      <div class="col-md-10">
                                        <div class="form-group">
    										<label class="control-label col-md-3"><?php echo $LANG['UI_JOB_BACKUP_NODE']?> <span class="required">
    										* </span>
    										</label>
        									<div class="col-md-7">
    										    <div class="vm_tree_div ">
    										        <select class="bs-select width300" data-show-subtext="true" id="nodeselect" >
                                                    </select>
    										    </div>
    										    <div class="mt10 min-height250">
    										        <div class="two_tree">
    										          <ul id="pointtypetree" class="ztree bd1de5 tree_div"></ul>
    										        </div>
													<div class="alert alert-block alert-info fade in display-hide"  id="nopointtips">
														<button type="button" class="close" data-dismiss="alert"></button>
														<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS']?></strong></h4>
														<ol class = "alert-ol">
															<li>
																<?php echo $LANG['UI_RECOVERY_NO_VM_TITLE']?>
															</li>
															<li>
																<?php echo $LANG['UI_RECOVERY_NO_VM_TIPS']?>
															</li>
														</ol>
													</div>
    										    </div>
    										</div>
    									</div>
                                      </div>
                                    </div>
									
								</div>
								<div class="tab-pane min-height400" id="tab2">
									<div class="row">
                                      <div class="col-md-10 min-height300">
    									
                                        <div class="form-group" id="selectHost">
    										<label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_SELECT_HOST']?> <span class="required">
    										* </span>
    										</label>
    										<div class="col-md-5 tree_div2">
                								<ul id="host_tree" class="ztree bd1de5"></ul>
                								<div><span class="help-block ">
                    									<?php echo $LANG['UI_RECOVERY_VM_SELECT_HOST']?>
                								</span></div>
                							</div>
    									</div>
    									
    									<div class="form-group">
                							<label class="control-label col-md-3"><?php echo $LANG['UI_INSTANT_VM_MOUNT']?> <span class="required">
                							* </span>
                							</label>
                							<div class="col-md-5">
                								<div class="selectipdiv">
                    								<select class="form-control" id="serveripaddr">
                    								</select>
                    								<div>
                    								<span class="help-block "><?php echo $LANG['UI_INSTANT_VM_TARGET']?>
                    									<a id="diyserverip"><?php echo $LANG['UI_INSTANT_VM_TARGET_DIY']?></a></span>
                    								</div>
                								</div>
                								<div class="input-icon right mb15 display-none inputipdiv">
                									<i class="fa"></i>
                									<input type="text" maxlength="128" class="form-control"  name="backupserveraddr"/>
                									<div>
                    								<span class="help-block "><?php echo $LANG['UI_INSTANT_VM_TARGET2']?>
                    									<a id="selectserverip"><?php echo $LANG['UI_INSTANT_VM_TARGET_DIY2']?></a></span>
                    								</div>
                								</div>
                							</div>
                						</div>
						
    									<div class="form-group" id="vmconfigs">
    										<label class="control-label col-md-3"> <?php echo $LANG['UI_RECOVERY_VM_SETTING']?> <span class="required">
    										* </span>
    										</label>
    										<div class="col-md-9">
    											<div class="portlet">
                            						<div class="portlet-body">
                            							<div class="panel-group accordion" id="accordionvm">
                            							</div>
                            						</div>
                            						<div class="margintop-15">
                            						  <span class="help-block "><?php echo $LANG['UI_RECOVERY_VM_SETTING_TIPS']?></span>
                            						</div>
                            					</div>
    										</div>
    									</div>
                                      </div>
                                    </div>
								</div>
								
								<div class="tab-pane min-height400" id="tab3">
									<h4 class="block"><?php echo $LANG['UI_DRILLS_SELECT_STRATEGY']?></h4>
									<div class="alert alert-danger display-none setstrategytip">
    								</div>
    								<div class="row">
									  <div class="col-md-10">
        								<div class="form-group">
                							<label class="control-label col-md-3"><?php echo $LANG['UI_DRILLS_SELECT_MODE']?> <span class="required">
                							* </span>
                							</label>
                							<div class="col-md-4">
    											<select class="form-control select2me" id="recovertype">
    												<option value="1"><?php echo $LANG['UI_DRILLS_MANUAL_CONTROL']?></option>
<!--     												<option value="2">按时间策略演练</option> -->
    											</select>
    										</div>
                						</div>
                					  </div>
            						<div class="col-md-10">
                						<div class="form-group display-none" id="setstrategy">
                							<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_SET_STRATEGY']?> <span class="required">
                							* </span>
                							</label>
                							<div class="col-md-9">
        											<div class="portlet">
                                						<div class="portlet-body">
                                							<div class="panel-group accordion" id="accordion3">
                                								<div class="panel panel-default" id="full">
                                									<div class="panel-heading">
                                										<h4 class="panel-title">
                                										<a class="accordion-toggle accordion-toggle-styled" data-toggle="collapse" data-parent="#accordion3" href="#recover">
                                										<i class=""></i> <?php echo $LANG['UI_DRILLS_STRATEGY']?>  </a>
                                										</h4>
                                									</div>
                                									<div id="recover" class="panel-collapse in">
                                										<div class="panel-body">
                                											<?php $strategyType = "full"; require  $_SESSION['ROOTPATH'].'content/platform/public/strategy.php'; ?>
                                										</div>
                                									</div>
                                								</div>
                                							</div>
                                						</div>
                                					</div>
                                			</div>
                						</div>
                					</div>
            						<div class="col-md-10">
                						<div class="form-group display-none" id="transportmodediv">
                							<label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_HIGH_SETTING']?> <span class="required">
                							* </span>
                							</label>
                    						<div class="col-md-9">
                								<div class="tabbable-line col-md-9 bd1d ">
                								    <ul class="nav nav-tabs ">
                                        				
                                        				<li class="active">
                                        					<a href="#transfer" data-toggle="tab">
                                        					<?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY']?> </a>
                                        				</li>
                                        				
                                        			</ul>
                                        			<div class="tab-content">
                                        			    
                                        				<div class="tab-pane active" id="transfer">
                                        					<div class="form-group">
                                        						<label class="control-label col-md-3 transferlabel"> <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE']?><span class="required">
                                    						* </span></label>
                                        						<div class="col-md-4">
                                        							<select class="form-control select2me" id="transport_mode">
                                    									<option value="nbd"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD']?></option>
                                    									<option value="san"><?php echo $LANG['UI_BACKUP_TRANSPORT_SAN']?></option>
                                    								</select>
                                        						</div>
                                        					</div>
                                        				</div>
                                        				
                                        			</div>
                								</div>
                							</div>
                						</div>
                					</div>
                				</div>
            						
								</div>
								
								<div class="tab-pane" id="tab4">
									<h3 class="block"><?php echo $LANG['UI_BACKUP_CONFIRM_SETTING']?></h3>
									<div class="alert alert-danger display-none jobnametip">
    								</div>
									<div class="form-group">
										<label class="control-label col-md-3"><?php echo $LANG['UI_JOB_RNAME']?>:</label>
										<div class="col-md-4">
            								<div class="input-icon right">
            									<input type="text" maxlength="64" class="form-control" id="jobname"/>
            									<span class="help-block "><?php echo $LANG['UI_JOB_RNAME_TIPS']?></span>
            								</div>
            							</div>
									</div>
									<h4 class="form-section"><?php echo $LANG['UI_DRILLS_PLAN_VM_POINT']?></h4>
									<div class="form-group">
										<label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_POINT_INFO']?>:</label>
										<div class="col-md-9">
											<p class="form-control-static colorgreen vmtypeshow">
											</p>
										</div>
									</div>
									
									<h4 class="form-section"><?php echo $LANG['UI_DRILLS_TARGET']?></h4>
									<div class="form-group">
										<label class="control-label col-md-3"><?php echo $LANG['UI_DRILLS_TARGET']?>:</label>
										<div class="col-md-9">
											<p class="form-control-static colorgreen recovershow">
											</p>
										</div>
									</div>
									
									<h4 class="form-section"><?php echo $LANG['UI_DRILLS_STRATEGY']?></h4>
									<div class="form-group">
										<label class="control-label col-md-3"><?php echo $LANG['UI_DRILLS_MODE']?>:</label>
										<div class="col-md-4">
											<p class="form-control-static colorgreen reservetypeshow">
											</p>
										</div>
									</div>
									<div class="form-group display-none" id="transportmodeshowdiv">
										<label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY']?>:</label>
										<div class="col-md-4">
											<p class="form-control-static colorgreen transportinfoshow">
											</p>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="form-actions">
							<div class="row">
								<div class="col-md-offset-6 col-md-6">
									<a href="javascript:;" class="btn default button-previous">
									<i class="m-icon-swapleft"></i> <?php echo $LANG['UI_PUBLIC_PREV_STEP']?> </a>
									<a href="javascript:;" class="btn green-turquoise button-next next-btn-margin-left">
									 <?php echo $LANG['UI_PUBLIC_NEXT_STEP']?><i class="m-icon-swapright m-icon-white"></i>
									</a>
									<a href="javascript:;" class="btn green-haze button-submit">
									 <?php echo $LANG['UI_PUBLIC_SUBMIT']?><i class="m-icon-swapright m-icon-white"></i>
									</a>
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- END PAGE CONTENT-->
			
			
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.vmConfigOrch.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script src="./scripts/platform/manoeuvre/instant_recover.js" type="text/javascript"></script>
