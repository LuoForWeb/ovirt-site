<?php include_once '../../../../tpl/permission.php';?>
<!-- BEGIN FORM-->
<form action="#" id="factoryform" class="form-horizontal mh510">
	<div class="form-body">
		<div class="form-group pt50">
			<div class="col-md-offset-2 col-md-8">
				<div class="alert alert-block alert-info fade in " >
				    <span><i class="viconfont vicon-ge_summary"></i> <?php echo $LANG['UI_SETTINGS_FACTORY_RESET_TIP']?></span>
					<ul class="linonepoint">
    					<li>
    						 <?php echo $LANG['UI_SETTINGS_FACTORY_RESET_OUT1']?>
    					</li>
    					<li>
							 <?php echo $LANG['UI_SETTINGS_FACTORY_RESET_OUT2']?>
    					</li>
    					<li>
							 <?php echo $LANG['UI_SETTINGS_FACTORY_RESET_OUT3']?>
    					</li>
    					<li>
							 <?php echo $LANG['UI_SETTINGS_FACTORY_RESET_OUT4']?>
    					</li>
    					<li>
							 <?php echo $LANG['UI_SETTINGS_FACTORY_RESET_OUT5']?>
    					</li>
    					<li>
							 <?php echo $LANG['UI_SETTINGS_FACTORY_RESET_OUT6']?>
    					</li>
    				</ul>
				</div>
			</div>
		</div>
		<div class="form-actions">
			<div class="row">
    			<div class="col-md-offset-2 col-md-4 pding30">
    				<button type="button" id="dofactory" class="btn btn-danger">
    				<i class="fa fa-undo"></i> <?php echo $LANG['WEB_SYSTEM_RESTORE_FACTORY_SETTING']?></button>
    			</div>
    		</div>
		</div>
	</div>
</form>