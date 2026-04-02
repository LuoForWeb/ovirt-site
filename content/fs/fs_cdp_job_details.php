<?php include_once '../../tpl/permission.php';?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <?php
        if($_GET['isCurrent'])
            echo
            '<a class="ajaxify" name="task" href=" ./content/platform/jobs/jobs.php">';
        else
            echo
            '<a class="ajaxify" name="task" href=" ./content/platform/jobs/jobs.php? tab=6">';  ?>
        <span>
                <?php
                if($_GET['isCurrent'])
                    echo $LANG['UI_PLATFORM_CURRENT_JOB'];
                else
                    echo 'CDP';  ?>
            </span>
        </a>
    </li>
    <span>></span>
    <span class="curent"><?php echo $LANG['UI_PLATFORM_JOB_DETAILS']?></span>
</h3>
<!-- BEGIN PAGE CONTENT-->
<div class="row">
    <div class="col-md-12">
        <div class="col-md-8">
            <input id="task_uuid" value="<?php echo $_GET['uuid'];?>" class="display-none"></input>
    		<!-- BEGIN DYNAMIC CHART PORTLET-->
    		<div class="portlet box ">
    			<div class="portlet-body width700 margin0auto" style="background-color: #f7f7f7;">
    			     <div class="row detaildiv">
    						<div class="col-md-offset-1 col-md-2">
    						    <div>
    						    <img id="productdb" src="./img/db/task/db-running.gif">
    						    </div>
    						    <div>
    						    <img id="producthost" src="./img/db/task/product-host.png" style="margin-left: 40px;">
    						    </div>
    						    <div class="textonline pt15">
    						        <div style="width: 115px;margin-left: 40px;">生产主机</div>
    						        <div style="width: 200px;height: 80px;text-align: center;margin: 10px 0 0 -30px;word-wrap:break-word;word-break:normal;"><span class="font-blue" id="producthostname"></span></div>
    						    </div>
    						</div>
    						<div class="col-md-6 " style="margin: 60px 0 0 0px;">
    						    <div class="pt15">
    						        <img id="dataline" src="./img/db/task/data-line.png">
    						    </div>
    						</div>
    						<div class="col-md-2 ">
    						    <div>
    						    <img id="standbydb" src="./img/db/task/db-waiting.png">
    						    </div>
    						    <div>
    						    <img id="standbyhost" src="./img/db/task/standby-host.png"  style="margin-left: 40px;">
    						    </div>
    						    <div  class="min-width500 pt15">
    						    <div style="width: 115px;margin-left: 40px;">备份主机</div>
    						        <div style="width: 200px;height: 80px;text-align: center;margin: 10px 0 0 -30px;word-wrap:break-word;word-break:normal;"><span class="font-blue" id="standbyhostname"></span></div>
    						    </div>
    						</div>
    			     </div>
    			</div>
    		</div>
    		<!-- END DYNAMIC CHART PORTLET-->
    		
    		
        </div>
        
        <div class="col-md-4 " style="padding: 0px;">
			<!-- BEGIN PORTLET-->
			<div class="portlet paddingless">
				<div class="portlet-body">
					<!--BEGIN TABS-->
					<div class="tabbable tabbable-custom" style="overflow: inherit;">
						<ul class="nav nav-tabs">
							<li class="active">
								<a href="#tab_1_1" data-toggle="tab" aria-expanded="true">
								<i class="viconfont vicon-ge_summary font-green-seagreen"></i> 概要信息 </a>
							</li>
							<li class="" id="">
								<a href="#tab_1_2" data-toggle="tab" aria-expanded="false">
								<i class="viconfont vicon-ge_advanced font-green-seagreen"></i> 高级配置 </a>
							</li>
							<li class=""  id="historytab">
								<a href="#tab_1_3" data-toggle="tab" aria-expanded="false">
								<i class="fa fa-random font-green-seagreen"></i> 历史数据 </a>
							</li>
							<li class="" id="hostlog">
								<a href="#tab_1_4" data-toggle="tab" aria-expanded="false">
								<i class="fa fa-list-alt font-green-seagreen"></i> 主机日志 </a>
							</li>
						</ul>
						<div class="tab-content min-height266">
							<div class="tab-pane active" id="tab_1_1">
							    <div class="portlet-body">
									<div class="row static-info">
										<div class="col-md-4 name">
											 操作:
										</div>
										<div class="col-md-8 value">
										    <div class="btn-group">
                								<button class="btn green-haze btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">
                								<i class="glyphicon glyphicon-hand-up"></i> <?php echo $LANG['UI_GRAIN_OPERATE']?> <i class="fa fa-angle-down"></i>
                								</button>
                								<ul class="dropdown-menu min-width100" role="menu">
                									<li id="start">
                										<a href="javascript:;">
                										<i class="glyphicon glyphicon-play"></i> <?php echo $LANG['WEB_JOB_START']?> </a>
                									</li>
                									<li id="stop">
                										<a href="javascript:;">
                										<i class="glyphicon glyphicon-stop"></i> <?php echo $LANG['UI_GRAIN_OPERATE_STOP']?> </a>
                									</li>
                								</ul>
                							</div>
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name">
											 <?php echo $LANG['UI_JOB_RNAME']?>:
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
											 同步类型:
										</div>
										<div class="col-md-8 value" id="backupType">
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
											 多线程传输:
										</div>
										<div class="col-md-8 value" id="multithreading">
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name">
											 目录文件镜像:
										</div>
										<div class="col-md-8 value" id="mirrorimage">
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name">
											 空目录同步:
										</div>
										<div class="col-md-8 value" id="emptydir">
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name">
											 时间策略:
										</div>
										<div class="col-md-8 value" id="strategy">
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name">
											时间过滤:
										</div>
										<div class="col-md-8 value" id="timefilter">
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name">
											名称过滤:
										</div>
										<div class="col-md-8 value" id="namefilter">
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name">
											防勒索病毒:
										</div>
										<div class="col-md-8 value" id="ransomwaretype">
										</div>
									</div>
								</div>
							</div>
							
							<div class="tab-pane" id="tab_1_3">
							    <div class="portlet-body">
							        <div class="row static-info">
										<div class="col-md-5 name">
											历史数据目录:
										</div>
										<div class="col-md-7 value" id="historyDir">
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-5 name">
											保留时间:
										</div>
										<div class="col-md-7 value" id="historytime">
										</div>
									</div>
									<div class="row static-info ">
										<div class="col-md-5 name">
											每天删除文件保留:
										</div>
										<div class="col-md-7 value" id="historydel">
										</div>
									</div>
									<div class="row static-info ">
										<div class="col-md-5 name">
											每天修改文件保留:
										</div>
										<div class="col-md-7 value" id="historymod">
										</div>
									</div>
									<div class="row static-info ">
										<div class="col-md-5 name">
											修改文件间隔:
										</div>
										<div class="col-md-7 value" id="historytimeinterval">
										</div>
									</div>
								</div>
							</div>
							
							<div class="tab-pane" id="tab_1_4">
							    <div class="portlet-body">
							        <div class="row static-info">
										<div class="col-md-4 name" style="margin-top: 7px;">
											 生产主机:
										</div>
										<div class="col-md-8 value">
											<button type="button" class="btn btn-sm green-haze" id="downloadplog">
    											<i class="viconfont vicon-ge_download"></i> 下载日志
    										</button>
										</div>
									</div>
									<div class="row static-info">
										<div class="col-md-4 name" style="margin-top: 7px;">
											 备份主机:
										</div>
										<div class="col-md-8 value">
											<button type="button" class="btn btn-sm green-haze" id="downloadslog">
    											<i class="viconfont vicon-ge_download"></i> 下载日志
    										</button>
										</div>
									</div>
								</div>
							</div>
							
						</div>
					</div>
					<!--END TABS-->
				</div>
			</div>
			<!-- END PORTLET-->
		</div>
        
    </div>
	<div class="col-md-12">
        <!-- BEGIN TAB PORTLET-->
		<div class="portlet box blue-hoki">
			<div class="portlet-title">
				<ul class="nav nav-tabs floatl" id="runul">
				    <li class="active ">
						<a href="#log" data-toggle="tab">
						<i class="viconfont vicon-ge_running_log "></i> <?php echo $LANG['UI_JOB_RUNING_LOG']?> </a>
					</li>
					<li id="fileinfos">
						<a href="#files" data-toggle="tab">
						<i class="fa fa-file "></i> <?php echo $LANG['UI_JOB_FILE_INFO']?> </a>
					</li>
				</ul>
			</div>
			<div class="portlet-body">
				<div class="tab-content row margin10" id="tabcontentrun">
				    <div class="tab-pane active" id="log">
				        <div class="scroller" style="height: 270px;" data-always-visible="1" data-rail-visible="0">
        					<ul class="feeds" id="runninglog">
        					</ul>
        				</div>
				    </div>
				    
					<div class="tab-pane" id="files">
                		<div class="row">
						  <div class="col-md-12">
                            <div class="form-group">
								<label class="control-label col-md-2"> <?php echo "生产主机"?>:</span>
								</label>
								<div class="col-md-2">
									<p id="producthostName"></p>
								</div>
								<label class="control-label col-md-2"> <?php echo "备份主机"?>:</span>
								</label>
								<div class="col-md-2">
									<p id="standbyhostName"></p>
								</div>
							</div>
						  </div>
						  <div class="col-md-12">
                            <div class="form-group">
								<label class="control-label col-md-2"> <?php echo "同步总大小"?>:</span>
								</label>
								<div class="col-md-2">
									<p id="totalSize"></p>
								</div>
								<label class="control-label col-md-2"> <?php echo "目录总数"?>:</span>
								</label>
								<div class="col-md-2">
									<p id="dirTotal"></p>
								</div>
								<label class="control-label col-md-2"> <?php echo "文件总数"?>:</span>
								</label>
								<div class="col-md-2">
									<p id="fileTotal"></p>
								</div>
							</div>
						  </div>
                          <div class="col-md-12">
                            <div class="form-group">
								<label class="control-label col-md-2"> <?php echo "完成文件数"?>:</span>
								</label>
								<div class="col-md-2">
									<p id="finishTotal"></p>
								</div>
								
								<label class="control-label col-md-2"> <?php echo "成功"?>:</span>
								</label>
								<div class="col-md-2">
									<p id="successTotal"></p>
								</div>
								
								<label class="control-label col-md-2"> <?php echo "失败"?>:</span>
								</label>
								<div class="col-md-2">
									<p id="failureTotal"></p>
								</div>
							</div>
                          </div>
                          <div class="col-md-12">
                            <div class="form-group">
								<label class="control-label col-md-2"> <?php echo "同步数据目录"?>:</span>
								</label>
								<div class="col-md-8">
									<p id="backupDir"></p>
								</div>
							</div>
                          </div>
                          <div class="col-md-12">
                            <div class="form-group">
								<label class="control-label col-md-2"> <?php echo "最近一次同步结束时间"?>:</span>
								</label>
								<div class="col-md-8">
									<p id="lastFinishTime"></p>
								</div>
							</div>
                          </div>
                          <div class="col-md-12">
                            <div class="form-group">
								<label class="control-label col-md-2"> <?php echo "当前同步文件"?>:</span>
								</label>
								<div class="col-md-8">
									<p id="currentFile"></p>
								</div>
							</div>
                          </div>
                          <div class="col-md-12">
                            <div class="form-group">
								<label class="control-label col-md-2 bacfilelist"> <?php echo $LANG['UI_BACKUP_FILE_BAK_FILE_LIST']?>:</span>
								</label>
								<div class="filelisttext col-md-7" id="filelist" >
                                     
                                </div>
							</div>
                          </div>
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
<script type="text/javascript" src="/assets/global/plugins/anime/anime.min.js" ></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/fs/fs_cdp_job_details.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	