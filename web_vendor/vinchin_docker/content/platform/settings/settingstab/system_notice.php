<?php include_once '../../../../tpl/permission.php';?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<!-- <link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" /> -->
<!-- BEGIN FORM-->
<div class="row">
	<div class="col-md-12">
		 <div class="tabbable-custom mh510">
			<ul class="nav nav-tabs ">
				<li class="active">
					<a href="#emailtab" data-toggle="tab" aria-expanded="false">
					<i class="icon-envelope-open font-green-seagreen"></i> <?php echo $LANG['UI_SETTINGS_NOTICE_EMAIL']?> </a>
				</li>
				<!--<li class="" id= "smsDiv">
					<a href="#smstab" data-toggle="tab" aria-expanded="false">
					<i class="icon-speech font-green-seagreen"></i> <?php echo $LANG['UI_SETTINGS_NOTICE_SMS']?> </a>
				</li>-->
			</ul>
			<div class="tab-content">
				<div class="tab-pane active" id="emailtab">
					<div class="row">
					   <form action="#" class="form-horizontal mt10">
					      <div class="form-body">
        					<div class="form-group">
        						<label class="control-label col-md-3"><?php echo $LANG['UI_SETTINGS_NOTICE_EMAIL']?>:</label>
        						<div class="col-md-3">
        							<input type="checkbox" id="emailcheck" checked  class="make-switch" data-on-color="primary" data-off-color="info" 
        							data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>" 
        							data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
        							<div><span class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_EMAIL_POWER_ON_TIP']?></span></div>
        						</div>
        						<div class="col-md-6">
        						    <span class="help-block lh30">
                    					<a id="testemail"><?php echo $LANG['UI_SETTINGS_NOTICE_TEST_EMAIL_HOST']?></a>
                    				</span>
        						</div>
        					</div>
        					
        					<div class="emailcontent">
        					<div class="form-group">
        						<label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_ALARM_SYSTEM']?>:</label>
        						<div class="col-md-2">
        							<input type="checkbox" id="systemchecke" checked  class="make-switch" data-on-color="primary" data-off-color="info" 
        							data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>" 
        							data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
        						</div>
        					</div>
        					
            				<div class="form-group systemcheckediv">
        						<label class="control-label col-md-3"><?php echo $LANG['UI_SETTINGS_NOTICE_LEVEL_SYSTEM']?>:</label>
        						<div class="col-md-8 lh30">
        							<label class="checkbox-inline pl0">
            				            <input type="checkbox" class="icheck level" name="level1"> 
            				            <?php echo $LANG['UI_SETTINGS_NOTICE_ALARM_TIP1']?>
            			            </label><br>
            			            <label class="checkbox-inline pl0">
            				            <input type="checkbox" class="icheck level" name="level2"> 
            				            <?php echo $LANG['UI_SETTINGS_NOTICE_ALARM_TIP2']?>
            			            </label><br>
            			            <label class="checkbox-inline pl0">
            				            <input type="checkbox" class="icheck level" name="level3"> 
            				            <?php echo $LANG['UI_SETTINGS_NOTICE_ALARM_TIP3']?>
            			            </label><br>
        						</div>
            				</div>
            				
            				<div class="form-group">
        						<label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_ALARM_TASK']?>:</label>
        						<div class="col-md-2">
        							<input type="checkbox" id="taskchecke" checked  class="make-switch" data-on-color="primary" data-off-color="info" 
        							data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>" 
        							data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
        						</div>
        					</div>
        					
            				<div class="form-group taskcheckediv">
        						<label class="control-label col-md-3"><?php echo $LANG['UI_SETTINGS_NOTICE_LEVEL_TASK']?>:</label>
        						<div class="col-md-8 lh30">
        							<label class="checkbox-inline pl0">
            				            <input type="checkbox" class="icheck level" name="level1"> 
            				            <?php echo $LANG['UI_SETTINGS_NOTICE_ALARM_TIP1']?>
            			            </label><br>
            			            <label class="checkbox-inline pl0">
            				            <input type="checkbox" class="icheck level" name="level2"> 
            				            <?php echo $LANG['UI_SETTINGS_NOTICE_ALARM_TIP2']?>
            			            </label><br>
            			            <label class="checkbox-inline pl0">
            				            <input type="checkbox" class="icheck level" name="level3"> 
            				            <?php echo $LANG['UI_SETTINGS_NOTICE_ALARM_TIP3']?>
            			            </label><br>
        						</div>
            				</div>
            				
            				<!-- 报表邮件通知 -->
            				<div class="form-group">
        						<label class="control-label col-md-3"><?php echo $LANG['UI_REPORT_NOTICE']?>:</label>
        						<div class="col-md-2">
        							<input type="checkbox" id="reportCheck"   class="make-switch" data-on-color="primary" data-off-color="info" 
        							data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>" 
        							data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
        						</div>
        					</div>
        					
        					<div class="form-group reportcheckediv display-none">
        						<label class="control-label col-md-3"><?php echo $LANG['UI_REPORT_TYPE']?>:</label>
        						<div class="col-md-8 lh30">
        							<label class="checkbox-inline pl0">
            				            <input type="checkbox" class="icheck level" id="storageReport"> 
            				            	<?php echo $LANG['UI_REPORT_STORAGE']?>
            			            </label><br>
            			            <label class="checkbox-inline pl0">
            				            <input type="checkbox" class="icheck level" id="vmReport"> 
            				            	<?php echo $LANG['UI_REPORT_VM']?>
            			            </label><br>
        						</div>
            				</div>
            				
            				<div class="form-group reportcheckediv display-none">
        						<label class="control-label col-md-3"><?php echo $LANG['UI_STRATEGY_TIME']?>:</label>
        						<div class="col-md-8 lh30" style="border: 1px solid #e0e0e0;padding-top:5px;margin-left: 15px;">
									<div class="row">
        								<div class="col-md-2 col-sm-2 col-xs-2">
        									<ul class="nav nav-tabs tabs-left">
        										<li class="dwm active" data-type="1">
        											<a href="#tab_day" data-toggle="tab" aria-expanded="true">
	        											<?php echo $LANG['UI_REPORT_DAILY']?><i class="glyphicon glyphicon-ok font-green-seagreen" id="dayIcon" style="margin-left:10px;display:none;"></i>
	        										</a>
	        									</li>
	        									<li class="dwm" data-type="2">
	        										<a href="#tab_week" data-toggle="tab" aria-expanded="true">
	        											<?php echo $LANG['UI_REPORT_WEEKLY']?> <i class="glyphicon glyphicon-ok font-green-seagreen " id="weekIcon" style="margin-left:10px;display:none;"></i>
	        										</a>
	        									</li>
	        									<li class="dwm" data-type="3">
	        										<a href="#tab_month" data-toggle="tab" aria-expanded="true">
	        											<?php echo $LANG['UI_REPORT_MONTHLY']?> <i class="glyphicon glyphicon-ok font-green-seagreen " id="monthIcon" style="margin-left:10px;display:none;"></i>
	        										</a>
	        									</li>
	        									
	        									<li class="dwm" data-type="3">
	        										<a href="#tab_year" data-toggle="tab" aria-expanded="true">
	        											<?php echo $LANG['UI_REPORT_ANNALS']?> <i class="glyphicon glyphicon-ok font-green-seagreen " id="yearIcon" style="margin-left:10px;display:none;"></i>
	        										</a>
	        									</li>
	        								</ul>
        								</div>
        								<div class="col-md-9 col-sm-9 col-xs-9">
        									<div class="tab-content">
	        									<div id="tab_day" class="tab-pane fade in active">
	        										<div class="form-group">
						        						<div class="col-md-4">
						        							<label class="control-label" style="width: 80px;text-align: left;"><?php echo $LANG['UI_REPORT_CONFIG_DAILY']?></label>
						        							<input type="checkbox" id="dayCheck"  class="make-switch" data-on-color="primary" data-off-color="info" 
						        							data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>" 
						        							data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
						        						</div>
						        					</div>
						        					<div id="dayDiv" class="display-none">
						        					
						        					</div>
	        									</div>
	        									
	        									<div id="tab_week" class="tab-pane fade in ">
	        										<div class="form-group">
						        						<div class="col-md-4">
						        							<label class="control-label" style="width: 80px;text-align: left;"><?php echo $LANG['UI_REPORT_CONFIG_WEEKLY']?></label>
						        							<input type="checkbox" id="weekCheck"  class="make-switch" data-on-color="primary" data-off-color="info" 
						        							data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>" 
						        							data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
						        						</div>
						        					</div>
						        					<div id="weekDiv" class="display-none">
						        					
						        					</div>
	        									</div>
	        									<div id="tab_month" class="tab-pane fade in ">
	        										<div class="form-group">
						        						<div class="col-md-4">
						        							<label class="control-label" style="width: 80px;text-align: left;"><?php echo $LANG['UI_REPORT_CONFIG_MONTHLY']?></label>
						        							<input type="checkbox" id="monthCheck"  class="make-switch" data-on-color="primary" data-off-color="info" 
						        							data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>" 
						        							data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
						        						</div>
						        					</div>
						        					<div id="monthDiv" class="display-none">
						        					
						        					</div>
	        									</div>
	        									<div id="tab_year" class="tab-pane fade in ">
	        										<div class="form-group">
						        						<div class="col-md-4">
						        							<label class="control-label"><?php echo $LANG['UI_REPORT_CONFIG_ANNALS']?></label>
						        							<input type="checkbox" id="yearCheck"  class="make-switch" data-on-color="primary" data-off-color="info" 
						        							data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>" 
						        							data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
						        						</div>
						        					</div>
						        					<div id="yearDiv" class="display-none">
						        						<div class="input-group date form_datetime" style="width: 300px;">
						    								<input type="text" size="16" readonly id="yearTime" class="form-control">
						    								<span class="input-group-btn">
						    									<button class="btn default" id="resetStartTime" type="button"><i class="fa fa-times"></i></button>
							    								<button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
						    								</span>
						    							</div>
						        					</div>
	        									</div>
		        							</div>
										</div>
									</div>
        						</div>
            				</div>
        					
        					
        					<div class="form-group emailtimediv display-none">
        						<label class="control-label col-md-2 lh30 textalignr"><?php echo $LANG['UI_SETTINGS_NOTICE_TIME']?>:</label>
        						<div class="col-md-2">
        							<div class="input-group">
        								<input type="text" class="form-control timepicker timepicker-24 emailstarttime">
        								<span class="input-group-btn">
        								<button class="btn default" type="button"><i class="fa fa-clock-o"></i></button>
        								</span>
        							</div>
        						</div>
        						<label class="control-label col-md-1 lh30 textalignc"><?php echo $LANG['UI_SETTINGS_NOTICE_TO']?></label>
        						<div class="col-md-2">
        							<div class="input-group">
        								<input type="text" class="form-control timepicker timepicker-24 emailendtime">
        								<span class="input-group-btn">
        								<button class="btn default" type="button"><i class="fa fa-clock-o"></i></button>
        								</span>
        							</div>
        						</div>
        						
        					</div>
        					
        					<div class="form-group recemaildiv">
                    			<label class="control-label col-md-3"><?php echo $LANG['UI_SETTINGS_NOTICE_RECV_EMAIL']?>
                    			</label>
                    			<div class="col-md-4">
                    				<textarea class="form-control" id="emaillist" rows="4" placeholder="example@example.com"></textarea>
                    				<div><span class="help-block ">
                    					<?php echo $LANG['UI_SETTINGS_NOTICE_RECV_EMAIL_TIP1']?><br>
                    					<?php echo $LANG['UI_SETTINGS_NOTICE_RECV_EMAIL_TIP2']?>
                    				</span></div>
                    			</div>
                    		</div>
        					
        					</div>
        					
        					<div class="form-actions">
                				<div class="row">
                        			<div class="col-md-offset-3 col-md-8 pt30">
                        				<button type="button" id="emailcancel" class="btn default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
                        				<button type="button" id="emailsubmit" class="btn green-haze"><?php echo $LANG['UI_PUBLIC_YES']?></button>
                        			</div>
                        		</div>
                    		</div>
                    	  </div>
                    	</form>
				    </div>
				</div>
				<div class="tab-pane" id="smstab">
				    <div class="row">
					   <form action="#" class="form-horizontal mt10">
					      <div class="form-body">
        					<div class="form-group">
        						<label class="control-label col-md-3"><?php echo $LANG['UI_SETTINGS_NOTICE_SMS']?>:</label>
        						<div class="col-md-3">
        							<input type="checkbox" id="smscheck" checked  class="make-switch" data-on-color="primary" data-off-color="info" 
        							data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>" 
        							data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
        							<div><span class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_POWER_ON_TIP']?></span></div>
        						</div>
        						<div class="col-md-6">
        						    <span class="help-block lh30">
                    					<a id="testsms"><?php echo $LANG['UI_SETTINGS_NOTICE_TEST_SMS']?></a>
                    				</span>
        						</div>
        						
        					</div>
        					
        					<div class="smscontent">
        					<div class="form-group">
        						<label class="control-label col-md-3"><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_SENDTYPE']?>:</label>
        						<div class="col-md-3">
        							<div class="margintop10"><label id="smssendtypedes"><i class="fa fa-globe font-green-seagreen"></i> <?php echo $LANG['UI_SETTINGS_NOTICE_SMS_INTERNET']?></label></div>
        							<div><span class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_SENDTYPE_TIPS']?></span></div>
        						</div>
        					</div>
        					
        					<div class="form-group">
        						<label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_ALARM_SYSTEM']?>:</label>
        						<div class="col-md-2">
        							<input type="checkbox" id="systemchecks" checked  class="make-switch" data-on-color="primary" data-off-color="info" 
        							data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>" 
        							data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
        						</div>
        					</div>
        					
            				<div class="form-group systemchecksdiv">
        						<label class="control-label col-md-3"><?php echo $LANG['UI_SETTINGS_NOTICE_LEVEL_SYSTEM']?>:</label>
        						<div class="col-md-8 lh30">
        							<label class="checkbox-inline pl0">
            				            <input type="checkbox" class="icheck level" name="level1"> 
            				            <?php echo $LANG['UI_SETTINGS_NOTICE_ALARM_TIP1']?>
            			            </label><br>
            			            <label class="checkbox-inline pl0">
            				            <input type="checkbox" class="icheck level" name="level2"> 
            				            <?php echo $LANG['UI_SETTINGS_NOTICE_ALARM_TIP2']?>
            			            </label><br>
            			            <label class="checkbox-inline pl0">
            				            <input type="checkbox" class="icheck level" name="level3"> 
            				            <?php echo $LANG['UI_SETTINGS_NOTICE_ALARM_TIP3']?>
            			            </label><br>
        						</div>
            				</div>
            				
            				<div class="form-group">
        						<label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_ALARM_TASK']?>:</label>
        						<div class="col-md-2">
        							<input type="checkbox" id="taskchecks" checked  class="make-switch" data-on-color="primary" data-off-color="info" 
        							data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>" 
        							data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
        						</div>
        					</div>
        					
            				<div class="form-group taskchecksdiv">
        						<label class="control-label col-md-3"><?php echo $LANG['UI_SETTINGS_NOTICE_LEVEL_TASK']?>:</label>
        						<div class="col-md-8 lh30">
        							<label class="checkbox-inline pl0">
            				            <input type="checkbox" class="icheck level" name="level1"> 
            				            <?php echo $LANG['UI_SETTINGS_NOTICE_ALARM_TIP1']?>
            			            </label><br>
            			            <label class="checkbox-inline pl0">
            				            <input type="checkbox" class="icheck level" name="level2"> 
            				            <?php echo $LANG['UI_SETTINGS_NOTICE_ALARM_TIP2']?>
            			            </label><br>
            			            <label class="checkbox-inline pl0">
            				            <input type="checkbox" class="icheck level" name="level3"> 
            				            <?php echo $LANG['UI_SETTINGS_NOTICE_ALARM_TIP3']?>
            			            </label><br>
        						</div>
            				</div>
            				</div>
        					
        					<div class="form-group cmstimediv display-none">
        						<label class="control-label col-md-2 lh30 textalignr"><?php echo $LANG['UI_SETTINGS_NOTICE_TIME']?>:</label>
        						<div class="col-md-2">
        							<div class="input-group">
        								<input type="text" class="form-control timepicker timepicker-24 emailstarttime">
        								<span class="input-group-btn">
        								<button class="btn default" type="button"><i class="fa fa-clock-o"></i></button>
        								</span>
        							</div>
        						</div>
        						<label class="control-label col-md-1 lh30 textalignc"><?php echo $LANG['UI_SETTINGS_NOTICE_TO']?></label>
        						<div class="col-md-2">
        							<div class="input-group">
        								<input type="text" class="form-control timepicker timepicker-24 emailendtime">
        								<span class="input-group-btn">
        								<button class="btn default" type="button"><i class="fa fa-clock-o"></i></button>
        								</span>
        							</div>
        						</div>
        						
        					</div>
        					<div class="form-actions">
                				<div class="row">
                        			<div class="col-md-offset-3 col-md-8 pt30">
                        				<button type="button" id="smscancel" class="btn default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
                        				<button type="button" id="smssubmit" class="btn green-haze"><?php echo $LANG['UI_PUBLIC_YES']?></button>
                        			</div>
                        		</div>
                    		</div>
                    	  </div>
                    	</form>
				    </div>
				</div>
			</div>
		</div>
        
        <!-- BEGIN EMAIL MODAL -->
		<div id="testemaildiv" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="icon-envelope-open font-green-seagreen"></i> <?php echo $LANG['UI_SETTINGS_NOTICE_TEST_EMAIL_HOST']?></h4>
			</div>
			<div class="modal-body">
			    <form action="#" id="testemailform" class="form-horizontal">
	                <div class="form-body">
        				<div class="form-group">
        			        <label class="col-md-3 control-label"><?php echo $LANG['UI_SETTINGS_NOTICE_SMTP']?><span class="required"> * </span></label>
                        	<div class="col-md-8">
                        		<div class="input-icon right ">
									<i class="fa"></i>
									<input type="text" class="form-control" maxlength="128" name="emailhost">
									<div><span class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_SMTP_TIP']?> </span></div>
								</div>
                        	</div>
                        </div>
                        <div class="form-group">
        			        <label class="col-md-3 control-label"><?php echo $LANG['UI_SETTINGS_NOTICE_PORT']?><span class="required"> * </span></label>
                        	<div class="col-md-8">
                        		<div class="input-icon right ">
									<i class="fa"></i>
									<input type="text" class="form-control" maxlength="6" name="emailport">
									<div><span class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_PORT_TIP']?> </span></div>
								</div>
                        	</div>
                        </div>
                        <div class="form-group">
        			        <label class="col-md-3 control-label"><?php echo $LANG['UI_SETTINGS_NOTICE_EMAIL_FROM']?><span class="required"> * </span></label>
                        	<div class="col-md-8">
                        	    <div class="input-icon right ">
									<i class="fa"></i>
									<input type="text" class="form-control" maxlength="128" name="emailuser">
									<div><span class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_EMAIL_FROM_TIP']?> </span></div>
								</div>
                        	</div>
                        </div>
                        <div class="form-group">
        			        <label class="col-md-3 control-label"><?php echo $LANG['UI_SETTINGS_NOTICE_EMAIL_FROM_PASS']?></label>
                        	<div class="col-md-8">
                        		<div class="input-icon right ">
									<i class="fa"></i>
									<input type="password" class="form-control" maxlength="64" name="emailpass">
									<div><span class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_EMAIL_FROM_PASS_TIP']?> </span></div>
								</div>
                        	</div>
                        </div>
                        <div class="form-group">
                			<label class="control-label col-md-3"><?php echo $LANG['UI_SETTINGS_NOTICE_EMAIL_ENCRYPTION_CONNECTION']?><span class="required">
                			* </span>
                			</label>
                			<div class="col-md-8">
                				<select class="form-control select2me" name="encryption">
                				    <option value="0"><?php echo $LANG['WEB_PLATFORM_PUBLIC_NONE']?></option>
                				    <option value="1">SSL</option>
                				    <option value="2">TLS</option>
                				</select>
                				<div><span class="help-block ">
                					<?php echo $LANG['UI_SETTINGS_NOTICE_EMAIL_ENCRYPTION_CONNECTION_TIPS']?>
                				</span></div>
                			</div>
                		</div>
                        <div class="form-group">
                        	<div class="col-md-offset-3 col-md-8">
                        		<button type="button"  class="btn btn-default textalignr" id="sendtestmail"><?php echo $LANG['UI_SETTINGS_NOTICE_EMAIL_SEND']?></button>
                        		<label class="control-label lh30 textalignc"><?php echo $LANG['UI_SETTINGS_NOTICE_TO']?></label>
                        		<label class="control-label lh30 textalignc" id="testrecemail"></label>
                        	</div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-offset-3 col-md-8">
                                <div class="alert alert-block alert-info fade in" id="tabletips">
    								<button type="button" class="close" data-dismiss="alert"></button>
                    				<p><?php echo $LANG['UI_SETTINGS_NOTICE_EMAIL_SEND_TIP1']?></p>
                    				<p><?php echo $LANG['UI_SETTINGS_NOTICE_EMAIL_SEND_TIP2']?></p>
    							</div>
                            </div>
                        </div>
        			</div>
        		</form>
			</div>
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
				<button type="button" class="btn btn-primary" id="testemailsubmit"><?php echo $LANG['UI_PUBLIC_SAVE']?></button>
			</div>
		</div>	
		<!-- END EMAIL MODAL -->
		
		<!-- BEGIN SMS MODAL -->
		<div id="testsmsdiv" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="icon-speech font-green-seagreen"></i> <?php echo $LANG['UI_SETTINGS_NOTICE_TEST_SMS']?></h4>
			</div>
			<div class="modal-body">
    			<div class="tabbable-custom">
    			<ul class="nav nav-tabs ">
    				<li class="active">
    					<a href="#internettab" data-toggle="tab" aria-expanded="false" id="sendtypeinternet">
    					<i class="fa fa-globe font-green-seagreen"></i> <?php echo $LANG['UI_SETTINGS_NOTICE_SMS_INTERNET']?> </a>
    				</li>
    				<li class="">
    					<a href="#smsmodemtab" data-toggle="tab" aria-expanded="false" id="sendtypemodem">
    					<i class="iconfont icon-modem font-green-seagreen"></i> <?php echo $LANG['UI_SETTINGS_NOTICE_SMS_MODEM']?> </a>
    				</li>
    			</ul>
    			<div class="tab-content">
    				<div class="tab-pane active" id="internettab">
    				    <div class="form-group">
                        	<div class="col-md-12">
                        		<div class="alert alert-block alert-info fade in" id="step1tips">
            						<button type="button" class="close" data-dismiss="alert"></button>
            						<h4 class="alert-heading"><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_TIPS']?>：</h4>
            						<p><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_TIPS1']?></p>
            						<p><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_TIPS2']?></p>
            						<p><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_TIPS3']?></p>
            						<p><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_TIPS4']?></p>
            						<p><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_TIPS5']?></p>
            					</div>
                        	</div>
                        </div>
                        <div class="form-group">
        			        <label class="col-md-3 control-label"> <?php echo $LANG['UI_SETTINGS_NOTICE_SMS_QUANTITY']?>: </label>
        			        <label class="col-md-1 control-label" id="smsquantity">  </label>
                        </div>
                        <div class="form-group">
                        	<div class="col-md-8">
                        		<button type="button"  class="btn btn-default textalignr" id="sendtestsms"><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_SEND']?></button>
                        		<label class="control-label lh30 textalignc"><?php echo $LANG['UI_SETTINGS_NOTICE_TO']?></label>
                        		<label class="control-label lh30 textalignc testrecphone" id="testrecphone"></label>
                        	</div>
                        </div>
    				</div>
    				<div class="tab-pane" id="smsmodemtab">
    				    <div class="form-group">
                        	<div class="col-md-12">
                        		<div class="alert alert-block alert-info fade in" id="step1tips">
            						<button type="button" class="close" data-dismiss="alert"></button>
            						<h4 class="alert-heading"><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_MODEM_TIPS']?></h4>
            						<p><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_MODEM_TIPS1']?></p>
            						<p><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_MODEM_TIPS2']?></p>
            						<p><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_MODEM_TIPS3']?></p>
            					</div>
                        	</div>
                        	<form action="#" id="setmodem" class="form-horizontal mh520">
                            	<div class="form-body">
                            		<div class="form-group">
                            			<label class="control-label col-md-4"><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_MODEM_IP']?> <span class="required">
                            			* </span>
                            			</label>
                            			<div class="col-md-6">
                            				<div class="input-icon right">
                            					<i class="fa"></i>
                            					<input type="text" maxlength="128" class="form-control" name="modemipaddr"/>
                            					<div><span class="help-block "><?php echo $LANG['UI_SETTINGS_IP_TIP']?></span></div>
                            				</div>
                            			</div>
                            		</div>
                            		
                            		<div class="form-group">
                            			<label class="control-label col-md-4"><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_MODEM_DATABASE']?><span class="required">
                            			* </span>
                            			</label>
                            			<div class="col-md-6">
                            				<div class="input-icon right">
                            					<i class="fa"></i>
                            					<input style="display:none">
                            					<input type="text" maxlength="128" class="form-control" name="modemdatabase"/>
                            					<div><span class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_MODEM_DATABASE_TIPS']?></span></div>
                            				</div>
                            			</div>
                            		</div>
                            		<div class="form-group">
                            			<label class="control-label col-md-4"><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_MODEM_PORT']?><span class="required">
                            			* </span>
                            			</label>
                            			<div class="col-md-6">
                            				<div class="input-icon right">
                            					<i class="fa"></i>
                            					<input style="display:none"><!-- for disable autocomplete on chrome -->
                            					<input type="text" maxlength="1280" class="form-control" name="modemport"/>
                            					<div><span class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_MODEM_PORT_TIPS']?></span></div>
                            				</div>
                            			</div>
                            		</div>
                            		<div class="form-group">
                            			<label class="control-label col-md-4"><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_MODEM_USER']?><span class="required">
                            			* </span>
                            			</label>
                            			<div class="col-md-6">
                            				<div class="input-icon right ">
                            					<i class="fa"></i>
                            					<input type="text" maxlength="64" class="form-control" name="modemusername"/>
                            					<div><span class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_MODEM_USER_TIPS']?></span></div>
                            				</div>
                            			</div>
                            		</div>
                            		<div class="form-group">
                            			<label class="control-label col-md-4"><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_MODEM_PASS']?><span class="required">
                            			* </span>
                            			</label>
                            			<div class="col-md-6">
                            				<div class="input-icon right ">
                            					<i class="fa"></i>
                            					<input type="password" maxlength="64" class="form-control" name="modempassword"/>
                            					<div><span class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_MODEM_PASS_TIPS']?></span></div>
                            				</div>
                            			</div>
                            		</div>
                            		<div class="form-group">
                                    	<div class="col-md-offset-4 col-md-8">
                                    		<button type="button"  class="btn btn-default textalignr" id="sendmodemsms"><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_SEND']?></button>
                                    		<label class="control-label lh30 textalignc"><?php echo $LANG['UI_SETTINGS_NOTICE_TO']?></label>
                                    		<label class="control-label lh30 textalignc testrecphone" id="modemrecphone"></label>
                                    	</div>
                                    </div>
                            		
                            	</div>
                            </form>
                        </div>
    				</div>
    			</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_CLOSE']?></button>
			</div>
		</div>	
		<!-- END SMS MODAL -->
	</div>
</div>
<!-- END FORM-->
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js" ></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script src="./scripts/libs/base64.min.js" type="text/javascript"></script>
