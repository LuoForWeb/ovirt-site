<?php include_once '../../../tpl/permission.php';?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<!--<h3 class="page-title">
<?php echo $LANG['UI_STORAGE_ADD']?><small> <?php echo $LANG['UI_STORAGE_ADD_TIPS']?></small>
</h3>-->
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row" style="height:100%;">
	<div class="col-md-12" style="height:100%;">
		 <!-- BEGIN VALIDATION STATES-->
		<div class="portlet box blue-hoki" id="addcontent" style="height: 100%;margin-bottom:0;">
			<div class="portlet-title" style="height: 5%;max-height: 40px;">
				<div class="caption">
					<i class="fa fa-edit"></i><?php echo $LANG['UI_STORAGE_ADD_IMPUT_INFO']?>
				</div>
			</div>
			<div class="portlet-body form" style="height:95%;">
				<!-- BEGIN FORM-->
				<div class="form-horizontal" style="height:100%;">
					<div class="form-body" style="height: 90%; overflow-x:hidden; overflow-y:auto;">
					    <form id="addnodeform">
						<div class="form-group pt50">
							<label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_TYPE']?><span class="required">
							* </span>
							</label>
							<div class="col-md-4">
								<select class="form-control select2me" name="storagetype">
								     	<option value="0"></option>
    									<option value="11"><?php echo $LANG['UI_STORAGE_TYPE11']?></option>
    									<option value="8"><?php echo $LANG['UI_COPY_ALLOPATRIC_BACKUP_SYSTEM']?></option>
                						<option value="9"><?php echo $LANG['UI_STORAGE_TYPE9']?></option>
                				</select>
                				<div><span class="help-block ">
                					<?php echo $LANG['UI_STORAGE_TYPE_SELECT_TIPS']?>
                				</span></div>
							</div>
						</div>
						<div class="form-group display-none" id="nodeDiv">
							<label class="control-label col-md-3"><?php echo $LANG['UI_NODE_IPADDR']?> <span class="required">
							* </span>
							</label>
							<div class="col-md-4">
								<select class="form-control select2me" name="nodeselect">
                				</select>
                				<div><span class="help-block ">
                					<?php echo $LANG['UI_STORAGE_ADD_NODE_TIPS']?>
                				</span></div>
							</div>
						</div>
						</form>
						
						<div class="form-group display-none wwndiv">
							<label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_FIBER_CHANNEL']?>
							</label>
							<div class="col-md-8">
								<div class="table-container">
                					<table class="table table-striped table-bordered table-hover" id="wwntable">
                					<thead>
                					<tr role="row" class="heading">
                						<th width="8%">
                							<?php echo $LANG['UI_PUBLIC_NUMBER']?>
                						</th>
                						<th width="15%">
                							<?php echo $LANG['UI_STORAGE_CHANNEL']?>
                						</th>
                						<th width="30%">
                							<?php echo $LANG['UI_STORAGE_FC_WWNN']?>
                						</th>
                						<th width="30%">
                							<?php echo $LANG['UI_STORAGE_FC_WWPN']?>
                						</th>
                						<th width="10%">
                							<?php echo $LANG['UI_STORAGE_FC_SPEED']?>
                						</th>
                						<th width="10%">
                							<?php echo $LANG['UI_PUBLIC_STATUS']?>
                						</th>
                					</tr>
                					</thead>
                					<tbody>
                					</tbody>
                					</table>
                				</div>
								<div><span class="help-block ">
                					<?php echo $LANG['UI_STORAGE_FC_TIPS']?>
                				</span></div>
							</div>
						</div>
						
						<!-- 1-4 STORAGE DIV START -->
						<div id="morestoragediv" class="display-none asdiv">
						  <form>
    						<div class="form-group">
    							<label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_SELECT_RES']?><span class="required">
    							* </span>
    							</label>
    							<div class="col-md-8">
									<div class="table-container">
                    					<table class="table table-striped table-bordered table-hover" id="resourcetable">
                    					<thead>
                    					<tr role="row" class="heading">
                    						<th width="10%">
                    							<input type="checkbox" class="group-checkable disabled" disabled>
                    						</th>
                    						<th width="50%">
                    							 <?php echo $LANG['UI_RECOVERY_STORAGE_NAME']?>
                    						</th>
                    						<th width="20%">
                    							 <?php echo $LANG['UI_PUBLIC_TYPE']?>
                    						</th>
                    						<th width="20%">
                    							 <?php echo $LANG['UI_PUBLIC_CAPACITY']?>
                    						</th>
                    					</tr>
                    					</thead>
                    					<tbody>
                    					</tbody>
                    					</table>
                    					<div id="selecttips"><span class="help-block ">
                        					<?php echo $LANG['UI_STORAGE_ISCSI_SELECT_TARGET']?>
                        				</span></div>
                    				</div>
								</div>
    						</div>
    				      </form>
						</div>
						<!-- 1-4 STORAGE DIV END -->
						
						<!-- ISCSI DIV START -->
						<form id="iscsidiv"  class="display-none asdiv">
    						<div class="form-group">
    							<label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_ISCSI_NAME']?><span class="required">
    							* </span>
    							</label>
    							<div class="col-md-4 margin10" id="iscsiname">
    							</div>
    						</div>
    						
    						<div class="form-group">
    							<label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_ISCSI_HOST']?><span class="required">
    							* </span>
    							</label>
    							<div class="col-md-3">
    								<div class="input-icon right">
    									<i class="fa"></i>
    									<input type="text" maxlength="15" class="form-control" name="iscsiip" placeholder="192.168.1.10"/>
    									<div><span class="help-block ">
    									<?php echo $LANG['UI_STORAGE_ISCSI_IP_TIPS']?>
    									<?php echo $LANG['UI_STORAGE_ISCSI_IP_TIPS1']?><a id="moreiscsi"><?php echo $LANG['UI_STORAGE_ISCSI_IP_TIPS2']?></a>
    									</span></div>
    								</div>
    							</div>
    							<div class="col-md-1">
    								<div class="input-icon right">
    									<i class="fa"></i>
    									<input type="text" maxlength="5" class="form-control" value='3260' name="iscsiport" placeholder="3260"/>
    									<div><span class="help-block ">
    									<?php echo $LANG['UI_SETTINGS_NOTICE_PORT']?>
    									</span></div>
    								</div>
    							</div>
    						</div>
    						
    						<div class="form-group">
                            	<div class="col-md-offset-3 col-md-4">
                            		<button type="button" class="btn btn-default textalignr" id="iscsiscan"><?php echo $LANG['UI_STORAGE_ISCSI_SCAN_TARGET']?></button>
                            	</div>
                            </div>
    						
    						<div class="form-group target">
    							<label class="control-label col-md-3"><?php echo 'Target LUN'?><span class="required">
    							* </span>
    							</label>
    							<div class="col-md-8">
    								<div class="table-container">
                    					<table class="table table-striped table-bordered table-hover" id="targetluntable">
                    					<thead>
                    					<tr role="row" class="heading">
                    						<th width="10%">
                    							<input type="checkbox" class="group-checkable disabled" disabled>
                    						</th>
                    						<th width="15%">
                    							 <?php echo $LANG['UI_RECOVERY_STORAGE_NAME']?>
                    						</th>
                    						<th width="50%">
                    							 <?php echo 'iqn'?>
                    						</th>
                    						<th width="10%">
                    							 <?php echo $LANG['UI_PUBLIC_TYPE']?>
                    						</th>
                    						<th width="10%">
                    							 <?php echo $LANG['UI_PUBLIC_CAPACITY']?>
                    						</th>
                    					</tr>
                    					</thead>
                    					<tbody>
                    					</tbody>
                    					</table>
                    				</div>
                    				<div><span class="help-block ">
                    					<?php echo $LANG['UI_STORAGE_ISCSI_SELECT_TARGET']?>
                    				</span></div>
    							</div>
    						</div>
						
						</form>
						<!-- ISCSI DIV END -->
						
						<!-- NFS DIV START -->
						<form id="nfsdiv"  class="display-none asdiv">
    						<div class="form-group">
    							<label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_SHARED_FOLDERS']?><span class="required">
    							* </span>
    							</label>
    							<div class="col-md-4">
    								<div class="input-icon right">
    									<i class="fa"></i>
    									<input style="display:none"><!-- for disable autocomplete on chrome -->
    									<input type="text" maxlength="1280" class="form-control" name="host" placeholder="192.168.1.10:/path/directory"/>
    									<div><span class="help-block ">
    									<?php echo $LANG['UI_STORAGE_NFS_TIPS']?> <a id="morenfsparams"><?php echo $LANG['UI_STORAGE_CONFIG_MOUNT_PARAMS']?></a>
    									</span></div>
    								</div>
    							</div>
    						</div>
    						<div class="form-group nfsConfigDiv display-hide">
    							<label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_MOUNT_PARAMS']?>
    							</label>
    							<div class="col-md-4">
    								<div class="input-icon right">
    									<i class="fa"></i>
    									<input style="display:none"><!-- for disable autocomplete on chrome -->
    									<input type="text" maxlength="1280" class="form-control" id="nfsConfig" placeholder="vers=3,xxx=xxx"/>
    									<div><span class="help-block ">
    									<?php echo $LANG['UI_STORAGE_CONFIG_MOUNT_PARAMS_TIPS']?>
    									</span></div>
    								</div>
    							</div>
    						</div>
						</form>
						<!-- NFS DIV END -->
						
						<!-- CIFS DIV START -->
						<form id="cifsdiv"  class="display-none asdiv">
    						<div class="form-group">
    							<label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_SHARED_FOLDERS']?><span class="required">
    							* </span>
    							</label>
    							<div class="col-md-4">
    								<div class="input-icon right">
    									<i class="fa"></i>
    									<input style="display:none"><!-- for disable autocomplete on chrome -->
    									<input type="text" maxlength="1280" class="form-control" name="host"  placeholder="//192.168.1.10/path/directory"/>
    									<div><span class="help-block ">
    									<?php echo $LANG['UI_STORAGE_CIFS_TIPS']?> <a id="morecifsparams"><?php echo $LANG['UI_STORAGE_CONFIG_MOUNT_PARAMS']?></a>
    									</span></div>
    								</div>
    							</div>
    						</div>
    						<div class="form-group cifsConfigDiv display-hide">
    							<label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_MOUNT_PARAMS']?>
    							</label>
    							<div class="col-md-4">
    								<div class="input-icon right">
    									<i class="fa"></i>
    									<input style="display:none"><!-- for disable autocomplete on chrome -->
    									<input type="text" maxlength="1280" class="form-control" id="cifsConfig" placeholder="vers=3,xxx=xxx"/>
    									<div><span class="help-block ">
    									<?php echo $LANG['UI_STORAGE_CONFIG_MOUNT_PARAMS_TIPS']?>
    									</span></div>
    								</div>
    							</div>
    						</div>
    						<div class="form-group">
    							<label class="control-label col-md-3"><?php echo $LANG['UI_LOGIN_USERNAME']?>
    							</label>
    							<div class="col-md-4">
    								<div class="input-icon right">
    									<i class="fa"></i>
    									<input style="display:none"><!-- for disable autocomplete on chrome -->
    									<input type="text" maxlength="128" class="form-control" name="username"/>
    									<div><span class="help-block ">
    									<?php echo $LANG['UI_STORAGE_CIFS_USER']?>
    									</span></div>
    								</div>
    							</div>
    						</div>
    						<div class="form-group">
    							<label class="control-label col-md-3"><?php echo $LANG['UI_LOGIN_PASSWORD']?>
    							</label>
    							<div class="col-md-4">
    								<div class="input-icon right">
    									<i class="fa"></i>
    									<input style="display:none"><!-- for disable autocomplete on chrome -->
    									<input type="password" maxlength="128" class="form-control" name="password"/>
    									<div><span class="help-block ">
    									<?php echo $LANG['UI_STORAGE_CIFS_PASS']?>
    									</span></div>
    								</div>
    							</div>
    						</div>
						</form>
						<!-- CIFS DIV END -->
						
						<!-- COPY STORAGE DIV START -->
						<form id="copydiv"  class="display-none asdiv">
    						<div class="form-group">
    							<label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_IP_OR_DOMAIN']?><span class="required">
    							* </span>
    							</label>
    							<div class="col-md-4">
    								<div class="input-icon right">
    									<i class="fa"></i>
    									<input type="text" maxlength="15" class="form-control" name="remoteip" placeholder="192.168.1.10"/>
    									<div><span class="help-block "><?php echo $LANG['UI_STORAGE_REMOTE_IP_OR_DOMAIN']?>
    									</span></div>
    								</div>
    							</div>
    							<div class="col-md-1 display-none">
    								<div class="input-icon right">
    									<i class="fa"></i>
    									<input style="padding-right: 12px;" type="text" maxlength="5" class="form-control" value='30051' name="remoteport" placeholder="30051"/>
    									<div><span class="help-block ">
    									<?php echo $LANG['UI_SETTINGS_NOTICE_PORT']?>
    									</span></div>
    								</div>
    							</div>
    						</div>
    						<div class="form-group">
    							<label class="control-label col-md-3"><?php echo $LANG['UI_LOGIN_USERNAME']?>
    							</label>
    							<div class="col-md-4">
    								<div class="input-icon right">
    									<i class="fa"></i>
    									<input style="display:none"><!-- for disable autocomplete on chrome -->
    									<input type="text" maxlength="128" class="form-control" name="username" placeholder="admin" disabled/>
    									<div><span class="help-block ">
    									<?php echo $LANG['UI_STORAGE_REMOTE_ADMIN_USER_NAME']?>
    									</span></div>
    								</div>
    							</div>
    						</div>
    						<div class="form-group">
    							<label class="control-label col-md-3"><?php echo $LANG['UI_LOGIN_PASSWORD']?>
    							</label>
    							<div class="col-md-4">
    								<div class="input-icon right">
    									<i class="fa"></i>
    									<input style="display:none"><!-- for disable autocomplete on chrome -->
    									<input type="password" maxlength="128" class="form-control" name="password"/>
    									<div><span class="help-block ">
    									<?php echo $LANG['UI_STORAGE_REMOTE_ADMIN_PASSWORD']?>
    									</span></div>
    								</div>
    							</div>
    						</div>
						</form>
						<!-- CIFS DIV END -->
						
						<!-- CLOUD STORAGE START -->
						<form id="cloudDiv"  class="display-none asdiv">
							<div class="form-group">
								<label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_CLOUD_VENDOR']?>
								</label>
								<div class="col-md-4">
									<select class="form-control select2me" id="vendorSelect" name="vendor">
										<option value="1">AWS S3</option>
										<option value="3"><?php echo $LANG['UI_STORAGE_CLOUD_VENDOR_ALI']?></option>
									</select>
									<div><span class="help-block ">
									<?php echo $LANG['UI_STORAGE_CLOUD_VENDOR_TIPS']?>
									</span></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_CLOUD_REGION']?>
								</label>
								<div class="col-md-4">
									<select class="form-control select2me" id="regionSelect" name="region">
										<option value="cn-north-1"><?php echo $LANG['UI_STORAGE_CLOUD_REGION1']?></option>
										<option value="cn-northwest-1"><?php echo $LANG['UI_STORAGE_CLOUD_REGION2']?></option>
										<option value=""><?php echo $LANG['UI_STORAGE_CLOUD_REGION3']?></option>
									</select>
									<div><span class="help-block ">
									<?php echo $LANG['UI_STORAGE_CLOUD_REGION_TIPS']?>
									</span></div>
								</div>
							</div>
							<div class="form-group">
    							<label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_CLOUD_USERNAME']?>
    							</label>
    							<div class="col-md-4">
    								<div class="input-icon right">
    									<i class="fa"></i>
    									<input style="display:none"><!-- for disable autocomplete on chrome -->
    									<input type="text" maxlength="128" class="form-control" name="cloudname" placeholder=""/>
    									<div><span class="help-block ">
										<?php echo $LANG['UI_STORAGE_CLOUD_ACCESS_KEY']?>
    									</span></div>
    								</div>
    							</div>
    						</div>
    						<div class="form-group">
    							<label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_CLOUD_PASSWORD']?>
    							</label>
    							<div class="col-md-4">
    								<div class="input-icon right">
    									<i class="fa"></i>
    									<input style="display:none"><!-- for disable autocomplete on chrome -->
    									<input type="password" maxlength="128" class="form-control" name="cloudpassword" placeholder=""/>
    									<div><span class="help-block ">
    									<?php echo $LANG['UI_STORAGE_CLOUD_SECRET_KEY']?>
    									</span></div>
    								</div>
    							</div>
							</div>
							<div class="form-group">
    							<label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_CLOUD_BUCKET_NAME']?>
    							</label>
    							<div class="col-md-2">
    								<div class="input-icon right">
    									<i class="fa"></i>
    									<input style="display:none"><!-- for disable autocomplete on chrome -->
    									<input type="text" maxlength="128" class="form-control" name="bucketname"/>
    									<div><span class="help-block ">
										<?php echo $LANG['UI_STORAGE_CLOUD_BUCKET_TIPS']?>
    									</span></div>
    								</div>
								</div>
								<div class="col-md-2">
                            		<button type="button" class="btn floatr btn-default textalignr" id="bucketscan"><?php echo $LANG['UI_STORAGE_CLOUD_SCAN_BUCKET']?></button>
                            	</div>
    						</div>

							<div class="form-group folderDiv">
								<label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_CLOUD_FOLDER']?>
								</label>
								<div class="col-md-4">
									<div class="selectdiv display-none">
										<select class="form-control select2me" id="folderSelect">
										</select>
										<span class="help-block "><?php echo $LANG['UI_STORAGE_CLOUD_SELECT_FOLDER_TIPS']?>
										<a id="diyTab"><?php echo $LANG['UI_INSTANT_VM_TARGET_DIY']?></a></span>
									</div>

									<div class="input-icon inputdiv">
										<i class="fa"></i>
										<input type="text" maxlength="128" class="form-control"  name="folderinput"/>
										<span class="help-block"><?php echo $LANG['UI_STORAGE_CLOUD_INPUT_FOLDER_TIPS']?>
											<a id="selectTab"><?php echo $LANG['UI_INSTANT_VM_TARGET_DIY2']?></a></span>
									</div>
								</div>
							</div>
						</form>
						<!-- CLOUD STORAGE END -->
						
						<!-- LOCAL DIR START -->
						<form id="localdiv"  class="display-none asdiv">
    						<div class="form-group">
    							<label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_LOCAL_DIR_PATH']?><span class="required">
    							* </span>
    							</label>
    							<div class="col-md-4">
    								<div class="input-icon right">
    									<i class="fa"></i>
    									<input style="display:none"><!-- for disable autocomplete on chrome -->
    									<input id="dirName" type="text" maxlength="1280" class="form-control" name="dir" placeholder="/path/directory"/>
    									<div><span class="help-block ">
										<?php echo $LANG['UI_STORAGE_LOCAL_DIR_PATH_TIPS']?>
    									</span></div>
    								</div>
    							</div>
    						</div>
						</form>
						<!-- NFS DIV END -->

						
						<div class="form-group">
							<label class="control-label col-md-3"><?php echo $LANG['UI_VCENTER_RNAME']?>&nbsp;&nbsp;
							</label>
							<div class="col-md-4">
								<div class="input-icon right ">
									<i class="fa"></i>
									<input type="text" maxlength="64" class="form-control" name="rname"/>
									<div><span class="help-block "><?php echo $LANG['UI_STORAGE_RNAME']?></span></div>
								</div>
							</div>
						</div>
						
						<!-- 存储用途 -->
						<div class="form-group" id="usemodeDiv">
							<label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_USED_FOR']?></label>
    						<div class="col-md-4">
    							<div class="input-group marginh10" id="useMode">
    								<label style="padding-right: 20px;"><input type="checkbox" id="backupCheck" data-checkbox="icheckbox_square-blue" data-mode="1" class="icheck"><?php echo $LANG['UI_PLATFORM_BACKUP']?></label>
									<label class="copycheckDiv" style="padding-right: 20px;"><input type="checkbox" id="copyCheck" data-checkbox="icheckbox_square-blue" data-mode="2" class="icheck"><?php echo $LANG['WEB_PLATFORM_DES_COPY']?></label>
									<label class="archivecheckDiv"><input type="checkbox" id="archiveCheck" data-checkbox="icheckbox_square-blue" data-mode="3" class="icheck"><?php echo $LANG['UI_PLATFORM_ARCHIVE'] ?></label>
								</div>
    						</div>
						</div>
						<div class="storagewarningdiv">
							<div class="form-group">
								<label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_ALERT']?></label>
								<div class="col-md-2">
									<input type="checkbox" id="noticeswitch" checked  class="make-switch" data-on-color="primary" data-off-color="info" 
									data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>" 
									data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
								</div>
							</div>
						
						
							<div class="form-group warnningdiv">
								<label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_ALERT_TYPE']?>
								</label>
								<div class="col-md-4">
									<select class="form-control select2me" name="noticetype">
										<option value="1"><?php echo $LANG['UI_STORAGE_ALERT_PERCENT']?></option>
										<option value="2"><?php echo $LANG['UI_STORAGE_ALERT_SIZE']?></option>
									</select>
									<div><span class="help-block ">
										<?php echo $LANG['UI_STORAGE_ALERT_TIPS']?>
									</span></div>
								</div>
							</div>
							
							<div class="form-group warnningdiv" id='percentdiv'>
								<label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_ALERT_THRESHOLD']?>
								</label>
								<div class="col-md-4" style="display:inline-flex;">
									<div id="spinnerpercent" style="width: 140px;">
										<div class="input-group" style="width:140px;">
											<input type="text" id="warningpercent" style="text-align: center;" class="spinner-input form-control" maxlength="3">
											<div class="spinner-buttons input-group-btn">
												<button type="button" class="btn spinner-up default">
													<i class="fa fa-angle-up"></i>
												</button>
												<button type="button" class="btn spinner-down default">
													<i class="fa fa-angle-down"></i>
												</button>
											</div>
										</div>
									</div>
									<div style="font-size: 16px;margin: 12px;">
									%
									</div><br>
									
									
								</div>
								
							</div>
							
							<div class="form-group warnningdiv display-none" id='sizediv'>
								<label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_ALERT_THRESHOLD']?>
								</label>
								<div class="col-md-4" style="display:inline-flex;">
									<div id="spinnersize" style="width: 140px;">
										<div class="input-group" style="width:140px;">
											<input type="text" id="warningsize" style="text-align: center;" class="spinner-input form-control" maxlength="8">
											<div class="spinner-buttons input-group-btn">
												<button type="button" class="btn spinner-up default">
													<i class="fa fa-angle-up"></i>
												</button>
												<button type="button" class="btn spinner-down default">
													<i class="fa fa-angle-down"></i>
												</button>
											</div>
										</div>
									</div>
									<div style="font-size: 16px;margin: 12px;">
									GB
									</div>
								</div>
							</div>
						</div>
						<div class="cloudwarningdiv display-none">
							<div class="form-group warnningdiv">
								<label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_ALERT_TYPE']?>
								</label>
								<div class="col-md-4">
									<select class="form-control select2me" name="cloudnoticetype">
										<option value="1"><?php echo $LANG['UI_STORAGE_CLOUD_CAPACITY_LIMIT']?></option>
									</select>
									<div><span class="help-block ">
										<?php echo $LANG['UI_STORAGE_CLOUD_ALERT_TIPS']?>
									</span></div>
								</div>
							</div>
							
							<div class="form-group warnningdiv" id='cloudsizediv'>
								<label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_ALERT_THRESHOLD']?>
								</label>
								<div class="col-md-4" style="display:inline-flex;">
									<div id="cloudsize" style="width: 140px;">
										<div class="input-group" style="width:140px;">
											<input type="text" id="limitsize" style="text-align: center;" class="spinner-input form-control" maxlength="8">
											<div class="spinner-buttons input-group-btn">
												<button type="button" class="btn spinner-up default">
													<i class="fa fa-angle-up"></i>
												</button>
												<button type="button" class="btn spinner-down default">
													<i class="fa fa-angle-down"></i>
												</button>
											</div>
										</div>
									</div>
									<div style="font-size: 16px;margin: 12px;">
									TB
									</div>
								</div>
							</div>
						</div>
					</div>
					
					<div class="form-actions pt50" style="height: 10%;">
						<div class="row">
							<div class="col-md-offset-3 col-md-4">
								<button type="button" id="cancelBut" class="btn default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
								<button type="button" id="addsubmit" class="btn green-haze"><?php echo $LANG['UI_PUBLIC_YES']?></button>
							</div>
						</div>
					</div>
				</div>
				<!-- END FORM-->
			</div>
		</div>
		<!-- END VALIDATION STATES-->
		
		<!-- BEGIN MODAL -->
		<div id="modaldiv" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="glyphicon glyphicon-plus"></i> <?php echo $LANG['UI_STORAGE_ADD']?></h4>
			</div>
			<div class="modal-body">
				<div class="portlet-body" id="childrendiv">
			        <div class="alert alert-danger" id="hypervisortip">
    				</div>
    				<div class="alert alert-warning" id="childrentip">
    				</div>
                </div>
                <div class="form-group" id="importdiv">
			        <label class="col-md-3 control-label"><?php echo $LANG['UI_STORAGE_IMPORT_BAKCUP_DATA']?></label>
                	<div class="col-md-8">
                		<div >
				            <label class="checkbox-inline pl0"><input type="checkbox" style="padding-left: 0;" class="icheck" id="import"> 
				            <?php echo $LANG['UI_STORAGE_IMPORT_DATA']?> </label>
						</div>
                		<span class="help-block" id="timepointtip">
                		 </span>
                	</div>
                </div>
                <div class="form-group display-none" id="copyimportdiv">
			        <label class="col-md-3 control-label"><?php echo $LANG['UI_COPY_DATA_IMPORT']?></label>
                	<div class="col-md-8">
                		<div >
				            <label class="checkbox-inline pl0"><input type="checkbox" style="padding-left: 0;" class="icheck" id="copyImport"> 
				            <?php echo $LANG['UI_STORAGE_IMPORT_DATA']?> </label>
						</div>
                		<span class="help-block" id="copytimepointtip">
                		 </span>
                	</div>
				</div>
				<div class="form-group display-none" id="cloudimportdiv">
			        <label class="col-md-3 control-label"><?php echo $LANG['UI_ARCHIVE_DATA_IMPORT']?></label>
                	<div class="col-md-8">
                		<div >
				            <label class="checkbox-inline pl0"><input type="checkbox" style="padding-left: 0;" class="icheck" id="cloudImport"> 
				            <?php echo $LANG['UI_STORAGE_IMPORT_DATA']?> </label>
						</div>
                		<span class="help-block" id="cloudtimepointtip">
                		 </span>
                	</div>
                </div>
                
                <div class="form-group" id="formatdiv">
			        <label class="col-md-3 control-label"><?php echo $LANG['UI_STORAGE_IMPORT_FORMAT_STR']?></label>
                	<div class="col-md-8">
                		<div >
				            <label class="checkbox-inline pl0"><input style="padding-left: 0;" type="checkbox" class="icheck" id="format"> 
				            <?php echo $LANG['UI_STORAGE_IMPORT_FORMAT']?> </label>
						</div>
                		<span class="help-block">
                		<?php echo $LANG['UI_STORAGE_IMPORT_FORMAT_TIPS']?> </span>
                	</div>
                </div>
			</div>
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
				<button type="button" class="btn btn-primary" id="submit"><?php echo $LANG['UI_PUBLIC_YES']?></button>
			</div>
		</div>	
		<!-- END MODAL -->
		
	</div>
</div>
<!-- END PAGE CONTENT-->
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<?php 
if($_SESSION['language'] != "en-us"){
    //如果不是英文,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' . 
         $_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/fuelux/js/spinner.min.js" ></script>
<script src="./scripts/libs/base64.js" type="text/javascript"></script>
<script src="./scripts/platform/storage/storage_add.js" type="text/javascript"></script>
	