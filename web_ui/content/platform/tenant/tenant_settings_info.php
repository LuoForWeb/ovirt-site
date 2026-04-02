<?php include_once '../../../tpl/permission.php';?>
<div class="col-md-6 highSettingsDiv_tenant ">
			    <div class="portlet box blue-hoki" style="height: 420px;overflow:hidden;position:relative;">
        			<div class="portlet-title">
        				<div class="caption">
        					<i class="fa  fa-pie-chart"></i><?php echo $LANG['UI_USER_HIGH_SETTING']?>
        				</div>
        			</div>
        			<div class="portlet-body">
        				<ul class="nav nav-tabs " style="margin: 0;">
                			<li id="baseinfotab_tenant"class="active">
                				<a href="#baseinfo_tab_tenant" data-toggle="tab" aria-expanded="false">
                				<i class="iconfont icon-tongyongcelve"></i> <?php echo $LANG['UI_TENANT_COMMON']?></a>
                			</li>
                			<!-- <li class="" id="backuptab_tenant">
                				<a href="#backup_tab_tenant" data-toggle="tab" aria-expanded="false">
                				<i class="iconfont icon-copyback font-green-seagreen"></i> <?php echo $LANG['UI_PLATFORM_BACKUP']?> </a>
                			</li>-->
                			<li class="" id="recovertab_tenant">
                				<a href="#recover_tab_tenant" data-toggle="tab" aria-expanded="false">
                				<i class="iconfont icon-copyoffsite"></i> <?php echo $LANG['UI_PLATFORM_RECOVER']?> </a>
                			</li>
                			<li class="" id="billingtab_tenant">
                				<a href="#billing_tab_tenant" data-toggle="tab" aria-expanded="false">
                				<i class="iconfont icon-feiyong"></i> <?php echo $LANG['UI_TENANT_BILLING']?> </a>
                			</li>
                			
                		</ul>
                		<div class="tab-content " style="padding-top:10px;height:320px;overflow-x:hidden;overflow-y:auto;">
                			<div class="tab-pane active" id="baseinfo_tab_tenant">
                				<div class="row static-info">
                                	<div class="col-md-4 textalignr name">
										<?php echo $LANG['UI_PLATFORM_TENANT_AUTOR_WAY']?>:
                					</div>
                                	<div class="col-md-6 value" >
                                		<span id="authTypeDes">---</span>
                                	</div>
                                </div>
                                <div class="row static-info display-none quotaInfoDiv">
                                	<div class="col-md-4 textalignr name">
                						 <?php echo $LANG['UI_TENANT_QUOTA']?>:
                					</div>
                                	<div class="col-md-6 value" >
                                		<span id="quota">---</span>
                                	</div>
                                </div>
                                <div class="row static-info display-none quotaDiv">
                                	<div class="col-md-4 textalignr name">
                						 <?php echo $LANG['UI_TENANT_QUOTA_SIZE']?>:
                					</div>
                                	<div class="col-md-6 value">
                                		<span id="quotaSize">---</span>
                                	</div>
                                </div>
                                <div class="row static-info display-none authNumDiv">
                                	<div class="col-md-4 textalignr name">
                						<?php echo $LANG['UI_VCENTER_MACHINE']?>:
                					</div>
                                	<div class="col-md-6 value" >
                                		<span id="vmNumDes">---</span>
                                	</div>
                                </div>
                                 <div class="row static-info display-none authNumDiv">
                                	<div class="col-md-4 textalignr name">
                						<?php echo $LANG['UI_PLATFORM_TENANT_FILE_AGENT']?>:
                					</div>
                                	<div class="col-md-6 value" >
                                		<span id="fsNumDes">---</span>
                                	</div>
                                </div>
                                 <div class="row static-info display-none authNumDiv">
                                	<div class="col-md-4 textalignr name">
                						<?php echo $LANG['UI_PLATFORM_TENANT_DATABASE_AGENT']?>:
                					</div>
                                	<div class="col-md-6 value" >
                                		<span id="dbNumDes">---</span>
                                	</div>
                                </div>
                                
                                 <!-- <div class="row static-info display-none">
                                	<div class="col-md-4 name textalignr ">
                						 <?php echo $LANG['UI_TENANT_SPEED_LIMIT']?>:
                					</div>
                                	<div class="col-md-6 value" >
                                		<span id="speedlimit">---</span>
                                	</div>
                                </div>
                                <div class="row static-info display-none speedDiv">
                                	<div class="col-md-4 textalignr name">
                						<?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_VALUE']?>:
                					</div>
                                	<div class="col-md-6 value" >
                                		<span id="speedSize">---</span>
                                	</div>
                                </div>-->
                                <div class="row static-info display-none">
                                	<div class="col-md-4 textalignr name">
                						 <?php echo $LANG['UI_TENANT_RESOURCE_SHARE']?>:
                					</div>
                                	<div class="col-md-6 value" >
                                		<span id="resource_share">---</span>
                                	</div>
                                </div>
                                <div class="row static-info">
                                	<div class="col-md-4 textalignr mt5">
                						 <?php echo $LANG['UI_PLATFORM_TENANT_MANAGE_BAKDATA']?>:
                					</div>
                                	<div class="col-md-6" style="margin-top: 3px;">
                                		<span id="backupDataManage"></span>
                                	</div>
                                </div>
                			</div>
                			
                			<div class="tab-pane" id="recover_tab_tenant">
                				<div class="row static-info">
                                	<div class="col-md-4 textalignr name">
                						 <?php echo $LANG['UI_TENANT_TARGET_HOST']?>:
                					</div>
                                	<div class="col-md-6 value">
                                		<span id="hostTypeDes">---</span>
                                	</div>
                                </div>
                                <div class="row static-info display-none hostDiv">
                                	<div class="col-md-4 textalignr name">
                						 <?php echo $LANG['UI_TENANT_SELECT_HOST']?>:
                					</div>
                                	<div class="col-md-6 tree_div2 value" >
        								<span id="hostName">---</span>
        							</div>
                                </div>
                                <div class="row static-info">
                                	<div class="col-md-4 textalignr name">
                						<?php echo $LANG['UI_TENANT_TARGET_STORAGE']?>:
                					</div>
                                	<div class="col-md-6 value">
                                		<span id="desStorageTypeDes">---</span>
                                	</div>
                                </div>
                                 <div class="row static-info display-none desStorageDiv">
                                	<div class="col-md-4 textalignr name">
                						<?php echo $LANG['UI_TENANT_SELECT_STORAGE']?>:
                					</div>
                                	<div class="col-md-6 value">
                                		<span id="desStorageName">---</span>
                                	</div>
                                </div>
                                <div class="row static-info ">
                                	<div class="col-md-4 textalignr name">
                						 <?php echo $LANG['UI_TENANT_TARGET_NETWORK']?>:
                					</div>
                                	<div class="col-md-6 value">
                                		<span id="networkTypeDes">---</span>
                                	</div>
                                </div> 
                                 <div class="row static-info display-none networkDiv">
                                	<div class="col-md-4 textalignr name">
                						 <?php echo $LANG['UI_TENANT_SELECT_NETWORK']?>:
                					</div>
                                	<div class="col-md-6 value">
                                		<span id="networkName">---</span>
                                	</div>
                                </div> 
                                
                			</div>
                			
                			<div class="tab-pane" id="billing_tab_tenant">
                				<div class="row static-info">
                                	<div class="col-md-4 textalignr name">
                						 <?php echo $LANG['UI_TENANT_ENABLE_BILLING']?>:
                					</div>
                                	<div class="col-md-6 value">
                                		<span id="billing">---</span>
                                	</div>
                                </div>
                                <div class="row static-info display-none billingDiv">
                                	<div class="col-md-4 textalignr name">
                						 <?php echo $LANG['UI_TENANT_BILLING_STRATEGY']?>:
                					</div>
                                	<div class="col-md-6 value">
                                		<span id="billingStrategy">---</span>
                                	</div>
                                </div>
                                
                			</div>
                		</div>
        			</div>
        		</div>
			</div>