<?php include_once '../../tpl/permission.php';?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
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
<div class="row">
	<div class="col-md-12">
	    <input id="taskuuid" value="<?php echo $_GET['uuid'];?>" class="display-none"></input>
	    <input id="pageUri" value="<?php echo $_SERVER['REQUEST_URI'];?>" class="display-none"></input>
		<input id="motionflag" class="display-none"></input>
	    <div class="portlet box blue-hoki oldhostmotion">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-desktop"></i><?php echo $LANG['UI_JOB_MOTION_INFO']?>
				</div>
			</div>
			<div class="portlet-body">
			     <div class="tab-content row ">
					<div class="tab-pane active ">
					
					   <div class="portlet-no-bgcolor col-md-12">
                			<div class="portlet-body width700 margin0auto">
                			     <div class="row detaildiv">
        								<div class="col-md-3">
        								    <div>
        								        <img src="./img/vm/instant/nfs.png">
        								    </div>
        								    <div>
        								        <img src="./img/vm/instant/vinchin.png">
            								    <img class="pt54" src="./img/vm/instant/datastore.png">
        								    </div>
        								    <div>
        								        <img id="oldmotion2dataimg" src="">
        								    </div>
        								    <div class="textonline">
        								        <div><?php echo $LANG['UI_JOB_DATACENTER']?></div>
        								        <div><span class="font-blue" id="oldserver"></span></div>
        								    </div>
        								</div>
        								<div class="col-md-4 ">
        								    <div class="pt30">
        								        <img id="oldmotion2nfsimg" src="">
        								    </div>
        								</div>
        								<div class="col-md-3 ">
        								    <div>
        								        <img id="oldinstantvm" src="./img/vm/instant/vm.png">
        								        <img id="oldmotionvm" src="./img/vm/instant/vm.png">
        								    </div>
        								    <div>
            								    <img id="oldinstanthost" src="./img/vm/instant/host.png">
            								    <img class="pt54" src="./img/vm/instant/datastore.png"> 
        								    </div>
        								    <div  class="instantHost min-width500 pt30">
        								        <div><?php echo $LANG['UI_JOB_HOST']?>:<span class="font-blue" id="oldinstanthostip"></span></div>
        								        <div><?php echo $LANG['UI_JOB_INSTANT_VMNAME']?>:<span class="font-blue break-word" id="oldinstantvmname"></span></div>
        								        <div><?php echo $LANG['UI_JOB_MOTION_VMNAME']?>:<span class="font-blue break-word" id="oldmotionvmname"></span></div>
        								    </div>
        								</div>
                			     </div>
                			</div>
                	   </div>
                	</div>
        		</div>
			</div>
		</div>
		
		
		<div class="portlet box blue-hoki newhostmotion">
			<div class="portlet-title">
				<div class="caption">
					<i class="fa fa-desktop"></i><?php echo $LANG['UI_JOB_MOTION_INFO']?>
				</div>
			</div>
			<div class="portlet-body">
			     <div class="tab-content row ">
					<div class="tab-pane active ">
					
					   <div class="portlet-no-bgcolor col-md-12">
                			<div class="portlet-body width1050 margin0auto">
                			     <div class="row detaildiv">
        								<div class="col-md-3">
        								    <div>
        								        <img src="./img/vm/instant/nfs.png">
        								    </div>
        								    <div>
            								    <img src="./img/vm/instant/vinchin.png">
            								    <img class="pt54" src="./img/vm/instant/datastore.png">
        								    </div>
        								    <div>
        								        <img id="newmotion2dataimg" style="width:720px;" src="">
        								    </div>
        								    <div class="textonline ">
        								        <div><?php echo $LANG['UI_JOB_DATACENTER']?></div>
        								        <div><span class="font-blue" id="newserver"></span></div>
        								    </div>
        								</div>
        								<div class="col-md-2 ">
        								    <div class="pt15">
        								        <img id="newmotion2nfsimg" src="">
        								    </div>
        								</div>
        								<div class="col-md-3">
        								    <div>
        								        <img id="newinstantvm" src="./img/vm/instant/vm.png">
        								    </div>
        								    <div>
        								        <img id="newinstanthost" src="./img/vm/instant/host.png">
        								    </div>
        								    <div  class="min-width340 pt30" style="max-width: 340px !important;">
        								        <div><?php echo $LANG['UI_JOB_HOST']?>:<span class="font-blue" id="newinstanthostip"></span></div>
        								        <div><?php echo $LANG['UI_JOB_INSTANT_VMNAME']?>:<span class="font-blue break-word" id="newinstantvmname"></span></div>
        								    </div>
        								</div>
        								<div class="col-md-1 ">
        								</div>
        								<div class="col-md-3 ">
        								    <div>
        								        <img id="newmotionvm" src="./img/vm/instant/vm.png">
        								    </div>
        								    <div>
        								        <img id="newmotionhost" src="./img/vm/instant/host.png">
            								    <img class="pt54" src="./img/vm/instant/datastore.png">
        								    </div>
        								    <div  class="motionHost min-width340 pt30">
        								        <div><?php echo $LANG['UI_JOB_HOST']?>:<span class="font-blue" id="newmotionhostip"></span></div>
        								        <div><?php echo $LANG['UI_JOB_MOTION_VMNAME']?>:<span class="font-blue break-word" id="newmotionvmname"></span></div>
        								    </div>
        								</div>
                			     </div>
                			</div>
                	   </div>
                	</div>
        		</div>
			</div>
		</div>
		
		
		
		<div><span><h4><?php echo $LANG['UI_JOB_TOTAL_PROGRESS']?></h4></span><span class="taskprogress" id="progressright"></span></div>
        <div class="progress progress-striped active">
        	<div id="total-progress" class="progress-bar progress-bar-success" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
        	</div>
        </div>
        <!-- BEGIN TAB PORTLET-->
		<div class="portlet box blue-hoki">
			<div class="portlet-title">
				<ul class="nav nav-tabs floatl">
				
				    <li class="active ">
						<a href="#log" data-toggle="tab">
						<i class="viconfont vicon-ge_running_log "></i> <?php echo $LANG['UI_JOB_RUNING_LOG']?> </a>
					</li>
					<li>
						<a href="#info" data-toggle="tab">
						<i class="viconfont vicon-ge_summary"></i> <?php echo $LANG['UI_JOB_BASE_INFO']?> </a>
					</li>
					<li>
						<a href="#vms" data-toggle="tab">
						<i class="viconfont vicon-pt_report_vm_report "></i> <?php echo $LANG['UI_VCENTER_MACHINE_LIST']?> </a>
					</li>
					
				</ul>
			</div>
			<div class="portlet-body">
				<div class="tab-content row margin10">
				<div class="tab-pane active" id="log">
        				<div class="scroller" style="height: 270px;" data-always-visible="1" data-rail-visible="0">
        					<ul class="feeds" id="runninglog">
        					</ul>
        				</div>
					</div>
					
					<div class="tab-pane " id="info">
						<!-- BEGIN Portlet PORTLET-->
                		<div class="portlet col-md-4">
                			<div class="portlet-body">
                			     <div class="row detaildiv ">
                			        <div class="form-group min-width260">
										<label class="col-md-4 detailslable"><?php echo $LANG['UI_TASK_REPORT_TASK_NAME']?>:</label>
										<div class="col-md-6">
											<p id="taskName">
											</p>
										</div>
									</div>
									<div class="form-group min-width260">
										<label class="col-md-4 detailslable"><?php echo $LANG['UI_BACKUP_DATA_REPORT_MODULE_TYPE']?>:</label>
										<div class="col-md-5">
											<p id="moduleType">
											</p>
										</div>
									</div>
									<div class="form-group min-width260">
										<label class="col-md-4 detailslable"><?php echo $LANG['UI_TASK_REPORT_TASK_TYPE']?>:</label>
										<div class="col-md-5">
											<p id="taskType">
											</p>
										</div>
									</div>
									<div class="form-group min-width260">
										<label class="col-md-4 detailslable"><?php echo $LANG['UI_JOB_CREATOR']?>:</label>
										<div class="col-md-5">
											<p id="user">
											</p>
										</div>
									</div>
									<div class="form-group min-width260 transferModeDiv">
										<label class="col-md-4 detailslable"><?php echo $LANG['UI_BACKUP_TRANSPORT_MODE']?>:</label>
										<div class="col-md-5">
											<p id="transferMode">
											</p>
										</div>
									</div>
									<div class="form-group min-width260">
										<label class="col-md-4 detailslable"><?php echo $LANG['UI_BACKUP_THREAD_NUM']?>:</label>
										<div class="col-md-5">
											<p id="threadNum">
											</p>
										</div>
									</div>
									<div class="form-group min-width260 transferNetDiv">
										<label class="col-md-4 detailslable"><?php echo $LANG['UI_MIGRATE_TRANSFER_NET']?>:</label>
										<div class="col-md-5">
											<p id="transferNet">
											</p>
										</div>
									</div>
                			     </div>
                			</div>
                		</div>
                		<!-- END Portlet PORTLET-->
                		
                		<!-- BEGIN Portlet PORTLET-->
                		<div class="portlet col-md-3">
                			<div class="portlet-body">
                			     <div class="row detaildiv">
                			        <div class="form-group min-width260">
										<label class="col-md-5 detailslable "><?php echo $LANG['UI_JOB_DETAIL_JOB_TYPE']?>:</label>
										<div class="col-md-5">
											<p id="status">
											</p>
										</div>
									</div>
									<div class="form-group min-width260" >
										<label class="col-md-5 detailslable "><?php echo $LANG['UI_JOB_TOTAL_SIZES']?>:</label>
										<div class="col-md-5">
											<p id="totalSize">
											</p>
										</div>
									</div>
									<div class="form-group min-width260">
										<label class="col-md-5 detailslable "><?php echo $LANG['UI_JOB_ALREADY_COMPLETED_SIZE']?>:</label>
										<div class="col-md-5">
											<p id="currentSize">
											</p>
										</div>
									</div>
									<div class="form-group min-width260">
										<label class="col-md-5 detailslable "><?php echo $LANG['UI_JOB_CURRENT_FLOW']?>:</label>
										<div class="col-md-5">
											<p id="speed">
											</p>
										</div>
									</div>
									<div class="form-group min-width260">
										<label class="col-md-5 detailslable "><?php echo $LANG['UI_JOB_CURRENT_PROGRESS']?>:</label>
										<div class="col-md-5">
											<p id="progress">
											</p>
										</div>
									</div>
                			     </div>
                			</div>
                		</div>
                		<!-- END Portlet PORTLET-->
                		
                		<!-- BEGIN Portlet PORTLET-->
                		<div class="portlet col-md-5">
                			<div class="portlet-body">
                			     <div class="row detaildiv">
                			        <div class="form-group min-width400">
										<label class="col-md-4 detailslable "><?php echo $LANG['UI_JOB_CREATE_TIME']?>:</label>
										<div class="col-md-8">
											<p id="createTime">
											</p>
										</div>
									</div>
									<div class="form-group min-width400">
										<label class="col-md-4 detailslable "><?php echo $LANG['UI_JOB_START_TIME']?>:</label>
										<div class="col-md-8">
											<p id="startTime">
											</p>
										</div>
									</div>
<!--									<div class="form-group min-width400">-->
<!--										<label class="col-md-4 detailslable ">--><?php //echo $LANG['UI_JOB_ESTIMATED_OVER_TIME']?><!--:</label>-->
<!--										<div class="col-md-8">-->
<!--											<p id="endTime">-->
<!--											</p>-->
<!--										</div>-->
<!--									</div>-->
									<div class="form-group reservedDiv  min-width400">
										<label class="col-md-4  detailslable "><?php echo $LANG['UI_BACKUP_RESERVE_STRATEGY']?>:</label>
										<div class="col-md-8">
											<p id="reservedStrategy">
											</p>
										</div>
									</div>
                			     </div>
                			</div>
                		</div>
                		<!-- END Portlet PORTLET-->
					</div>
					
					<div class="tab-pane" id="vms">
        				<div class="table-container margin10">
        					<table class="table table-striped table-bordered table-hover" id="vmstable">
        					<thead>
        					<tr role="row" class="heading">
        						<th width="2%">
        							<?php echo $LANG['UI_PUBLIC_NUMBER']?>
        						</th>
        						<th width="15%">
        							<?php echo $LANG['UI_VCENTER_MACHINE_NAME']?>
        						</th>
        						<th width="10%">
        							<?php echo $LANG['UI_PUBLIC_TASK_TYPE']?>
        						</th>
        						<th width="8%">
        							<?php echo $LANG['UI_JOB_VM_SIZE']?>
        						</th>
        						<th width="8%">
        						
									<?php echo $LANG['UI_OS_VALID_DATA_SIZE']?>
        						</th>
        						<th width="8%">
        							<?php echo $LANG['UI_JOB_TRANSFER_SIZE']?>
        						</th>
        						<th width="8%">
        							<?php echo $LANG['UI_JOB_REAL_SIZE']?>
        						</th>
        						<th width="8%">
        							<?php echo $LANG['UI_JOB_SPEED']?>
        						</th>
        						<th width="10%">
        							 <?php echo $LANG['UI_JOB_PROGRESS']?>
        						</th>
        						<th width="10%">
        							 <?php echo $LANG['UI_PUBLIC_STATUS']?>
        						</th>
        						<th width="20%">
        							 <?php echo $LANG['UI_PUBLIC_DESCRIPTION']?>
        						</th>
        					</tr>
        					</thead>
        					<tbody>
        					</tbody>
        					</table>
        				</div>
					</div>
					
					
				</div>
			</div>
		</div>
		<!-- END TAB PORTLET-->
        
        
	</div>
</div>
<!-- END PAGE CONTENT-->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/vm/vm_motion_job_details.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	