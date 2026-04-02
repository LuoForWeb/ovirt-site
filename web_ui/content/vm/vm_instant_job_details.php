<?php include_once '../../tpl/permission.php'; ?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css" />

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
	<div class="col-md-12 job-detail__halftop">
		<input id="taskuuid" value="<?php echo $_GET['uuid']; ?>" class="display-none"></input>
		<input id="pageUri" value="<?php echo $_SERVER['REQUEST_URI']; ?>" class="display-none"></input>
		<input id="instantflag" class="display-none"></input>
		<!-- BEGIN VALIDATION STATES-->

		<div class="portlet box blue-hoki portlet-instant-detail">
			<div class="portlet-title">
				<div class="caption">
					<i class="viconfont vicon-ge_vm"></i><?php echo $LANG['UI_JOB_INSTANT_INFO'] ?>
				</div>
			</div>
			<div class="portlet-body">
				<div class="tab-content row">
					<div class="tab-pane active" id="info">
						<div class="portlet-no-bgcolor col-md-12">
							<div class="portlet-body width700 margin0auto">
								<div class="row detaildiv">
									<div class="col-md-2">
										<div>
											<img src="./img/vm/instant/nfs.png">
										</div>
										<div>
											<img src="./img/vm/instant/vinchin.png">
										</div>
										<div class="textonline pt15">
											<div style="width: 115px;"><?php echo $LANG['UI_JOB_DATACENTER'] ?></div>
											<div><span class="font-blue" id="serverip"></span></div>
										</div>
									</div>
									<div class="col-md-6 ">
										<div class="pt15">
											<img id="instantnfsimg" src="">
										</div>
									</div>
									<div class="col-md-2 ">
										<div>
											<img id="instantvmimg" src="./img/vm/instant/vm.png">
										</div>
										<div>
											<img id="instanthostimg" src="./img/vm/instant/host.png">
										</div>
										<div class="instantHost min-width500 pt15">
											<div><?php echo $LANG['UI_JOB_HOST'] ?>:<span class="font-blue" id="hostip"></span></div>
											<div><?php echo $LANG['UI_VCENTER_MACHINE_NAME'] ?>:<span class="font-blue break-word" id="vmname"></span></div>
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

	<div class="col-md-12 job-detail__halfbottom">
		<div class="portlet box blue-hoki portlet-instant-detail">
			<div class="portlet-title">
				<div class="caption">
					<i class="viconfont vicon-tasklog"></i><?php echo $LANG['UI_JOB_RUNING_LOG'] ?>
				</div>
			</div>
			<div class="portlet-body">
				<div class="tab-content row">
					<div class="tab-pane active" id="log">
						<ul class="feeds" id="runninglog">
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- END PAGE CONTENT-->
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/vm/vm_instant_job_details.js"></script>
<!-- END PAGE LEVEL PLUGINS -->