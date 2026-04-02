<?php include_once '../../tpl/permission.php';?>

<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<!-- BEGIN PAGE HEADER-->
<style type="text/css">
/* .table-scrollable{border: 0;} */
/* .dataTables_scrollBody { */
/*     border-bottom: 0 solid #ddd !important; */
/* } */
</style>
<style type="text/css">
.table-scrollable{overflow: auto;max-height: 258px; }
</style>
<h3 class="page-title">
	数据库实时备份 <small>修改数据库CDP实时备份任务</small>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
		<input id="task_uuid" value="<?php 
	    echo $_GET['uuid'];
	    ?>" class="display-none">
		<div class="portlet box blue-hoki full-height-content full-height-content-scrollable" id="dbcdpcontent">
			<div class="portlet-title">
				<div class="caption">
					<i class="iconfont icon-dbcdp"></i><?php echo $LANG['UI_BACKUP_NEW_TASK']?>
				</div>
			</div>
			<div class="portlet-body form">
				<form action="#" class="form-horizontal" id="submit_form"
					method="POST">
					<div class="form-wizard">
						<div class="form-body">
							<ul class="nav nav-pills nav-justified steps">
								<li><a href="#tab1" data-toggle="tab" class="step"> <span
										class="number"> 1 </span> <span class="desc"> <i
											class="fa fa-check"></i> 生产主机
									</span>
								</a></li>
								<li><a href="#tab2" data-toggle="tab" class="step"> <span
										class="number"> 2 </span> <span class="desc"> <i
											class="fa fa-check"></i> 备份主机
									</span>
								</a></li>
								<li><a href="#tab3" data-toggle="tab" class="step active"> <span
										class="number"> 3 </span> <span class="desc"> <i
											class="fa fa-check"></i> 高级配置
									</span>
								</a></li>
								<li><a href="#tab4" data-toggle="tab" class="step"> <span
										class="number"> 4 </span> <span class="desc"> <i
											class="fa fa-check"></i> 确认配置
									</span>
								</a></li>
							</ul>
							<div id="bar" class="progress progress-striped"
								role="progressbar">
								<div class="progress-bar progress-bar-success"></div>
							</div>
							<div class="tab-content bakuptab">
								<div class="tab-pane active" id="tab1">
									<h4 class="block">请选择您需要实时备份的数据库</h4>
									<div
										class="alert alert-danger display-none selectproducthosttip">
									</div>
									<div class="row">
										<div class="col-md-12">
											<div class="form-group">
												<label class="control-label col-md-3"> 选择生产主机<span
													class="required"> * </span>
												</label>
												<div class="col-md-4 ">
													<div>
														<select class="form-control select2me" id="producthost">
<!-- 															<option value="1">选择数据库所在的主机</option> -->
														</select>
														<div>
															<span class="help-block "> 选择数据库所在的生产主机 </span>
														</div>
													</div>
												</div>
											</div>
											<div class="form-group display-none" id="dbtypediv">
												<label class="control-label col-md-3"> 选择数据库类型<span
													class="required"> * </span>
												</label>
												<div class="col-md-4 ">
													<div>
														<select class="form-control select2me" id="dbtype">
															<option value=""></option>
															<option value="2">Oracle</option>
															<option value="0">SQL Server</option>
															<option value="5">MySQL</option>
															<option value="3">Sybase</option>
															<option value="1">DB2</option>
<!-- 															<option value="10">达梦数据库(DM)</option> -->
<!-- 															<option value="8">人大金仓(Kingbase)</option> -->
<!-- 															<option value="9">神舟通用(ShenTong)</option> -->
														</select>
														<div>
															<span class="help-block "> 选择需要备份的数据库类型 </span>
														</div>
													</div>
												</div>
											</div>
											<div class="form-group display-none" id="instancediv">
												<label class="control-label col-md-3"> 选择数据库实例<span
													class="required"> * </span>
												</label>
												<div class="col-md-8 ">
													<div class="instancediv bd1de5" style="min-height: 62px">
														<table style="margin: 10px 0 10px 15px;"
															id="instancetable">
															<!-- 
															<tr style="height: 40px;">
																<td><label
																	style="padding-right: 40px; margin-bottom: 0;"><input
																		type="checkbox" id=""
																		data-checkbox="icheckbox_square-blue" data-mode="1"
																		class="icheck">实例名称</label></td>
																<td style="padding-right: 20px;"><label>数据库</label> <input
																	type="text" maxlength="64" class="" name="idatabase" />
																</td>
																<td style="padding-right: 20px;"><label>用户名</label> <input
																	type="text" maxlength="64" class="" name="iusername" />
																</td>
																<td><label style="padding-right: 0px;">密码</label> <input
																	type="password" maxlength="64" class=""
																	name="ipassword" /></td>
															</tr>
															 -->
														</table>
													</div>
													<span class="help-block ">请填写连接数据库实例的用户名和密码,完成后扫描数据库</span>
												</div>
												<div class="col-md-offset-3 col-md-3">
													<div class="btn-group">
														<button type="button" id="scandb" class="btn btn-sm green-haze">扫描数据库</button>
													</div>
												</div>
											</div>
											<div class="form-group display-none" id="databasediv">
												<label class="control-label col-md-3"> 选择数据库<span
													class="required"> * </span>
												</label>
												<div class="col-md-6 tree_div2">
													<ul id="db_tree" class="ztree bd1de5"></ul>
													<div>
														<span class="help-block "> 请选择需要实时备份的数据库 </span>
													</div>
												</div>
											</div>
										</div>
									</div>

								</div>
								<div class="tab-pane" id="tab2">
								    <h4 class="block">请选择您备份目的地信息</h4>
									<div
										class="alert alert-danger display-none selectstandbyhosttip">
									</div>
									<div class="row">
										<div class="col-md-12 min-height300">
											<div class="form-group">
												<label class="control-label col-md-3"> 选择备份主机<span
													class="required"> * </span>
												</label>
												<div class="col-md-4 ">
													<div>
														<select class="form-control select2me" id="standbyhost">
<!-- 															<option value="1">选择备份目的主机</option> -->
														</select>
														<div>
															<span class="help-block "> 实时备份数据存储的主机 </span>
														</div>
													</div>
												</div>
											</div>
											<div class="form-group display-none standbyhostchangediv">
												<label class="control-label col-md-3"> 备份类型<span
													class="required"> * </span>
												</label>
												<div class="col-md-4 ">
													<div>
														<select class="form-control select2me" id="backuptype">
															<option value="0">实时备份</option>
															<option value="2">业务接管</option>
															<option value="3">实时备份+业务接管</option>
														</select>
														<div>
															<span class="help-block "> 根据实际情况选择备份类型 </span>
														</div>
													</div>
												</div>
												<div class="col-md-1 mt10">
													<a class="popovers" data-container="body"
														data-trigger="hover" data-placement="right"
														data-html="true"
														data-content="<?php 
														echo "实时备份:实时备份数据到备份主机,当生产环境数据丢失时,可从备份数据恢复到生产环境,不支持业务接管." . "<br><br>" .  
														     "业务接管:实时备份数据到备份主机,要求备份主机和生产主机安装相同环境的操作系统和数据库,当生产环境宕机后,可由备份主机直接接管业务,不支持数据恢复." . "<br><br>" . 
														     "实时备份+业务接管:实时备份和业务接管结合,要求备份主机和生产主机安装相同环境的操作系统和数据库,既可进行数据恢复,也可以进行业务接管";
													?>"><i class="fa fa-info-circle fa-lg"></i> </a>
												</div>
											</div>
											
											<div class="form-group display-none storagediv">
												<label class="control-label col-md-3 diystoragelabel"><?php echo $LANG['UI_COPY_BACK_TARGET_STORAGE']?><span class="required">
                							* </span></label>
												<div class="col-md-4">
													<div>
    													<select class="form-control select2me" id="selectstorage">
    													</select>
    													<div>
    														<span class="help-block "> 用于存放备份数据 </span>
    													</div>
													</div>
												</div>
											</div>
        									
											<div class="form-group display-none standbyhostchangediv dirdiv">
												<label class="control-label col-md-3"> 备份数据目录<span
													class="required"> * </span>
												</label>
												<div class="col-md-4 ">
													<div class="input-icon right">
														<i class="fa"></i> <input type="text" class="form-control"
															id="backupdir" placeholder="d:/databases/backup" /> <span
															class="help-block ">用于存放备份数据的目录,支持自定义目录,请按格式填写</span>
													</div>
												</div>
												<div class="col-md-1 mt10">
													<a class="popovers" data-container="body"
														data-trigger="hover" data-placement="right"
														data-html="true"
														data-content="<?php 
														echo "备份数据主要用于数据回退,目录大小建议为生产数据库的1.25倍";
													?>"><i class="fa fa-info-circle fa-lg"></i> </a>
												</div>
												
												<div class="col-md-4 display-none" id="backupdirtip">
													<div class="alert alert-warning">
                        								<i class="fa fa-info-circle fa-lg"></i> 修改目录会导致已有备份数据无法使用!
                        							</div>
												</div>
												
											</div>
											<div class="form-group display-none standbyhostchangediv hisdiv dirdiv" id="hisdirdiv">
												<label class="control-label col-md-3"> 历史数据目录<span
													class="required"> * </span>
												</label>
												<div class="col-md-4 ">
													<div class="input-icon right">
														<i class="fa"></i> <input type="text" class="form-control"
															id="historydir" placeholder="/home/backup" /> <span
															class="help-block ">用于存放历史数据的目录,支持自定义目录,请按格式填写</span>
													</div>
												</div>
												<div class="col-md-1 mt10">
													<a class="popovers" data-container="body"
														data-trigger="hover" data-placement="right"
														data-html="true"
														data-content="<?php 
														echo "历史数据主要用于数据恢复,目录大小建议为生产数据库的1.25倍";
													?>"><i class="fa fa-info-circle fa-lg"></i> </a>
												</div>
												<div class="col-md-4 display-none" id="historydirtip">
													<div class="alert alert-warning">
                        								<i class="fa fa-info-circle fa-lg"></i> 修改目录会导致已有历史数据无法使用!
                        							</div>
												</div>
											</div>
											<div class="form-group display-none standbyhostchangediv hisdiv" id="hisnumdiv">
												<label class="control-label col-md-3"> 历史数据保留<span
													class="required"> * </span>
												</label>
												<div class="col-md-4 ">
													<div class="input-icon right">
														<i class="fa"></i> <input type="text" class="form-control"
															id="historycopys" placeholder="1" value="1"/> <span
															class="help-block ">设置历史数据保留的份数</span>
													</div>
												</div>
												<div class="col-md-1 mt10">
													<a class="popovers" data-container="body"
														data-trigger="hover" data-placement="right"
														data-html="true"
														data-content="<?php 
														echo "任务停止再重新启动后,之前的备份数据会自动归档到历史数据目录,可以根据备份数据和存储空间大小灵活调整";
													?>"><i class="fa fa-info-circle fa-lg"></i> </a>
												</div>
											</div>
										</div>
									</div>

								</div>
								<div class="tab-pane" id="tab3">
									<h4 class="block">请设置高级配置</h4>
									<div class="alert alert-danger display-none sethighsettip"></div>
									<div class="row">
										<div class="tabbable-custom pdlr15 col-md-offset-1 col-md-10">
											<ul class="nav nav-tabs " id="tab3ul">
												<li class="active"><a href="#tab_log" class="popovers"
													data-content="配置日志管理方式"
													data-container="body" data-trigger="hover"
													data-placement="top" data-toggle="tab"> <i
														class="fa fa-clipboard"></i> 日志
												</a></li>
												<li class="" id="tabtakeover"><a href="#tab_takeover" class="popovers"
													data-content="配置任务接管参数"
													data-container="body" data-trigger="hover"
													data-placement="top" data-toggle="tab"> <i
														class="fa fa-random"></i> 接管
												</a></li>
											</ul>
											<div class="tab-content min-height300" id="tabcontentdiv">
												<div class="tab-pane active" id="tab_log">
													<div class="panel-body">
														<div class="col-md-12 ">
															<div class="form-group">
																<label class="control-label col-md-3 logtypelabel">日志管理方式</label>
																<div class="col-md-4">
																	<select class="form-control select2me" id="logtype">
																		<option value="3">按时间保留(小时)</option>
																		<option value="0">按恢复条数保留</option>
            															<option value="2">按容量大小保留(自适应)</option>
            															<option value="1">按容量大小保留(自定义)</option>
																	</select>
																</div>
																<div class="col-md-1 mt10">
																	<a class="popovers" data-container="body"
																		data-trigger="hover" data-placement="right"
																		data-html="true"
																		data-content="<?php 
																		      echo  "备份日志的管理方式,备份日志可用作数据库恢复.<br><br>".
																			  "按时间保留:日志会保存设定的值时间的恢复数据,超过设定的值无法恢复<br><br>" . 
																			  "按恢复条数保留:日志会保存设定的值的恢复数据,超过设定的值无法恢复<br><br>" . 
																		      "按容量大小保留:系统会按设定的容量大小来保存日志,操作容量会自动覆盖最早的日志,选择自适应会自动适应日志大小,直到把磁盘空间使用完";
																		?>">
																		<i class="fa fa-info-circle fa-lg"></i>
																	</a>
																</div>
															</div>
															<div class="form-group logdaydiv logdiv">
                												<label class="control-label col-md-3"> 小时<span
                													class="required"> * </span>
                												</label>
                												<div class="col-md-4 ">
                													<div class="input-icon right">
                														<i class="fa"></i> <input type="text" class="form-control"
                															id="hours" value="48" placeholder="48" /> <span
                															class="help-block ">按设置的小时恢复数据</span>
                													</div>
                												</div>
                												<div class="col-md-1 mt10">
																	<a class="popovers" data-container="body"
																		data-trigger="hover" data-placement="right"
																		data-content="可根据数据库容量和日志每天变化量设置保留的值">
																		<i class="fa fa-info-circle fa-lg"></i>
																	</a>
																</div>
                											</div>
                											<div class="form-group display-none lognumdiv logdiv">
                												<label class="control-label col-md-3"> 恢复条数<span
                													class="required"> * </span>
                												</label>
                												<div class="col-md-4 ">
                													<div class="input-icon right">
                														<i class="fa"></i> <input type="text" class="form-control"
                															id="recoverystep" value="1000000" placeholder="1000000" /> <span
                															class="help-block ">按设置的值恢复数据</span>
                													</div>
                												</div>
                												<div class="col-md-1 mt10">
																	<a class="popovers" data-container="body"
																		data-trigger="hover" data-placement="right"
																		data-content="可根据数据库容量和日志每天变化量设置保留的值">
																		<i class="fa fa-info-circle fa-lg"></i>
																	</a>
																</div>
                											</div>
															<div class="form-group display-none logsizediv logdiv">
                                    							<label class="control-label col-md-3">日志容量 </label>
                                    							<div class="col-md-4" >
                                    								<div class="spinnerNum">
                                    		                        	<div class="input-group" >
                                    			                        	<input type="text" id="logsize" style="text-align: center;" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control" maxlength="8">
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
                                    							<div class="col-md-2" style="margin-left: -20px;">
																	<select class="form-control select2me" id="logsizeunits">
																		<option value="0">MB</option>
																	    <option value="1">GB</option>
            															<option value="2">TB</option>
																	</select>
																</div>
																<div class="col-md-1 mt10">
																	<a class="popovers" data-container="body"
																		data-trigger="hover" data-placement="right"
																		data-content="可根据数据库容量和日志每天变化量设置保留的值">
																		<i class="fa fa-info-circle fa-lg"></i>
																	</a>
																</div>
                                    						</div>
														</div>
													</div>
												</div>
												<div class="tab-pane" id="tab_takeover">
													<div class="panel-body" style="overflow-y: auto;">
														<div class="col-md-12 ">
															<div class="form-group ">
																<label class="control-label col-md-3 ">业务接管</label>
																<div class="col-md-4">
																	<input type="checkbox" id="takeovercheck"
																		class="make-switch" data-on-color="primary"
																		data-off-color="info"
																		data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>"
																		data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
																</div>
																<div class="col-md-1 mt10">
																	<a class="popovers" data-container="body"
																		data-trigger="hover" data-placement="right"
																		data-content="如果开启业务接管,备份系统会在设定的业务监控尝试失败次数后,自动接管生产业务">
																		<i class="fa fa-info-circle fa-lg"></i>
																	</a>
																</div>
															</div>
															<div class="form-group tkdivs display-none">
																<label class="control-label col-md-3 ">接管方式</label>
																<div class="col-md-4">
																	<select class="form-control select2me" id="takeovertype">
																	    <option value="0">手动接管</option>
            															<!-- <option value="1">自动接管</option> -->
																	</select>
																</div>
																<div class="col-md-1 mt10">
																	<a class="popovers" data-container="body" data-html="true"
																		data-trigger="hover" data-placement="right"
																		data-content="<?php echo "手动接管:需要手动启动接管<br>自动接管:系统检查状态自动接管"?>">
																		<i class="fa fa-info-circle fa-lg"></i>
																	</a>
																</div>
															</div>
															<div class="form-group tkdivs display-none">
																<label class="control-label col-md-3 ">连接次数</label>
                                    							<div class="col-md-4" >
                                    								<div class="spinnertakeover">
                                    		                        	<div class="input-group" >
                                    			                        	<input type="text" id="takeoverstep" style="text-align: center;" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control" maxlength="8">
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
                                    							<div class="col-md-4" style="margin: 15px 0 0 -20px;">
																	次失败后,启动接管
																</div>
                                    						</div>
                                    						<div class="form-group tkdivs display-none">
																<label class="control-label col-md-3 ">接管网卡</label>
																<div class="col-md-9">
                                    								<div class="table-container">
                                                    					<table class="table table-striped table-bordered table-hover" 
                                                    					id="takeovernetworktable" >
                                                    					<thead>
                                                    					<tr role="row" class="heading">
                                                    						<th width="20%">
                                                    							 网卡地址
                                                    						</th>
                                                    						<th width="30%">
                                                    							备份主机IP信息
                                                    						</th>
                                                    						<th width="30%">
                                                    							生产主机IP信息
                                                    						</th>
                                                    						<th width="20%">
                                                    							操作
                                                    						</th>
                                                    					</tr>
                                                    					</thead>
                                                    					<tbody>
                                                    					</tbody>
                                                    					</table>
                                                    				</div>
                                                    				<div><span class="help-block ">
                                                    					请选择备份主机接管生产主机的网卡对应关系
                                                    				</span></div>
                                    							</div>
															</div>
															<div class="form-group tkdivs display-none">
																<label class="control-label col-md-3 ">启动服务</label>
																<div class="col-md-9">
                                    								<div class="table-container">
                                                    					<table class="table table-striped table-bordered table-hover" 
                                                    					id="takeoverservicetable" >
                                                    					<thead>
                                                    					<tr role="row" class="heading">
                                                    						<th width="2%">
                                                    							<input type="checkbox" class="group-checkable">
                                                    						</th>
                                                    						<th width="25%">
                                                    							 名称
                                                    						</th>
                                                    						<th width="40%">
                                                    							 描述
                                                    						</th>
                                                    						<th width="10%">
                                                    							状态
                                                    						</th>
                                                    						<th width="10%">
                                                    							启动类型
                                                    						</th>
                                                    					</tr>
                                                    					</thead>
                                                    					<tbody>
                                                    					</tbody>
                                                    					</table>
                                                    				</div>
                                                    				<!-- <div><span class="help-block ">
                                                    					请选择备份主机接管生产主机的网卡对应关系
                                                    				</span></div> -->
                                    							</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="tab-pane" id="tab4">
									<h3 class="block"><?php echo $LANG['UI_BACKUP_CONFIRM_SETTING']?></h3>
									<div class="alert alert-danger display-none jobnametip"></div>
									<div class="form-group mb0">
										<label class="control-label col-md-3"><?php echo $LANG['UI_JOB_RNAME']?>:</label>
										<div class="col-md-4">
											<div class="input-icon right">
												<i class="fa"></i> <input type="text" maxlength="64"
													class="form-control" id="jobname" /> <span
													class="help-block "><?php echo $LANG['UI_JOB_RNAME_TIPS']?></span>
											</div>
										</div>
									</div>
									<h4 class="form-section">生产主机</h4>
									<div class="form-group mb0">
										<label class="control-label col-md-3">生产主机:</label>
										<div class="col-md-7">
											<p class="form-control-static colorgreen producthostshow"></p>
										</div>
									</div>
									<div class="form-group mb0">
										<label class="control-label col-md-3">数据库:</label>
										<div class="col-md-7">
											<p class="form-control-static colorgreen dbshow"></p>
										</div>
									</div>
									<h4 class="form-section">备份主机</h4>
									<div class="form-group mb0">
										<label class="control-label col-md-3">备份主机:</label>
										<div class="col-md-4">
											<p class="form-control-static colorgreen backuphostshow"></p>
										</div>
									</div>
									<div class="form-group mb0">
										<label class="control-label col-md-3">备份类型:</label>
										<div class="col-md-6">
											<p class="form-control-static colorgreen backuptypeshow"></p>
										</div>
									</div>
									<div class="form-group mb0 backupdiv">
										<label class="control-label col-md-3">备份数据目录:</label>
										<div class="col-md-6">
											<p class="form-control-static colorgreen backupdirshow"></p>
										</div>
									</div>
									<div class="form-group mb0 hisdiv" id="hisdivshow">
										<label class="control-label col-md-3">历史数据目录:</label>
										<div class="col-md-6">
											<p class="form-control-static colorgreen historydirshow"></p>
										</div>
									</div>
									<div class="form-group mb0 hisdiv" id="hisnumdivshow">
										<label class="control-label col-md-3">历史数据份数:</label>
										<div class="col-md-6">
											<p class="form-control-static colorgreen historycopysshow"></p>
										</div>
									</div>
									<h4 class="form-section">高级配置</h4>

									<div class="form-group mb0 ">
										<label class="control-label col-md-3">日志:</label>
										<div class="col-md-7">
											<p class="form-control-static colorgreen logshow"></p>
										</div>
									</div>
									<div class="form-group mb0 " id="takeoverdes">
										<label class="control-label col-md-3">接管:</label>
										<div class="col-md-4">
											<p class="form-control-static colorgreen takeovershow">
											</p>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="form-actions">
							<div class="row">
								<div class="col-md-offset-6 col-md-6">
									<a href="javascript:;" class="btn default button-previous"> <i
										class="m-icon-swapleft"></i> <?php echo $LANG['UI_PUBLIC_PREV_STEP']?> </a>
									<a href="javascript:;" class="btn green-turquoise button-next  next-btn-margin-left">
									<?php echo $LANG['UI_PUBLIC_NEXT_STEP']?> <i
										class="m-icon-swapright m-icon-white"></i>
									</a> <a href="javascript:;"
										class="btn green-haze button-submit">
									<?php echo $LANG['UI_PUBLIC_SUBMIT']?> <i
										class="m-icon-swapright m-icon-white"></i>
									</a>
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
		
		<!-- BEGIN MODAL -->
		<div id="modalnetwordcard" class="modal xmodal fade form-horizontal" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="icon-badge"></i> 选择接管网卡</h4>
			</div>
			<div class="modal-body">
    			<div class="portlet-body">
    			    <div class="table-toolbar">
    					<div class="row">
    						<div class="col-md-12">
    						    
    						    <div class="btn-group">
    								<button type="button" id="addtakeovercard" class="btn btn-sm green-haze">
    								<i class="viconfont vicon-ge_authorization2"></i> 接管勾选的网卡
    								</button>
    							</div>
    						</div>
    					</div>
    				</div>
                    <div class="table-container">
    					<table class="table table-striped table-bordered table-hover" id="standbyNetworkTable">
    					<thead>
    					<tr role="row" class="heading">
    						<th width="2%">
    							<input type="checkbox" class="group-checkable">
    						</th>
    						<th width="20%">
    							网卡名称
    						</th>
    						<th width="15%">
    							MAC地址
    						</th>
    						<th width="20%">
    							IP信息
    						</th>
    						<th width="45%">
    							描述
    						</th>
    					</tr>
    					</thead>
    					<tbody>
    					</tbody>
    					</table>
    					
						<div class="alert alert-block alert-info fade in">
							<button type="button" class="close" data-dismiss="alert"></button>
							<ul class="alert-ul">
								<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS']?>:</strong>
								<li>
									<i class="fa fa-warning"></i> 请选择备份主机需要接管的生产主机网卡
								</li>
							</ul>
						</div>
    				</div>
				</div>
			</div>
		</div>	
		<!-- END MODAL -->
		
	</div>
</div>
<!-- END PAGE CONTENT-->


<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/additional-methods.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/fuelux/js/spinner.min.js" ></script>

<script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/libs/base64.min.js"></script>

<!-- END PAGE LEVEL PLUGINS -->
<script src="./scripts/db/dbcdpedit.js" type="text/javascript"></script>
