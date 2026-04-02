<?php include_once '../../tpl/permission.php';?>
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="appliance_manager" href="./content/appliance/appliance_manager.php">
            <?php echo $LANG['UI_PLATFORM_APPLIANCE']?>
        </a>
    </li>
    <span>></span>
    <span class="curent"><?php echo $LANG['UI_APPLIANCE_MODIFY']?></span>
</h3>
<input style="display:none" >
<div class="row" style="height:100%;">
	<div class="col-md-12" style="height:100%;">
	    <input id="applianceUUID" value="<?php echo $_GET['uuid'];?>" class="display-none"></input>
	    <input id="pageUri" value="<?php echo $_SERVER['REQUEST_URI'];?>" class="display-none"></input>
		 <!-- BEGIN VALIDATION STATES-->
		<div class="portlet box blue-hoki" id="modifycontent" style="height:100%;margin-bottom:0;">
        <div class="portlet-title" style="height:5%;">
				<div class="caption">
					<i class="viconfont vicon-ge_modify"></i><?php echo $LANG['UI_APPLIANCE_MODIFY']?>
				</div>
			</div>
			<div class="portlet-body form" style="height:95%;">
				<!-- BEGIN FORM-->
				<form action="#" id="form_sample_2" class="form-horizontal" style="height:100%;">
					<div class="form-body" style="height:90%; overflow-x:hidden; overflow-y:auto;">
						<div class="form-group pt50">
							<label class="control-label col-md-3"><?php echo $LANG['UI_APPLIANCE_IP']?> <span class="required">
							* </span>
							</label>
							<div class="col-md-4">
								<div class="input-icon right">
									<i class="fa"></i>
									<input type="text" maxlength="128" class="form-control" name="ipaddr" placeholder="192.168.1.100" disabled/>
									<div><span class="help-block "><?php echo $LANG['UI_APPLIANCE_ADD_IP_TIPS']?></span></div>
								</div>
							</div>
						</div>
						
						<div class="form-group">
                			<label class="control-label col-md-3"><?php echo $LANG['UI_SETTINGS_DNS_SETTING']?><span class="required">
                			* </span>
                			</label>
                			<div class="col-md-4">
                				<textarea class="form-control" id="dnslist" rows="8" placeholder="192.168.1.110  example.com"></textarea>
                				<div><span class="help-block ">
                					<?php echo $LANG['UI_SETTINGS_DNS_SETTING_TIPS']?>
                				</span></div>
                			</div>
                		</div>

						<div class="form-group">
							<label class="control-label col-md-3"><?php echo $LANG['UI_APPLIANCE_NICKNAME']?>&nbsp;&nbsp;
							</label>
							<div class="col-md-4">
								<div class="input-icon right ">
									<i class="fa"></i>
									<input type="text" maxlength="64" class="form-control" name="rname"/>
									<div><span class="help-block "><?php echo $LANG['UI_APPLIANCE_ADD_NICKNAME_TIPS']?></span></div>
								</div>
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-md-3"><?php echo $LANG['UI_APPLIANCE_PORT']?><span class="required">
							* </span>
							</label>
							<div class="col-md-4">
								<div class="input-icon right">
									<i class="fa"></i>
									<input style="display:none"><!-- for disable autocomplete on chrome -->
									<input id ="port" type="text" maxlength="128" class="form-control" name="port" placeholder="23001"/>
									<div><span class="help-block ">
									<?php echo $LANG['UI_APPLIANCE_PORT_ADD_TIPS']?>
									</span></div>
								</div>
							</div>
						</div>

                        <div class="form-group">
							<label class="control-label col-md-3"><?php echo $LANG['UI_APPLIANCE_PROGRESS_SERVER_PORT']?><span class="required">
							* </span>
							</label>
							<div class="col-md-4">
								<div class="input-icon right">
									<i class="fa"></i>
									<input style="display:none"><!-- for disable autocomplete on chrome -->
									<input id="progress_server_listen_port" type="text" maxlength="128" class="form-control" name="progressport" placeholder="22790"/>
									<div><span class="help-block ">
										<?php echo $LANG['UI_APPLIANCE_PROGRESS_SERVER_PORT']?>
									</span></div>
								</div>
							</div>
						</div>

                        <div class="form-group">
							<label class="control-label col-md-3"><?php echo $LANG['UI_APPLIANCE_PROGRESS_SERVER_PORT_RANGE']?><span class="required">
							* </span>
							</label>
							<div class="col-md-2" style="position:relative;">
								<div class="input-icon right">
									<i class="fa"></i>
									<input style="display:none"><!-- for disable autocomplete on chrome -->
									<input id="progress_server_start_port" type="text" maxlength="128" class="form-control" name="startport" placeholder="22790"/>
									<div><span class="help-block ">
									</span></div>
								</div>
								<span style="position:absolute;top:8px;right:-5px;">~</span>
							</div>
							<div class="col-md-2">
								<div class="input-icon right">
									<i class="fa"></i>
									<input style="display:none"><!-- for disable autocomplete on chrome -->
									<input id="progress_server_end_port" type="text" maxlength="128" class="form-control" name="endport" placeholder="22790"/>
									<div><span class="help-block ">
									</span></div>
								</div>
							</div>
						</div>

                        <div class="form-group display-none">
							<label class="control-label col-md-3"><?php echo $LANG['UI_APPLIANCE_CDP_CLIENT_PORT']?><span class="required">
							* </span>
							</label>
							<div class="col-md-4">
								<div class="input-icon right">
									<i class="fa"></i>
									<input style="display:none"><!-- for disable autocomplete on chrome -->
									<input id="cdp_client_listen_port" type="text" maxlength="128" class="form-control" name="port" placeholder="22790"/>
									<div><span class="help-block ">
									<?php echo $LANG['UI_APPLIANCE_CDP_CLIENT_PORT']?>
									</span></div>
								</div>
							</div>
						</div>

                        <div class="form-group display-none">
							<label class="control-label col-md-3"><?php echo $LANG['UI_APPLIANCE_CDP_CLIENT_LOG_PORT']?><span class="required">
							* </span>
							</label>
							<div class="col-md-4">
								<div class="input-icon right">
									<i class="fa"></i>
									<input style="display:none"><!-- for disable autocomplete on chrome -->
									<input id="cdp_client_log_listen_port" type="text" maxlength="128" class="form-control" name="port" placeholder="22790"/>
									<div><span class="help-block ">
									<?php echo $LANG['UI_APPLIANCE_CDP_CLIENT_LOG_PORT']?>
									</span></div>
								</div>
							</div>
						</div>

                        <div class="form-group display-none">
							<label class="control-label col-md-3"><?php echo $LANG['UI_APPLIANCE_CDP_SERVER_LOG_PORT']?><span class="required">
							* </span>
							</label>
							<div class="col-md-4">
								<div class="input-icon right">
									<i class="fa"></i>
									<input style="display:none"><!-- for disable autocomplete on chrome -->
									<input id="log_server_listen_port" type="text" maxlength="128" class="form-control" name="port" placeholder="22790"/>
									<div><span class="help-block ">
									<?php echo $LANG['UI_APPLIANCE_CDP_SERVER_LOG_PORT']?>
									</span></div>
								</div>
							</div>
						</div>

					</div>
					<div class="form-actions pt50" style="height:10%;">
						<div class="row">
							<div class="col-md-offset-3 col-md-4">
								<button type="button" id="cancelBut" class="btn default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
								<button type="button" id="addsubmit" class="btn green-haze"><?php echo $LANG['UI_PUBLIC_YES']?></button>
							</div>
						</div>
					</div>
				</form>
				<!-- END FORM-->
		</div>
		<!-- END VALIDATION STATES-->
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
<script src="./scripts/libs/base64.js" type="text/javascript"></script>
<script src="./scripts/appliance/edit_appliance.js" type="text/javascript"></script>
	