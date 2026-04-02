<?php include_once '../../../tpl/permission.php';?>
<div class="col-md-6 highSettingsDiv">
			    <div class="portlet box blue-hoki" style="height: 420px;overflow:hidden;position:relative;">
        			<div class="portlet-title">
        				<div class="caption">
        					<i class="iconfont icon-gaojipeizhi"></i><?php echo $LANG['UI_USER_HIGH_SETTING']?>
        				</div>
        			</div>
        			<div class="portlet-body">
        				<ul class="nav nav-tabs " style="margin: 0;">
                			<li id="baseinfotab"class="active">
                				<a href="#baseinfo_tab" data-toggle="tab" aria-expanded="false">
                				<i class="iconfont icon-tongyongcelve "></i> <?php echo $LANG['UI_TENANT_COMMON']?></a>
                			</li>
                			<!-- <li class="" id="backuptab">
                				<a href="#backup_tab" data-toggle="tab" aria-expanded="false">
                				<i class="iconfont icon-copyback font-green-seagreen"></i> <?php echo $LANG['UI_PLATFORM_BACKUP']?> </a>
                			</li> -->
                			<li class="" id="recovertab">
                				<a href="#recover_tab" data-toggle="tab" aria-expanded="false">
                				<i class="iconfont icon-copyoffsite "></i> <?php echo $LANG['UI_PLATFORM_RECOVER']?> </a>
                			</li>
                			<li class="" id="billingtab">
                				<a href="#billing_tab" data-toggle="tab" aria-expanded="false">
                				<i class="iconfont icon-feiyong "></i> <?php echo $LANG['UI_TENANT_BILLING']?> </a>
                			</li>
                			
                		</ul>
                		<div class="tab-content " style="padding-top:10px;height:320px;overflow-x:hidden;overflow-y:auto;">
                			<div class="tab-pane active" id="baseinfo_tab">
                                <div class="row static-info">
                                	<div class="col-md-4 textalignr mt5">
                						<?php echo $LANG['UI_PLATFORM_TENANT_AUTOR_WAY']?>:
                					</div>
                                	<div class="col-md-6 " >
                                		<select class="form-control select2me input-sm" id="authSelect">
                                			<option value="1"><?php echo $LANG['UI_PLATFORM_TENANT_AUTHOR_BY_CONTAINER']?></option>
                                			<option value="2"><?php echo $LANG['UI_PLATFORM_TENANT_AUTHOR_BY_NUM']?></option>
										</select>
                                	</div>
                                </div>
                                <div class="row static-info quotaInfoDiv">
                                	<div class="col-md-4 textalignr mt5">
                						<?php echo $LANG['UI_TENANT_QUOTA']?>:
                					</div>
                                	<div class="col-md-6 " >
                                		<select class="form-control select2me input-sm" id="quotaSelect">
                                			<option value="0"><?php echo $LANG['UI_TENANT_QUOTA_UNLIMITED']?></option>
                                			<option value="1"><?php echo $LANG['UI_TENANT_QUOTA_APPOINT']?></option>
										</select>
                                	</div>
                                </div>
                                <div class="row static-info display-none quotaDiv">
                                	<div class="col-md-4 textalignr mt5">
                						<?php echo $LANG['UI_TENANT_QUOTA_SIZE']?>:
                					</div>
                                	<div class="col-md-6 ">
                                		<div id="custom" style="display:inline-flex;">
        								    <div class="input-icon width130" >
        										<div id="spinnerNum">
        				                        	<div class="input-group " >
        					                        	<input type="text" id="quotaInput" style="text-align: center;" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" maxlength="3">
        					                        	<div class="spinner-buttons input-group-btn">
        					                        		<button type="button" class="btn spinner-up default input-sm">
        					                        			<i class="fa fa-angle-up"></i>
        					                        		</button>
        					                        		<button type="button" class="btn spinner-down default input-sm">
        					                        			<i class="fa fa-angle-down"></i>
        					                        		</button>
        					                        	</div>
        				                        	</div>
        			                        	</div>
        									</div>
        									<div class="">
        										<select id="quotaUnit" class="input-sm" style="margin-left: 10px;">
        										    <option value="MB">MB</option>
        										    <option value="GB">GB</option>
        										    <option value="TB">TB</option>
        										    <option value="PB">PB</option>
        									    </select>
        								    </div>
        								    <div class="maxNum">
        								    	<span id="maxNum" style="color: #737373;"></span>
        								    </div>
        								</div>
                                	</div>
                                </div>
                                <div class="row static-info display-none authNumDiv">
									<div class="col-md-4 textalignr mt5">
                						<?php echo $LANG['UI_VCENTER_MACHINE']?>:
                					</div>
									<div class="col-md-3">
										<div id="vmNumDiv">
											<div class="input-group" >
												<input type="text" id="vmNumInput" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control" maxlength="3">
												<div class="spinner-buttons input-group-btn">
													<button type="button" class="btn spinner-up default">
													<i class="fa fa-angle-up"></i>
													</button>
													<button type="button" class="btn spinner-down default">
													<i class="fa fa-angle-down"></i>
													</button>
												</div>
											</div>
										</div>
									</div>
								</div>
								
								<div class="row static-info display-none authNumDiv">
									<div class="col-md-4 textalignr mt5">
                						<?php echo $LANG['UI_PLATFORM_TENANT_FILE_AGENT']?>:
                					</div>
									<div class="col-md-3">
										<div id="fsNumDiv">
											<div class="input-group" >
												<input type="text" id="fsNumInput" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control" maxlength="3">
												<div class="spinner-buttons input-group-btn">
													<button type="button" class="btn spinner-up default">
													<i class="fa fa-angle-up"></i>
													</button>
													<button type="button" class="btn spinner-down default">
													<i class="fa fa-angle-down"></i>
													</button>
												</div>
											</div>
										</div>
									</div>
								</div>
								
								<div class="row static-info display-none authNumDiv">
									<div class="col-md-4 textalignr mt5">
                						<?php echo $LANG['UI_PLATFORM_TENANT_DATABASE_AGENT']?>:
                					</div>
									<div class="col-md-3">
										<div id="dbNumDiv">
											<div class="input-group" >
												<input type="text" id="dbNumInput" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control" maxlength="3">
												<div class="spinner-buttons input-group-btn">
													<button type="button" class="btn spinner-up default">
													<i class="fa fa-angle-up"></i>
													</button>
													<button type="button" class="btn spinner-down default">
													<i class="fa fa-angle-down"></i>
													</button>
												</div>
											</div>
										</div>
									</div>
								</div>
                                
                                <!-- <div class="row static-info display-none">
                                	<div class="col-md-4 textalignr mt5">
                						 <?php echo $LANG['UI_TENANT_SPEED_LIMIT']?>:
                					</div>
                                	<div class="col-md-6" >
                                		<input type="checkbox" id="speedlimitCheck" class="make-switch" data-on-color="primary" data-off-color="info" data-size="small"
										data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>" 
										data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
                                	</div>
                                </div>
                                <div class="row static-info display-none speedDiv">
                                	<div class="col-md-4 textalignr mt5">
                						 <?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_VALUE']?>:
                					</div>
                                	<div class="col-md-6" >
                                		<div id="rateDiv" style="display:inline-flex;">
            								<div class="input-icon" >
            									<div id="speedSpinnerNum" >
            										<div class="input-group" >
            											<input type="text" id="speedSpinnerNumInput" style="text-align: center;" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm">
            											<div class="spinner-buttons input-group-btn">
            												<button type="button" class="btn spinner-up default input-sm">
            													<i class="fa fa-angle-up"></i>
            												</button>
            												<button type="button" class="btn spinner-down default input-sm">
            													<i class="fa fa-angle-down"></i>
            												</button>
            											</div>
            										</div>
            									</div>
            								</div>
            								<div class="">
            									<select id="unit" class="input-sm" style="margin-left: 10px;">
            										<option value="KB/s">KB/s</option>
            										<option selected value="MB/s">MB/s</option>
            										<option value="GB/s">GB/s</option>
            									</select>
            								</div>
            							</div>
                                	</div>
                                </div> -->
                                
                                <div class="row static-info">
                                	<div class="col-md-4 textalignr mt5">
                						 <?php echo $LANG['UI_PLATFORM_TENANT_MANAGE_BAKDATA']?>:
                					</div>
                                	<div class="col-md-6" >
                                		<input type="checkbox" id="manageDataCheck" class="make-switch" data-on-color="primary" data-off-color="info" data-size="small"
										data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>" 
										data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
										<a class="popovers ml15" data-container="body" data-trigger="hover" data-placement="right" data-html="true" 
										data-content="<?php echo $LANG['UI_PLATFORM_TENANT_MANAGE_BAKDATA_AFTEROPEN']?>"><i class="fa fa-info-circle fa-lg"></i>
										</a>
                                	</div>
                                </div>
                                
                                <div class="row static-info">
                                	<button type="button" id="common_submit" class="btn btn-sm green-haze" style="position:relative; left:35%; margin-top: 20px;">
        							<i class="viconfont vicon-ge_modify"></i> <?php echo $LANG['UI_PUBLIC_SAVE_CONFIG']?>
        							</button>
                                	
                                </div>
                			</div>
                			
                			<div class="tab-pane" id="recover_tab">
                				<div class="row static-info">
                                	<div class="col-md-4 textalignr mt5">
                						 <?php echo $LANG['UI_TENANT_TARGET_HOST']?>:
                					</div>
                                	<div class="col-md-6">
                                		<select class="form-control select2me input-sm" id="hostType" >
                                			<option value=""></option>
                                			<option value="0"><?php echo $LANG['UI_TENANT_ALL_HOST']?></option>
                                			<option value="1"><?php echo $LANG['UI_TENANT_APPOINT_HOST']?></option>
                                			<!-- <option value="2"><?php echo $LANG['UI_TENANT_HIDE_AND_APPOINT_HOST']?></option> -->
										</select>
                                	</div>
                                </div>
                                <div class="row static-info display-none hostDiv">
                                	<div class="col-md-4 textalignr mt5">
                						<?php echo $LANG['UI_TENANT_SELECT_HOST']?>:
                					</div>
                                	<div class="col-md-6 tree_div2">
        								<ul id="host_tree" class="ztree bd1de5" style="overflow:auto;"></ul>
        								<div><span class="help-block ">
            									<?php echo $LANG['UI_RECOVERY_VM_SELECT_HOST']?>
        								</span></div>
        							</div>
        							<div class="col-md-6 display-none" id="nohosttips">
										<div class="alert alert-block alert-info fade in">
											<button type="button" class="close" data-dismiss="alert"></button>
											<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS']?></strong></h4>
											<ol class = "alert-ol">
												<li>
													<?php echo $LANG['UI_RECOVERY_NO_HOST']?>
												</li>
												<li>
													<a id="toaddvcenter"><small><?php echo $LANG['UI_RECOVERY_ADD_VCENTER_TIPS']?></small></a>
												</li>
											</ol>
										</div>
                    				</div>
                                </div>
                                <div class="row static-info">
                                	<div class="col-md-4 textalignr mt5">
                						 <?php echo $LANG['UI_TENANT_TARGET_STORAGE']?>:
                					</div>
                                	<div class="col-md-6">
                                		<select class="form-control select2me input-sm" id="desStorageType" >
                                			<option value="0"><?php echo $LANG['UI_TENANT_ALL_STORAGE']?></option>
                                			<option value="1"><?php echo $LANG['UI_TENANT_APPOINT_STORAGE']?></option>
                                			<!-- <option value="2"><?php echo $LANG['UI_TENANT_HIDE_AND_APPOINT_STORAGE']?></option> -->
										</select>
                                	</div>
                                </div>
                                 <div class="row static-info display-none desStorageDiv">
                                	<div class="col-md-4 textalignr mt5">
                						<?php echo $LANG['UI_TENANT_SELECT_STORAGE']?>:
                					</div>
                                	<div class="col-md-6">
                                		<select class="form-control select2me input-sm" id="desStorageSelect" >
										</select>
                                	</div>
                                </div>
                                <div class="row static-info ">
                                	<div class="col-md-4 textalignr mt5">
                						 <?php echo $LANG['UI_TENANT_TARGET_NETWORK']?>:
                					</div>
                                	<div class="col-md-6">
                                		<select class="form-control select2me input-sm" id="networkType" >
                                			<option value="0"><?php echo $LANG['UI_TENANT_ALL_NETWORK']?></option>
                                			<option value="1"><?php echo $LANG['UI_TENANT_APPOINT_NETWORK']?></option>
                                			<!-- <option value="2"><?php echo $LANG['UI_TENANT_HIDE_AND_APPOINT_NETWORK']?></option> -->
										</select>
                                	</div>
                                </div> 
                                 <div class="row static-info display-none networkDiv">
                                	<div class="col-md-4 textalignr mt5">
                						 <?php echo $LANG['UI_TENANT_SELECT_NETWORK']?>:
                					</div>
                                	<div class="col-md-6">
                                		<select class="form-control select2me input-sm" id="networkSelect" >
										</select>
                                	</div>
                                </div> 
                                
                                 <div class="row static-info">
                                	<button type="button" id="recover_submit" class="btn btn-sm green-haze" style="position:relative; left:35%; margin-top: 20px;">
        							<i class="viconfont vicon-ge_modify"></i> <?php echo $LANG['UI_PUBLIC_SAVE_CONFIG']?>
        							</button>
                                	
                                </div>
                               
                			</div>
                			
                			<div class="tab-pane" id="billing_tab">
                				<div class="row static-info">
                                	<div class="col-md-4 textalignr name">
                						 <?php echo $LANG['UI_TENANT_ENABLE_BILLING']?>:
                					</div>
                                	<div class="col-md-6 value">
                                		<span id="billingCheck">---</span>
                                	</div>
                                </div>
                                <div class="row static-info display-none billingDiv">
                                	<div class="col-md-4 textalignr name">
                						 <?php echo $LANG['UI_TENANT_BILLING_STRATEGY']?>:
                					</div>
                                	<div class="col-md-6 value">
                                		<span id="billingSelect">---</span>
                                	</div>
                                </div>
                                
                			</div>
                		</div>
        			</div>
        		</div>
			</div>