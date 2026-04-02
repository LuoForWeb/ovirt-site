<?php include_once '../../tpl/permission.php';?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>

<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="task" href=" ./content/platform/jobs/jobs.php">
            <span><?php echo $LANG['UI_PLATFORM_CURRENT_JOB']?></span>
        </a>
    </li>
    <span>></span>
    <span class="curent"><?php echo $LANG['UI_PLATFORM_JOB_DETAILS'] ?></span>
</h3>
<!-- BEGIN PAGE CONTENT-->
<div class="row job-detail">
	<div class="col-md-12 job-detail__halftop">
	   <input id="taskuuid" value="<?php echo $_GET['uuid'];?>" class="display-none"></input>
	   <input id="pageUri" value="<?php echo $_SERVER['REQUEST_URI'];?>" class="display-none"></input>
	    <input id="instantflag" class="display-none"></input>
	    <!-- BEGIN VALIDATION STATES-->
	    
		<div class="portlet box blue-hoki portlet-instant-detail">
			<div class="portlet-title">
				<div class="caption">
					<i class="viconfont  vicon-vminstantrecover"></i><?php echo $LANG['UI_OS_INSTANT_RECOVERY_TASK_INFO'] ?>
				</div>
			</div>
			<div class="portlet-body">
			     <div class="tab-content row ">
					<div class="tab-pane active " id="info">
					   <div class="portlet-no-bgcolor col-md-12">
                			<div class="portlet-body width700 margin0auto">
                			     <div class="row" style="display:flex;align-items:baseline;margin:90px 0">
        								<div class="col-md-3" style="position:relative;">
        								    <div style="text-align:center">
        								    <img src="./img/os/os_server.png">
        								    </div>
        								    <div class="textonline pt15" style="position:relative;left:50%;transform:translateX(-50%);width:180px;text-align:center">
												<?php echo $LANG['UI_OS_BACKUP_SERVER'] ?>:
                                                <span style="color:#20c99a" id="serverip"></span>
                                            </div>
        								</div>
        								<div class="col-md-6 " style="position: relative;z-index: 1;">
											<div id="tasknormal" style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%)" class="display-none" >
												<img src="./img/os/data_transmission.gif" style="width:447px;margin-left:-17.5px">
											</div>
											<div style="width:139.5%;margin-left:-22.3%;height:6px;background:#EFEFF2" id="taskabnormal" class="display-none">
												<div style="position:absolute;margin-top:-13px;width:128.1%">
													<img  src="./img/os/status_abnormal.png"  style="position:relative;z-index:2;left:50%;transform:translateX(-50%);">
												</div>

											</div>
        								</div>
        								<div class="col-md-3" style="position:relative;">
        								    <div style="text-align:center">
        								    <!-- <img id="instanthostimg" src="./img/vm/instant/host.png"> -->
                                            <img id="oshostabnormal" src="./img/os/os_host_abnormal.png" style="display:none">
                                            <img id="oshostnormal" src="./img/os/os_host_normal.png">
        								    </div>
                                            <div class="textonline pt15" style="position:relative;left:50%;transform:translateX(-50%);width:180px;text-align:center">
                                                <?php echo $LANG['UI_JOB_HOST']?>:
                                                <span style="color:#20c99a" id="hostip"></span>
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
					<i class="viconfont vicon-ge_modify"></i><?php echo $LANG['UI_JOB_RUNING_LOG']?>
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
<script type="text/javascript" src="./scripts/os/os_instant_job_details.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	
