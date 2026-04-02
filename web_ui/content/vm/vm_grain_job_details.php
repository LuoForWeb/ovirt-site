<?php include_once '../../tpl/permission.php'; ?>
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">

<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css" />
<link rel="stylesheet" type="text/css" href="./css/fs/filetype-small.css" />
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
<li>
        <?php
            echo
            '<a class="ajaxify" name="task" href=" ./content/platform/jobs/jobs.php">';
        ?>
        <span>
                <?php
                    echo $LANG['UI_PLATFORM_CURRENT_JOB'];
                ?>
            </span>
        </a>
    </li>
	<span>></span>
	<span class="curent"><?php echo $LANG['UI_PLATFORM_JOB_DETAILS'] ?></span>
</h3>
<!-- BEGIN PAGE CONTENT-->
<div class="row job-detail">
	<div class="col-md-12 col-detail-grain">
		<input id="task_uuid" value="<?php echo $_GET['uuid']; ?>" class="display-none"></input>
		<div class="col-md-4 col-detail-grain__left">
			<div class="portlet box blue-hoki">
				<div class="portlet-title">
					<div class="caption">
						<i class="levelchild viconfont vicon-ge_summary"></i> <?php echo $LANG['UI_JOB_SUMMARY'] ?>
					</div>
				</div>
				<div class="portlet-body" id="detailsbody">
					<div class="row static-info">
						<div class="col-md-4 name">
							<?php echo $LANG['UI_JOB_DETAIL_JOB_TYPE'] ?>:
						</div>
						<div class="col-md-8 value textvalue" id="status">

						</div>
					</div>
					<div class="row static-info">
						<div class="col-md-4 name">
							<?php echo $LANG['UI_VCENTER_MACHINE'] ?>:
						</div>
						<div class="col-md-8 value textvalue col-taskname" id="vmname"></div>
					</div>
					<div class="row static-info">
						<div class="col-md-4 name">
							<?php echo $LANG['UI_RECOVERY_BACKUP_POINT'] ?>:
						</div>
						<div class="col-md-8 value textvalue" id="timepoint"></div>
					</div>
					<div class="row static-info">
						<div class="col-md-4 name">
							<?php echo $LANG['UI_BACKUP_DATA_EXPORT_OPERATION'] ?> :
						</div>
						<div class="col-md-8 value textvalue">
							<div class="btn-group">
								<button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">
									<i class="glyphicon glyphicon-hand-up"></i> <?php echo $LANG['UI_GRAIN_OPERATE'] ?> <i class="fa fa-angle-down"></i>
								</button>
								<ul class="dropdown-menu min-width100" role="menu">
									<li id="start">
										<a href="javascript:;">
											<i class="viconfont vicon-ge_play"></i> <?php echo $LANG['WEB_JOB_START'] ?> </a>
									</li>
									<li id="stop">
										<a href="javascript:;">
											<i class="viconfont vicon-ge_suspend-copy"></i> <?php echo $LANG['UI_GRAIN_OPERATE_STOP'] ?> </a>
									</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="alert alert-block alert-info fade in " id="marktips1">
						<button type="button" class="close" data-dismiss="alert"></button>
						<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
						<ol class="alert-ol">
							<li>
								<?php echo $LANG['UI_GRAIN_INSTRUCTIONS_TIPS_1'] ?>
							</li>
							<li>
								<?php echo $LANG['UI_GRAIN_INSTRUCTIONS_TIPS_2'] ?>
							</li>
						</ol>
					</div>
				</div>
			</div>

			<div class="portlet box blue-hoki">
				<div class="portlet-title">
					<div class="caption">
						<i class="levelchild viconfont vicon-ge_running_log"></i> <?php echo $LANG['UI_JOB_RUNING_LOG'] ?>
					</div>
				</div>
				<div class="portlet-body">
					<div class="tab-content row">
						<div class="tab-pane active" id="log">
							<ul class="feeds" id="runninglog"></ul>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-8 col-detail-grain__right">
			<!-- BEGIN TAB PORTLET-->
			<div class="portlet box blue-hoki">
				<div class="portlet-title">
					<div class="caption">
						<i class="levelchild viconfont vicon-vmrecovera"></i> <?php echo $LANG['UI_GRAIN_FILE_RECOVERY'] ?>
					</div>
				</div>
				<div class="portlet-body" id="filediv">
					<div class="portlet-body__form">
						<div class="portlet-body__form__item">
							<label class="control-label floatl"> <?php echo $LANG['UI_GRAIN_DISPLAY_MODE'] ?>
							</label>
							<div class="col-md-8">
								<select class="bs-select width50p" data-show-subtext="true" id="fileshowtype">
									<option data-icon="systemdir icon-default" value="1"><?php echo $LANG['UI_GRAIN_SYSTEM_DIR'] ?></option>
									<option data-icon="normaldevice icon-default" value="2"><?php echo $LANG['UI_GRAIN_NORMAL_DEVICE'] ?></option>
									<option data-icon="logicaldevice icon-default" value="3"><?php echo $LANG['UI_GRAIN_LOGICAL_DEVICE'] ?></option>
								</select>
							</div>
						</div>
						<div class="portlet-body__form__item">
							<div class="portlet-body__form__item__left">
								<div class="btn-group">
									<a class="btn backup">
										<i class="viconfont vicon-arrow"></i>
									</a>
								</div>
								<div class="btn-group filepath"><a class="intofile" data-root="true" title=""><?php echo $LANG['UI_GRAIN_ALL_FILES'] ?></a> &gt; </div>
							</div>
							<div class="portlet-body__form__item__right">
								<div class="table-actions-wrapper page-right">
									<span>
									</span>
									<input id="searchinput" type="search" maxlength="128" class="searchinput table-group-action-input form-control input-inline input" placeholder="<?php echo $LANG['UI_GRAIN_FILE_SEARCH'] ?>" aria-controls="example">
								</div>
							</div>
						</div>
					</div>
					<div class="portlet-body__table">
						<table class="table table-bordered table-hover" style="margin-bottom: 0px;border-bottom: 0px;">
							<colgroup style="width: 45%;"></colgroup>
							<colgroup style="width: 12%;"></colgroup>
							<colgroup style="width: 25%;"></colgroup>
							<colgroup style="width: 18%;"></colgroup>
							<thead>
								<tr>
									<th style="width: 45%;">
										<?php echo $LANG['UI_BACKUP_FILE_FILENAME'] ?>
									</th>
									<th style="width: 12%;">
										<?php echo $LANG['UI_BACKUP_FILE_FILESIZE'] ?>
									</th>
									<th style="width: 25%;" id="thirdcolumn">
										<?php echo $LANG['UI_DATA_FILE_MODIFY_TIME'] ?>
									</th>
									<th style="width: 18%;">
										<?php echo $LANG['UI_PUBLIC_OPERATION'] ?>
									</th>
								</tr>
							</thead>
						</table>
						<div class="scroller" id="fileScrollerDiv">
							<table class="table table-bordered table-hover mb0">
								<colgroup style="width: 45%;"></colgroup>
								<colgroup style="width: 12%;"></colgroup>
								<colgroup style="width: 25%;"></colgroup>
								<colgroup style="width: 18%;"></colgroup>
								<tbody id="filetbody">
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<!-- END TAB PORTLET-->
		</div>

		<!-- END DYNAMIC CHART PORTLET-->
	</div>
</div>
<!-- END PAGE CONTENT-->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/vm/vm_grain_job_details.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<!-- END PAGE LEVEL PLUGINS -->