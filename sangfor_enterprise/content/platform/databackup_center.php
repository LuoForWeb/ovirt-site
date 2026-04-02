<?php 
include_once '../../tpl/permission.php';
$userAllPermission = $_SESSION['permission'];
?>
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/swiper/css/swiper.min.css"/>
<link href="../../assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./css/platform/sangfor-databackup-center.css" rel="stylesheet" type="text/css">
<!-- BEGIN PAGE HEADER-->
<div class="page-bar">
</div>
<!-- END PAGE HEADER-->
<div class="sangfor-content">
	<div class="sangfor-content-top">
		<!-- 存储资源池开始 -->
		<div class="col-md-3 storageresourcediv p10">
			<div class="parentdiv">
				<div class="storage-resource">
					<div class="storage-top">
						<span class="sangfor-label">存储资源池</span>
						<a id="storage_manager"><span class="storage-more more fr">查看更多</span></a>
						<select name="storageName" id="storageName" class="fr">
						</select>
					</div>
					<div class="storage-content">
						<div class="col-md-4">
							<img src="/img/platform/sangfor/storage-resource.png" alt="">
							<div class="totalstor"></div>
							<p class="totalstorlabel">总容量</p>
						</div>
						<div class="col-md-4">
							<div class="usedtor"></div>
							<span>占比<span class="usedper"></span></span>
							<p>已用容量</p>
						</div>
						<div class="col-md-4">
							<div class="freestor"></div>
							<span>占比<span class="freeper"></span></span>
							<p>剩余容量</p>
						</div>
					</div>

				</div>
			</div>
		</div>
		<!-- 存储资源池结束 -->
		<!-- 数据保护开始 -->
		<div class="col-md-7 padding0 dataprotectdiv">
			<div class="parentdiv">
				<div class="data-protect">
					<div class="sangfor-label" style="display: inline-block;">数据保护</div>
					<!-- Nav tabs -->
					<ul id="dataTabs" class="nav nav-tabs fr" role="tablist">           
    					<li class="backup" style="display:none" role="presentation"><a id="backup" data-toggle="tab" href="#backup_pane">备份</a></li>
    					<li class="cdp" style="display:none" role="presentation"><a id="cdp" role="tab" data-toggle="tab" href="#cdp_pane">实时保护</a></li>
    					<li class="copy" style="display:none" role="presentation"><a id="copy" role="tab" data-toggle="tab" href="#copy_pane">复制容灾</a></li>
    				</ul>
					<!-- Tab panes -->
					<div id="tab-content" class="tab-content">  
						<!-- 备份 -->
    				    <div role="tabpanel" class="tab-pane fade" id="backup_pane">
							<div class="swiper" id="backupSwiper">
							  	<div class="swiper-wrapper backup-swiper-wrapper">
									<div class="swiper-slide first-slide">
										<li class="device-box">
											<div class="small-label">设备概览</div>
											<div id="devicePie" class="pie-chart"></div>
										</li>
									</div>
									<div class="swiper-slide">
										<div class="small-label">虚拟化</div>
										<li class="d-flex">
											<div class="box-img">
												<img src="/img/platform/sangfor/vm01.png" alt="">
											</div>
											<div class="box-info">
												<div>
													<div class="top-num">
														<span class="item-text-num">14</span>
													</div>
                            				    	<span>受保护</span>
												</div>
												<div>
													<div class="top-num">
														<span class="item-text-num">14.52</span>
                                						<span class="item-text-unit">TB</span>
													</div>
                            				    	<span>备份数据</span>
												</div>
                            
                            				</div>
										</li>
									</div>
									<div class="swiper-slide">
										<div class="small-label">公有云</div>
										<li class="d-flex">
											<div class="box-img">
												<img src="/img/platform/sangfor/vm01.png" alt="">
											</div>
											<div class="box-info">
												<div>
													<div class="top-num">
														<span class="item-text-num">14</span>
													</div>
                            				    	<span>受保护</span>
												</div>
												<div>
													<div class="top-num">
														<span class="item-text-num">14.52</span>
                                						<span class="item-text-unit">TB</span>
													</div>
                            				    	<span>备份数据</span>
												</div>
                            
                            				</div>
										</li>
									</div>
									<div class="swiper-slide">
										<div class="small-label">私有云</div>
										<li class="d-flex">
											<div class="box-img">
												<img src="/img/platform/sangfor/vm01.png" alt="">
											</div>
											<div class="box-info">
												<div>
													<div class="top-num">
														<span class="item-text-num">14</span>
													</div>
                            				    	<span>受保护</span>
												</div>
												<div>
													<div class="top-num">
														<span class="item-text-num">14.52</span>
                                						<span class="item-text-unit">TB</span>
													</div>
                            				    	<span>备份数据</span>
												</div>
                            
                            				</div>
										</li>
									</div>
							
							  	</div>
								<div class="swiper-button-prev backup"><img src="/img/platform/sangfor/arrow-l.png" alt=""></div>
    							<div class="swiper-button-next backup"><img src="/img/platform/sangfor/arrow-r.png" alt=""></div>
							</div>
    				    </div>
						<!-- 实时 -->
    				    <div role="tabpanel" class="tab-pane fade" id="cdp_pane">
							<div class="swiper" id="cdpSwiper">
							  	<div class="swiper-wrapper cdp-swiper-wrapper">
								</div>
							  	<div class="swiper-button-prev cdp"><img src="/img/platform/sangfor/arrow-l.png" alt=""></div>
							  	<div class="swiper-button-next cdp"><img src="/img/platform/sangfor/arrow-r.png" alt=""></div>
							</div>
    				    </div>
						<!-- 复制容灾 -->
    				    <div role="tabpanel" class="tab-pane fade" id="copy_pane">
							<div class="swiper" id="copySwiper">
							  	<div class="swiper-wrapper copy-swiper-wrapper">
							  	  	<div class="swiper-slide first-slide">
										<li class="device-box">
											<div class="small-label">设备概览</div>
											<div id="devicePie" class="pie-chart"></div>
										</li>
									</div>
									<div class="swiper-slide">
										<div class="small-label">整机</div>
										<li class="d-flex">
											<div class="box-img">
												<img src="/img/platform/sangfor/vm01.png" alt="">
											</div>
											<div class="box-info">
												<div>
													<div class="top-num">
														<span class="item-text-num">14</span>
													</div>
                            				    	<span>受保护</span>
												</div>
												<div>
													<div class="top-num">
														<span class="item-text-num">14.52</span>
                                						<span class="item-text-unit">TB</span>
													</div>
                            				    	<span>备份数据</span>
												</div>
                            
                            				</div>
										</li>
									</div>
									<div class="swiper-slide">
										<div class="small-label">卷</div>
										<li class="d-flex">
											<div class="box-img">
												<img src="/img/platform/sangfor/vm01.png" alt="">
											</div>
											<div class="box-info">
												<div>
													<div class="top-num">
														<span class="item-text-num">14</span>
													</div>
                            				    	<span>受保护</span>
												</div>
												<div>
													<div class="top-num">
														<span class="item-text-num">14.52</span>
                                						<span class="item-text-unit">TB</span>
													</div>
                            				    	<span>备份数据</span>
												</div>
                            
                            				</div>
										</li>
									</div>
									<div class="swiper-slide">
										<div class="small-label">文件</div>
										<li class="d-flex">
											<div class="box-img">
												<img src="/img/platform/sangfor/vm01.png" alt="">
											</div>
											<div class="box-info">
												<div>
													<div class="top-num">
														<span class="item-text-num">14</span>
													</div>
                            				    	<span>受保护</span>
												</div>
												<div>
													<div class="top-num">
														<span class="item-text-num">14.52</span>
                                						<span class="item-text-unit">TB</span>
													</div>
                            				    	<span>备份数据</span>
												</div>
                            
                            				</div>
										</li>
									</div>
									<div class="swiper-slide">
										<div class="small-label">数据库</div>
										<li class="d-flex">
											<div class="box-img">
												<img src="/img/platform/sangfor/vm01.png" alt="">
											</div>
											<div class="box-info">
												<div>
													<div class="top-num">
														<span class="item-text-num">14</span>
													</div>
                            				    	<span>受保护</span>
												</div>
												<div>
													<div class="top-num">
														<span class="item-text-num">14.52</span>
                                						<span class="item-text-unit">TB</span>
													</div>
                            				    	<span>备份数据</span>
												</div>
                            
                            				</div>
										</li>
									</div>
								</div>
								<div class="swiper-button-prev copy"><img src="/img/platform/sangfor/arrow-l.png" alt=""></div>
							  	<div class="swiper-button-next copy"><img src="/img/platform/sangfor/arrow-r.png" alt=""></div>
							</div>
    				    </div>
    				</div>
				</div>
			</div>
		</div>
		<!-- 数据保护结束 -->
		<!-- 告警统计开始 -->
		<div class="col-md-2 alarmstatisticsdiv p10">
			<div class="parentdiv">
				<div class="alarm-statistics">
					<div class="alarm-top">
						<span class="sangfor-label">告警统计</span>
						<a href="./monisystem.html"><span class="alarm-more more fr">查看更多</span></a>
					</div>
					<div class="alarm-content">
						<div id="alarmPie">

						</div>
					</div>
				
				</div>
			</div>
		</div>
		<!-- 告警统计结束 -->

	</div>
	<div class="sangfor-content-bottom">
		<!-- 备份计划开始 -->
		<div class="col-md-6 bakplandiv p10">
			<div class="parentdiv">
				<div class="backup-plan">
					<div class="plan-top">
						<span class="sangfor-label">备份计划</span>
						<a href="./monitor.html"><span class="plan-more more fr">查看更多</span></a>
						<select name="planTaskStatus" id="planTaskStatus" class="fr">
							<option value="" disabled selected style='display:none;'>任务状态</option>
							<option value="0">全部</option>
							<option value="17">成功</option>
							<option value="1">等待</option>
							<option value="8">错误</option>
							<option value="4">停止</option>
							<option value="7">异常</option>
							<option value="2">运行</option>
						</select>
						<select name="planTaskType" id="planTaskType" class="fr">
							<option value="" disabled selected style='display:none;'>任务类型</option>
							<option value="0">全部</option>
							<option value="1,28,32,35,56">备份</option>
							<option value="2,29,33,36,47,57">恢复</option>
							<option value="17,30,38">副本</option>
							<option value="19,40">归档</option>
							<option value="34">接管</option>
							<option value="54">迁移</option>
							<option value="7,49,53">瞬时恢复</option>
							<option value="55">细粒度恢复</option>
							<option value="52">跨平台恢复</option>
							<option value="37">数据验证</option>
							<option value="46,62,65">复制容灾</option>
							<option value="63">对比</option>
							<option value="64">演练</option>
						</select>
					</div>
					<div class="plan-content">
						<div class="table-container">
                			<table class="table table-bordered table-hover lh2" id="planTable">
                			<thead>
                			<tr role="row" class="heading">
                				<th width="29%" style="background-color: #232E31 !important;">
                					<?php echo $LANG['UI_RECOVERY_STORAGE_NAME']?>
                				</th>
                				<th width="12%" style="background-color: #232E31 !important;">
                					<?php echo $LANG['UI_PUBLIC_STATUS']?>
                				</th>
                				<th width="30%" style="background-color: #232E31 !important;">
                					<?php echo $LANG['UI_JOB_PROGRESS']?>
                				</th>
                				<th width="13%" style="background-color: #232E31 !important;">
                					<?php echo $LANG['UI_JOB_SPEED']?>
                				</th>
                				<th width="15%" style="background-color: #232E31 !important;">
                					<?php echo $LANG['UI_PUBLIC_TASK_TYPE']?>
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
		<!-- 备份计划结束 -->
		<!-- 硬件设备状态开始 -->
		<div class="col-md-6 pl0 devicestatusdiv" style="padding-right: 10px;">
			<div class="parentdiv">
				<div class="device-status">
					<div class="device-top">
						<span class="sangfor-label">硬件设备状态</span>
						<a><span class="storage-more more fr" id="node_manager">查看更多</span></a>
						<select name="node_uuid" id="node_uuid" class="fr">
						</select>
					</div>
					<div class="device-content">
						<div class="col-md-6">
							<div class="cpudiv">
								<div id="cpuChart" class="lineChart"></div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="memerydiv">
								<div id="memeryChart" class="lineChart"></div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="netdiv">
								<div id="netChart" class="lineChart"></div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="diskdiv">
								<div id="diskChart" class="lineChart"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- 硬件设备状态结束 -->
	</div>

    
   
	
</div>





<!-- BEGIN PAGE LEVEL PLUGINS -->
<!-- <script src="./scripts/plugins/flexible.js"></script> -->
<script type="text/javascript" src="../../assets/global/plugins/jquery-fontFlex/jQuery.fontFlex.js" ></script>
<script src="./assets/global/plugins/echarts/V5.01/echarts.common.min.js"></script>
<script src="./assets/global/plugins/counterup/jquery.waypoints.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/counterup/jquery.counterup.min.js" type="text/javascript"></script>
<script type="text/javascript" src="./assets/global/plugins/swiper/js/swiper.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-easypiechart/jquery.easypiechart.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/extensions/TableTools/js/dataTables.tableTools.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/extensions/ColReorder/js/dataTables.colReorder.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/extensions/Scroller/js/dataTables.scroller.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/sangfor-datatable.js"></script>
<script type="text/javascript" src="./scripts/platform/sangfor-databackup_center.js"></script>

<!-- END PAGE LEVEL PLUGINS -->	