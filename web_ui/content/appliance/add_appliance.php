<?php include_once '../../tpl/permission.php';?>
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="appliance_manager" href="./content/appliance/appliance_manager.php">
            <?php echo $LANG['UI_PLATFORM_APPLIANCE']?>
        </a>
    </li>
    <span>></span>
    <span class="curent"><?php echo $LANG['UI_APPLIANCE_ADD']?></span>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12" style="height: 100%;">
		 <!-- BEGIN VALIDATION STATES-->
		<div class="portlet box blue-hoki" id="addcontent"  style="height: 100%;margin-bottom:0;">
			<div class="portlet-title"  style="height: 5%;">
				<div class="caption">
					<i class="viconfont vicon-ge_modify"></i><?php echo $LANG['UI_APPLIANCE_ADD']?>
				</div>
			</div>
			<div class="portlet-body form"  style="height: 95%;">
				<!-- BEGIN FORM-->
				<form action="#" id="form_sample_2" class="form-horizontal"  style="height: 100%;">
					<div class="form-body"  style="height: 90%;overflow-x:hidden; overflow-y: auto;">
						<div class="form-group pt50">
							<label class="control-label col-md-3"><?php echo $LANG['UI_APPLIANCE_IP']?> <span class="required">
							* </span>
							</label>
							<div class="col-md-4">
								<div class="input-icon right">
									<i class="fa"></i>
									<input type="text" maxlength="128" class="form-control" name="ipaddr" placeholder="192.168.1.110"/>
									<div><span class="help-block "><?php echo $LANG['UI_APPLIANCE_ADD_IP_TIPS']?></span></div>
								</div>
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
									<input type="text" maxlength="128" class="form-control" name="port" placeholder="23001" value="23001"/>
									<div><span class="help-block ">
									<?php echo $LANG['UI_APPLIANCE_PORT_ADD_TIPS']?>
									</span></div>
								</div>
							</div>
						</div>
						<div class="form-group">
							<div class="col-md-offset-3 col-md-4">
								<div class="alert alert-info">
                                    <button type="button" class="close" data-dismiss="alert"></button>
                                    <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS']?></strong></h4>
                                    <ol class = "alert-ol">
                                        <li>
                                            <?php echo $LANG['UI_APPLIANCE_ADD_TIPS1']?>
                                        </li>
                                        <li>
                                            <?php echo $LANG['UI_APPLIANCE_ADD_TIPS2']?>
                                        </li>
                                    </ol>
								</div>
							</div>

						</div>
						
					</div>
					<div class="form-actions pt50"  style="height: 10%;">
						<div class="row">
							<div class="col-md-offset-6 col-md-6">
								<button type="button" id="cancelBut" class="btn default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
								<button type="button" id="addsubmit" class="btn green-haze"><?php echo $LANG['UI_PUBLIC_YES']?></button>
							</div>
						</div>
					</div>
				</form>
				<!-- END FORM-->
			</div>
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
<script src="./scripts/appliance/add_appliance.js" type="text/javascript"></script>
	