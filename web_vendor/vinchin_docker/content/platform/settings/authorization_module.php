<?php include_once '../../../tpl/permission.php';?>
<link href="./assets/global/plugins/jquery-file-upload/css/jquery.fileupload.css" rel="stylesheet"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<!-- BEGIN PAGE HEADER-->
<!-- <h3 class="page-title">
<?php echo $LANG['UI_SETTINGS_MODULE_AUTH']?> <small><?php echo $LANG['UI_SETTINGS_MODULE_AUTH_DESCRIPTION']?></small>
</h3> -->
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row" style="height: 100%;">
	<div class="col-md-12" id="licensediv" style="height: 100%;">
		<!-- Begin: life time stats -->
		<div class="portlet box blue-hoki" id='authdiv' style="height: 100%;margin-bottom:0;">
			<div class="portlet-title" style="height: 5%;">
				<div class="caption">
					<i class="icon-badge"></i><?php echo $LANG['UI_SETTINGS_AUTH_INFO']?>
				</div>
			</div>
			<div class="portlet-body" style="height: 94.4%; overflow-x: hidden; overflow-y: auto;">
				<div class="panel-body" style="height: 100%;padding: 0 15px;">
				    <div class="row">
    				    <div class="col-md-4">
    				        <div id="lisenceinfo">
        				        <div style="padding: 20px;border-bottom: 1px solid #eee; min-height: 180px;" class="linseceinfo-list">
        				            <div class="">
        				                <label class="width120"><i class="fa fa-bank"></i> <?php echo $LANG['UI_SETTINGS_AUTH_USERNAME']?>：</label><label  id="customer" style="text-overflow: ellipsis;white-space: nowrap;width: 9em;overflow: hidden;display:inline;"></label>
        				            </div>
        				            <div class="">
        				                <label class="width120"><i class="fa fa-suitcase"></i> <?php echo $LANG['UI_SETTINGS_AUTH_VERSION']?>：</label><span id="software"></span>
        				            </div>
        				            <div class="">
        				                <label class="width120"><i class="icon-badge"></i> <?php echo $LANG['UI_SETTINGS_AUTH_TYPE']?>：</label><span id="trial"></span>
        				            </div>
        				            <div class="">
        				                <label class="width120"><i class="icon-clock"></i> <?php echo $LANG['UI_SETTINGS_EXPIRE_TIME']?>：</label><span id="expireTime"></span>
        				            </div>
        				            
        				            <div class="serviceDiv display-none">
        				                <label class="width120"><i class="icon-trophy"></i> <?php echo $LANG['UI_SETTINGS_AUTH_SERVER_TYPE']?>：</label><span id="serviceType"></span>
        				            </div>
        				            <div class="serviceDiv display-none">
        				                <label class="width120"><i class="icon-clock"></i> <?php echo $LANG['UI_SETTINGS_AUTH_SERVER_ENDTIME']?>：</label><span id="serviceTime"></span>
        				            </div>
        				            
        				        </div>
        				        <div style="padding: 20px;border-bottom: 1px solid #eee;" class="linseceinfo-list">
        				            <div class="display-none vmauthType">
        				               <label class="width130"><?php echo $LANG['UI_SETTINGS_AUTH_MODE']?>：</label><span id="vmtype"></span>
        				            </div>
        				            <div class="display-none">
        				               <label class="width130"><?php echo $LANG['UI_SETTINGS_AUTH_HOST']?>：</label><span id="lisencehost" class="lisencetype"></span>
        				            </div>
        				            <div class="display-none">
        				               <label class="width130"><?php echo $LANG['UI_SETTINGS_AUTH_VM_NUM']?>：</label><span id="lisencevm" class="lisencetype"></span>
        				            </div>
        				            <div class="display-none">
        				               <label class="width130"><?php echo $LANG['UI_SETTINGS_AUTH_STORAGE']?>：</label><span id="lisencestorage" class="lisencetype"></span>
        				            </div>
        				            <div class="display-none">
        				               <label class="width130"><?php echo $LANG['UI_SETTINGS_AUTH_CPU_NUM']?>：</label><span id="lisencecpu" class="lisencetype"></span>
        				            </div>
        				            
        				            <div class="vmDiv display-none">
        				               <label class="width130"><?php echo $LANG['UI_SETTINGS_AUTH_VM_BACKUP']?>：</label><span id="vmBackup" class=""></span>
        				            </div>
        				            <div class="fileDiv display-none">
        				               <label class="width130"><?php echo $LANG['UI_PLATFORM_FILEBACKUP']?>：</label><span id="fileAuth" class=""></span>
        				            </div>
        				            
        				            <div class="vmandfsDiv display-none">
        				               <label class="width130"><?php echo $LANG['UI_SETTINGS_AUTH_MODE']?>：</label><span id="authtype" class=""></span>
        				            </div>
        				            <div class="vmandfsDiv display-none">
        				               <label class="width130"><?php echo $LANG['UI_SETTINGS_AUTH_STORAGE']?>：</label><span id="storageSize" class=""></span>
        				            </div>
        				            <div class="vmandfsDiv display-none">
        				               <label class="width130"><?php echo $LANG['UI_SETTINGS_AUTH_VM_NUM']?>：</label><span class=""><?php echo $LANG['UI_SETTINGS_AUTH_UNLIMITED']?></span>
        				            </div>
        				            <div class="vmandfsDiv display-none">
        				               <label class="width130"><?php echo $LANG['UI_SETTINGS_AUTH_AGENT_NUM']?>：</label><span class=""><?php echo $LANG['UI_SETTINGS_AUTH_UNLIMITED']?></span>
        				            </div>
        				            
        				            <div class="dbtimingDiv display-none">
        				               <label class="width130"><?php echo $LANG['UI_SETTINGS_AUTH_DATA_TIMING']?>：</label><span id="dataTiming" class=""></span>
        				            </div>
        				            <div class="cdpDiv display-none">
        				               <label class="width130 "><?php echo $LANG['UI_SETTINGS_AUTH_CDP_PRODUCTHOST']?>：</label><span id="producthost" class=""></span>
        				            </div>
        				            <div class="cdpDiv display-none">
        				               <label class="width130 "><?php echo $LANG['UI_SETTINGS_AUTH_CDP_STANDBY']?>：</label><span id="standbyhost" class=""></span>
        				            </div>
        				            <div class="cdpDiv display-none">
        				               <label class="width130 "><?php echo $LANG['UI_SETTINGS_AUTH_CDP_TAKEOVER']?>：</label><span id="takeover" class=""></span>
        				            </div>
        				            <div class="display-none">
        				               <label class="width130  "><?php echo $LANG['UI_SETTINGS_AUTH_CDP_ENDTIME']?>：</label><span id="cdpendtime" class=""></span>
        				            </div>
        				        </div>
        				        <div style="padding: 20px;" id="feature" class="linseceinfo-list display-none">
        				            <!-- <div class="">
        				               <label class="width130"><?php echo $LANG['UI_PLATFORM_ORCHESTRATION']?>：</label><span ></span>
        				            </div> -->
        				            <div class="display-none">
        				               <label class="width130"><?php echo $LANG['UI_SETTINGS_AUTH_DATA_VISUAL']?>：</label><span id="visual" class=""></span>
        				            </div>
        				            <div class="display-none">
        				               <label class="width130"><?php echo $LANG['UI_SETTINGS_AUTH_LANFREE']?>：</label><span id="lanfree"></span>
        				            </div>
        				            <div class="">
        				               <label class="width130"><?php echo $LANG['UI_SETTINGS_AUTH_DEDUPLICATION']?>：</label><span id="deduplication"></span>
        				            </div>
        				            <div class="">
        				               <label class="width130"><?php echo $LANG['UI_SETTINGS_AUTH_VCBT']?>：</label><span id="vcbt"></span>
        				            </div>
        				            <div class="display-none">
        				               <label class="width130"><?php echo $LANG['UI_SETTINGS_AUTH_NODE_EXTEND']?>：</label><span id="nodeExtend"></span>
        				            </div>
        				            <div class="">
        				               <label class="width130"><?php echo $LANG['UI_SETTINGS_AUTH_GRAIN']?>：</label><span id="grain"></span>
        				            </div>
        				            <div class="">
        				               <label class="width130"><?php echo $LANG['UI_PLATFORM_VM_COPY']?>：</label><span id="copy"></span>
        				            </div>
        				            <div class="">
        				               <label class="width130"><?php echo $LANG['UI_PLATFORM_DATA_ARCHIVE']?>：</label><span id="archive"></span>
        				            </div>
        				        </div>
    				        </div>
    				    </div>
    				    <div class="col-md-8" >
                			<div class="" style="border: 1px solid #eee;line-height: 28px; min-height: 553px;">
                			    <div class="row" style="padding: 20px;">
                    			    <div class="col-md-12" id="lisencediv">
            				            <div class="">
                    						<div class="margin10">
                    							<div class="btn-group">
                    								<button id="download" class="btn green-haze">
                    								<i class="fa fa-download"></i> <?php echo $LANG['UI_SETTINGS_DOWNLOAD_FILE']?>
                    								</button>
                    							</div>
                    							<div class="btn-group display-none" id="sdiv">
                    								<button id="fileuploads" class="btn green-haze">
                    								<i class="fa fa-upload"></i> <?php echo $LANG['UI_SETTINGS_UPLOAD_FILE']?>
                    								</button>
                    							</div>
                    							<div class="btn-group " id="ediv">
                    								<span class="btn green-haze fileinput-button">
                    								<i class="fa fa-upload"></i>
                    								<span>
                    								 <?php echo $LANG['UI_SETTINGS_UPLOAD_FILE']?></span>
                    								<input type="file" name="files" multiple="">
                    								</span>
                    							</div>
                    						</div>
            				            </div>
            				            <div class="">
            				                <div class="margin10">
                				                <div class="alert alert-block alert-info fade in " id="marktips1">
                        						    <span><i class="fa fa-info-circle"></i> <?php echo $LANG['UI_SETTINGS_MODULE_AUTH']?>:</span>
                        							<ul class="linonepoint">
                                    					<li>
                                    						 <?php echo $LANG['UI_SETTINGS_AUTH_TIPS1']?>
                                    					</li>
                                    					<li>
                                    						 <?php echo $LANG['UI_SETTINGS_AUTH_TIPS2']?>
                                    					</li>
                                    					<li>
                                    						 <?php echo $LANG['UI_SETTINGS_AUTH_TIPS3']?>
                                    					</li>
                                    					<li>
                                    						 <?php echo $LANG['UI_SETTINGS_AUTH_TIPS4']?>
                                    					</li>
                                    					<li>
                                    						 <?php echo $LANG['UI_SETTINGS_AUTH_TIPS5']?>
                                    					</li>
                                    				</ul>
                        						</div>
                				            </div>
            				            </div>
            				        </div>
            				        <div class="col-md-6 display-none" id="updatetoediv">
            				            <div class="">
                    						<div style="margin: 13px">
                    							<p style="color: #3c763d;font-size: 16px;"><i class="fa fa-rocket"></i> <?php echo $LANG['UI_SETTINGS_AUTH_UPGRADE']?></p>
                    						</div>
            				            </div>
            				            <div class="">
            				                <div class="margin10">
                				                <div class="alert alert-block alert-success fade in " id="marktips2">
                        						    <span><i class="fa fa-info-circle"></i> <?php echo $LANG['UI_SETTINGS_AUTH_UPGRADE']?>:</span>
                        							<ul class="linonepoint">
                                    					<li>
                                    						 <?php echo $LANG['UI_SETTINGS_AUTH_UPGRADE_TIP1']?>
                                    					</li>
                                    					<li>
                                    						 <?php echo $LANG['UI_SETTINGS_AUTH_UPGRADE_TIP2']?>
                                    					</li>
                                    					<li>
                                    						 <?php echo $LANG['UI_SETTINGS_AUTH_UPGRADE_TIP3']?>
                                    					</li>
                                    					<li>
                                    						&nbsp;
                                    					</li>
                                    					<li>
                                    						 &nbsp;
                                    					</li>
                                    				</ul>
                        						</div>
                				            </div>
            				            </div>
            				        </div>
        				        </div>
        				        <div class="row row-right" style="padding: 0 20px;">
                    			    <div class="col-md-12 " style="padding: 0 40px;">
            				            <div class="well row">
            				                <div class="col-md-12 margin10">
                				                <div class=" " id="marktips3">
                        						    <span style="margin-left:5px;"><i class="fa fa-info-circle" style="width: 1.05em;"></i> <?php echo $LANG['UI_SETTINGS_AUTH_CONTACT_US']?>: <b><?php echo $CONF['SYSTEM_INFO']['company_name']?></b></span>
                        							<ul class="linonepoint" style="padding-left:0;">
                                    					<li>
                                    						 <i class="fa  fa-globe"></i> <?php echo $LANG['UI_SETTINGS_AUTH_WEBSITE']?>: <a href="<?php echo $CONF['REMOTE']['website']?>" target="_black"><?php echo $CONF['REMOTE']['website']?></a>
                                    					</li>
                                    					<li>
                                    						 <i class="fa  fa-phone"></i> <?php echo $LANG['UI_SETTINGS_AUTH_PHONE']?>: <?php echo $CONF['SYSTEM_INFO']['company_tel']?>
                                    					</li>
                                    					<li>
                                    						<i class="fa  fa-envelope"></i> <?php echo $LANG['UI_SETTINGS_AUTH_EMAIL']?>: <?php echo $CONF['SYSTEM_INFO']['company_email']?>
                                    					</li>
                                    				</ul>
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
		</div>
		<!-- End: life time stats -->
		
		<!-- BEGIN MODAL -->
		<div id="modaldiv" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="icon-badge"></i> <?php echo $LANG['UI_SETTINGS_MODULE_AUTH']?></h4>
			</div>
			<div class="modal-body">
                <div class="form-group">
        		    <div class="col-md-12">
        			    <div class=""  style="height: 320px; overflow-y:auto; border: 1px solid #ddd; background: #fff; padding:0 20px;">
        			        <pre id="agreecontent" style="word-wrap: break-word;white-space: pre-wrap;">
        			            <?php require_once ROOT_PATH . '/api/xphp/conf/agreement/zh-cn.php';?>
        			        </pre>
                        </div>
        			</div>
				</div>
			</div>
			<div class="modal-footer">
			    <div style="float: left">
			        <label style="margin: 5px;font-size: 16px;">
			            <input type="checkbox" id="agreecheck" data-checkbox="icheckbox_square-blue" data-mode="1" class="icheck">我已经阅读并同意用户协议
			        </label>
			    </div>
				<div class="btn-group display-none" id="modalupload">
					<span class="btn green-haze fileinput-button">
					<i class="fa fa-upload"></i>
					<span>
					 <?php echo $LANG['UI_SETTINGS_UPLOAD_FILE']?></span>
					<input type="file"  name="files" multiple="" >
					</span>
				</div>
				
			</div>
		</div>	
		<!-- END MODAL -->
		
	</div>
</div>
<!-- END PAGE CONTENT-->
	

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./assets/global/plugins/jquery-file-upload/js/vendor/jquery.ui.widget.js"></script>
<script src="./assets/global/plugins/jquery-file-upload/js/jquery.fileupload.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/platform/settings/authorization_module.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	