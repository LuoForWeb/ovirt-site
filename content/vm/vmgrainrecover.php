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
					<i class="levelchild viconfont vicon-vmrecovera"></i><?php echo $LANG['UI_BACKUP_NEW_TASK']?>
				</div>
			</div>
			<div class="portlet-body form">
				<form action="#" id="grainrecoverform" class="form-horizontal">
				    <div class="form-body form-body__instant">	
						<div class="form-group">
							<label class="control-label col-md-3">
							</label>
							
							<div class="col-md-7">
                                <select class="bs-select width100p form-control" style="height: 34px" data-show-subtext="true" id="storageselect">
                                </select>
								<div class="vm_tree_div " style="margin-top:10px;">
    								
							        <select class="bs-select width45p pointshowtype-display display-none" data-show-subtext="true" id="pointshowtype">
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
    									<?php echo $LANG['UI_GRAIN_VM_POINT']?>
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
						<div class="form-group display-none encryptpassdiv">
							<label class="control-label col-md-3">
								<span class="required">* </span>
								<?php echo $LANG['UI_VM_DATABASE_ENCR_PWD']?>
							</label>
							<div class="col-md-4">
								<div class="input-icon right btn-group">
									<i class="fa"></i>
									<input type="password" autocomplete="off" maxlength="128" class="form-control" name="encryptpass" id="encyptPassword" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')"/>
								</div>
								<input id="verifyPassword" type="button" class="btn green-haze btn-group" value="<?php echo $LANG['UI_VM_VERIFY']?>">
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
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<?php 
if($_SESSION['language'] != "en-us"){
    //如果不是英文,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' . 
         $_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script src="./scripts/vm/vmgrainrecover.js" type="text/javascript"></script>