<?php include_once '../../../tpl/permission.php';?>
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title settings-title">
	<a style="color: #20c99a;" class="ajaxify" name="agent" href="./content/platform/agent/agent.php?tab=2">
		<i class="fa fa-list" ></i>
		<?php echo $LANG['UI_AGENT_GROUP_MANAGE']?> -
	</a>
	<span style="color: #575962;"><?php echo $LANG['UI_PUBLIC_ADD_OPERATION']?></span>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
	<!-- BEGIN VALIDATION STATES-->
	<div class="portlet box blue-hoki" id="addAgentGroup">
	<div class="portlet-title">
		<div class="caption">
			<i class="viconfont vicon-ge_add_task"></i><?php echo $LANG['UI_AGENT_GROUP_ADD']?>
		</div>
	</div>
	<div class="portlet-body form">
	<!-- BEGIN FORM-->
	<form action="#" id="form_addAgentGroup" class="form-horizontal">
		<div class="form-body">
	
        	<div class="form-group pt70" >
    			<label class="control-label col-md-3"><?php echo $LANG['UI_AGENT_GROUP_NAME']?><span class="required">
    			* </span>
    			</label>
    			<div class="col-md-4">
    				<div class="input-icon right">
    					<i class="fa"></i>
    					<input type="text" maxlength="128" class="form-control" name="groupname" id="groupname"/>
    					<div><span class="help-block ">
    					
    					</span></div>
    				</div>
        		</div>
        	</div>
        	<div class="form-group agentDiv">
        		<label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_AGENT']?><span class="required">
        		* </span>
        		</label>
        		<div class="col-md-4">
        			<div class="mt10">
        				<ul id="agentTree" class="ztree bd1de5  tree_div ztree-fa height200"></ul>
        			</div>
        		</div>
        	</div>
        	<div class="form-group display-hide" id="noagenttips">
				<label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_AGENT']?> <span class="required">
				* </span>
				</label>
				<div class="col-md-4 alert alert-block alert-info fade in ml15">
					<button type="button" class="close" data-dismiss="alert"></button>
					<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS']?></strong></h4>
					<ol class = "alert-ol">
						<li>
							<?php echo $LANG['UI_AGENT_GROUP_NO_AGENT_TITLE']?> 
						</li>
						<li>
							<small><?php echo $LANG['UI_AGENT_GROUP_NO_AGENT_TIPS']?></small>
						</li>
					</ol>
				</div>
			</div>
        	<div class="form-group">
    			<label class="control-label col-md-3"><?php echo $LANG['UI_PUBLIC_REMARK']?>
    			</label>
    			<div class="col-md-4">
    				<div class="input-icon right">
    					<i class="fa"></i>
    					<textarea class="form-control" id="description" name="description" rows="3" placeholder="<?php echo $LANG['UI_AGENT_GROUP_REMARK_PLACEHOLDER']?>"></textarea>
    					<div><span class="help-block ">
    					</span></div>
        			</div>
            	</div>
        	</div>
	
    		<div class="form-actions pt50">
        		<div class="row">
        			<div class="col-md-offset-3 col-md-4">
        				<button type="button" id="cancelBut" class="btn default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
        				<button type="button" id="submitBut" class="btn green-haze"><?php echo $LANG['UI_PUBLIC_YES']?></button>
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
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<?php 

if($_SESSION['language'] != "en-us"){
    //如果不是英文,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' . 
         $_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./scripts/platform/agent/add_agent_group.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	

