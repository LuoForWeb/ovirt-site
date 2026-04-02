<?php 
    include_once '../../../../tpl/permission.php';
    $userAllPermission = $_SESSION['permissionArr'];
?>
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
	<li>
		<a class="ajaxify" name="setting_manager" href="./content/platform/settings/setting_manager.php">
			<span><?php echo $LANG['UI_PLATFORM_SYSTEM_SET'] ?></span>
		</a>
	</li>
	<span>></span>
	<span class="curent"><?php echo $LANG['UI_SETTINGS_CARBON_MONITOR_PLATFORM'] ?></span>
</h3>
<div class="row row-manager">
	<div class="col-md-12 col-manager">
		<!-- BEGIN TAB PORTLET-->
		<div class="portlet box blue-hoki" id=''>
			<div class="portlet-title">
				<div class="caption">
					<i class="viconfont vicon-carbon-platform"></i><?php echo $LANG['UI_SETTINGS_CARBON_MONITOR_PLATFORM'] ?>
				</div>
			</div>
			<div class="portlet-body form">
				<!-- BEGIN FORM-->
				<form action="#" id="carbonMonitorForm" class="form-horizontal">
					<div class="form-body-wrapper">
						<div class="form-body wp-50 hp-100 overflow-visible width80p_en">
                            <div class="form-group">
                                <label class="control-label form-group-top4-label col-md-3">
                                    <?php echo $LANG['UI_SETTINGS_CARBON_MONITOR']?>
                                </label>
                                <div class="col-md-6">
                                    <input type="checkbox" id="configCarbon" class="make-switch" data-on-color="primary"
                                           data-size="small" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                    <a class="popovers ml15" data-container="body" data-trigger="hover" data-placement="right" data-content=" <?php echo $LANG['UI_SETTINGS_CARBON_MONITOR_TIPS']?>">
                                        <i class="viconfont vicon-tishi"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="form-group ipDiv display-none">
                                <label class="control-label col-md-3"><?php echo $LANG['UI_EXERCISE_PLATFORM_IP_OR_NAME']?> <span class="required">
						    	* </span>
                                </label>
                                <div class="col-md-9">
                                    <div class="col-md-3" style="padding: 0 !important;">
                                        <select class="form-control" id="httpType">
                                            <option value="https://" selected>https://</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6" style="padding: 0 !important;">
                                        <div class="input-icon right">
                                            <i class="fa"></i>
                                            <input type="text" maxlength="128" class="form-control" name="ipaddr" placeholder="192.168.1.110"/>
                                        </div>
                                    </div>
                                    <div class="col-md-12" style="padding: 0 !important;">
                                        <span class="help-block iptips"><?php echo $LANG['UI_SETTINGS_CARBON_MONITOR_PLATFORM_INPUT']?></span>
                                    </div>
                                </div>
						    </div>
                            <div class="alert alert-block alert-info fade in mt20" id="marktips">
                                <button type="button" class="close" data-dismiss="alert"></button>
                                <ul class="alert-ul">
                                    <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS']?></strong>
                                    <li>
                                        <?php echo $LANG['UI_SETTINGS_CARBON_MONITOR_PLATFORM_TIPS1']?>
                                    </li>
                                </ul>
                            </div>
					    </div>
                    </div>
					<div class="form-actions flex-items-center justify-content-center">
						<div class="wp-50">
							<label class="control-label min-w-145px col-md-3"></label>
							<div class="col-md-6">
								<button type="button" id="carbonCancel"
									class="btn default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
                                <?php
                                    if (in_array("p_carbon_monitor_platform_operate", $userAllPermission)) {
                                        echo '<button type="button" id="carbonSubmit" class="btn green-haze btn-confirm">'. $LANG['UI_PUBLIC_YES'] .'</button>';
                                    }
                                ?>    
							</div>
						</div>
					</div>
				</form>
				<!-- END FORM-->
			</div>
		</div>
	</div>
</div>
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<?php
if($_SESSION['language'] != "en-us"){
    //如果不是英文,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
        $_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script src="./scripts/libs/base64.js" type="text/javascript"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/platform/settings/settingstab/carbon_monitor_platform.js"></script>
