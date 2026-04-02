<?php include_once '../../tpl/permission.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./scripts/plugins/ztree/css/metroStyle/metroStyle.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/swiper/css/swiper.min.css"/>
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

<div class="row job-detail" id="jobDetailDiv">
	<div class="col-md-12 job-detail__halftop">
		<div class="portlet-body" id="jobDetail">
			<div class="col-md-7 job-detail__halftop__charts">
				<input id="task_uuid" value="<?php echo $_GET['uuid']; ?>" class="display-none"></input>
				<input id="task_type" value="<?php echo $_GET['type']; ?>" class="display-none"></input>
				<input id="pageUri" value="<?php echo $_SERVER['REQUEST_URI']; ?>" class="display-none"></input>
				<!-- BEGIN DYNAMIC CHART PORTLET-->
				<div class="portlet-charts">
					<div class="portlet-body">
						<!--BEGIN TABS-->
						<div class="tabbable tabbable-custom" id="taskFlowOrMapTabs">
							<ul class="nav nav-tabs" id="taskFlowOrMapNavTabs">
                                <li class="active" id="task_plot_li">
                                    <a href="#tab_task_map" data-toggle="tab" aria-expanded="true">
                                        <i class="viconfont vicon-ge_data_flow"></i> <?php echo $LANG['UI_JOB_TASK_DATA_FLOW']; ?> </a>
                                </li>
								<li class="nolb">
									<a href="#tab_chart" data-toggle="tab" aria-expanded="false">
										<i class="viconfont vicon-ge_task_flow"></i> <?php echo $LANG['UI_JOB_FLOW']; ?> </a>
								</li>
								
							</ul>
						</div>
						<div class="tab-content">
							<div class="tab-pane  display-none" id="tab_chart">
								<div class="portlet-charts__body" style="height: 100%;">
									<div class="portlet-charts__body__speedchart">
										<div id="speedcharthover"></div>
										<div id="speedchart"></div>
									</div>
									<div class="progressDiv display-none">
										<div class="progressDiv__label task_total_progress">
											<span><?php echo $LANG['UI_JOB_TOTAL_PROGRESS']; ?></span>
										</div>

										<div class="progressDiv__label task_consistency_progress display-none">
											<span><?php echo $LANG['UI_JOB_DATA_CONSISTENCY_PROGRESS']; ?></span>
										</div>
										
										<div class="progress progress-striped active">
											<div id="total-progress" class="progress-bar progress-bar-success" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
										</div>
										<div class="taskprogress" id="progressright"></div>
									</div>
								</div>
							</div>
							<div class="tab-pane active" id="tab_task_map">
								<div class="portlet-body">
									<div class="tab-content row tab-data-flow">
										<div class="tab-pane active" id="info">
											<div class="portlet col-md-12 col-data-flow">
												<div class="portlet-body">
													<div class="row detaildiv cdptaskmap">
														<div class="row-host-head col-md-10">
															<div  class="row-host-head__speed volcdp-trans-speed_standby-to-host display-none" id="transSpeedStandbyToHostDiv">
																<span id="transSpeedStandbyToHost" class='colorgreen'>0 B/s</span>
															</div>
															<div class="">
																<img class="display-none volcdpfailbacktransdiv" id="standbyToHostMap" src="./img/cdp/data-failback.gif">
															</div>
														</div>
														<div class="row-host-panel">
															<div class="col-md-3 col-master-host pr0">
                                                                <div class="col-md-5"></div>
																<div class="col-md-7 col-master-host__img pr0" style="text-align: center">
																	<img id="hostImg" src="./img/cdp/master-host.png"> <!-- 主机示意图 -->
                                                                    <div class="maphostdesc pt15"><?php echo $LANG['UI_PLATFORM_AGENT']; ?></div>
                                                                    <div class="font-blue" id="hostServerIp"></div>
																</div>
															</div>
															<div class="col-md-3 col-master-transfer">
																<div class="col-master-transfer__img width80p ml-25">
																	<img id="hostToBsTransferImg" src="./img/cdp/connected-no-state.png">
																</div>
																<div style="text-align:center;" class ="display-none width80p  ml-20" id="transSpeedHostToBsDiv">
																	<span id="transSpeedHostToBs" class='colorgreen'>0 B/s</span>
																</div>
															</div>
															<div class="col-md-2 col-master-server ml-90">
																<div class="col-master-host__img">
																	<img id="backupServerStatus" src="./img/cdp/backup-server.png" title="<?php echo $LANG['UI_VOL_CDP_BAK_SERVER']; ?>"> <!-- 备份服务器状态示意图 -->
																</div>
																<div class="pt15">
                                                                    <div class="flex-items-center flex-direction-column"><div><?php echo $LANG['UI_VOL_CDP_BAK_SERVER']; ?></div>
																	<div class="font-blue cdp-task-details-textalignc" id="backupServerIp"></div></div>
																</div>
															</div>
															<div class="col-md-3 col-master-transfer">
																<div class="col-master-transfer__img width80p ml-40">
																	<img id="bsToStandbyTransferImg" src="./img/cdp/connected-no-state.png">
																</div>
																<div style="text-align:center;"  class ="display-none width80p  ml-30"  id="transSpeedBsToStandbyDiv">
																	<span id="transSpeedBsToStandby" class='colorgreen'>0 B/s</span>
																</div>
															</div>
															<div class="col-md-2 col-master-backup standby">
																<div class="">
																	<img id="standbyImg" src="./img/cdp/standby.png"> <!-- 备机示意图 -->
																</div>
																<div class="textalignl pt15">
																	<div class="ml-20 ml-26_en">
																		<div class ="ml45 ml-0_en">
                                                                            <?php echo $LANG['UI_VOL_CDP_STANDBY_MACHINE']; ?>
                                                                        </div>
                                                                        <div class="ml15">
                                                                            <a href="javascript:void(0)" id="standbyHostRemoteControl" target="">
                                                                                <!--                                                                            <span id="autoTakeoverStandby"></span>-->
																				
																				<i class="viconfont vicon-web-console fs18 clickicon display-none"></i>
																				<span class="font-blue" id="standbyIp"></span>
																				
                                                                            </a>
                                                                        </div>
																	</div>
																</div>
															</div>
                                                            
														</div>

													</div>
													<!-- 未配置备机,只有两台主机示意图的情况 -->
                                                    <div class="row detaildiv display-none nostandbymap row-no-standby">
                                                        <div class="row-host-head col-md-10"></div>
                                                        <div class="row-host-panel">
                                                            <div class="col-md-5 col-master-host">
                                                                <div class="textalignr">
                                                                    <img id="hostImgNoStandby" src="./img/cdp/master-host.png"> <!-- 主机示意图 -->
                                                                </div>
                                                                <div class=" pt15 textalignr mr-22_en">
                                                                    <div>
                                                                        <div class="pr40 pr10_en" id="hostname"><?php echo $LANG['UI_VOL_CDP_HOST']; ?></div>
                                                                        <div class="pr11 font-blue pr35_en" id="masterServerInfo"></div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3 col-master-transfer ml-25">
                                                                <div >
                                                                    <img id="noStandbyTrancImg" class="col-master-transfer__img ms-3" src="./img/cdp/connected-no-state.png">
                                                                </div>
                                                                <div style="text-align:center;"  class ="display-none  ml-30" id="transSpeedNoStandbyDiv">
                                                                    <span id="transSpeedNoStandby" class='colorgreen'>0 B/s</span>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 col-master-host ml-40">
                                                                <div class="textalignl">
                                                                    <img id="backupServerStatus" src="./img/cdp/backup-server.png" title="<?php echo $LANG['UI_VOL_CDP_BAK_SERVER']; ?>"> <!-- 备份服务器状态示意图 -->
                                                                </div>
                                                                <div class=" width200 pt15 ml-20">
                                                                    <div>
                                                                        <div class="pl25  pl10_en">
                                                                            <?php echo $LANG['UI_VOL_CDP_BAK_SERVER']; ?>
                                                                        </div>
                                                                        <div class="font-blue pl20" id="backupServerInfo"></div>
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
							</div>
						</div>
					</div>
				</div>
				<!-- END DYNAMIC CHART PORTLET-->
			</div>
			<div class="col-md-5 job-detail__halftop__navtabs">
				<!-- BEGIN PORTLET-->
				<div class="portlet">
					<div class="portlet-body">
						<!--BEGIN TABS-->
						<div class="tabbable tabbable-custom swiper-detail">
							<div class="swiper-button-prev_detail"></div>
							<div class="swiper-button-next_detail"></div>
							<ul class="nav nav-tabs">
								<li class="active nolb">
									<a href="#tab_1_1" data-toggle="tab" aria-expanded="true">
										<i class="viconfont vicon-gaiyao1"></i> <?php echo $LANG['UI_JOB_SUMMARY']; ?> </a>
								</li>
								<li class="" id="storageli">
									<a href="#tab_1_2" data-toggle="tab" aria-expanded="false">
										<i class="viconfont vicon-cunchu"></i> <?php echo $LANG['UI_PALTFORM_STORAGE']; ?> </a>
								</li>
								<li class="" id="strategyli">
									<a href="#tab_1_3" data-toggle="tab" aria-expanded="false">
										<i class="viconfont vicon-celve1"></i> <?php echo $LANG['UI_JOB_STRATEGY']; ?> </a>
								</li>
								<li id="transli">
									<a href="#tab_transfer" data-toggle="tab" aria-expanded="false">
										<i class="viconfont vicon-a-chuanshu"></i> <?php echo $LANG['UI_JOB_TRANSMISSION']; ?> </a>
								</li>

								<li id="seniorli">
									<a href="#tab_advanced" data-toggle="tab" aria-expanded="false">
										<i class="viconfont vicon-ge_advanced"></i> <?php echo $LANG['UI_JOB_HIGH']; ?> </a>
								</li>
								<li id="takeoverli">
									<a href="#tab_takeover" data-toggle="tab" aria-expanded="false">
										<i class="icon-puzzle "></i> <span id="takeover_title"><?php echo $LANG['UI_VOL_CDP_FAILOVER']; ?></span> </a>
								</li>
							</ul>
							<div class="tab-content">
								<div class="tab-pane active" id="tab_1_1">
									<div class="portlet-body">
										<div class="row static-info taskOperateDiv">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_EMERGENCY_PLAN_OPERATE']; ?>:
											</div>
											<div class="col-md-8 col-operate value">
												<div class="btn-group">
													<button type="button" class="btn btn-primary btn-sm  btn-dropdown-operate taskOperateButton" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true">
														<i class="glyphicon glyphicon-hand-up"></i> <?php echo $LANG['UI_PUBLIC_OPERATION']; ?> <i class="fa fa-angle-down"></i>
													</button>
													<ul class="dropdown-menu min-width100" role="menu" id="volCdpOpList">
													</ul>
												</div>
											</div>
										</div>
                                        <!-- 任务名 -->
										<div class="row static-info">
											<div class="col-md-4 name" id="">
												<?php echo $LANG['UI_TASK_REPORT_TASK_NAME']; ?>:
											</div>
											<div class="col-md-8 value col-taskname" id="taskName">
											</div>
										</div>
                                        <!-- 备份模式 -->
                                        <div class="row static-info display-none task_backup_mode_view">
                                            <div class="col-md-4 name" id="">
                                                <?php echo $LANG['UI_BACKUP_MODE']; ?>:
                                            </div>
                                            <div class="col-md-8 value col-taskname" id="taskDetailBackupMode">
                                            </div>
                                        </div>

										<div class="row static-info">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_JOB_DETAIL_JOB_TYPE']; ?>:
											</div>
											<div class="col-md-8 value" id="status">
											</div>
										</div>
										<div class="row static-info">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_JOB_TASK_PHASE']; ?>:
											</div>
											<div class="col-md-8 value" id="taskStage">
											</div>
										</div>
                                        <!--当前校验卷 -->
                                        <div class="row static-info display-none currentcheckvolumeDiv">
                                            <div class="col-md-4 name" >
                                                <?php echo $LANG['UI_VOL_CDP_CURRENT_CHECK_VOLUME']; ?>:
                                            </div>
                                            <div class="col-md-8 value" id="currentCheckVolume">
                                            </div>
                                        </div>
                                        
										<div class="row static-info totalsizeDiv">
											<div class="col-md-4 name" id="taskTotalSizeStr">
												<?php echo $LANG['UI_JOB_TOTAL_SIZES']; ?>:
											</div>
											<div class="col-md-8 value" id="totalSize">
											</div>
										</div>
										<div class="row static-info display-none takeoverTypeDiv">
											<div class="col-md-4 name" id="takeoverTypeStr">
												<?php echo $LANG['UI_JOB_TAKEOVER_TYPE']; ?>:
											</div>
											<div class="col-md-8 value" id="takeoverType">
											</div>
										</div>
										<div class="row static-info display-none" id="currentSizeView">
											<div class="col-md-4 processeddata" id="currentSizeStr">
												<?php echo $LANG['UI_JOB_ALREADY_COMPLETED_SIZE']; ?>:
											</div>
											<div class="col-md-8 value" id="currentSize">
											</div>
										</div>
										<div class="row static-info display-none" id="takeoverDataView">
											<div class="col-md-4 name ">
												<?php echo $LANG['UI_VOL_CDP_TAKEOVER_DATA_SOURCE']; ?>:
											</div>
											<div class="col-md-8 value" id="takeoverDataSource">
											</div>
										</div>
										<div class="row static-info">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_JOB_START_TIME']; ?>:
											</div>
											<div class="col-md-8 value" id="startTime">
											</div>
										</div>
										<div class="row static-info">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_JOB_INTERVAL_TIME']; ?>:
											</div>
											<div class="col-md-8 value" id="intervalTime">
											</div>
										</div>
										<!-- <div class="row static-info">
    										<div class="col-md-4 name">
    											<?php echo $LANG['UI_JOB_ESTIMATED_OVER_TIME']; ?>:
    										</div>
    										<div class="col-md-8 value" id="endTime">
    										</div>
    									</div> -->
									</div>
								</div>
								<!-- 存储配置view -->
								<div class="tab-pane" id="tab_1_2">
									<div class="portlet-body">
										<div class="row static-info nodeDiv">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_JOB_BACKUP_NODE']; ?>:
											</div>
											<div class="col-md-8 value" id="nodeinfo">
											</div>
										</div>
										<div class="row static-info storageDiv">
											<div class="col-md-4 name">
												<!-- 存储设备 -->
												<?php echo $LANG['UI_JOB_STORAGE_DEV']; ?>:
											</div>
											<div class="col-md-8 value" id="storageinfo">
											</div>
										</div>
										<!-- cdp模块暂时不支持重复数据删除功能 modify-time:2022-6-10 -->
										<div class="row static-info deduplicationdiv display-none">
											<div class="col-md-4 name">
												<!-- 重复数据删除 -->
												<?php echo $LANG['UI_BACKUP_DEDUPLICATION']; ?>:
											</div>
											<div class="col-md-8 value" id="deduplication">
											</div>
										</div>
										<div class="row static-info compressferDiv">
											<div class="col-md-4 name">
												<!-- 压缩存储 -->
												<?php echo $LANG['UI_BACKUP_COMPRESS_TRANSFER']; ?>:
											</div>
											<div class="col-md-8 value" id="compressed">
											</div>
										</div>
										<!-- 压缩等级 -->
										<div class="row static-info  compressMethodDiv display-none">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_JOB_COMPRESS_PRIORITY']; ?>:
											</div>
											<div class="col-md-8 value" id="compressMethod">
											</div>
										</div>
										<div class="row static-info blocksizediv">
											<div class="col-md-4 name">
												<!-- 数据块大小 -->
												<?php echo $LANG['UI_BACKUP_BLOCK_SIZE']; ?>:
											</div>
											<div class="col-md-8 value" id="blocksize">
											</div>
										</div>

										<div class="row static-info  encryptStoragediv">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_BACKUP_DATA_ENCRYPT']; ?>:
											</div>
											<div class="col-md-8 value" id="encryptStorage">
											</div>
										</div>
										<!-- 存储加密算法 -->
										<div class="row static-info display-none encrypt-method-div">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?>:
											</div>
											<div class="col-md-8 value" id="encryptMethod">
											</div>
										</div>

										<div class="row static-info  passwordAutodiv">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_BACKUP_PASSOWRD_AUTO']; ?>:
											</div>
											<div class="col-md-8 value" id="passwordAuto">
											</div>
										</div>
									</div>
								</div>
								<!-- 策略view -->
								<div class="tab-pane" id="tab_1_3">
									<div class="portlet-body">
										<div class="row static-info">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_JOB_CREATE_TIME']; ?>:
											</div>
											<div class="col-md-8 value" id="createTime">
											</div>
										</div>
										<!-- 下次标签执行时间 -->
										<div class="row static-info tagexecutetimediv">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_JOB_NEXT_LABEL_EXECUTION_TIME']; ?>:
											</div>
											<div class="col-md-8 value">

												<span class="label label-success" id="nextTime">
												</span>
											</div>
										</div>

										<div class="row static-info display-none" id="recoverStartStrategyView">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_JOB_START_STRATEGY']; ?>:
											</div>
											<div class="col-md-8 value" id="recoverStartStrategy">
											</div>
										</div>

										<div class="row static-info" id="tagdesView">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_JOB_LABEL_STRATEGY']; ?>:
											</div>
											<div class="col-md-8 value" id="tagdes" style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 2;overflow: hidden;">
											</div>
										</div>

										<div class="row static-info" id="reservedStrategyView">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_BACKUP_RESERVE_STRATEGY']; ?>:
											</div>
											<div class="col-md-8 value" id="reservedStrategy">
											</div>
										</div>

										<div class="row static-info display-none taskEncryptdiv">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_JOB_TASK_THREDS']; ?>:
											</div>
											<div class="col-md-8 value" id="taskThreadNum">
											</div>
										</div>

										<div class="row static-info speedDiv">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT']; ?>:
											</div>
											<div class="col-md-8 value" id="speedlimit" style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 2;overflow: hidden;">
											</div>
										</div>
										<!-- 任务等级 -->
										<div class="row static-info taskPriorityDiv">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_JOB_TASK_PRIORITY'] ?>:
											</div>
											<div class="col-md-8 value" id="taskPriority">
											</div>
										</div>
									</div>
								</div>

								<!-- 传输配置 -->
								<div class="tab-pane" id="tab_transfer">
									<div class="portlet-body">

										<!-- 传输模式 -->
										<div class="row static-info  transportModediv">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_BACKUP_TRANSPORT_MODE']; ?>:
											</div>
											<div class="col-md-8 value" id="transportMode">
											</div>
										</div>

										<!-- 压缩传输 -->
										<div class="row static-info  transportCompressdiv">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_COPY_COMPRESS_TRANSFER']; ?>:
											</div>
											<div class="col-md-8 value" id="transportCompress">
											</div>
										</div>
										<!-- 压缩等级 -->
										<div class="row static-info  transportCompressMethodDiv display-none">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_JOB_COMPRESS_PRIORITY']; ?>:
											</div>
											<div class="col-md-8 value" id="transportCompressMethod">
											</div>
										</div>

										<!-- 传输加密 -->
										<div class="row static-info  transportEncryptdiv">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER']; ?>:
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

										<!-- 传输线程个数 -->
										<div class="row static-info  transportThreadNumdiv">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_VOL_CDP_NUMBERS_THREADS']; ?>:
											</div>
											<div class="col-md-8 value" id="transportThreadNum">
											</div>
										</div>

										<!-- 传输大小 -->
										<div class="row static-info  transportPacketSizediv">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_VOL_CDP_TRANSMISSION_PACKET_SIZE']; ?>:
											</div>
											<div class="col-md-8 value" id="transportPacketSize">
											</div>
										</div>

										<!-- 传输网络 -->
										<div class="row static-info  transportNetworkDiv">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_NODE_TRANSFER_NETWORK']; ?>:
											</div>
											<div class="col-md-8 value" id="transportNetworkInfo">
											</div>
										</div>
                                        <!-- 重连次数 Bug #14474 需求屏蔽 -->
                                        <div class="row static-info  display-none transport-reconnect-times-div">
                                            <div class="col-md-4 name">
                                                <?php echo $LANG['UI_BACKUP_RECONNECT_TIMES']; ?>:
                                            </div>
                                            <div class="col-md-8 value" >
                                                <span id="transportReconnectTimes"></span>
                                                <span><?php echo $LANG['WEB_PALTFORM_DC_TIME'] ?></span>
                                            </div>
                                        </div>
                                        <!-- 重连间隔 Bug #14474 需求屏蔽-->
                                        <div class="row static-info display-none transport-reconnect-interval-div">
                                            <div class="col-md-4 name">
                                                <?php echo $LANG['UI_BACKUP_RECONNECT_INTERVAL']; ?>:
                                            </div>
                                            <div class="col-md-8 value" >
                                                <span id="transportReconnectIntrval"></span>
                                                <span><?php echo $LANG['UI_DRILLS_SECOND'] ?></span>
                                            </div>
                                        </div>

									</div>
								</div>

								<div class="tab-pane" id="tab_advanced">
									<div class="portlet-body">
										<div class="row static-info  display-none doublehostmirrorview">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_VOL_CDP_DUAL_MACHINE']; ?>:
											</div>
											<div class="col-md-8 value" id="doubleHostmirrorview">
											</div>
										</div>

										<!-- 双机镜像下的备份数据view -->
										<div class="row static-info  display-none mirrordataisbackupview">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_PLATFORM_VOL_CDP_BACKUPSET']; ?>:
											</div>
											<div class="col-md-8 value" id="mirrorBackupView">
											</div>
										</div>

										<div class="row static-info doubleMachineImgHostDiv display-none">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_JOB_MIRROR_MACHINE']; ?>:
											</div>
											<div class="col-md-8 value" id="doubleMachineImgHost">
											</div>
										</div>

										<div class="row static-info volCdpHostFileCachePathDiv display-none">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_JOB_FILE_CACHE_PATH']; ?>:
											</div>
											<div class="col-md-8 value" id="volCdpHostFileCachePath">
											</div>
										</div>

										<div class="row static-info volCdpHostFileCacheSizeDiv display-none">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_JOB_FILE_CACHE_SUMMARY']; ?>:
											</div>
											<div class="col-md-8 value" id="volCdpHostFileCacheSize">
											</div>
										</div>

										<div class="row static-info volCdpMemoryCacheDiv display-none">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_VOL_CDP_MEMORY_CACHE']; ?>:
											</div>
											<div class="col-md-8 value" id="volCdpMemoryCache">
											</div>
										</div>

										<div class="row static-info volCdpMemoryCacheSizeDiv display-none">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_JOB_MEMORY_CACHE_SUMMARY']; ?>:
											</div>
											<div class="col-md-8 value" id="volCdpMemoryCacheSize">
											</div>
										</div>
										<!-- IO复制模式 -->
										<div class="row static-info volCdpIoReplicationModeDiv display-none">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_VOL_CDP_IO_REPLICATION_MODE']; ?>:
											</div>
											<div class="col-md-8 value" id="ioReplicationMode">
											</div>
										</div>
										<!-- 故障自动恢复 -->
										<div class="row static-info volCdpFaultDynamicRecoveryDiv display-none">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_VOL_CDP_AUTO_FAULT_RECOVERY']; ?>:
											</div>
											<div class="col-md-8 value" id="volCdpFaultDynamicRecovery">
											</div>
										</div>

										<!-- 重建分区 -->
										<div class="row static-info volCdpRebuildPartitionDiv display-none">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_JOB_IF_REBUILD_PARTITION']; ?>:
											</div>
											<div class="col-md-8 value" id="volCdpRebuildPartition">
											</div>
										</div>

										<div class="row static-info recoveryDiv sqlserverReDiv display-none">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_DB_LOG_FILE_PATH']; ?>:
											</div>
											<div class="col-md-8 value" id="logPath">
											</div>
										</div>

										<div class="row static-info recoveryDiv dmReDiv display-none">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_DB_SPECIFIED_FILE_PATH']; ?>:
											</div>
											<div class="col-md-8 value" id="specifiedPath">
											</div>
										</div>

										<div class="row static-info oracleDiv display-none backupDiv">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_DB_ORACLE_ARCHIVE_DAYS']; ?>:
											</div>
											<div class="col-md-8 value" id="archivedays">
											</div>
										</div>
										<div class="row static-info oracleDiv dmDiv display-none backupDiv">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_DB_IF_DELETE_ARCHIVE_LOG']; ?>:
											</div>
											<div class="col-md-8 value" id="deletearchiveflag">
											</div>
										</div>

										<div class="row static-info dmDiv display-none backupDiv">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_DB_DM_COMPRESS']; ?>:
											</div>
											<div class="col-md-8 value" id="dmcompress">
											</div>
										</div>
									</div>
								</div>
								<div class="tab-pane" id="tab_takeover">
									<div class="portlet-body">
										<!-- 自动接管 -->
										<div class="row static-info cdpAutoTakeoverConfDiv display-none">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_VOL_CDP_AUTO_TAKEOVER']; ?>:
											</div>
											<div class="col-md-8 value">
                                                <div class="floatl" id = "cdpAutoTakeoverConf"></div>
<!--                                                <div class="floatl ml15 removeAutotakeoverView" >-->
<!--                                                    <a class= "removeAutotakeover">-->
<!--                                                        <span class = "autotakeovertext">停用自动接管</span>-->
<!--                                                    </a>-->
<!--                                                </div>-->
											</div>
										</div>

										<div class="row static-info autoTakeoverStandbyDiv display-none">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_VOL_CDP_STANDBY_MACHINE']; ?>:
											</div>
											<div class="col-md-8 value" >
                                                <a href="javascript:void(0)" id="takeoverStandbyRemoteControl" target="">
												<i class="viconfont vicon-web-console fs18 clickicon display-none"></i>	
													<span id="autoTakeoverStandby"></span>
                                                </a>
                                            </div>
                                        </div>
										<div class="row static-info autotakeovercatbackupipdiv display-none">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_VOL_CDP_TAKEOVER_RECOVER_IP']; ?>:
											</div>
											<div class="col-md-8 value" id="autoTakeoverCatbackIp">
											</div>
										</div>
										 <!-- 应用场景 -->
										<div class="row static-info  standbyEmdVmRoleDiv">
                                            <div class="col-md-4 name ">
                                                <?php echo $LANG['UI_VOL_CDP_TEMP_USE_SCENE']; ?>:
                                            </div>
                                            <div class="col-md-8 value" id="standbyEmdVmRole">
                                            </div>
                                        </div>
										
										<!-- 接管类型 -->
										<div class="row static-info  taskTakeoverTypeDiv display-none">
                                            <div class="col-md-4 name ">
                                                <?php echo $LANG['UI_TASK_REPORT_TASK_TYPE']; ?>:
                                            </div>
                                            <div class="col-md-2 value width37_en" id="taskTakeoverType">
                                            </div>
											<a class = "ml-30 display-none sidebar-memu-diy-fixed colorgreen  tempagentconfherf" href="javascript:void(0)" ><?php echo $LANG['UI_JOB_TEMP_AGENT_CONF'] ?></a>
                                        </div>

										<!-- 心跳间隔 -->
										<div class=" row static-info display-none heartbeatFailureTimeView">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_VOL_CDP_HEARTBEAT_NOT_ENABLE_TIME']; ?>:
											</div>
											<div class="col-md-8 value" id="heartbeatFailureTime">
											</div>
										</div>

										<!-- 应用故障接管 -->
										<div class="row static-info volCdpAppTakeoverDiv display-none">
											<div class="col-md-4 name volcdpapptakeoverstr" >
												<?php echo $LANG['UI_VOL_CDP_APPLICATION_TAKEOVER']; ?>:
											</div>
											<div class="col-md-8 value" id="volCdpAppTakeover">
											</div>
										</div>

										<!-- 应用故障监测-->
										<div class="row static-info volCdpAppMonitorDiv display-none">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_VOL_CDP_ENABLE_FAULT_MONITOR_TAKEOVER']; ?>:
											</div>
											<div class="col-md-8 value" id="volCdpTaskAppMonitor">
											</div>
										</div>

										<!--连续故障次数 -->
										<div class="row static-info appConseFailureNumDiv display-none">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_VOL_CDP_FAULT_TIMES']; ?>:
											</div>
											<div class="col-md-8 value" id="appConseFailureNum">
											</div>
										</div>
										<!-- 故障监测间隔 -->
										<div class="row static-info appFaultDetectionInterDiv display-none">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_VOL_CDP_FAULT_INTERVA']; ?>:
											</div>
											<div class="col-md-8 value" id="appFaultDetectionInter">
											</div>
										</div>
										
                                       
										<!-- 接管应用-->
										<div class="row static-info cdpTakeoverAppDiv display-none">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_VOL_CDP_TAKEOVER_APPLICATION']; ?>:
											</div>
											<div class="col-md-8 value" id="cdpTakeoverApp">
											</div>
										</div>
										<!-- 自定义监控脚本 -->
										<div class="row static-info  customScriptDiv">
											<div class="col-md-4 name autoScriptDiv">
												<?php echo $LANG['UI_VOL_CDP_TAKEOVER_CUSTOM_SCRIPT']; ?>:
											</div>
											<div class="col-md-4 name manualScriptDiv display-none">
												<?php echo $LANG['UI_VOL_CDP_TAKEOVER_CUSTOM_SCRIPT']; ?>:
											</div>
											<div class="col-md-8 value" id="customScript">
												<a href="javascript:void(0)" id="takeoverScriptConfDetail"><?php echo $LANG['UI_JOB_SCRIPT_INFO'] ?></a>
											</div>
										</div>
										<!-- 手动接管时间点 -->
										<div class="row static-info display-none takeoverTimestampDiv">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_VOL_CDP_TAKEOVER_POINT']; ?>:
											</div>
											<div class="col-md-8 value" id="takeoverTimestamp">
											</div>
										</div>

                                        <!-- 主机IP漂移 -->
                                        <div class="row static-info display-none volCdpMasterIpSwitchDiv">
                                            <div class="col-md-4 name">
                                                <?php echo $LANG['UI_VOL_CDP_HOST_IP_DRIFT']; ?>:
                                            </div>
                                            <div class="col-md-8 value" id="volCdpMasterIpSwitch">
                                            </div>
                                        </div>
                                        
										<!-- 获取/业务IP接管配置 -->
										<div class="row static-info display-none takeoverNetworConfDiv ">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_VOL_CDP_HOST_IP_SERIVCE_CONF']; ?>:
											</div>
											<div class="col-md-8 value" id="">
												<a href="javascript:void(0)" id="taskTakeoverNetworkConf" class="colorgreen"><?php echo $LANG['UI_VOL_CDP_VIEW_MODIFY_IP_SERVICE_CONF']; ?></a>
											</div>
										</div>

										<!-- 获取/设置接管回切配置 -->
										<div class="row static-info display-none takeoverFailbackConfDiv ">
											<div class="col-md-4 name">
												<?php echo $LANG['UI_JOB_TASK_FAILBACK_CONFIGURE']; ?>:
											</div>
											<div class="col-md-8 value">
												<a href="javascript:void(0)" id="takeoverFailbackConf" class="colorgreen"><?php echo $LANG['UI_JOB_VIEW_MODIFY_FAILBACK']; ?></a>
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
	</div>

	<div class="col-md-12 job-detail__halfbottom">
		<!-- BEGIN TAB PORTLET-->
		<div class="job-detail__halfbottom__portlet">
			<div class="portlet-title">
				<ul class="nav nav-tabs">
					<li class="active ">
						<a href="#log" data-toggle="tab">
							<i class="viconfont vicon-tasklog"></i> <?php echo $LANG['UI_JOB_RUNING_LOG']; ?> </a>
					</li>
					<li>
						<a href="#volinfo" data-toggle="tab">
							<i class="viconfont vicon-ge_volume_information"></i> <?php echo $LANG['UI_JOB_VOL_INFO']; ?> </a>
					</li>
					<li id="historyli">
						<a href="#history" data-toggle="tab">
							<i class="viconfont vicon-histroytask"></i> <?php echo $LANG['UI_JOB_HISTORY']; ?> </a>
					</li>
				</ul>
			</div>
			<div class="portlet-body">
				<div class="tab-content">
					<div class="tab-pane active" id="log">
						<ul class="feeds" id="runninglog"></ul>
					</div>
					<div class="tab-pane" id="volinfo">
						<div class="table-container">
							<table class="table table-striped table-bordered table-hover" id="volAppTable">
								<thead>
									<tr role="row" class="heading">
										<th width="2%">
											<?php echo $LANG['UI_PUBLIC_NUMBER']; ?>
										</th>
										<th width="18%" id="bakcupAgentTh">
											<?php echo $LANG['UI_VOL_CDP_CLIENT_BAK']; ?>
										</th>
										<th width="10%" id="backupVolumeTh">
											<?php echo $LANG['UI_JOB_BAK_VOL']; ?>
										</th>
										<th width="10%" id=volCdpVolSizeTh>
											<?php echo $LANG['UI_VOL_CDP_VOL_CAPACITY']; ?>
										</th>
										<th width="10%" id="volCdpVolValidSizeTh">
											<?php echo $LANG['UI_JOB_VOL_VALID_DATA']; ?>
										</th>
										<th width="10%" id="volCdpVolTransferSizeTh">
											<?php echo $LANG['UI_JOB_TRANSFER_SIZE']; ?>
										</th>
										<th width="10%" id="taskWriteSizeTh">
											<?php echo $LANG['UI_JOB_REAL_SIZE']; ?>
										</th>
										<th width="18%" id="otherAgentServerTh">
											<?php echo $LANG['UI_VOL_CDP_STANDBY']; ?>
										</th>
										<th width="13%" id="targetVolumeTh">
											<?php echo $LANG['UI_JOB_MAP_VOL']; ?>
										</th>
									</tr>
								</thead>
								<tbody>
								</tbody>
							</table>
							<div class="alert alert-block alert-info fade in display-hide" id="vmbackuptips">
								<button type="button" class="close" data-dismiss="alert"></button>
								<ul class="alert-ul">
									<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
									<li>
										<?php echo $LANG['UI_BACKUP_SELECT_VM_BACKUP_TIPS'] ?>
									</li>
								</ul>
							</div>
						</div>
					</div>

					<div class="tab-pane" id="history">
						<div class="table-container">
							<table class="table table-striped table-bordered table-hover" id="historytable">
								<thead>
									<tr role="row" class="heading">
										<th width="4%">
											<?php echo $LANG['UI_PUBLIC_NUMBER']; ?>
										</th>
										<th width="18%">
											<?php echo $LANG['UI_PUBLIC_TASK_TYPE']; ?>
										</th>
										<th width="10%">
											<?php echo $LANG['UI_TENANT_DELETE_RESULT']; ?>
										</th>
										<th width="14%">
											<?php echo $LANG['UI_JOB_TRANSFER_SIZE']; ?>
										</th>
										<th width="14%">
											<?php echo $LANG['UI_JOB_REAL_SIZE']; ?>
										</th>
										<th width="20%">
											<?php echo $LANG['UI_JOB_START_TIME']; ?>
										</th>
										<th width="20%">
											<?php echo $LANG['UI_JOB_OVER_TIME']; ?>
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

<!-- 模板虚拟机配置信息 -->
<div id="" class="modal xmodal fade form-horizontal taskTempAgentConfig" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
	<div class="modal-header ">
        <button type="button" class="close " data-dismiss="modal" aria-hidden="true"></button>
		<h4 class="modal-title"><i class="levelchild viconfont vicon-beiji"></i> <?php echo $LANG['WEB_VOL_CDP_STANDBY_CONF']; ?>
		</h4>
	</div>
	<div class="modal-body">
		<div class="portlet-body form">
			<div class="form-group pd5">
				<div class = "pd10">
					<h4 class="form-section"><?php echo $LANG['WEB_VOL_CDP_STANDBY_BASIC_CONF']; ?></h4>
					<!-- 模板虚拟机名 -->
					<div class="form-group mb0 mt10">
						<label class="control-label col-md-3"><?php echo $LANG['UI_VM_MACHINE_TITLE']; ?>:</label>
						<div class="col-md-9">
							<p class="form-control-static  tasktempvirtualname">  
							</p>
						</div>
					</div>

					<!-- 任务目标虚拟机cpu配置 -->
					<div class="form-group mb0 mt10 ">
						<label class="control-label col-md-3"><?php echo $LANG['UI_VM_MACHINE_CPU_USE']; ?>:</label>
						<div class="col-md-9">
							
							<p class="form-control-static  tasktempvirtualcpu">
							</p>
						</div>
					</div>
					<!-- 内存大小配置 -->
					<div class="form-group mb0 mt10">
						<label class="control-label col-md-3"><?php echo $LANG['UI_VM_MACHINE_MEMS']; ?>:</label>
						<div class="col-md-9">
							<p class="form-control-static  tasktempvirtualmachinemems">
							</p>
						</div>
					</div>
					<!--引导固件 -->
					<div class="form-group mb0 mt10">
						<label class="control-label col-md-3"><?php echo $LANG['UI_VM_MACHINE_BOOT_FIREWARE']; ?>:</label>
						<div class="col-md-9">
							<p class="form-control-static tasktempvmmachinebootfireware">
							</p>
						</div>
					</div>
					<!--CPU 类型 -->
					<div class="form-group mb0 mt10">
						<label class="control-label col-md-3"><?php echo $LANG['UI_VM_MACHINE_CPU_MODEL']; ?>:</label>
						<div class="col-md-9">
							<p class="form-control-static tasktempvmmachinecpumode">
							</p>
						</div>
					</div>

					<!-- 网络配置 -->
					<h4 class="form-section mt10"><?php echo $LANG['UI_VOL_CDP_TEMP_VM_NET_CONF']; ?></h4>
					<div class="form-group mb0 mt10">
						<label class="control-label col-md-3"><?php echo $LANG['UI_VM_MACHINE_NETWORKS']; ?>:</label>
						<div class="col-md-9">
							<p class="form-control-static tasktempvirtualmachinenet">
							</p>
						</div>
					</div>

					<!-- 高级配置 -->
					<!-- <h4 class="form-section"><?php echo $LANG['UI_BACKUP_HIGH_CONFIG']; ?></h4> -->
					

					<!--启动方式 -->
					<!-- <div class="form-group mb0 mt10">
						<label class="control-label col-md-3"><?php echo $LANG['UI_VM_MACHINE_BOOT_MODE']; ?>:</label>
						<div class="col-md-9">
							<p class="form-control-static tasktempvmmachinebootmode">
							</p>
						</div>
					</div> -->

				</div>
				
			</div>
		</div>
	</div>
	<!-- modal-footer -->
	<div class="modal-footer">
		<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_OFF_TWO']; ?></button>
	</div>
</div>

<!-- 接管脚本查看 -->
<div id="takeoverScriptTaskModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
	<div class="modal-header ">
<!--		<button type="button" class="close" aria-hidden="true"></button>-->
        <button type="button" class="close " data-dismiss="modal" aria-hidden="true"></button>
		<h4 class="modal-title"><i class="levelchild viconfont vicon-ge_configuration"></i> <?php echo $LANG['UI_VOL_CDP_TAKEOVER_CUSTOM_SCRIPT']; ?>
		</h4>
	</div>
	<div class="modal-body">
		<div class="portlet-body">
			<div class="form-group">
				<ul id="taskTakeoverScriptList" style="padding-left: 0;width:100%;margin-top:-2%;"></ul>
			</div>
		</div>
	</div>
	<!-- modal-footer -->
	<div class="modal-footer">
		<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_OFF_TWO']; ?></button>
	</div>
</div>
<!-- 添加自动接管自定义脚本--modal END -->

<!-- BEGIN TASK  FAILBACK MODAL-->
<div id="taskFailbackupModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
	<div class="modal-header ">
		<button type="button" class="close" id="closeBack" data-dismiss="modal" aria-hidden="true"></button>
		<h4 class="modal-title"><i class="viconfont vicon-ge_configuration"></i> <?php echo $LANG['UI_JOB_FAILBACK_CONFIGURE']; ?>
		</h4>
	</div>
	<div class="modal-body">
		<div class="portlet-body">
			<div class="tabbable tabbable-custom margintop-10" style="overflow: visible;margin-bottom:-10px;">
				<div class="row">
					<div class="col-md-11">
						<!-- 目标主机  -->
						<div class="form-group margintop20  failbackuphostview ">
							<label class="control-label col-md-3 applianceselectlabel"><?php echo $LANG['UI_JOB_TARGET_HOST']; ?></label>
							<div class="col-md-8">
								<select class="form-control select2me" id="failBackHost">
								</select>
							</div>
						</div>
                        <div class="form-group  margintop20   display-none">
                            <label class="control-label col-md-3 diystoragelabel"><?php echo $LANG['UI_BACKUP_TARGET_NODE']; ?></label>
                            <div class="col-md-8">
                                <select class="form-control select2me" id="selectnode">
                                </select>
                            </div>
                        </div>
						

						<!-- 是否备份镜像数据到备份服务器 -->
						<div class="form-group  fbmirrordatatoserverdiv">
							<label class="control-label col-md-3 doublehostmirrorlabel form-group-label"><?php echo $LANG['UI_PLATFORM_VOL_CDP_BACKUPSET']; ?></label>
							<div class="col-md-5 form-group-content">
								<input type="checkbox" id="fbMirrorDataToServer" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" checked="checked" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>" data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>">
								<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_COL_CDP_BACKUP_MIRROR_DATA_TIPS'] ?>">
									<i class="viconfont vicon-tishi"></i>
								</a>
							</div>
						</div>

                        <div class="form-group  margintop20   display-none  selectstoragediv">
                            <label class="control-label col-md-3 diystoragelabel"><?php echo $LANG['UI_COPY_BACK_TARGET_STORAGE']; ?></label>
                            <div class="col-md-8">
                                <select class="form-control select2me" id="selectstorage">
                                </select>
                            </div>
                        </div>

                        <!--回切传输策略配置 -->
                        <div class="form-group">
                            <label class="control-label col-md-3 diystoragelabel"><?php echo $LANG['UI_VOL_CDP_FAILBACK_TRANSFER_CONF_TEXT']; ?></label>
                            <div class=" col-md-9 accordion confvolmap" >
                                <div class="panel panel-default strategy-panel">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a class="accordion-toggle accordion-toggle-styled popovers mapvolumeview collapsed"
                                               data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".confvolmap" href="#failback_transfer_conf" aria-expanded="true">
                                                <i class="viconfont vicon-ge_transfer font-green-seagreen"></i>
                                                <label class="font-green-seagreen"><?php echo  $LANG['UI_JOB_TRANSMISSION']; ?></label>
                                                <!--                                                <span class="strategyDes standbyhostDes"></span>-->
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="failback_transfer_conf" class="panel-collapse collapse stdmapvolume">
                                        <div class="panel-body">
                                            <div class="col-md-12">
                                                <!-- 传输加密 -->
                                                <div class="form-group ml-90 takeovertranencryptdiv ml-0_en">
                                                    <label class="control-label col-md-4 transferencrypt"><?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER']; ?></label>
                                                    <div class="col-md-5">
                                                        <input type="checkbox" id="takeoverTranEncryptSwitch"  class="make-switch" data-on-color="primary" data-off-color="info"
                                                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>"
                                                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>">
                                                        <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true"
                                                           data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_ENCRYPTED_TRANSMISSION_TIPS']; ?>">
                                                            <i class="viconfont vicon-tishi"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                                <!-- 传输加密算法 -->
                                                <div class="form-group ml-90 transfer-encrypt-method-form display-none ml-0_en">
                                                    <label class="control-label transfer-encrypt-method-label col-md-4"><?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?></label>
                                                    <div class="col-md-4">
                                                        <select class="form-control select2me input-sm" id="cutbackTransferEncryptMethod">
															<?php
															
															foreach ($CONF['TRANSPORT_ENCRYPT_METHOD'] as $index => $encryptMethod) {
																if ($_SESSION['language'] != "zh-cn" && $_SESSION['language'] != "zh-tw" && $index == 2) {
																} else {
																	echo '<option value="' . $index . '">' . $encryptMethod . '</option>';
																}
															}
															?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <!-- 传输压缩 -->
                                                <div class="form-group ml-90 takeovertrancompressdiv ml-0_en">
                                                    <label class="control-label col-md-4 transfercompress"><?php echo $LANG['UI_COPY_COMPRESS_TRANSFER']; ?></label>
                                                    <div class="col-md-5">
                                                        <input type="checkbox" id="takeoverTranCompressSwitch"  class="make-switch" data-on-color="primary" data-off-color="info"
                                                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>"
                                                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>">
                                                        <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true"
                                                           data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_TRANSMISSION_COMPRESSION_TIPS']; ?>">
                                                            <i class="viconfont vicon-tishi"></i>
                                                        </a>
                                                    </div>
                                                </div>

                                                <!-- 压缩等级选择 -->
                                                <div class="form-group ml-90 transferCompressGradeDiv display-none ml-0_en">
                                                    <label class="control-label col-md-4"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE'] ?></label>
                                                    <div class="col-md-4">
                                                        <select class="form-control select2me input-sm" id="cutBacktransferCompressGrade">
                                                            <option value="1"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_FAST'] ?></option>
                                                            <option value="2"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_NORMAL'] ?></option>
                                                            <option value="3"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_BETTER'] ?></option>
                                                            <option value="4"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_BEST'] ?></option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <!-- 传输线程个数 -->
                                                <div class="form-group ml-90 takeovertranencryptdiv ml-0_en">
                                                    <label class="control-label col-md-4 transferthread"><?php echo $LANG['UI_VOL_CDP_NUMBERS_THREADS']; ?>
                                                    </label>
                                                    <div class="col-md-5">
                                                        <div id="transfer_thread_div">
                                                            <div class="input-group">
                                                                <input type="text" id="transfer_thread_number" oninput="if(!/^[1-4]+$/.test(value)) value=value.replace(/\D/g,'');if(value>4)value=4;if(value<1)value=1" class="spinner-input form-control" maxlength="1"  >
                                                                <div class="spinner-buttons input-group-btn">
                                                                    <button type="button" class="btn spinner-up default">
                                                                        <i class="fa fa-angle-up"></i>
                                                                    </button>
                                                                    <button type="button" class="btn spinner-down default">
                                                                        <i class="fa fa-angle-down"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2 mt5">
                                                        <a class="popovers" data-container="body" data-trigger="hover" data-html="true"
                                                           data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_NUMBERS_THREADS_TIPS']; ?>">
                                                            <i class="viconfont vicon-tishi"></i>
                                                        </a>
                                                    </div>
                                                </div>

                                                <!-- 传输数据包大小 -->
                                                <div class="form-group ml-90 takeovertranencryptdiv ml-0_en">
                                                    <label class="control-label col-md-4 transferdatapackage"><?php echo $LANG['UI_VOL_CDP_TRANSMISSION_PACKET_SIZE']; ?>
                                                    </label>
                                                    <div class="col-md-5">
                                                        <select class="form-control select2me" id="transfer_datapackage_size">
                                                            <option value=1> 1 MB</option>
                                                            <option value=2>2 MB</option>
                                                            <option value=4 selected>4 MB</option>
                                                            <option value=8>8 MB</option>
                                                            <option value=16>16 MB</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <!-- 传输网络 -->
                                                <div class="form-group ml-90 display-none tasktransfernetworkDiv ml-0_en">
                                                    <label class="control-label col-md-4 tasktransfernetworklabel"><?php echo $LANG['UI_NODE_TRANSFER_NETWORK']; ?></label>
                                                    <div class="col-md-5">
                                                        <select class="form-control select2me" id="taskTransferNetwork">
                                                        </select>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--回切网络配置 -->
                        <div class="form-group">
                            <label class="control-label col-md-3 diystoragelabel"><?php echo $LANG['UI_VOL_CDP_FAILBACK_NETWORK_CONF_TEXT']; ?></label>
                            <div class=" col-md-9 accordion confvolmap" >
                                <div class="panel panel-default strategy-panel">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a class="accordion-toggle accordion-toggle-styled popovers mapvolumeview collapsed"
                                               data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".confvolmap" href="#failback_network_conf" aria-expanded="true">
                                                <i class="viconfont vicon-pt_setting_service_network font-green-seagreen"></i>
                                                <label class="font-green-seagreen"><?php echo  $LANG['UI_VOL_CDP_FAILBACK_CACHE_NETWORK']; ?></label>
                                                <!--                                                <span class="strategyDes standbyhostDes"></span>-->
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="failback_network_conf" class="panel-collapse collapse stdmapvolume">
                                        <div class="panel-body">
                                            <div class="col-md-12">
                                                <!-- 接管网络配置 -->
                                                <div class="form-group applianceselectview ml-50 ">
                                                    <div class="col-md-12 ml25">
                                                        <button type="button" class="btn green-haze takeovernetworkconf" id="netconfbtn"><?php echo $LANG['UI_VOL_CDP_TASK_TAKEOVER_NET_CONF']; ?></button>
                                                        <ul id="" style="padding-left: 0;width:90%;" class = "failback_takeover_ip_map_list">
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!--回切高级配置 -->
                        <div class="form-group">
                            <label class="control-label col-md-3 diystoragelabel"><?php echo $LANG['UI_VOL_CDP_FAILBACK_ADVANCED_CONF_TEXT']; ?></label>
                            <div class=" col-md-9 accordion confvolmap" >
                                <div class="panel panel-default strategy-panel">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a class="accordion-toggle accordion-toggle-styled popovers mapvolumeview collapsed"
                                               data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".confvolmap" href="#failback_cache_conf" aria-expanded="true">
                                                <i class=" iconfont icon-gaojipeizhi font-green-seagreen"></i>
                                                <label class="font-green-seagreen"><?php echo  $LANG['UI_VOL_CDP_FAILBACK_ADVANCED_CONF_LABLE']; ?></label>
                                                <!--                                                <span class="strategyDes standbyhostDes"></span>-->
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="failback_cache_conf" class="panel-collapse collapse stdmapvolume">
                                        <div class="panel-body">
                                            <div class="col-md-12">

                                                <!--是否开启内存缓存-->
                                                <div class="form-group ml-50  fbmirrordatatoserverdiv ml-0_en">
                                                    <label class="control-label col-md-4 doublehostmirrorlabel"><?php echo $LANG['UI_VOL_CDP_MEMORY_CACHE']; ?></label>
                                                    <div class="col-md-5" id="memorycachechange">
                                                        <input type="checkbox" id="memorycacheset"  class="make-switch" data-on-color="primary" data-off-color="info" checked="checked"
                                                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>"
                                                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>">
                                                        <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true"
                                                           data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_CLIENT_MEMORY_CACHE_SIZE_TIPS'] ?>">
                                                            <i class="viconfont vicon-tishi"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                                <!-- 设置客户端内存缓存大小-->
                                                <div class="form-group ml-50 takeovermemorycachediv ml-0_en">
                                                    <label class="control-label col-md-4 memorycachesizelabel"><?php echo $LANG['UI_VOL_CDP_CLIENT_MEMORY_CACHE_SIZE']; ?>
                                                    </label>
                                                    <div class="col-md-5">
                                                        <select class="form-control select2me" id="memory_cache_size">
                                                            <option value=128 >128 MB</option>
                                                            <option value=512 selected>512 MB</option>
                                                            <option value=1024>1 GB</option>
                                                            <option value=2048>2 GB</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <!-- 客户端文件缓存 路径-->
                                                <div class="form-group ml-50 takeoverfilecache_div ml-0_en">
                                                    <label class="control-label col-md-4 filecachepathlabel"><?php echo $LANG['UI_JOB_FILE_CACHE_PATH']; ?>
                                                    </label>

                                                    <div class="col-md-5">
                                                        <input type="text" maxlength="1024" readonly class="form-control" id="takeoverFileCachePath" title=""  value =<?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_MODE_DEFAULT'];?>  />
                                                    </div>

                                                    <div class="col-md-3 mt10 ml-20">
                                                        <a href = "javascript:void(0)" id = "customFileCachePath" class = "colorgreen">
                                                            <i class="levelchild viconfont vicon-ge_editable_item"></i>
                                                            <?php echo $LANG['UI_JOB_CHANGE_CUSTOM']; ?>
                                                        </a>
                                                        <a href = "javascript:void(0)" id = "defaultFileCachePath" class = "colorgreen display-none">
                                                            <i class="levelchild viconfont vicon-ge_editable_item"></i>
                                                            <?php echo $LANG['UI_JOB_CHANGE_SYSTEM_DEFAULT']; ?>
                                                        </a>
                                                    </div>
                                                    <div class="col-md-2 mt5 display-none">
                                                        <a class="popovers" data-container="body" data-trigger="hover" data-html="true"
                                                           data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_CLIENT_CACHE_PATH_TIPS']; ?>">
                                                            <i class="viconfont vicon-tishi"></i>
                                                        </a>
                                                    </div>
                                                </div>

                                                <!-- 客户端文件缓存 -->
                                                <div class="form-group ml-50 takeoverfilecachediv ml-0_en">
                                                    <label class="control-label col-md-4 filecachesizelabel"><?php echo $LANG['UI_JOB_FILE_CACHE_SIZE']; ?>
                                                    </label>
                                                    <div class="col-md-5">
                                                        <select class="form-control select2me" id="file_cache_size">
                                                            <option value=4096 selected> 4 GB </option>
                                                            <option value=8192> 8 GB </option>
                                                            <option value=16384> 16 GB</option>
                                                            <option value=32768> 32 GB </option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2 mt5">
                                                        <a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_CLIENT_CACHE_SIZE_TIPS']; ?>">
                                                            <i class="viconfont vicon-tishi"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                                <!-- 数据IO复制模式 -->
                                                <div class="form-group ml-50 taskioreplicationdiv ml-0_en">
                                                    <label class="control-label col-md-4 ioreplicationlable"><?php echo $LANG['UI_VOL_CDP_IO_REPLICATION_MODE']; ?></label>
                                                    <div class="col-md-6">
                                                        <label class="control-label ">
                                                            <input type="radio" name="task_io_replication_modle" id = "ioReplicationSyncModle" value="1"  /> <?php echo $LANG['UI_VOL_CDP_SYN']; ?>
                                                        </label>
                                                        <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true"
                                                           data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_SYN_TIPS']; ?>">
                                                            <i class="viconfont vicon-tishi"></i>
                                                        </a>
                                                        <label class="control-label ml50 ml-0_en">
                                                            <input class = "ml15" type="radio" name="task_io_replication_modle" id = "ioReplicationAsyncModle" value="2" /> <?php echo $LANG['UI_VOL_CDP_ASY']; ?>
                                                        </label>
                                                        <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true"
                                                           data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_ASY_TIPS']; ?>">
                                                            <i class="viconfont vicon-tishi"></i>
                                                        </a>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- live-cd 重建分区 -->
                        <div class="form-group  display-none" id="rebuildPartDiv">
                            <label class="control-label col-md-3 diynodelabel form-group-label"><?php echo $LANG['WEB_OS_RESTORE_VOLUME']; ?></label>
                            <div class="col-md-5 form-group-content" id="rebuildPartChange">
                                <input type="checkbox" id="rebuildPartSwitch" class="make-switch" data-size="small"
                                       data-on-color="primary" data-off-color="info"
                                       data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>"
                                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>">
                                <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true"
                                   data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_RESTORE_VOLUME_TIPS']; ?>">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                            </div>
                        </div>


                        <div class="form-group " id ="failBackupVolMapdiv">
                            <label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_MAP_RELATION']; ?></label>
                            <div class="col-md-9">
                                <div class="table-container">
                                    <table class="table table-striped table-bordered table-hover" id="cutBackVolTable">
                                        <thead>
                                        <tr role="row" class="heading">
                                            <th width="20%" id = "dataSource">
                                                <?php echo $LANG['UI_RECOVERY_FILE_DATA_SRC']; ?>
                                            </th>
                                            <th width="20%" id= "dataSize">
                                                <?php echo $LANG['UI_PUBLIC_CAPACITY']; ?>
                                            </th>
                                            <th width="60%" id="dataDiskVol">
                                                <?php echo $LANG['WEB_OS_GOAL_VOLUME']; ?>
                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                    <div class="alert alert-block alert-info fade in display-none" id="takeoverVolMapIps">
                                        <button type="button" class="close" data-dismiss="alert"></button>
                                        <ul class="alert-ul">
                                            <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS']?>:</strong>
                                            <li>
                                                <?php echo $LANG['UI_VOL_CDP_TAKEOVER_VOL_MAP_IPS_ALERT']?>
                                            </li>
                                        </ul>
                                    </div>

                                </div>
                            </div>
                        </div>


                    </div>
                </div>

            </div>
        </div>
    </div>

	<div class="modal-footer">
		<button type="button" data-dismiss="modal" id="closeCatBack" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']; ?></button>
		<button type="button" class="btn btn-primary" id="catBackTaskSubmit"><?php echo $LANG['UI_PUBLIC_YES']; ?></button>
	</div>
</div>
<!-- END TASK  FAILBACK MODAL-->

<!-- 任务接管主机应用IP映射配置  START -->
<div id="taskTakeoverNetworkConfModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
	<div class="modal-header ">
		<button type="button" class="close " data-dismiss="modal" aria-hidden="true"></button>
		<h4 class="modal-title"><i class="iconfont icon-xiansucelve"></i> <?php echo $LANG['UI_VOL_CDP_HOST_IP_SERIVCE_CONF']; ?>
		</h4>
	</div>
	<div class="modal-body">
		<div class="portlet-body">
			<label class="control-label col-md-2"> <?php echo $LANG['UI_VOL_CDP_TASK_IP_SERVICE_CONF']; ?></label>
			<div class="col-md-10">
				<div class="portlet">
					<div class="portlet-body panel panel-default strategy-panel">
						<div class="panel-group accordion mt10 height200" style="overflow-y:auto" id="task_takeover_ip_server_conf">

						</div>
                        <div class="alert alert-block alert-info fade in mt-20" style="margin-bottom:0px;" id="">
                            <button type="button" class="close" data-dismiss="alert"></button>
                            <ul class="alert-ul">
                                <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS']?>:</strong>
                                <li><?php echo $LANG['UI_VOL_CDP_NET_CARD_CONF_TIP']; ?> </li>
                            </ul>
                        </div>
					</div>
				</div>
			</div>

			<label class="control-label col-md-2"> <?php echo $LANG['UI_VOL_CDP_STANDBY_GATEWAY_CONF']; ?></label>
			<div class="col-md-10">
				<div class="portlet">
					<div class="portlet-body panel panel-default strategy-panel">
						<div class="panel-group accordion mt10 height200" style="overflow-y:auto" id="task_standby_gateway_conf">

						</div>
					</div>
				</div>
			</div>

		</div>
	</div>
	<!-- modal-footer -->
	<div class="modal-footer">
		<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']; ?></button>
		<button type="button" class="btn btn-primary" id="submit_task_takeover_network_conf"><?php echo $LANG['UI_PUBLIC_YES']; ?></button>
	</div>
</div>
<!-- 任务接管主机应用IP映射配置  END -->


<!-- 接管網絡配置--modal START -->
<div id="failbackTakeoverNetworkConfModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
	<div class="modal-header ">
		<button type="button" class="close closenetworkconfmodal" data-dismiss="modal" aria-hidden="true"></button>
		<h4 class="modal-title"><i class="iconfont icon-xiansucelve"></i> <?php echo $LANG['UI_VOL_CDP_TASK_FAILBACK_IP_SERVICE_CONF']; ?>
		</h4>
	</div>
	<div class="modal-body">
		<div class="portlet-body">
			<label class="control-label col-md-3"> <?php echo $LANG['UI_VOL_CDP_TASK_FAILBACK_IP_SERVICE_CONF']; ?></label>

            <div class="col-md-9">
                <div class="portlet">
                    <div class="portlet-body panel panel-default strategy-panel">
                        <div class="panel-group accordion mt10 height200" style= "overflow-y:auto" id="failback_takeover_ip_server_conf">

                        </div>
                        <div class="alert alert-block alert-info fade in mt-20" style="margin-bottom:0px;" id="">
                            <button type="button" class="close" data-dismiss="alert"></button>
                            <ul class="alert-ul">
                                <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS']?>:</strong>
                                <li><?php echo $LANG['UI_VOL_CDP_TASK_FAILBACK_IP_SERVICE_CONF_TIP']; ?> </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

			<label class="control-label col-md-3"> <?php echo $LANG['UI_VOL_CDP_FAILBACK_TARGET_GATEWAY_CONF']; ?></label>
			<div class="col-md-9">
				<div class="portlet">
					<div class="portlet-body panel panel-default strategy-panel">
						<div class="panel-group accordion mt10 height200" style="overflow-y:auto" id="failback_standby_gateway_conf">

						</div>
					</div>
				</div>
			</div>

		</div>
	</div>
	<!-- modal-footer -->
	<div class="modal-footer">
		<button type="button" data-dismiss="modal" class="btn btn-default cancelnetworkconfmodal"><?php echo $LANG['UI_PUBLIC_NO']; ?></button>
		<button type="button" class="btn btn-primary" id="submit_failback_takeover_network_conf"><?php echo $LANG['UI_PUBLIC_YES']; ?></button>
	</div>
</div>
<!-- 接管網絡配置  --modal end> -->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./assets/global/plugins/echarts/V5.6.0/echarts.common.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script src="./assets/global/plugins/flot/jquery.flot.min.js"></script>

<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/swiper/js/swiper.min.js"></script>

<script type="text/javascript" src="./scripts/volcdp/vol_cdp_job_details.js"></script>
<script type="text/javascript" src="./scripts/volcdp/takeover_ip_server_map.js"></script>