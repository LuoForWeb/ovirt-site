<?php include_once '../../../tpl/permission.php';?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<!-- <link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" /> -->
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
<?php echo $LANG['UI_FEEDBACK_TITLE']?><small><?php echo $LANG['UI_FEEDBACK_TITLE_TIPS']?></small>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
		<div class="portlet box blue-hoki">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-gift"></i><?php echo $LANG['UI_FEEDBACK_UEIP']?>
				</div>
			</div>
			<div class="portlet-body">
				<div class="panel-body">
				    <div class="col-md-offset-2 col-md-8 mb15">
				        <label class="checkbox-inline pl0">
				            <input type="checkbox" class="icheck" id="yesback"> 
				              <?php echo $LANG['UI_FEEDBACK_UEIP_TIPS']?>
			            </label>
				    </div>
				    <div class="col-md-offset-2 col-md-8">
    				    <div class="col-md-3"><a href="<?php echo $CONF['REMOTE']['ueiplan']?>" target="_blank">
			                <i class="fa fa-file-text-o fs24"></i> <?php echo $LANG['UI_FEEDBACK_UEIP']?></a></div>
    				    <div class="col-md-3"><a href="<?php echo $CONF['REMOTE']['privacy']?>" target="_blank">
    				        <i class="fa fa-file-text-o fs24"></i> <?php echo $LANG['UI_FEEDBACK_PRIVACY']?></a></div>
				    </div>
				</div>
			</div>
		</div>
		<div class="portlet box blue-hoki">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-gift"></i><?php echo $LANG['UI_FEEDBACK_INFO_TITLE']?>
				</div>
			</div>
			<div class="portlet-body">
				<div class="panel-body">
				    <div class="col-md-offset-2 col-md-8 mb15">
				        <label class="pl0">
				        <i class="icon-emoticon-smile font-green-seagreen fs24"></i> <?php echo $LANG['UI_FEEDBACK_INFO_TIPS']?>
			            </label>
				    </div>
				    <form action="#" id="feedbackform" class="form-horizontal">
                    	<div class="form-body">
                    		<div class="form-group">
                    			<label class="control-label col-md-3"><?php echo $LANG['UI_FEEDBACK_PHONE']?>
                    			</label>
                    			<div class="col-md-4">
                    				<div class="input-icon right">
                    					<i class="fa"></i>
                    					<input type="text" maxlength="20" class="form-control" name="phone"/>
                    				</div>
                    			</div>
                    		</div>
                    		
                    		<div class="form-group">
                    			<label class="control-label col-md-3"><?php echo $LANG['UI_FEEDBACK_EMAIL']?>
                    			</label>
                    			<div class="col-md-4">
                    				<div class="input-icon right">
                    					<i class="fa"></i>
                    					<input style="display:none"><!-- for disable autocomplete on chrome -->
                    					<input type="text" maxlength="65" class="form-control" name="email"/>
                    				</div>
                    			</div>
                    		</div>
                    		<div class="form-group">
                    			<label class="control-label col-md-3"><?php echo $LANG['UI_FEEDBACK_INFOMATION']?><span class="required">
                    			* </span>
                    			</label>
                    			<div class="col-md-7">
                    				<div class="input-icon right">
                    					<i class="fa"></i>
                    					<textarea class="form-control" maxlength="1000" rows="6" name="msg"></textarea>
                    				</div>
                    			</div>
                    		</div>
                    		
                    	</div>
                    	<div class="form-actions">
                    		<div class="row">
                    			<div class="col-md-offset-3 col-md-4">
                    				<button type="button" id="feedbackcancel" class="btn default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
                    				<button type="button" id="feedbacksubmit" class="btn green-haze"><?php echo $LANG['UI_PUBLIC_YES']?></button>
                    			</div>
                    		</div>
                    	</div>
                    </form>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- END PAGE CONTENT-->
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script src="./scripts/platform/users/feedback.js" type="text/javascript"></script>
	