<?php include_once '../../tpl/permission.php';?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>

<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
		<input id="agentUUID" value="<?php echo $_GET['uuid'];?>" class="display-none"></input>
		<input id="dbType" value="<?php echo $_GET['dbtype'];?>" class="display-none"></input>
	    <input id="pageUri" value="<?php echo $_SERVER['REQUEST_URI'];?>" class="display-none"></input>
		 <!-- BEGIN VALIDATION STATES-->
		<div class="portlet box blue-hoki" id="addAgent">
			<div class="portlet-title">
				<div class="caption">
					<i class="viconfont vicon-ge_modify"></i><?php echo $LANG['UI_DB_CONFIG_CLIENT_INFO']?>
				</div>
			</div>
			<div class="portlet-body form">
				<!-- BEGIN FORM-->
				<form action="#" id="form_sample_2" class="form-horizontal">
					<div class="form-body">
						<div class="form-group pt70">
							<label class="control-label col-md-3"><?php echo $LANG['UI_DB_AGENT_NAME']?>&nbsp;&nbsp;
							</label>
							<div class="col-md-4">
								<div class="pt7 value" id="agentName"></div>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-md-3"><?php echo $LANG['UI_DB_DATABASE_TYPE']?><span class="required">
							* </span>
							</label>
							<div class="col-md-2">
								<select class="form-control select2me" id="dbtypeCheck" name="dbtype">
									<option value="1">SQL Server</option>
									<option value="2">Oracle</option>
									<option value="3">MySQL</option>		
									<option value="4">DM</option>
									<option value="5">PostgreSQL</option>		
									<option value="6">KingbaseES</option>		
									<option value="7">UXDB</option>			
									<option value="8">Highgo DB</option>
                                    <option value="9">openGauss</option>
                                    <option value="10">Vastbase</option>
                                    <option value="11">AntDB</option>

								</select>
								<div><span class="help-block ">
									<?php echo $LANG['UI_DB_ADD_DATABASE_TYPE_TIPS']?>
								</span></div>
							</div>
						</div>
						<div class="form-group instanceDiv">
							<label class="control-label col-md-3"><?php echo $LANG['UI_DB_SELECT_INSTANCE_VERIFY']?> <span class="required">
							* </span>
							</label>
							<div class="col-md-8">
								<div class="table-toolbar">
                					<div class="row">
                						<div class="col-md-12">
                							<button type="button" id="btInstance" class="btn btn-sm green-haze addInstance display-none">
            									<?php echo $LANG['UI_DB_ADD_INSTANCE']?>
            								</button>
                						</div>
                					</div>
                				</div>
								<div class="table-container">
                					<table class="table table-striped table-bordered table-hover" id="instance_table">
                					<thead>
                					<tr role="row" class="heading">
                						<th width="5%">
                							<input type="checkbox" class="group-checkable" disabled>
                						</th>
                						<th width="15%" id="instanceTitle">
                							 <?php echo $LANG['UI_DB_INSTANCE_NAME']?>
                						</th>
                						<th width="10%">
                							<?php echo $LANG['UI_DB_DATABASE_TYPE']?>
                						</th>
                						<th width="20%">
                							<?php echo $LANG['UI_VCENTER_VERSION']?>
                						</th>
                						<th width="15%">
                							<?php echo $LANG['UI_DB_LOGIN_NAME']?>
                						</th>
                						<th width="15%">
                							<?php echo $LANG['UI_DB_VERIFY_TYPE']?>
                						</th>
                						<th width="20%">
                							<?php echo $LANG['UI_DB_VERIFY_TIME']?>
                						</th>
                						<th width="20%">
                							<?php echo $LANG['UI_DB_INSTANCE_CLUSTER_PATH']?>
                						</th>	
                					</tr>
                					</thead>
                					<tbody>
                					</tbody>
                					</table>
                					<div id="selecttips"><span class="help-block ">
                    					
                    				</span></div>
                				</div>
							</div>
						</div>
					</div>
					<div class="form-actions pt50">
						<div class="row">
							<div class="col-md-offset-3 col-md-4">
								<button type="button" id="goBack" class="btn default"><?php echo $LANG['UI_DB_RETURN']?></button>
								<button type="button" id="addsubmit" class="btn green-haze"><?php echo $LANG['UI_DB_CONFIG_INSTANCE_INFO']?></button>
							</div>
						</div>
					</div>	
				</form>
				<!-- END FORM-->
			</div>
		</div>
		<!-- END VALIDATION STATES-->
		<!-- BEGIN ADD MODAL -->
		<div id="dbAuthModal" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
			<div id="uuid" class="display-none"></div>
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title display-none otherDiv"><i class="icon-badge"></i> <?php echo $LANG['UI_DB_DATABASE_INSTANCE_VERIFY']?></h4>
				<h4 class="modal-title display-none addTitleDiv" ><i class="viconfont vicon-ge_add_task"></i> <?php echo $LANG['UI_DB_ADD_INSTANCE']?></h4>
			</div>
			<div class="modal-body">
    			<div class="portlet-body">
    				<div class="list-option authtypeDiv">
    					<div class="row">
    						<label class="control-label col-md-3"><?php echo $LANG['UI_DB_IDENTITY_VERIFY_TYPE']?>
							</label>
							<div class="col-md-6">
								<select class="form-control select2me" id="authtype" name="authtype">
									<option value="1"><?php echo $LANG['WEB_DB_WINDOWS_AUTH']?></option>
									<option value="2"><?php echo $LANG['WEB_DB_SQLSERVER_AUTH']?></option>
								</select>
							</div>
							
							<div class="col-md-2 mt10">
								<a class="popovers " data-container="body" data-trigger="hover" 
								data-placement="right" data-content="<?php echo $LANG['UI_DB_IDENTITY_VERIFY_TIPS']?>">
								<i class="fa fa-info-circle fa-lg"></i>
								</a>
							</div>
							
    					</div>
    				</div>
    				<div class="list-option oracleAddDiv display-hide">
    					<div class="row">
    						<label class="control-label col-md-3"><?php echo $LANG['UI_DB_INSTANCE_NAME']?><span class="required">
							* </span>
							</label>
							<div class="col-md-8">
								<div class="input-icon right">
									<input type="text" maxlength="128" class="form-control" id="instancename" />
									<div><span class="help-block ">
									<?php echo $LANG['UI_DB_ORACLE_INSTANCE_NAME_TIPS']?>
									</span></div>
								</div>
							</div>
    					</div>
    				</div>
    				<div class="userDiv display-hide">
    					<div class="row installnameDiv">
    						<label class="control-label col-md-3 installLabel"><?php echo $LANG['UI_DB_INSTALL_USER_NAME']?><span class="required">
							* </span>
							</label>
							<div class="col-md-8">
								<div class="input-icon right">
									<input type="text" maxlength="128" class="form-control" id="installname" />
									<div><span class="help-block installTips">
									<?php echo $LANG['UI_DB_ORACLE_INSTALL_NAME_TIPS']?>
									</span></div>
								</div>
							</div>
    					</div>
    					<div class="row installpathDiv display-hide">
    						<label class="control-label col-md-3 installpathLabel"><?php echo $LANG['UI_DB_DATABASE_BIN_PATH']?><span class="required">
							* </span>
							</label>
							<div class="col-md-8">
								<div class="input-icon right">
									<input type="text" maxlength="128" class="form-control" id="installpath" placeholder="/path"/>
									<div><span class="help-block installpathTips">
									<?php echo $LANG['UI_DB_DATABASE_BIN_DIRECTORY_PATH']?>
									</span></div>
								</div>
							</div>
    					</div>
    					<div class="row mt10">
    						<label class="control-label col-md-3"><?php echo $LANG['UI_LOGIN_USERNAME']?><span class="required">
							* </span>
							</label>
							<div class="col-md-8">
								<div class="input-icon right">
									<input type="text" maxlength="128" class="form-control" id="authname"/>
									<div><span class="help-block ">
									<?php echo $LANG['UI_DB_INSTANCE_USER_NAME']?>
									</span></div>
								</div>
							</div>
    					</div>
    					
    					<div class="row">
    						<label class="control-label col-md-3"><?php echo $LANG['UI_LOGIN_PASSWORD']?><span class="required">
							* </span>
							</label>
							<div class="col-md-8">
								<div class="input-icon right">
									<input type="password" autocomplete="off" maxlength="128" class="form-control" id="password" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')"/>
									<div><span class="help-block ">
									<?php echo $LANG['UI_DB_INSTANCE_PASSWORD']?>
									</span></div>
								</div>
							</div>
    					</div>
    				</div>
    				
    				<div class="list-option display-none oracleDiv">
    					<div class="row">
							<label class="control-label col-md-3"><?php echo $LANG['UI_DB_CLUSTER_CONNECT']?>
							</label>
							<div class="col-md-6">
								<input type="checkbox" id="clusterCheck" class="make-switch" data-on-color="primary" data-off-color="info" 
								data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>" 
								data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
								<a class="popovers ml15" data-container="body" data-trigger="hover" 
								data-placement="right" data-content="<?php echo $LANG['UI_DB_ORACLE_CLUSTER_CONNECT_TIPS']?>">
								<i class="fa fa-info-circle fa-lg"></i>
								</a>
							</div>
						</div>
						
						<div class="row display-none oracleTreeDiv mt15">
							<label class="control-label col-md-3"><?php echo $LANG['UI_DB_CLUSTER_SELECT_INSTANCE']?>
							</label>
							<div class="col-md-8">
    							<div class="alert alert-block alert-info fade in display-hide"  id="noagent">
    								<button type="button" class="close" data-dismiss="alert"></button>
                                    <ul class="alert-ul">
                                        <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS']?>:</strong>
                                        <li>
                                            <?php echo $LANG['UI_BACKUP_NO_VALID_DB_AGENT_TIPS']?>
                                        </li>
                                    </ul>
    							</div>
							  	<ul id="agent_tree" class="ztree bd1de5  tree_div height266"></ul>
							</div>
						</div>	
									
    				</div>
    				
    				<div class="list-option mysqlDiv display-hide">
    					<div class="row">
    						<label class="control-label col-md-3"><?php echo $LANG['UI_DB_MYSQL_CNF_PATH']?><span class="required">
							* </span>
							</label>
							<div class="col-md-8">
								<div class="input-icon right">
									<input type="text" maxlength="128" class="form-control" id="cnfPath" />
									<div><span class="help-block ">
									<?php echo $LANG['UI_DB_MYSQL_CNF_PATH_TIPS']?>
									</span></div>
								</div>
							</div>
    					</div>
    					<!-- <div class="row">
    						<label class="control-label col-md-3"><?php echo $LANG['UI_DB_MYSQL_DATA_PATH']?><span class="required">
							* </span>
							</label>
							<div class="col-md-4">
								<div class="input-icon right">
									<input type="text" maxlength="128" class="form-control" id="dataPath" />
									<div><span class="help-block ">
									
									</span></div>
								</div>
							</div>
    					</div>-->
    					
    					<div class="row">
    						<label class="control-label col-md-3"><?php echo $LANG['UI_VECNTER_ADD_VC_IP_PORT']?><span class="required">
							* </span>
							</label>
							<div class="col-md-4">
								<div class="input-icon right">
									<input type="text" maxlength="128" class="form-control" id="port" />
									<div><span class="help-block ">
									<?php echo $LANG['UI_DB_MYSQL_PORT_TIPS']?>
									</span></div>
								</div>
							</div>
    					</div>
    					
    					<div class="row">
    						<label class="control-label col-md-3"><?php echo $LANG['UI_LOGIN_USERNAME']?><span class="required">
							* </span>
							</label>
							<div class="col-md-8">
								<div class="input-icon right">
									<input type="text" maxlength="128" class="form-control" id="mysqlname"/>
									<div><span class="help-block ">
									<?php echo $LANG['UI_DB_INSTANCE_USER_NAME']?>
									</span></div>
								</div>
							</div>
    					</div>
    					
    					<div class="row">
    						<label class="control-label col-md-3"><?php echo $LANG['UI_LOGIN_PASSWORD']?><span class="required">
							* </span>
							</label>
							<div class="col-md-8">
								<div class="input-icon right">
									<input type="password" autocomplete="off" maxlength="128" class="form-control" id="mysqlpassword" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')"/>
									<div><span class="help-block ">
									<?php echo $LANG['UI_DB_INSTANCE_PASSWORD']?>
									</span></div>
								</div>
							</div>
    					</div>
    				</div>
				</div>
			</div>
			
			<!-- modal-footer -->
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
				<button type="button"class="btn btn-primary" id="auth_submit"><?php echo $LANG['UI_VCENTER_REFRESH_TIME_SAVE']?></button>
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

<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script src="./scripts/libs/base64.js" type="text/javascript"></script>
<script src="./scripts/dbprotect/add_agent.js" type="text/javascript"></script>
	