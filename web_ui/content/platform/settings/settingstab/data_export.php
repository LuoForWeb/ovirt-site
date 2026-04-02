<?php include_once '../../../../tpl/permission.php';?>
<link href="./assets/global/plugins/jquery-file-upload/css/jquery.fileupload.css" rel="stylesheet"/>
<div class="row">
	<div class="col-md-12">
	    <div class="tabbable-custom mh510">
    		<ul class="nav nav-tabs ">
    			<li class="active">
    				<a href="#export_tab" data-toggle="tab" aria-expanded="false">
    				<i class="fa fa-sign-out fa-rotate-90 font-green-seagreen"></i> <?php echo $LANG['UI_SETTINGS_EXPORT_OUT']?></a>
    			</li>
    			<li class="">
    				<a href="#import_tab" data-toggle="tab" aria-expanded="false">
    				<i class="fa fa-sign-in fa-rotate-270 font-green-seagreen"></i> <?php echo $LANG['UI_SETTINGS_EXPORT_IN']?> </a>
    			</li>
    		</ul>
    		<div class="tab-content">
    			<div class="tab-pane active" id="export_tab">
    			    <div class="row">
    			        <!-- BEGIN FORM-->
                        <form action="#" class="form-horizontal mt10">
                        	<div class="form-body">
                        		<div class="form-group pt50">
                        			<div class="col-md-offset-2 col-md-8">
                        			    <div class="alert alert-info">
            								<strong><?php echo $LANG['UI_SETTINGS_EXPORT_TIP']?></strong>:
            								<p><?php echo $LANG['UI_SETTINGS_EXPORT_OUT1']?></p>
            								<p><?php echo $LANG['UI_SETTINGS_EXPORT_OUT2']?></p>
            								<p><?php echo $LANG['UI_SETTINGS_EXPORT_OUT3']?></p>
            							</div>
                        			    <span class="btn green-haze fileinput-button" id="exportTaskData">
                                            <i class="fa fa-sign-out fa-rotate-90"></i>
                                            <span> <?php echo $LANG['UI_SETTINGS_EXPORT_OUT_DATA']?> </span>
                                        <input type="button"  > </span>
                        			</div>
                        		</div>
                        	</div>
                        </form>
                        <!-- END FORM-->
                	</div>
    			</div>
    			
    			<div class="tab-pane" id="import_tab">
    			    <div class="row">
    			        <!-- BEGIN FORM-->
                        <form action="#" class="form-horizontal mt10">
                        	<div class="form-body">
                        		<div class="form-group pt50">
                        			<div class="col-md-offset-2 col-md-8">
                        			    <div class="alert alert-info">
            								<strong><?php echo $LANG['UI_SETTINGS_EXPORT_TIP']?></strong>:
            								<p><?php echo $LANG['UI_SETTINGS_EXPORT_IN1']?></p>
            								<p><?php echo $LANG['UI_SETTINGS_EXPORT_IN2']?></p>
            								<p><?php echo $LANG['UI_SETTINGS_EXPORT_IN3']?></p>
            								<p><?php echo $LANG['UI_SETTINGS_EXPORT_IN4']?></p>
            								<p><?php echo $LANG['UI_SETTINGS_EXPORT_IN5']?></p>
            							</div>
                        			    <span class="btn green-haze fileinput-button">
                                            <i class="fa fa-sign-in fa-rotate-270"></i>
                                            <span> <?php echo $LANG['UI_SETTINGS_EXPORT_IN_DATA']?> </span>
                                        <input type="file" id="taskFileImport" name="files" multiple=""> </span>
                                        <span class="help-block "><?php echo $LANG['UI_SETTINGS_EXPORT_IN_DATA_TIPS']?></span>
                        			</div>
                        		</div>
                        	</div>
                        </form>
                        <!-- END FORM-->
    			    </div>
    			</div>
    		</div>
    	</div>
	</div>
</div>
<script src="./assets/global/plugins/jquery-file-upload/js/vendor/jquery.ui.widget.js"></script>
<script src="./assets/global/plugins/jquery-file-upload/js/jquery.fileupload.js"></script>