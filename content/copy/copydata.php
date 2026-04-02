<?php include_once '../../tpl/permission.php';
$userAllPermission = $_SESSION['permission'];
?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./scripts/plugins/ztree/css/metroStyle/metroStyle.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css" />
<link rel="stylesheet" type="text/css" href="./css/fs/filetype-small.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<link rel="stylesheet" type="text/css" href="./scripts/plugins/popModal/popModal.css" />
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="backupdata-wrapper copydata-wrapper">
    <div class="backupdata-wrapper__head">
        <i class="levelchild viconfont vicon-vmdata"></i>
        <span class="backupdata-wrapper__head__title"><?php echo $LANG['UI_COPY_DATA'] ?></span>
    </div>
	<div class="backupdata-wrapper__content row">
		<div class="col-md-4 timepoint-wrapper">
            <div class="timepoint-wrapper__head">
				<i class="viconfont vicon-ge_time_point"></i>
                <span class="timepoint-wrapper__head__title"><?php echo $LANG['UI_COPY_DATA_JOB_LIST'] ?></span>
            </div>
            <div class="timepoint-wrapper__content">
				<div class="timepoint-wrapper__content__btn">
					<button type="button" id="allDelete" class="btn btn-sm green-haze">
						<i class="viconfont vicon-a-Deleteshanchu2" style="font-size: 16px;"></i>
						<?php echo $LANG['UI_PUBLIC_DELETE'] ?>
					</button>
				</div>
				<div class="timepoint-wrapper__content__btn">
					<?php
						if (($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") || ($_GET['type'] != "vm")) {
							echo '
									<select class="bs-select form-control" style="height: 34px" data-show-subtext="true" id="module">';

							//虚拟机
							if (in_array("vmprotect", $userAllPermission)) {
								echo '<option value="2">' . $LANG['UI_PLATFORM_VM'] . '</option>';
								echo '<option value="22">' . $LANG['WEB_PLATFORM_DES_PRIVATE_CLOUD'] . '</option>';
							}
							// 公有云
							if (in_array("awsprotect", $userAllPermission)) {
								echo '<option value="17">' . $LANG['WEB_PLATFORM_DES_PUBLIC_CLOUD'] . '</option>';
							}
							//文件
							if (in_array("fileprotect", $userAllPermission)) {
								echo '<option value="3">' . $LANG['UI_PLATFORM_FILE'] . '</option>';
							}
							// nas
							if (in_array("nas_protect", $userAllPermission)) {
								echo '<option value="11">' . $LANG['WEB_PLATFORM_DES_NAS'] . '</option>';
							}
							// s3
							if (in_array("obs_protect", $userAllPermission)) {
								echo '<option value="999">' . $LANG['UI_PLATFORM_OBS'] . '</option>';
							}
							// hadoop
							if (in_array("hadoop_protect", $userAllPermission)) {
								echo '<option value="998">' . $LANG['UI_PLATFORM_HADOOP'] . '</option>';
							}
							//数据库
							if (in_array("db_protect", $userAllPermission)) {
								echo '<option value="4">' . $LANG['UI_PLATFORM_DB'] . '</option>';
							}
							//操作系统
							if (in_array("os_protect", $userAllPermission)) {
								echo '<option value="5">' . $LANG['WEB_PLATFORM_DES_OS'] . '</option>';
							}
							// exchange
							if (in_array("office365_protect", $userAllPermission)) {
								echo '<option value="14">' . $LANG['WEB_PLATFORM_DES_M365'] . '</option>';
							}
							echo '
									</select>';
						}
					?>
				</div>
				<div class="timepoint-wrapper__content__tree tree-wrapper">
					<div class="tree-wrapper__operates">
						<div class="tree-wrapper__operates__select">
							<select class="bs-select form-control" data-show-subtext="true" id="storageSelect"></select>
						</div>
						
						<div class="input-icon" id="searchBtn">
							<input style="display:none" type="text" maxlength="128" placeholder="<?php echo $LANG['UI_COPY_SEARCH_AS_KEYWORD'] ?>" class="form-control " />
							<button type="button" id="filterSearch" class="btn btn-sm green-haze">
								<i class="viconfont vicon-ge_filter"></i> <?php echo $LANG['UI_DATA_SCREEN'] ?>
							</button>
						</div>
					</div>
					<div class="tree-wrapper__ztree copyDataTree">
						<p id="markStr"></p>
						<div class="alert alert-block alert-info fade in display-hide" id="noPointTips">
							<button type="button" class="close" data-dismiss="alert"></button>
							<ul class="alert-ul">
								<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
								<li>
									<?php echo $LANG['UI_COPY_DATA_NODATA_TIPS'] ?>
								</li>
							</ul>
						</div>
						<ul id="copyNodeTree" class="ztree ztree-fa tree-scroll"></ul>
						<div class="alert alert-block alert-info fade in display-hide" id="noSearchTips">
							<button type="button" class="close" data-dismiss="alert"></button>
							<ul class="alert-ul">
								<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
								<li>
									<?php echo $LANG['UI_DATA_NOSEARCH_TIPS'] ?>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-8 tabledata-wrapper">
            <div class="tabledata-wrapper__head">
                <i class="viconfont viconfont vicon-ge_backup_file"></i>
                <span class="tabledata-wrapper__head__title"><?php echo $LANG['UI_COPY_DATA_POINT'] ?></span>
                <span id="copyDataUrl" class="tabledata-wrapper__head__taskname"></span>
            </div>
            <div class="tabledata-wrapper__content">
				<div class="alert alert-block alert-info fade in" id="tableTips">
					<button type="button" class="close" data-dismiss="alert"></button>
					<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
					<ol class="alert-ol">
						<li>
							<?php echo $LANG['UI_COPY_DATA_VM_TIPS1'] ?>
						</li>
						<li>
							<?php echo $LANG['UI_COPY_DATA_VM_TIPS2'] ?>
						</li>
						<li>
							<?php echo $LANG['UI_COPY_DATA_VM_TIPS3'] ?>
						</li>
						<li>
							<?php echo $LANG['UI_COPY_DATA_VM_TIPS4'] ?>
						</li>
					</ol>
				</div>
                <div id="copyTable" class="tabledata-wrapper__content__table display-none">
					<div class="table-toolbar">
						<div class="row">
							<div class="col-md-8">
							</div>
							<div class="col-md-4 display-none">
								<div class="table-actions-wrapper page-right">
									<span>
									</span>
									<input id="searchAll" type="button" class="btn btn-sm default" value="<?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?>">
								</div>
							</div>
							<div class="col-md-10">
								<div id="searchDiv" class="display-none"><?php echo $LANG['UI_PUBLIC_SEARCH_CONDITION'] ?> <span style="color:#5b9bd1;" class="searchContent"></span> </div>
							</div>
						</div>
					</div>
					<div class="table-container"><table id="dataTable"></table></div>
					<div class="alert alert-block alert-info fade in display-none" id="markTips">
						<button type="button" class="close" data-dismiss="alert"></button>
						<ul class="alert-ul">
							<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
							<li>
								<?php echo $LANG['UI_COPY_DATA_STAR_TIPS'] ?>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<!-- BEGIN MODAL -->
		<div id="searchmodal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
			<input id="vcenteruuid" class="display-none"></input>
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="icon-badge"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?>
				</h4>
			</div>
			<div class="modal-body">
				<div class="portlet-body">

					<!-- 时间点范围 -->
					<div class="list-option" id="startTimeDiv">
						<div class="row">
							<label class="control-label col-md-3"><?php echo $LANG['UI_DATA_TIMEPOINT_RANGE'] ?> ：
							</label>
							<div class="col-md-4" style="padding:0 5px;">
								<div class="input-group date form_datetime">
									<input type="text" size="16" readonly id="startTime" class="form-control">
									<span class="input-group-btn">
										<button class="btn default" id="resetStartTime" type="button"><i class="fa fa-times"></i></button>
										<button class="btn default date-set" type="button"><i class="viconfont vicon-ge_calendar"></i></button>
									</span>
								</div>
							</div>
							<div class="col-md-4" style="padding:0 5px;">
								<div class="input-group date form_datetime">
									<input type="text" size="16" readonly id="endTime" class="form-control">
									<span class="input-group-btn">
										<button class="btn default" id="resetEndTime" type="button"><i class="fa fa-times"></i></button>
										<button class="btn default date-set" type="button"><i class="viconfont vicon-ge_calendar"></i></button>
									</span>
								</div>
							</div>
						</div>
					</div>

					<div class="list-option">
						<div class="row">
							<!-- 时间点类型 -->
							<label class="control-label col-md-2"><?php echo $LANG['UI_PUBLIC_TYPE'] ?> ：
							</label>
							<div class="col-md-4">
								<select class="form-control select2me" id="timepointType">
									<option value="0"><?php echo $LANG['UI_PUBLIC_ALL'] ?></option>
									<option value="1"><?php echo $LANG['UI_BACKUP_FULL'] ?></option>
									<option value="2"><?php echo $LANG['UI_BACKUP_INCREMENT'] ?></option>
									<option value="3"><?php echo $LANG['UI_BACKUP_DIFFERENCE'] ?></option>
								</select>
							</div>

							<!-- 是否标星 -->
							<label class="control-label col-md-2"> <?php echo $LANG['UI_DATA_STAR'] ?>：
							</label>
							<div class="col-md-4">
								<select class="form-control select2me" id="starFlag">
									<option value="0"><?php echo $LANG['UI_PUBLIC_ALL'] ?></option>
									<option value="1"><?php echo $LANG['UI_DATA_STAR_TAGGED'] ?></option>
									<option value="2"><?php echo $LANG['UI_DATA_STAR_UNTAGGED'] ?></option>
								</select>
							</div>
						</div>
					</div>



				</div>
			</div>

			<!-- modal-footer -->
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
				<button type="button" class="btn btn-primary" id="serach_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
			</div>
		</div>
		<!-- END MODAL -->

		<!-- BEGIN MODAL -->
		<div id="setAllMark" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
			<input id="Marktimepoint_uuid" class="display-none"></input>
			<input id="MarktimepointNodeUUID" class="display-none"></input>
			<input id="Marksubmode_type" class="display-none"></input>
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="viconfont vicon-ge_sign"></i> <?php echo $LANG['WEB_PT_OP_GFS_FLAG'] ?>
				</h4>
			</div>
			<div class="modal-body">
				<div class="portlet-body">

					<div style="margin-top: 10px;">
						<div class="row">
							<div class="col-md-2"></div>
							<div class="col-md-10">
								<label style="padding-right: 20px;">
									<input type="checkbox" name="foreverCheck" class="icheck">
									<i class="viconfont vicon-remark-forever"></i>
									<?php echo $LANG['WEB_VM_GFS_FOREVER_POINT'] ?>
								</label>
							</div>
						</div>
					</div>



				</div>
			</div>

			<!-- modal-footer -->
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
				<button type="button" class="btn btn-primary" id="mark_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
			</div>
		</div>
		<!-- END MODAL -->
		<div id="filter-content" class="display-none">
			<div class="modal-body">
				<div class="portlet-body">
					<div style="margin-top: 10px;">
						<div class="row">
							<div class="col-md-12">
								<label class="vmbackdata-padding_cn vmbackdata-padding_en">
									<input type="checkbox" name="foreverFilter" class="icheck">
									<i class="viconfont vicon-remark-forever"></i>
									<?php echo $LANG['WEB_VM_GFS_FOREVER_POINT'] ?>
								</label>
							</div>
						</div>
					</div>
					<div style="margin-top: 10px;">
						<input type="text" maxlength="128" placeholder="<?php echo $LANG['UI_COPY_SEARCH_AS_KEYWORD'] ?>" class="form-control " id="searchCopy">
					</div>
				</div>
			</div>
			<div class="popModal_footer">
				<button type="button" data-popModalBut="cancel" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
				<button type="button" data-popModalBut="ok" class="btn btn-primary"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
			</div>

		</div>

		<div id="syncPointData" class="modal xmodal fade" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title">
					<i class="viconfont vicon-ge_refresh mr8"></i><?php echo $LANG['WEB_M365_COMMON_OP_CODE_SYNC_META_FILE'] ?>
				</h4>
			</div>
			<div class="modal-body">
				<div class="portlet-body">
					<div class="row">
						<div class="form-group col-md-12">
							<label class="control-label col-md-3 text-align_r">
								<?php echo $LANG['WEB_M365_COMMON_OP_CODE_SIZE_META_FILE'] ?>:
							</label>
							<div class="col-md-6 pd0">
								<div class="syncSize">--</div>
							</div>
						</div>
						<div class="form-group col-md-12">
							<label class="control-label col-md-3 text-align_r">
								<?php echo $LANG['WEB_M365_SYNC_PROGRESS'] ?>:
							</label>
							<div class="syncSpeed col-md-8 pd0">
								<div class="progress progress-striped active margin0">
									<span id="total-progress" class="progress-bar progress-bar-success mr5" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></span>
									<span class="taskprogress" id="progressright">--</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="alert alert-block alert-info fade in mb0">
					<button type="button" class="close" data-dismiss="alert"></button>
					<ul class="alert-ul">
						<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
						<li> <?php echo $LANG['WEB_M365_SYNC_PROGRESS_TIPS'] ?></li>
					</ul>
				</div>
			</div>

			<!-- modal-footer -->
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
			</div>
		</div>
	</div>
</div>
<!-- END PAGE CONTENT-->


<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<?php
if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
	//如果不是英文,加载语言包
	echo '<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>';
}
?>
<script type="text/javascript" src="./scripts/plugins/popModal/popModal.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/copy/copydata.js"></script>
<!-- END PAGE LEVEL PLUGINS -->