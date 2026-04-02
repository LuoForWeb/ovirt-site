<?php include_once '../../tpl/permission.php';?>
<!-- BEGIN PAGE LEVEL STYLES -->

<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row page-content-row">
	<div class="col-md-12 page-content-col">
		<div class="portlet box blue-hoki" id="recovercontent">
			<div class="portlet-title">
				<div class="caption">
					<i class="levelchild viconfont vicon-vminstantrecover"></i><?php echo $LANG['UI_BACKUP_NEW_TASK']?>
				</div>
			</div>
			<div class="portlet-body form">
				<form action="#" id="instantrecoverform" class="form-horizontal">
				    <div class="form-body form-body__instant">					
						<div class="form-group">
							<label class="control-label col-md-3">
							</label>
							
							<div class="col-md-7">
                                <select class="bs-select width100p form-control" style="height: 34px" data-show-subtext="true" id="storageselect">
                                </select>
    								
								<div class="vm_tree_div " style="margin-top: 10px;">
							        <select class="bs-select pointshowtype-display display-none" data-show-subtext="true" id="pointshowtype">
                                        <option data-icon="timevmtype icon-default" value="1" > <?php echo $LANG['UI_RECOVERY_BACKUP_POINT_VM_GROUP']?></option>
                                        <option data-icon="timepointtype icon-default" value="2" style="display: none;"> <?php echo $LANG['UI_RECOVERY_BACKUP_POINT_TIME_GROUP']?></option>
                                    </select>

									<div class="input-icon right">
										<input type="text" maxlength="128" placeholder="<?php echo $LANG['UI_COPY_SEARCH_AS_KEYWORD']?>" class="form-control" id="searchvm" autocomplete="off"/>
									</div>
							    </div>
							</div>
							
							<div class="col-md-7 col-md-offset-3 col-tree">
								<div class="two_tree">
								    <ul id="pointtypetree" class="ztree bd1de5 tree_div ztree-fa tree-scroll"></ul>
    								<ul id="vmtypetree" class="ztree bd1de5  tree_div display-hide tree-scroll"></ul>
    								<div><span class="help-block ">
    									<?php echo $LANG['UI_INSTANT_VM_POINT']?>
    								</span></div>
								</div>
								<div class="alert alert-block alert-info fade in display-hide"  id="nopointtips">
									<button type="button" class="close" data-dismiss="alert"></button>
									<ul class="alert-ul">
										<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS']?>:</strong>
										<li>
											<strong><?php echo $LANG['UI_RECOVERY_NO_VM_TITLE']?></strong><a id="tobackup"><small><?php echo $LANG['UI_RECOVERY_NO_VM_TIPS']?></small></a>
										</li>
									</ul>
								</div>
								<div class="alert alert-block alert-info fade in display-hide"  id="nosearchtips">
									<button type="button" class="close" data-dismiss="alert"></button>
									<ul class="alert-ul">
										<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS']?>:</strong>
										<li>
											<strong><?php echo $LANG['UI_DATA_NOSEARCH_TIPS']?></strong>
										</li>
									</ul>
								</div>
							</div>
						</div>

                        <div class="form-group" id="selectOpenstackTip" style="display: none">
                            <label class="control-label col-md-3"></label>
                            <div class="col-md-7">
                                <div class="alert alert-block alert-danger fade in" >
                                    <button type="button" class="close" data-dismiss="alert"></button>
                                    <ul class="alert-ul">
                                        <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS']?>:</strong>
                                        <li>
                                            <?php echo $LANG['UI_RECOVERY_SELECT_OPENSTACK_TIPS']?>
                                        </li>
                                    </ul>
                                    <!--注意！由于云平台架构特殊性，在运行瞬时恢复任务时会重启平台存储服务，可能会对云平台环境造成影响。建议选择一套临时环境进行瞬时恢复任务，接管业务后迁移至生产环境-->
                                </div>
                            </div>
                        </div>
						
						<div class="form-group display-none usergroup_tree_div" id="selectUsergroup">
							<label class="control-label col-md-3">
								<span class="required">* </span>
								<?php echo $LANG['UI_RECOVERY_SELECT_USER_GROUP']?>
							</label>
							<div class="col-md-7 tree_div2">
								<ul id="usergroup_tree" class="ztree bd1de5 tree_div"></ul>
								<div><span class="help-block ">
    									<?php echo $LANG['UI_RECOVERY_SELECT_USER_GROUP_TIPS']?>
								</span></div>
							</div>
						</div>
						
						<div class="form-group display-none groupdiv">
							<label class="control-label col-md-3">
								<span class="required">* </span>
								<?php echo $LANG['UI_RECOVERY_INPUT_USER_NMAE']?>
							</label>
							<div class="col-md-3">
								<div class="input-icon right ">
									<i class="fa"></i>
									<select class="form-control" id="groupusername"></select>
									<div><span class="help-block "><?php echo $LANG['UI_RECOVERY_SELECT_GROUP_USER_NAME']?></span></div>
								</div>
							</div>
							<div class="col-md-3">
								<div class="input-icon right ">
									<i class="fa"></i>
									<input type="password" autocomplete="off" maxlength="128" class="form-control" id="grouppassword" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')"/>
									<div><span class="help-block "><?php echo $LANG['UI_RECOVERY_INPUT_PASSWORD']?></span></div>
								</div>
							</div>
							<div class="col-md-3">
								<div class="btn-group" style="margin-top: 6px;">
    								<button type="button" id="verifyuser" class="btn btn-sm green-haze">
    								<?php echo $LANG['UI_RECOVERY_VERIFY_USER']?></button>
    							</div>
							</div>
						</div>
						
						<div class="form-group display-none host_tree_div" id="selectHost">
							<label class="control-label col-md-3">
								<span class="required">* </span>
								<?php echo $LANG['UI_RECOVERY_SELECT_HOST']?>
							</label>
							<div class="col-md-7 tree_div2">
								<ul id="host_tree" class="ztree bd1de5" name="hosttree"></ul>
								<div><span class="help-block ">
										<?php echo $LANG['UI_INSTANT_VM_HOST']?>
								</span></div>
							</div>
						</div>
						<div class="form-group display-hide" id="nohosttips">
							<label class="control-label col-md-3">
								<span class="required">* </span>
								<?php echo $LANG['UI_RECOVERY_SELECT_HOST']?>
							</label>
							<div class="col-md-6 alert alert-block alert-info fade in" >
                                <button type="button" class="close" data-dismiss="alert"></button>
                                <ul class="alert-ul">
                                    <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS']?>:</strong>
                                    <li>
                                        <strong><?php echo $LANG['UI_RECOVERY_NO_HOST']?></strong>
                                        <?php
                                        //if(empty($_SESSION['tenantuuid'])){
                                            echo '<p><a id="toaddvcenter"><small>'.$LANG['UI_RECOVERY_ADD_VCENTER_TIPS'].'</small></a></p>';
                                        //}
                                        ?>
                                    </li>
                                </ul>
							</div>
						</div>
						
						<div class="form-group display-none ipDiv">
							<label class="control-label col-md-3">
								<span class="required">* </span>
								<?php echo $LANG['UI_INSTANT_VM_OPENSTACK_CONTROLLER_IP']?>
							</label>
							<div class="col-md-4">
								<div class="input-icon right">
									<i class="fa"></i>
									<input style="display:none"><!-- for disable autocomplete on chrome -->
									<input type="text" maxlength="128" class="form-control" name="openstackIP"/>
									<div><span class="help-block ">
									<?php echo $LANG['UI_INSTANT_VM_OPENSTACK_CONTROLLER_IP_TIPS']?>
									</span></div>
								</div>
							</div>
							<div class="col-md-3">
                				<div class="btn-group">
                    				<button id="testIP" type="button" class="btn btn-sm green-haze">
                    				<?php echo $LANG['UI_INSTANT_TEST_CONNECTION']?></button>
                    			</div>
                			</div>
						</div>
						
						<div class="form-group display-none dndiv">
							<label class="control-label col-md-3">
								<span class="required">* </span>
								<?php echo $LANG['UI_INSTANT_VM_MOUNT']?>
							</label>
							<div class="col-md-4">
								<div class="selectipdiv">
    								<select class="form-control" id="serveripaddr">
    								</select>
    								<div>
    								<span class="help-block "><?php echo $LANG['UI_INSTANT_VM_TARGET']?>
    									<a id="diyserverip"><?php echo $LANG['UI_INSTANT_VM_TARGET_DIY']?></a></span>
    								</div>
								</div>
								<div class="input-icon right mb15 display-none inputipdiv">
									<i class="fa"></i>
									<input type="text" maxlength="128" class="form-control"  name="backupserveraddr"/>
									<div>
    								<span class="help-block "><?php echo $LANG['UI_INSTANT_VM_TARGET2']?>
    									<a id="selectserverip"><?php echo $LANG['UI_INSTANT_VM_TARGET_DIY2']?></a></span>
    								</div>
								</div>
							</div>
						</div>
						
    					<div class="form-group display-none" id="vmconfigs">
    						<label class="control-label col-md-3">
								<span class="required">* </span>
								<?php echo $LANG['UI_RECOVERY_VM_SETTING']?>
							</label>
    						<div class="col-md-9" style="padding-right: 20px;">
                        		<div class="portlet">
                        			<div class="portlet-body" id="accordionvmdiv">
                        				<div class="panel-group accordion" id="accordionvm">
                                            <?php include_once('../../content/platform/component/vm_recovery.php'); ?>
                        				</div>
                        			</div>
                        			<div class="margintop-15">
                        			  <span class="help-block "><?php echo $LANG['UI_RECOVERY_VM_SETTING_TIPS']?></span>
                        			</div>
                        		</div>
                        	</div>
    					</div>
    									
						<div class="form-group display-none dndiv">
							<label class="control-label col-md-3">
								<span class="required">* </span>
								<?php echo $LANG['UI_JOB_RNAME']?>
							</label>
							<div class="col-md-4">
								<div class="input-icon right ">
									<i class="fa"></i>
									<input type="text" maxlength="128" class="form-control" name="taskname"/>
									<div><span class="help-block "><?php echo $LANG['UI_JOB_RNAME_TIPS']?></span></div>
								</div>
							</div>
						</div>
						
					</div>
					<div class="form-actions">
						<div class="row">
							<div class="col-md-offset-6 col-md-6">
								<button type="button" id="cancelbtn" class="btn default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
								<button type="button" disabled id="submitbtn" class="btn green-haze btn-confirm"><?php echo $LANG['UI_PUBLIC_YES']?></button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<?php 
if($_SESSION['language'] != "en-us"){
    //如果不是英文,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
        $_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script src="./scripts/vm/vminstantrecover.js" type="text/javascript"></script>