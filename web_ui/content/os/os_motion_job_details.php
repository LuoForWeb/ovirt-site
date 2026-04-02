<?php include_once '../../tpl/permission.php';?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/swiper/css/swiper.min.css"/>
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="task" href=" ./content/platform/jobs/jobs.php">
            <span><?php echo $LANG['UI_PLATFORM_CURRENT_JOB']?></span>
        </a>
    </li>
    <span>></span>
    <span class="curent"><?php echo $LANG['UI_PLATFORM_JOB_DETAILS']?></span>
</h3>
<div class="row job-detail" id="jobDetail">
    <div class="col-md-12 job-detail__halftop">
        <div class="col-md-8 job-detail__halftop__charts">
            <input id="taskuuid" value="<?php echo $_GET['uuid'];?>" class="display-none"></input>
            <input id="pageUri" value="<?php echo $_SERVER['REQUEST_URI'];?>" class="display-none"></input>
    		<!-- BEGIN DYNAMIC CHART PORTLET-->
    		<div class="portlet portlet box blue-hoki portlet-instant-detail">
    			<div class="portlet-title">
    				<div class="caption">
    					<i class="viconfont vicon-qianyixinxi"></i><?php echo $LANG['UI_OS_MIGRATION_INFO']?>
    				</div>
    			</div>
    			<div class="portlet-charts__body">
					<div class="portlet-charts__body__speedchart" style="position: relative;">
						<div class="row" style="position: relative; top:50%;transform:translateY(-50%)">
							<div class="col-md-8">
								<div class="row detaildiv" style="display:flex;align-items:baseline;">
									<div class="col-md-3" style="position:relative;">
										<div style="text-align:center">
										<img src="./img/os/os_server.png">
										</div>
										<div class="textonline pt15" style="position:relative;left:50%;transform:translateX(-50%);text-align:center;width:130%">
											<?php echo $LANG['UI_OS_BACKUP_SERVER']?>:
											<span style="color:#20c99a" id="serverip"></span>
										</div>
									</div>
									<div class="col-md-6 " style="position: relative;z-index: 1;">
										<div id="tasknormal" style="position:relative;left:27.5%;top:50%;transform:translate(-50%,-22%)" class="display-none">
											<img src="./img/os/data_transmission.gif" style="width: 140%;" >
											<img src="./img/os/data_retransmission.gif" style="width:140%">
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
										<img id="oshostabnormal" src="./img/os/os_host_abnormal.png" style="display:none" >
										<img id="oshostnormal" src="./img/os/os_host_normal.png" style="display:none">
										</div>
										<div class="textonline pt15" style="position:relative;left:50%;transform:translateX(-50%);text-align:center">
											<?php echo $LANG['UI_JOB_HOST']?>:
											<span style="color:#20c99a" id="hostip"></span>
										</div>
									</div>
								</div>
							</div>
							<div  class="col-md-4">
								<div style="background-color: #F1F3F5;margin-right:44px;height:187px;padding:12px">
									<div>
										<div style="color:#999999"><?php echo $LANG['UI_OS_INSTANT_RECOVERY_HOST_NAME']?>:</div>
										<div style="color:#0FBF98;word-break: break-all;" id="recoverhostname"></div>
									</div>
									<div style="margin-top: 12px;">
										<div style="color:#999999"><?php echo $LANG['UI_OS_MIGRATION_HOST_NAME']?>:</div>
										<div style="color:#0FBF98;word-break: break-all;" id="motionhostname"></div>
									</div>

								</div>
								

							</div>
	
						</div>
					</div>
					<div class="progressDiv display-none">
						<div class="progressDiv__label">
							<span>
								<?php echo $LANG['UI_JOB_TOTAL_PROGRESS'] ?>
							</span>
						</div>
						<div class="progress progress-striped active">
							<div id="total-progress" class="progress-bar progress-bar-success" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
							</div>
						</div>
						<div class="taskprogress" id="progressright"></div>
					</div>
				</div>
    		</div>
    		<!-- END DYNAMIC CHART PORTLET-->
        </div>
        
        <div class="col-md-4 job-detail__halftop__navtabs">
			<div class="portlet">
				<div class="portlet-body">
					<div class="tabbable tabbable-custom swiper-detail">
						<div class="swiper-button-prev_detail"></div>
						<div class="swiper-button-next_detail"></div>
						<ul class="nav nav-tabs">
							<li class="active nolb">
								<a href="#tab_1_1" data-toggle="tab" aria-expanded="true">
								<i class="viconfont vicon-gaiyao1"></i> <?php echo $LANG['UI_JOB_SUMMARY']?> </a>
							</li>
							<li class="" id="strategyli">
								<a href="#tab_1_2" data-toggle="tab" aria-expanded="false">
								<i class="viconfont vicon-celve1"></i> <?php echo $LANG['UI_JOB_STRATEGY']?> </a>
							</li>
							<li class="" id="modeli">
								<a href="#tab_1_3" data-toggle="tab" aria-expanded="false">
								<i class="viconfont vicon-ge_advanced"></i> <?php echo $LANG['UI_JOB_HIGH']?> </a>
							</li>
						</ul>
						<div class="tab-content">
							<div class="tab-pane active" id="tab_1_1">
							    <div class="portlet-body">
									<div class="row static-info taskOperateDiv display-none">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_EMERGENCY_PLAN_OPERATE']?>:
										</div>
										<div class="col-md-8 value">
    										<div class="btn-group">
                                            	<button type="button" class="btn btn-primary btn-sm   dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true"> 
                                            		<i class="glyphicon glyphicon-hand-up"></i> <?php echo $LANG['UI_PUBLIC_OPERATION']?> <i class="fa fa-angle-down"></i>
                                            	</button>
                                            	<ul class="dropdown-menu min-width100" role="menu" id="" >
                                            		<li id="taskStartFull"><a href="javascript:;" ><i class="viconfont vicon-ge_play"></i><?php echo $LANG['UI_BACKUP_FULL']?></a></li>
                                            		<li id="taskStartIncr"><a href="javascript:;" ><i class="viconfont vicon-ge_increment"></i><?php echo $LANG['UI_BACKUP_INCREMENT']?></a></li>
                                            		<li id="taskStartDiff"><a href="javascript:;" ><i class="viconfont vicon-ge_differentia_backup"></i><?php echo $LANG['UI_BACKUP_DIFFERENCE']?></a></li>
                                            		<li id="stoptask"><a href="javascript:;" ><i class="viconfont vicon-ge_suspend-copy"></i><?php echo $LANG['UI_GRAIN_OPERATE_STOP']?></a></li>
                                            	</ul>
                                            </div>
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name">
											 <?php echo $LANG['UI_TASK_REPORT_TASK_NAME']?>:
										</div>
										<div class="col-md-8 value" id="taskName" style="word-break:break-word; max-width:300px;">
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name">
											 <?php echo $LANG['UI_TASK_REPORT_TASK_TYPE']?>:
										</div>
										<div class="col-md-8 value" id="taskType">
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name">
											 <?php echo $LANG['UI_JOB_DETAIL_JOB_TYPE']?>:
										</div>
										<div class="col-md-8 value" id="status">
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name">
											 <?php echo $LANG['UI_JOB_TOTAL_SIZES']?>:
										</div>
										<div class="col-md-8 value" id="totalSize">
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name">
											 <?php echo $LANG['UI_JOB_ALREADY_COMPLETED_SIZE']?>:
										</div>
										<div class="col-md-8 value" id="currentSize">
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name">
											 <?php echo $LANG['UI_JOB_CURRENT_FLOW']?>:
										</div>
										<div class="col-md-8 value" id="speed">
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name">
											 <?php echo $LANG['UI_JOB_CURRENT_PROGRESS']?>:
										</div>
										<div class="col-md-8 value" id="progress">
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name">
											 <?php echo $LANG['UI_JOB_START_TIME']?>:
										</div>
										<div class="col-md-8 value" id="startTime">
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_JOB_INTERVAL_TIME']?>:
										</div>
										<div class="col-md-8 value" id="intervalTime">
										</div>
									</div>
								</div>
							</div>								
								
							<div class="tab-pane" id="tab_1_2">
							    <div class="portlet-body">
									<div class="row static-info">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER']?>:
										</div>
										<div class="col-md-8 value" id="transportEncrypt">
										</div>
									</div>
									<!-- 传输加密算法 -->
									<div class="row static-info transfer-encrypt-method-div display-none">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?>:
										</div>
										<div class="col-md-8 value" id="transferEncryptMethod">
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT']?>:
										</div>
										<div class="col-md-8 value" id="speedlimit" style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 4;overflow: hidden;">
										</div>
									</div>
									
									<div class="row static-info">
										<div class="col-md-4 name">
											 <?php echo $LANG['UI_GLOBAL_STRATEGY_CREATE_TIME']?>:
										</div>
										<div class="col-md-8 value" id="createTime">
										</div>
									</div>
								</div>
							</div>
							
							<div class="tab-pane" id="tab_1_3">
							    <div class="portlet-body">
									
									<div class="row static-info threadDiv">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_BACKUP_THREAD_NUM']?>:
										</div>
										<div class="col-md-8 value" id="threadNum">
										</div>
									</div>
									<div class="row static-info transferNetworkdiv">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_NODE_TRANSFER_NETWORK']?>:
										</div>
										<div class="col-md-8 value" id="transferNetwork">
										</div>
									</div>
									<!-- 重连次数 -->
									<div class="row static-info reconnectTimesDiv">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_BACKUP_RECONNECT_TIMES'] ?>:
										</div>
										<div class="col-md-8 value" id="reconnectTimes">
										</div>
									</div>
									<!-- 重连时间间隔 -->
									<div class="row static-info reconnectIntervalDiv">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_BACKUP_RECONNECT_INTERVAL'] ?>:
										</div>
										<div class="col-md-8 value" id="reconnectInterval">
										</div>
									</div>
									<div class="row static-info builtFlag">
										<div class="col-md-4 name">
											 <?php echo $LANG['WEB_OS_RESTORE_VOLUME']?>:
										</div>
										<div class="col-md-8 value" id="builtSize">
										</div>
									</div>
									<div class="row static-info display-none recoverOsFlag">
										<div class="col-md-4 name">
											<?php echo $LANG['WEB_OS_RESTORE_RECOVERY_OS'] ?>:
										</div>
										<div class="col-md-8 value" id="recoverOS">
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
		<div class="job-detail__halfbottom__portlet">
			<div class="portlet-title">
				<ul class="nav nav-tabs">
				    <li class="active ">
						<a href="#log" data-toggle="tab">
						<i class="viconfont vicon-tasklog"></i> <?php echo $LANG['UI_JOB_RUNING_LOG']?> </a>
					</li>
					<li>
						<a href="#os" data-toggle="tab">
						<i class="viconfont vicon-zhuji"></i> <?php echo $LANG['WEB_OS_LIST']?> </a>
					</li>
				</ul>
			</div>
			<div class="portlet-body">
				<div class="tab-content">
				    <div class="tab-pane active" id="log">
						<ul class="feeds" id="runninglog"></ul>
				    </div>
				    
					<div class="tab-pane" id="os">
						<div class="table-toolbar">
        					<div class="row">
        						<div class="col-md-12">
        							<div class="btn-group  <?php if(in_array("global_write", $_SESSION['permission']) || !in_array("global_observer", $_SESSION['permission']) ){echo "vmStartDiv";}?> display-none">
                						<button type="button" class="btn btn-primary btn-sm   dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true"> 
                        					<i class="glyphicon glyphicon-hand-up"></i> <?php echo $LANG['UI_PUBLIC_OPERATION']?> <i class="fa fa-angle-down"></i>
                        				</button>
                        				<ul class="dropdown-menu min-width100 mt30" role="menu" id="" >
                        					<li id="startFull"><a href="javascript:;" ><i class="viconfont vicon-ge_play"></i><?php echo $LANG['UI_BACKUP_FULL']?></a></li>
                        					<li id="startIncr"><a href="javascript:;" ><i class="viconfont vicon-ge_increment"></i><?php echo $LANG['UI_BACKUP_INCREMENT']?></a></li>
                        					<li id="startDiff"><a href="javascript:;" ><i class="viconfont vicon-ge_differentia_backup"></i><?php echo $LANG['UI_BACKUP_DIFFERENCE']?></a></li>
                        				</ul>
                    				</div>
        						</div>
        					</div>
        				</div>
        				<div class="table-container">
        					<table class="table table-striped table-bordered table-hover" id="ostable">
								<thead>
									<tr role="row" class="heading">
										<th width="2%">
											<?php echo $LANG['UI_PUBLIC_NUMBER']?>
										</th>
										<th width="18%">
											<?php echo $LANG['UI_OS_HOST']?>
										</th>
										<th width="10%">
											<?php echo $LANG['UI_TASK_REPORT_TASK_TYPE']?>
										</th>
										<th width="10%">
											<?php echo $LANG['WEB_OS_HOST_SIZE']?>
										</th>
										<th width="10%">
											<?php echo $LANG['UI_OS_VALID_DATA_SIZE']?>
										</th>
										<th width="10%">
											<?php echo $LANG['UI_JOB_TRANSFER_SIZE']?>
										</th>
										<th width="10%">
											<?php echo $LANG['UI_JOB_REAL_SIZE']?>
										</th>
										<th width="10%">
											<?php echo $LANG['UI_JOB_SPEED']?>
										</th>
										<th width="10%">
											<?php echo $LANG['UI_JOB_TRA_PROGRESS']?>
										</th>
										<th width="10%">
											<?php echo $LANG['UI_PUBLIC_STATUS']?>
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
	</div>
</div>
<!-- END PAGE CONTENT-->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./assets/global/plugins/echarts/V5.6.0/echarts.common.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script src="./assets/global/plugins/flot/jquery.flot.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/swiper/js/swiper.min.js"></script>
<script type="text/javascript" src="./scripts/os/os_motion_job_details.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	