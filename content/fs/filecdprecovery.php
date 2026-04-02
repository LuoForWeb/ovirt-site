<?php include_once '../../tpl/permission.php';?>
<!-- BEGIN PAGE LEVEL STYLES -->

<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<style type="text/css">
.table-scrollable{overflow: auto;max-height: 258px; }
</style>
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
文件数据恢复 <small>创建文件CDP数据恢复任务</small>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
		<div class="portlet box blue-hoki" id="recovercontent">
			<div class="portlet-title">
				<div class="caption">
					<i class="iconfont icon-fscdprec"></i><?php echo $LANG['UI_BACKUP_NEW_TASK']?>
				</div>
			</div>
			<div class="portlet-body form">
				<form action="#" id="filerecoveryform" class="form-horizontal">
				    <div class="form-body min-height480">
						<div class="form-group pt50">
							<label class="control-label col-md-3"> 选择恢复源<span
								class="required"> * </span>
							</label>
							<div class="col-md-4 ">
								<div>
									<select class="form-control select2me" name="standbyhost" id="standbyhost">
									</select>
									<div>
										<span class="help-block "> 选择备份数据所在的主机 </span>
									</div>
								</div>
							</div>
						</div>
						
						
						<div class="form-group display-none" id="srcfilediv">
							<label class="control-label col-md-3"> 选择恢复文件/文件夹<span
								class="required"> * </span>
							</label>
							<div class="col-md-6 tree_div2">
								<ul id="file_tree" class="ztree bd1de5"></ul>
								<div>
									<span class="help-block "> 请选择需要恢复的文件/文件夹 </span>
								</div>
							</div>
						</div>
						
                		<div class="form-group  display-none" id="disthostdiv">
							<label class="control-label col-md-3"> 恢复目标<span
								class="required"> * </span>
							</label>
							<div class="col-md-4 ">
								<div>
									<select class="form-control select2me" name="recoveryhost"  id="recoveryhost">
									</select>
									<div>
										<span class="help-block "> 选择主机作为恢复目的地 </span>
									</div>
								</div>
							</div>
						</div>
						
						<div class="form-group display-none dndiv" id="distfilediv">
							<label class="control-label col-md-3"> 选择目的文件夹<span
								class="required"> * </span>
							</label>
							<div class="col-md-6 tree_div2">
								<ul id="dist_tree" class="ztree bd1de5"></ul>
								<div>
									<span class="help-block "> 请选择需要恢复到的文件夹 </span>
								</div>
							</div>
						</div>
						
						<div class="form-group display-none dndiv">
							<label class="control-label col-md-3"><?php echo $LANG['UI_JOB_RNAME']?><span class="required">
							* </span>
							</label>
							<div class="col-md-4">
								<div class="input-icon right ">
									<i class="fa"></i>
									<input type="text" maxlength="128" class="form-control" name="jobname" id="jobname"/>
									<div><span class="help-block "><?php echo $LANG['UI_JOB_RNAME_TIPS']?></span></div>
								</div>
							</div>
						</div>
						
					</div>
					<div class="form-actions pt50">
						<div class="row">
							<div class="col-md-offset-6 col-md-6">
								<button type="button" id="cancelbtn" class="btn default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
								<button type="button" id="submitbtn" class="btn green-haze"><?php echo $LANG['UI_PUBLIC_YES']?></button>
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
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script src="./scripts/fs/filecdprecovery.js" type="text/javascript"></script>