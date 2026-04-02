<?php 
include_once '../../tpl/permission.php';
$userAllPermission = $_SESSION['permission'];
?>
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/swiper/css/swiper.min.css"/>
<link href="../../assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css">
<link href="./css/platform/revision.css" rel="stylesheet" type="text/css">
<!-- BEGIN PAGE HEADER-->
<div class="page-bar page-xf-bar">
	<ul class="page-breadcrumb">
		<li>
			<div class="data-center-icon"></div>
			<span ><?php echo $LANG['UI_PLATFORM_HOMEPAGE']?></span>
		</li>
	</ul>
    <!-- <div class="systemTimeLabel"><?php echo $LANG['UI_DATACENTER_SYSTEM_TIME']?>: <span id="systemtime"></span></div> -->
</div>
<!-- END PAGE HEADER-->
<div class="datacenter-content datacenter-xf">
    <div class="row mb16 topdiv">
				<!-- 系统信息 -->
			<div  class="col-md-4 systeminfo">
				<div class="row mb16">
					<div class="col-md-6 pr8">
						<div class="databox databox1">
							<div class="img"></div>
							<div class="data" style="display:none">
								<span class="yearspan"><span id="year"></span>年</span>
								<span id="day"></span>天<span id="hour"></span>小时</div>
							<div class="desc">系统累计运行时间</div>
							<div class="databoxtime" id="databoxtime"></div>
							<div class="desc">系统时间</div>
						</div>
					</div>
					<div class="col-md-6 pl8">
						<div class="databox databox2">
							<div class="img"></div>
							<div class="data" id="protectDataAll"></div>
							<div class="desc">累计保护数据</div>
	
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6 pr8 ">
						<div class="databox databox3" id="currenttask">
							<div class="img"></div>
							<div class="data" id="currenttaskNum"></div>
							<div class="desc">当前任务</div>
							<div class="line"></div>
							<div  class="statusbox">
								<div>
									<div class="num" id="successnum"></div>
									<div class="status" style="color:#1DBC5D;box-shadow: 0px 2px 4px 0px rgba(29,188,93,0.2);">运行</div>
								</div>
								<div>
									<div class="num" id="waitnum"></div>
									<div class="status" style="color:#448FFF;box-shadow: 0px 2px 4px 0px rgba(68,143,255,0.2);">等待</div>
								</div>
								<div>
									<div class="num" id="othernum"></div>
									<div class="status" style="color: #86909C;box-shadow: 0px 2px 4px 0px rgba(134,144,156,0.2);">其他</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-6 pl8">
						<div class="databox databox4" id="historytask">
							<div class="img"></div>
							<div class="data" id="historytaskNum"></div>
							<div class="desc">历史任务</div>
							<div class="line"></div>
							<div  class="statusbox">
								<div>
									<div class="num" id="successnum"></div>
									<div class="status" style="color:#1DBC5D;box-shadow:0px 2px 4px 0px rgba(29,188,93,0.2);">成功</div>
								</div>
								<div>
									<div class="num" id="failnum"></div>
									<div class="status" style="color: #ED5A5A;box-shadow: 0px 2px 4px 0px rgba(237,90,90,0.2);">失败</div>
								</div>
								<div>
									<div class="num" id="abnormalnum"></div>
									<div class="status" style="color: #F9AA34;box-shadow: 0px 2px 4px 0px rgba(249,170,52,0.2);">异常</div>
								</div>
								<div>
									<div class="num" id="cancelnum"></div>
									<div class="status" style="color: #86909C;box-shadow:0px 2px 4px 0px rgba(134,144,156,0.2);">中止</div>
								</div>
								
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- 备份存储 -->
			<div  class="col-md-3 pl0 backupinfo" >
				<div class="backupdiv">
					<div class="datatop">
						<div class="title">备份存储</div>
					</div>
					<div class="databottom">
						<div class="toppercent">
							<div>
								<div class="data usedPercent"></div>
								<div class="des">已用容量占比</div>
							</div>
							<div>
								<div class="data remainPercent"></div>
								<div class="des">剩余容量占比</div>
							</div>
						</div>
						<div class="backupCapacity">
							<div id="capacityCircle"></div>
							<div id="backupdetail">
								<a href="javascript:;" style="color:#fff;text-decoration:none">查看详情</a>
							</div>
						</div>
						<div class="backupnum">
							<div style="display: flex;">
								<div class="img"></div>
								<div class="rightdiv">
									<div  class="data usedNum"></div>
									<div  class="des">已用容量</div>
								</div>
							</div>
							<div style="display: flex;">
								<div class="img"></div>
								<div class="rightdiv">
									<div  class="data remianNum"></div>
									<div  class="des">剩余容量</div>
								</div>
							</div>

						</div>

					</div>

					
				</div>
			</div>
			<!-- 存储统计 -->
			<div  class="col-md-5 pl0 storageinfo" >
				<div class="storagediv">
					<div class="datatop">
						<div class="title">存储统计</div>
					</div>
					<div id="consumpChart"></div>
				</div>
				
			</div>
    </div>
	<!-- 系统监控 -->
	<div class="row ">
		<div class="col-md-12 col-xs-12">
			<div class="monitordiv ">
				<div class="datatop">
					<div class="title">系统监控</div>
					<div class="floatRight">
						<select class="form-control select2me" name="standbyhost" id="node_uuid"></select>
					</div>
				</div>
				<div class="chartdiv">
					<div class="row">
						<!-- CPU使用率 -->
						<div class="col-md-4 pl0 littleScreen-pr cpudiv" >
							<div class="cpu">
								<div id="cpuChart"></div>
							</div>
						</div>
						<!-- 内存使用率 -->
						<div class="col-md-4 pl0 littleScreen-pr memerydiv" >
							<div class="memery">
								<div id="memeryChart"></div>
							</div>
						</div>
						<!-- 网络流量 -->
						<div class="col-md-4 pl0 littleScreen-pr networkTrafficdiv">
							<div class="networkTraffic">
								<div id="networkTrafficChart"></div>
							</div>
						</div>
					</div>
				</div>
					
					<div style="clear:both"></div>
			</div>
		</div>

	</div>
</div>





<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="../../assets/global/plugins/jquery-fontFlex/jQuery.fontFlex.js" ></script>
<script src="./assets/global/plugins/echarts/V5.01/echarts.min.js"></script>
<script src="./assets/global/plugins/counterup/jquery.waypoints.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/counterup/jquery.counterup.min.js" type="text/javascript"></script>
<script type="text/javascript" src="./assets/global/plugins/swiper/js/swiper.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./scripts/platform/databackup_center.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	