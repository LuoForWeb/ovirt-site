<?php include_once '../../../../tpl/permission.php';?>
<link href="./assets/global/plugins/jquery-file-upload/css/jquery.fileupload.css" rel="stylesheet"/>
<div class="row">
	<div class="col-md-12">
	    <div class="tabbable-custom mh510">
    		<ul class="nav nav-tabs ">
    			<li class="active">
    				<a href="#systemname_tab" data-toggle="tab" aria-expanded="false">
    				<i class="fa fa-institution font-green-seagreen"></i> <?php echo $LANG['UI_SETTINGS_DIY_SYSTEM_NAME_SET']?></a>
    			</li>
    			<li class="">
    				<a href="#logo_tab" data-toggle="tab" aria-expanded="false">
    				<i class="fa fa-picture-o font-green-seagreen"></i> <?php echo $LANG['UI_SETTINGS_DIY_SYSTEM_LOGO_SET']?> </a>
    			</li>
    		</ul>
    		<div class="tab-content">
    			<div class="tab-pane active" id="systemname_tab">
    			    <div class="row">
    			        <!-- BEGIN FORM-->
                        <form action="#" id="setsystemname" class="form-horizontal mt10">
                        	<div class="form-body">
                        		<div class="form-group pt50">
                        			<label class="control-label col-md-3"><?php echo $LANG['UI_SETTINGS_DIY_SET_SYSTEM_NAME']?> <span class="required">
                        			* </span>
                        			</label>
                        			<div class="col-md-4">
                        				<div class="input-icon right">
                        					<i class="fa"></i>
                        					<input type="text" maxlength="128" class="form-control" name="systemname"/>
                        					<div><span class="help-block "><?php echo $LANG['UI_SETTINGS_DIY_SYSTEM_NAME_TIP']?></span></div>
                        				</div>
                        			</div>
                        		</div>
                        		
                        	</div>
                        	<div class="form-actions pt50">
                        		<div class="row">
                        			<div class="col-md-offset-3 col-md-4">
                        				<button type="button" id="systemnamecancel" class="btn default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
                        				<button type="button" id="systemnamesubmit" class="btn green-haze"><?php echo $LANG['UI_PUBLIC_YES']?></button>
                        			</div>
                        		</div>
                        	</div>
                        </form>
                        <!-- END FORM-->
                	</div>
    			</div>
    			
    			<div class="tab-pane" id="logo_tab">
    			    <div class="row">
    			        <div class="col-md-12 mt10">
        					<div class="form-group">
        						<div class="col-md-12 mt10">
                					<div class="form-group">
                						<label class="control-label col-md-2 lh30 textalignr"><?php echo $LANG['UI_SETTINGS_DIY_CURRENT_LOGO']?>:</label>
                						<div class="col-md-9">
                							<div class="page-logo head-color pding30">
                                    			<img src="./img/platform/logo.png" alt="logo" class="logo-default logosize">
                                    		</div>
                						</div>
                					</div>
                				</div>
                				<div class="col-md-12 mt10 display-none" id="newlogodiv">
                					<div class="form-group">
                						<label class="control-label col-md-2 lh30 textalignr"><?php echo $LANG['UI_SETTINGS_DIY_UPLOAD_LOGO']?>:</label>
                						<div class="col-md-9">
                							<div class="page-logo head-color pding30">
                                    			<img src="" id="newlogo" alt="logo" class="logo-default logosize">
                                    		</div>
                						</div>
                					</div>
                				</div>
        					</div>
        					<div class="form-actions">
                				<div class="row">
                        			<div class="col-md-offset-2 col-md-4 pding30 uploadbtndiv">
        								<span class="btn green-haze fileinput-button">
                                            <i class="viconfont vicon-ge_add_task"></i>
                                            <span> <?php echo $LANG['UI_SETTINGS_DIY_SELECT_LOGO']?> </span>
                                        <input type="file" id="fileselect" name="files" multiple=""> </span>
                                        <span class="help-block "><?php echo $LANG['UI_SETTINGS_DIY_LOGO_TIP']?></span>
                        			</div>
                        			<div class="col-md-offset-2 col-md-4 pding30 display-none savebtndiv">
                                        <button type="button" id="cancellogo" class="btn green-haze">
                        				 <?php echo $LANG['UI_PUBLIC_NO']?></button>
                                        <button type="button" id="savelogo" class="btn green-haze">
                        				<i class="fa fa-save"></i> <?php echo $LANG['UI_SETTINGS_DIY_SAVE_LOGO']?></button>
                        			</div>
                        		</div>
                    		</div>
                            
        				</div>
    			    </div>
    			</div>
    		</div>
    	</div>
	</div>
</div>
<script src="./assets/global/plugins/jquery-file-upload/js/vendor/jquery.ui.widget.js"></script>
<script src="./assets/global/plugins/jquery-file-upload/js/jquery.fileupload.js"></script>