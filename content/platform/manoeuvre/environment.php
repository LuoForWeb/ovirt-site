<?php include_once '../../../tpl/permission.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->

<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
<?php echo $LANG['UI_DEPLOY_DRILLS_ENVIRONMENT'] ?> <small><?php echo $LANG['UI_DRILLS_CONFIGURE'] ?></small>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
		<!-- Begin: life time stats -->
		<div class="portlet box blue-hoki" id="proxycontent">
			<div class="portlet-title">
				<div class="caption">
					<i class="iconfont icon-fuwuqidizhiduixiang"></i><?php echo $LANG['UI_DRILLS_HOST_LIST'] ?>
				</div>
			</div>
			<div class="portlet-body">
				<div class="table-toolbar-wrapper">
					<div class="table-toolbar-wrapper__left">
						<div class="btn-group ">
							<button type="button" id="addhost" class="btn table-toolbar-btn">
							<i class="viconfont vicon-ge_add_task"></i> <?php echo $LANG['UI_PUBLIC_ADD'] ?>
							</button>
						</div>
						<div class="btn-group">
							<button type="button" id="deletehost" class="btn table-toolbar-btn">
							<i class="viconfont vicon-ge_delete"></i> <?php echo $LANG['UI_PUBLIC_DELETE'] ?>
							</button>
						</div>
					</div>
				</div>
				<div class="table-container">
					<table class="table table-striped table-hover" id="datatable">
					<thead>
					<tr role="row" class="heading">
						<th width="3%">
							<input type="checkbox" class="group-checkable">
						</th>
						<th width="16%">
							<?php echo $LANG['UI_VCENTER_HOST'] ?>
						</th>
						<th width="12%">
							<?php echo $LANG['UI_PUBLIC_IP_ADDRESS'] ?>
						</th>
						<th width="10%">
							<?php echo $LANG['UI_VCENTER_TYPE'] ?>
						</th>
						<th width="15%">
							<?php echo $LANG['UI_DRILLS_NETWORK'] ?>
						</th>
						<th width="15%">
							<?php echo $LANG['UI_DRILLS_AGENT_GATEWAY'] ?>
						</th>
						<th width="8%">
							<?php echo $LANG['UI_DRILLS_HOST_SERVER'] ?>
						</th>
						<th width="8%">
							<?php echo $LANG['UI_PUBLIC_STATUS'] ?>
						</th>
					</tr>
					</thead>
					<tbody>
					</tbody>
					</table>
				</div>
			</div>
		</div>
		<!-- End: life time stats -->
		
		<!-- BEGIN MODAL -->
		<div id="modaldproxy" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
			<input id="deleteorchuuid" class="display-none"></input>
			<input id="deleteorchtype" class="display-none"></input>
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="viconfont vicon-ge_add_task"></i> <?php echo $LANG['UI_DRILLS_DEPLOY_ENVIRONMENT'] ?></h4>
			</div>
			<div class="modal-body">
				<div class="portlet-body">
					<div class="row">
						<div class="col-md-4 col-sm-4 col-xs-4">
							<ul class="nav nav-tabs tabs-left" id="steps" >
								<li class="active " >
									<a href="javascript:;" class="stepvm" data-toggle="tab">
									<?php echo $LANG['UI_DRILLS_ADD_HOST_TIPS1'] ?> </a>
								</li>
								<li>
									<a href="javascript:;" class="stepvm" data-toggle="tab">
									<?php echo $LANG['UI_DRILLS_ADD_HOST_TIPS2'] ?> </a>
								</li>
								<li>
									<a href="javascript:;" class="stepvm" data-toggle="tab">
									<?php echo $LANG['UI_DRILLS_ADD_HOST_TIPS3'] ?> </a>
								</li>
								<li id="masterli">
									<a href="javascript:;" class="stepvm" data-toggle="tab">
									<?php echo $LANG['UI_DRILLS_ADD_HOST_TIPS4'] ?> </a>
								</li>
							</ul>
							<div class="">
								<img id="orchlegend" src="../../img/platform/orch/orchlegend.svg" alt="">
							</div>
						</div>
						<div class="col-md-8 col-sm-8 col-xs-8">
							<div class="tab-content " id="stepscontent">
								<div class="tab-pane contentpane active">
									<div class="form-group col-md-12">
										<div class="form-group mt10">
											<label class="col-md-3 control-label"><?php echo $LANG['UI_DRILLS_HOST_TYPE'] ?> </a><span class="required"> * </span></label>
											<div class="col-md-8">
												<div class="input-icon right">
													<select class="form-control select2me" name="hosttype">
													   <option value="1"><?php echo $LANG['UI_DRILLS_HOST_SERVER'] ?></option>
													   <option value="2"><?php echo $LANG['UI_DRILLS_CLIENT_SERVER'] ?></option>
													</select>
													<span class="help-block">
													<?php echo $LANG['UI_DRILLS_HOST_TYPE_TIPS'] ?> </span>
												</div>
											</div>
										</div>
										<div class="form-group mt10 display-none" id="masterhostdiv">
											<label class="col-md-3 control-label"><?php echo $LANG['UI_DRILLS_HOST_SERVER'] ?><span class="required"> * </span></label>
											<div class="col-md-8">
												<div class="input-icon right">
													<select class="form-control select2me" name="primaryhost">
													</select>
													<span class="help-block">
													<?php echo $LANG['UI_DRILLS_CLIENT_TYPE_TIPS'] ?> </span>
												</div>
											</div>
										</div>
										<div class="alert alert-block alert-info fade in">
											<button type="button" class="close" data-dismiss="alert"></button>
											<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
											<ol class = "alert-ol">
												<li>
													<?php echo $LANG['UI_DRILLS_SELECT_HOST_TYPE'] ?>
												</li>
												<li>
													<?php echo $LANG['UI_DRILLS_SELECT_HOST_TYPE_TIPS1'] ?>
												</li>
												<li>
													<?php echo $LANG['UI_DRILLS_SELECT_HOST_TYPE_TIPS2'] ?>
												</li>
												<li>
													<?php echo $LANG['UI_DRILLS_SELECT_HOST_TYPE_TIPS3'] ?>
												</li>
											</ol>
										</div>
									</div>
								</div>
								<div class="tab-pane contentpane">
									<div class="form-group col-md-12">
										<div class="mt10 mb15 min-height250">
											<div class="two_tree">
											  <ul id="hosttree" class="ztree bd1de5 tree_div"></ul>
											</div>
										</div>
										<div class="alert alert-block alert-info fade in">
											<button type="button" class="close" data-dismiss="alert"></button>
											<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
											<ol class = "alert-ol">
												<li>
													<?php echo $LANG['UI_DRILLS_SELECT_EMERGENCY_HOST'] ?>
												</li>
												<li>
													<?php echo $LANG['UI_DRILLS_SELECT_EMERGENCY_HOST_TIPS1'] ?>
												</li>
												<li>
													<?php echo $LANG['UI_DRILLS_SELECT_EMERGENCY_HOST_TIPS2'] ?>
												</li>
											</ol>
										</div>
									</div>
								</div>
								<div class="tab-pane contentpane fade ">
									<div class="form-group col-md-12 ">
										<div class="panel-group accordion">
											<form action="#" class="form-horizontal vmconfigdiv">
												 <div class="form-body">
													<div class="alert alert-block alert-info fade in">
														<button type="button" class="close" data-dismiss="alert"></button>
														<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
														<ol class = "alert-ol">
															<li>
																<?php echo $LANG['UI_DRILLS_CONFIGURE_AGENT_GATEWAY'] ?>
															</li>
															<li>
																<?php echo $LANG['UI_DRILLS_CONFIGURE_AGENT_GATEWAY_TIPS1'] ?>
															</li>
															<li>
																<?php echo $LANG['UI_DRILLS_CONFIGURE_AGENT_GATEWAY_TIPS2'] ?>
															</li>
															<li>
																<?php echo $LANG['UI_DRILLS_CONFIGURE_AGENT_GATEWAY_TIPS3'] ?>
															</li>
														</ol>
													</div>
													<div class="form-group">
														<label class="col-md-3 control-label"><?php echo $LANG['UI_RECOVERY_STORAGE_NAME'] ?><span class="required"> * </span></label>
														<div class="col-md-8">
															<div class="input-icon right">
																<i class="fa"></i>
																<input style="display:none"><!-- for disable autocomplete on chrome -->
																<input type="text" maxlength="128" class="form-control" name="vmname"/>
																<span class="help-block">
																<?php echo $LANG['UI_DRILLS_AGENT_GATEWAY_NAME'] ?> </span>
															</div>
														</div>
													</div>
													<div class="form-group">
														<label class="col-md-3 control-label"><?php echo $LANG['UI_DRILLS_STORAGE'] ?><span class="required"> * </span></label>
														<div class="col-md-8">
															<div class="input-icon right">
																<select class="form-control select2me" name="vmstorage"></select>
																<span class="help-block">
																<?php echo $LANG['UI_DRILLS_STORAGE_TIPS'] ?> </span>
															</div>
														</div>
													</div>
													<div class="form-group">
														<label class="col-md-3 control-label"><?php echo $LANG['UI_NETWORK'] ?><span class="required"> * </span></label>
														<div class="col-md-8">
															<div class="input-icon right">
																<select class="form-control select2me" name="vmnetwork"></select>
																<span class="help-block">
																<?php echo $LANG['UI_DRILLS_NETWORK_TIPS'] ?> </span>
															</div>
														</div>
													</div>
													<div class="form-group">
														<label class="col-md-3 control-label"><?php echo $LANG['UI_DRILLS_INPUT_AGENT_GATAWAY_IP'] ?><span class="required"> * </span></label>
														<div class="col-md-8">
															<div class="input-icon right">
																<i class="fa"></i>
																<input style="display:none"><!-- for disable autocomplete on chrome -->
																<input type="text" maxlength="128" placeholder="192.168.3.163" class="form-control" name="vmip"/>
																<span class="help-block">
																<?php echo $LANG['UI_DRILLS_INPUT_AGENT_GATAWAY_IP_TIPS'] ?> </span>
															</div>
														</div>
													</div>
													<div class="form-group">
														<label class="col-md-3 control-label"><?php echo $LANG['UI_SETTINGS_NETMASK'] ?><span class="required"> * </span></label>
														<div class="col-md-8">
															<div class="input-icon right">
																<select class="form-control select2me" name="vmnetmask">
																	<option value=""></option>
																	<option value="255.255.255.255">  255.255.255.255 </option>
																	<option value="255.255.255.254">  255.255.255.254 </option>
																	<option value="255.255.255.252">  255.255.255.252 </option>
																	<option value="255.255.255.248">  255.255.255.248 </option>
																	<option value="255.255.255.240">  255.255.255.240 </option>
																	<option value="255.255.255.224">  255.255.255.224 </option>
																	<option value="255.255.255.192">  255.255.255.192 </option>
																	<option value="255.255.255.128">  255.255.255.128 </option>
																	<option value="255.255.255.0">  255.255.255.0   </option>
																	<option value="255.255.254.0">  255.255.254.0 </option>
																	<option value="255.255.252.0">  255.255.252.0 </option>
																	<option value="255.255.248.0">  255.255.248.0 </option>
																	<option value="255.255.240.0">  255.255.240.0 </option>
																	<option value="255.255.224.0">  255.255.224.0   </option>
																	<option value="255.255.192.0">  255.255.192.0</option>
																	<option value="255.255.128.0">  255.255.128.0   </option>
																	<option value="255.255.0.0">  255.255.0.0     </option>
																	<option value="255.254.0.0">  255.254.0.0     </option>
																	<option value="255.252.0.0">  255.252.0.0     </option>
																	<option value="255.248.0.0">  255.248.0.0     </option>
																	<option value="255.240.0.0">  255.240.0.0     </option>
																	<option value="255.224.0.0">  255.224.0.0     </option>
																	<option value="255.192.0.0">  255.192.0.0</option>
																	<option value="255.128.0.0">  255.128.0.0     </option>
																	<option value="255.0.0.0">  255.0.0.0       </option>
																	<option value="254.0.0.0">  254.0.0.0       </option>
																	<option value="252.0.0.0">  252.0.0.0       </option>
																	<option value="248.0.0.0">  248.0.0.0       </option>
																	<option value="240.0.0.0">  240.0.0.0       </option>
																	<option value="224.0.0.0">  224.0.0.0       </option>
																	<option value="192.0.0.0">  192.0.0.0       </option>
																	<option value="128.0.0.0">  128.0.0.0       </option>
																	<option value="0.0.0.0">  0.0.0.0 </option>
																</select>
																<span class="help-block">
																<?php echo $LANG['UI_SETTINGS_NETMASK'] ?> </span>
															</div>
														</div>
														
													</div>
													<div class="form-group">
														<label class="col-md-3 control-label"><?php echo $LANG['UI_DRILLS_GATEWAY'] ?></label>
														<div class="col-md-8">
															<div class="input-icon right">
																<i class="fa"></i>
																<input style="display:none"><!-- for disable autocomplete on chrome -->
																<input type="text" maxlength="128" placeholder="192.168.3.1" class="form-control" name="vmgateway"/>
																<span class="help-block">
																<?php echo $LANG['UI_DRILLS_GATEWAY_TIPS'] ?> </span>
															</div>
														</div>
													</div>
													
													<hr>
													<div class="alert alert-block alert-info fade in priserver">
														<button type="button" class="close" data-dismiss="alert"></button>
														<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
														<ol class = "alert-ol">
															<li>
																<?php echo $LANG['UI_DRILLS_CONFIGURE_BACKUP_SERVER'] ?>
															</li>
															<li>
																<?php echo $LANG['UI_DRILLS_CONFIGURE_BACKUP_SERVER_TIPS'] ?>
															</li>
														</ol>
													</div>
													<div class="form-group priserver">
														<label class="col-md-3 control-label"><?php echo $LANG['UI_DRILLS_BACKUP_SERVER_NETWORK_CARD'] ?><span class="required"> * </span></label>
														<div class="col-md-8">
															<div class="input-icon right">
																<select class="form-control select2me" name="networkcard"></select>
																<span class="help-block">
																<?php echo $LANG['UI_DRILLS_BACKUP_SERVER_NETWORK_CARD_TIPS'] ?></span>
															</div>
														</div>
													</div>
													<div class="form-group priserver">
														<label class="col-md-3 control-label"><?php echo $LANG['UI_DRILLS_BACKUP_SERVER_VIP'] ?><span class="required"> * </span></label>
														<div class="col-md-8">
															<div class="input-icon right">
																<i class="fa"></i>
																<input style="display:none"><!-- for disable autocomplete on chrome -->
																<input type="text" maxlength="128" placeholder="192.168.3.108" class="form-control" name="serverip"/>
																<span class="help-block">
																<?php echo $LANG['UI_DRILLS_BACKUP_SERVER_VIP_TIPS'] ?> </span>
															</div>
														</div>
													</div>
													<div class="form-group priserver">
														<label class="col-md-3 control-label"><?php echo $LANG['UI_SETTINGS_NETMASK'] ?><span class="required"> * </span></label>
														<div class="col-md-8">
															<div class="input-icon right">
																<select class="form-control select2me"  name="servernetmask">
																	<option value=""></option>
																	<option value="255.255.255.255">  255.255.255.255 </option>
																	<option value="255.255.255.254">  255.255.255.254 </option>
																	<option value="255.255.255.252">  255.255.255.252 </option>
																	<option value="255.255.255.248">  255.255.255.248 </option>
																	<option value="255.255.255.240">  255.255.255.240 </option>
																	<option value="255.255.255.224">  255.255.255.224 </option>
																	<option value="255.255.255.192">  255.255.255.192 </option>
																	<option value="255.255.255.128">  255.255.255.128 </option>
																	<option value="255.255.255.0">  255.255.255.0   </option>
																	<option value="255.255.254.0">  255.255.254.0 </option>
																	<option value="255.255.252.0">  255.255.252.0 </option>
																	<option value="255.255.248.0">  255.255.248.0 </option>
																	<option value="255.255.240.0">  255.255.240.0 </option>
																	<option value="255.255.224.0">  255.255.224.0   </option>
																	<option value="255.255.192.0">  255.255.192.0</option>
																	<option value="255.255.128.0">  255.255.128.0   </option>
																	<option value="255.255.0.0">  255.255.0.0     </option>
																	<option value="255.254.0.0">  255.254.0.0     </option>
																	<option value="255.252.0.0">  255.252.0.0     </option>
																	<option value="255.248.0.0">  255.248.0.0     </option>
																	<option value="255.240.0.0">  255.240.0.0     </option>
																	<option value="255.224.0.0">  255.224.0.0     </option>
																	<option value="255.192.0.0">  255.192.0.0</option>
																	<option value="255.128.0.0">  255.128.0.0     </option>
																	<option value="255.0.0.0">  255.0.0.0       </option>
																	<option value="254.0.0.0">  254.0.0.0       </option>
																	<option value="252.0.0.0">  252.0.0.0       </option>
																	<option value="248.0.0.0">  248.0.0.0       </option>
																	<option value="240.0.0.0">  240.0.0.0       </option>
																	<option value="224.0.0.0">  224.0.0.0       </option>
																	<option value="192.0.0.0">  192.0.0.0       </option>
																	<option value="128.0.0.0">  128.0.0.0       </option>
																	<option value="0.0.0.0">  0.0.0.0 </option>
																</select>
																<span class="help-block">
																<?php echo $LANG['UI_DRILLS_SELECT_BACKUP_SERVER_NETMASK'] ?></span>
															</div>
														</div>
													</div>
													<div class="form-group priserver">
														<label class="col-md-3 control-label"><?php echo $LANG['UI_DRILLS_GATEWAY'] ?></label>
														<div class="col-md-8">
															<div class="input-icon right">
																<i class="fa"></i>
																<input style="display:none"><!-- for disable autocomplete on chrome -->
																<input type="text" maxlength="128" placeholder="192.168.3.1" class="form-control" name="servergateway"/>
																<span class="help-block">
																<?php echo $LANG['UI_DRILLS_INPUT_BACKUP_SERVER_GATEWAY'] ?> </span>
															</div>
														</div>
													</div>
												</div>
											</form>
										</div>
									</div>
								</div>
								<div class="tab-pane contentpane fade">
									<div class="form-group col-md-12 ">
										<div class="alert alert-block alert-info fade in">
											<button type="button" class="close" data-dismiss="alert"></button>
											<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
											<ol class = "alert-ol">
												<li>
													<?php echo $LANG['UI_DRILLS_WORK_TIPS1'] ?>
												</li>
												<li>
													<?php echo $LANG['UI_DRILLS_WORK_TIPS2'] ?>
												</li>
												<li>
													<?php echo $LANG['UI_DRILLS_WORK_TIPS3'] ?>
												</li>
											</ol>
										</div>
									</div>
									<div class="form-group col-md-12 ">
										<div class="panel-group accordion">
											<form action="#" class="form-horizontal networkconfig" >
												 <div class="form-body">
													<table class="table table-bordered table-striped table-condensed flip-content  tdvalignm">
														<thead class="flip-content">
														<tr>
															<th width="10%">
																 <?php echo $LANG['UI_NETWORK'] ?>
															</th>
															<th width="20%">
																 <?php echo $LANG['UI_DRILLS_OPTION'] ?>
															</th>
															<th>
																<?php echo $LANG['UI_DRILLS_PRODUCT_NETWORK'] ?>
															</th>
															<th>
																<?php echo $LANG['UI_DRILLS_ISOLATED_NETWORK'] ?>
															</th>
														</tr>
														</thead>
														<tbody id="segmenttbody">
														<tr class="first">
															<td rowspan="3">
															 #1
															</td>
															<td>
																<?php echo $LANG['UI_DRILLS_NETWORK_SEGMENT'] ?>
															</td>
															<td>
																<div class="mycheck">
																	<div class="input-icon right">
																		<i class="fa"></i>
																		<input type="text" maxlength="128" placeholder="192.168.1.0" class="form-control required ipv4 autocheck oldsegment" name="oldsegment1"/>
																	</div>
																</div>
															</td>
															<td>
																<div class="mycheck">
																	<div class="input-icon right">
																		<i class="fa"></i>
																		<input type="text" maxlength="128" placeholder="192.168.2.0" class="form-control required ipv4 autocheck newsegment" name="newsegment1"/>
																	</div>
																</div>
															</td>
														</tr>
														<tr>
															<td>
																<?php echo $LANG['UI_SETTINGS_NETMASK'] ?>
															</td>
															<td>
																<div class="mycheck">
																	<div class="input-icon right">
																		<i class="fa"></i>
																		<input type="text" maxlength="128" placeholder="255.255.255.0" class="form-control required  netmask autocheck oldnetmask" name="oldnetmask1"/>
																	</div>
																</div>
															</td>
															<td>
																<div class="mycheck">
																	<div class="input-icon right">
																		<i class="fa"></i>
																		<input type="text" maxlength="128" placeholder="<?php echo $LANG['UI_DRILLS_AUTOMATIC_GENERATE'] ?>" class="form-control required  netmask newnetmask" disabled name="newnetmask1"/>
																	</div>
																</div>
															</td>
														</tr>
														<tr>
															<td>
																 <?php echo $LANG['UI_SETTINGS_GATEWAY'] ?>
															</td>
															<td>
																<div class="mycheck">
																	<div class="input-icon right">
																		<i class="fa"></i>
																		<input type="text" maxlength="128" placeholder="192.168.1.1" class="form-control required ipv4 autocheck oldgateway" name="oldgateway1"/>
																	</div>
																</div>
															</td>
															<td>
																<div class="mycheck">
																	<div class="input-icon right">
																		<i class="fa"></i>
																		<input type="text" maxlength="128" placeholder="<?php echo $LANG['UI_DRILLS_AUTOMATIC_GENERATE'] ?>" class="form-control required ipv4 newgateway" disabled name="newgateway1"/>
																	</div>
																</div>
															</td>
														</tr>
														</tbody>
													</table>
													
												</div>
											</form>
											<div class="textalignr">
												<span class="help-block"><?php echo $LANG['UI_DRILLS_ADD_NETWORK_TIPS1'] ?><a id="addsegment"><?php echo $LANG['UI_DRILLS_ADD_NETWORK_TIPS2'] ?></a></span>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default" id="cancel"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
				<button type="button" class="btn btn-default display-none" id="prevstep"><?php echo $LANG['UI_PUBLIC_PREV_STEP'] ?></button>
				<button type="button" class="btn btn-default" id="nextstep"><?php echo $LANG['UI_PUBLIC_NEXT_STEP'] ?></button>
				<button type="button" class="btn btn-primary display-none" id="submitaddvm"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
			</div>
		</div>	
		<!-- END MODAL -->
		
	</div>
</div>
<!-- END PAGE CONTENT-->
	

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<?php
if ($_SESSION['language'] != "en-us") {
	//如果不是英文,加载语言包
	echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
		$_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/platform/manoeuvre/environment.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	