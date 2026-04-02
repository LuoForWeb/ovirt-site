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
<!--<h3 class="page-title">-->
<!--	文件实时同步 <small>创建文件实时同步任务</small>-->
<!--</h3>-->
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
		<div class="portlet box blue-hoki full-height-content full-height-content-scrollable" id="filecdpcontent">
			<div class="portlet-title">
				<div class="caption">
					<i class="levelchild viconfont vicon-filecdpbackup"></i><?php echo $LANG['UI_BACKUP_NEW_TASK']?>
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
											class="fa fa-check"></i> <?php echo $LANG['UI_JOB_PRODUCT_HOST']?>
									</span>
								</a></li>
								<li><a href="#tab2" data-toggle="tab" class="step"> <span
										class="number"> 2 </span> <span class="desc"> <i
											class="fa fa-check"></i> <?php echo $LANG['UI_JOB_BACKUP_HOST']?>
									</span>
								</a></li>
								<li><a href="#tab3" data-toggle="tab" class="step active"> <span
										class="number"> 3 </span> <span class="desc"> <i
											class="fa fa-check"></i>  <?php echo $LANG['UI_BACKUP_INDIVIDUAL_CONFIG']?>
									</span>
								</a></li>
								<li><a href="#tab4" data-toggle="tab" class="step"> <span
										class="number"> 4 </span> <span class="desc"> <i
											class="fa fa-check"></i><?php echo $LANG['UI_BACKUP_CONFIRM_CONFIG']?>
									</span>
								</a></li>
							</ul>
							<div id="bar" class="progress progress-striped"
								role="progressbar">
								<div class="progress-bar progress-bar-success"></div>
							</div>
							<div class="tab-content bakuptab">
								<div class="tab-pane active" id="tab1">
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
														</select>
														<div>
															<span class="help-block "> 选择文件所在的生产主机 </span>
														</div>
													</div>
												</div>
											</div>
											<div class="form-group display-none" id="filediv">
												<label class="control-label col-md-3"> 选择文件/文件夹<span
													class="required"> * </span>
												</label>
												<div class="col-md-6 tree_div2">
													<ul id="file_tree" class="ztree bd1de5"></ul>
													<div>
														<span class="help-block "> 请选择需要实时同步的文件/文件夹 </span>
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
															<span class="help-block "> 实时同步数据存储的主机 </span>
														</div>
													</div>
												</div>
											</div>
											<div class="form-group display-none standbyhostchangediv">
												<label class="control-label col-md-3"> 同步类型<span
													class="required"> * </span>
												</label>
												<div class="col-md-4 ">
													<div>
														<select class="form-control select2me" id="backuptype">
															<option value="1">实时同步+历史数据</option>
															<option value="0">实时同步</option>
														</select>
														<div>
															<span class="help-block "> 根据实际情况选择同步类型 </span>
														</div>
													</div>
												</div>
												<div class="col-md-1 mt10">
													<a class="popovers" data-container="body"
														data-trigger="hover" data-placement="right"
														data-html="true"
														data-content="<?php 
														echo "实时同步:实时同步数据到备份主机" . "<br><br>" .  
														     "实时同步+历史数据:实时同步数据到备份主机,当生产主机删除或修改文件后,会保存删除或修改的文件到备份主机历史目录" . "<br><br>";
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
    														<span class="help-block "> 用于存放同步数据 </span>
    													</div>
													</div>
												</div>
											</div>
        									
											<div class="form-group display-none standbyhostchangediv dirdiv">
												<label class="control-label col-md-3"> 同步数据目录<span
													class="required"> * </span>
												</label>
												<div class="col-md-4 ">
													<div class="input-icon right">
														<i class="fa"></i> <input type="text" class="form-control"
															id="backupdir" placeholder="d:/file/backup" /> <span
															class="help-block ">用于存放同步数据的目录,支持自定义目录,请按格式填写</span>
													</div>
												</div>
												<div class="col-md-1 mt10">
													<a class="popovers" data-container="body"
														data-trigger="hover" data-placement="right"
														data-html="true"
														data-content="<?php 
														echo "同步数据目录建议大于等于生产文件数据总量,并考虑到生产端数据增长量";
													?>"><i class="fa fa-info-circle fa-lg"></i> </a>
												</div>
											</div>
											<div class="form-group display-none standbyhostchangediv hisdiv dirdiv">
												<label class="control-label col-md-3"> 历史数据目录<span
													class="required"> * </span>
												</label>
												<div class="col-md-4 ">
													<div class="input-icon right">
														<i class="fa"></i> <input type="text" class="form-control"
															id="historydir" placeholder="d:/file/history" /> <span
															class="help-block ">用于存放历史数据的目录,支持自定义目录,请按格式填写</span>
													</div>
												</div>
												<div class="col-md-1 mt10">
													<a class="popovers" data-container="body"
														data-trigger="hover" data-placement="right"
														data-html="true"
														data-content="<?php 
														echo "历史数据主要用于找回删除或修改过的文件";
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
												<li class="active"><a href="#tab_convention" class="popovers"
													data-content="经常会使用到的配置"
													data-container="body" data-trigger="hover"
													data-placement="top" data-toggle="tab"> <i
														class="iconfont icon-normalsetting "></i> 常规配置
												</a></li>
												<li class=""><a href="#tab_ransomware" class="popovers"
													data-content="防勒索病毒配置"
													data-container="body" data-trigger="hover"
													data-placement="top" data-toggle="tab"> <i
														class="iconfont icon-fanglesuo "></i> 防勒索
												</a></li>
												<li class=""><a href="#tab_filter" class="popovers"
													data-content="文件过滤配置"
													data-container="body" data-trigger="hover"
													data-placement="top" data-toggle="tab"> <i
														class="iconfont icon-fsfitter "></i> 文件过滤
												</a></li>
												<li class=""><a href="#tab_strategy" class="popovers"
													data-content="时间策略配置"
													data-container="body" data-trigger="hover"
													data-placement="top" data-toggle="tab"> <i
														class="iconfont icon-timestrategy "></i> 时间策略
												</a></li>
												<li class="hisdivtab" id="tabhistory"><a href="#tab_history" class="popovers"
													data-content="历史文件保留配置"
													data-container="body" data-trigger="hover"
													data-placement="top" data-toggle="tab"> <i
														class="iconfont icon-hisdata "></i> 历史数据
												</a></li>
											</ul>
											<div class="tab-content min-height300" id="tabcontentdiv">
												<div class="tab-pane active" id="tab_convention">
													<div class="panel-body">
														<div class="col-md-12 ">
															<div class="form-group ">
																<label class="control-label col-md-3 " id="multithreadinglable">多线程传输</label>
																<div class="col-md-4">
																	<input type="checkbox" id="multithreading"
																		class="make-switch" data-on-color="primary"
																		data-off-color="info"
																		data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>"
																		data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
																</div>
																<div class="col-md-1 mt10">
																	<a class="popovers" data-container="body"
																		data-trigger="hover" data-placement="right"
																		data-content="如果开启多线程传输,备份系统会按照选择同步文件目录对顶层的数量来创建线程数进行数据传输,可以有效增加数据同步效率">
																		<i class="fa fa-info-circle fa-lg"></i>
																	</a>
																</div>
															</div>
															<div class="form-group ">
																<label class="control-label col-md-3 " id="mirrorimagelable">目录文件镜像</label>
																<div class="col-md-4">
																	<input type="checkbox" id="mirrorimage"
																		class="make-switch" data-on-color="primary"
																		data-off-color="info"
																		data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>"
																		data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
																</div>
																<div class="col-md-1 mt10">
																	<a class="popovers" data-container="body"
																		data-trigger="hover" data-placement="right"
																		data-content="开启配置后备份端会完全跟生产端数据一致,多余的数据会删除,如果关闭此项,生产端数据删除后,备份端数据不会删除">
																		<i class="fa fa-info-circle fa-lg"></i>
																	</a>
																</div>
															</div>
															<div class="form-group ">
																<label class="control-label col-md-3 " id="emptydirlabel">空目录同步</label>
																<div class="col-md-4">
																	<input type="checkbox" id="emptydir"
																		class="make-switch" data-on-color="primary"
																		data-off-color="info"
																		data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>"
																		data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
																</div>
																<div class="col-md-1 mt10">
																	<a class="popovers" data-container="body"
																		data-trigger="hover" data-placement="right"
																		data-content="开启配置后备份端会同步生产端的空目录,关闭后不同步空目录">
																		<i class="fa fa-info-circle fa-lg"></i>
																	</a>
																</div>
															</div>
														</div>
													</div>
												</div>
												
												<div class="tab-pane" id="tab_ransomware">
													<div class="panel-body">
														<div class="col-md-12 ">
															<div class="form-group">
																<label class="control-label col-md-3 logtypelabel">防勒索病毒</label>
																<div class="col-md-4">
																	<select class="form-control select2me" id="ransomwaretype">
																		<option value="0">关闭</option>
																		<option value="2">指定文件类型备份</option>
																	</select>
																</div>
																<div class="col-md-1 mt10">
																	<a class="popovers" data-container="body"
																		data-trigger="hover" data-placement="right"
																		data-content="开启防勒索病毒功能后,每次同步文件会检查文件的数据结构,同步效率会比关闭此功能低">
																		<i class="fa fa-info-circle fa-lg"></i>
																	</a>
																</div>
															</div>
                											<div class="form-group display-none" id="ransomwarediv">
																<label class="control-label col-md-3 ">选择文件类型<span class="required">
                                                    			* </span></label>
																<div class="col-md-9">
                                    								<div class="table-container">
                                                    					<table class="table table-striped table-bordered table-hover" 
                                                    					id="ransomwaretable" >
                                                    					<thead>
                                                    					<tr role="row" class="heading">
                                                    						<th width="2%">
                                                    							<input type="checkbox" class="group-checkable">
                                                    						</th>
                                                    						<th width="25%">
                                                    							 文件扩展名
                                                    						</th>
                                                    						<th width="70%">
                                                    							 文件类型
                                                    						</th>
                                                    					</tr>
                                                    					</thead>
                                                    					<tbody>
                                                    					</tbody>
                                                    					</table>
                                                    				</div>
                                                    				<div><span class="help-block ">
                                                    					请选择指定的文件类型,列表展示了所有本主机的文件扫描记录,如果没有找到对应的文件类型,您可以<a id="openscan">立即扫描</a>
                                                    				</span></div>
                                    							</div>
															</div>
														</div>
													</div>
												</div>
												
												<div class="tab-pane" id="tab_filter">
													<div class="panel-body">
														<div class="col-md-12 ">
															<div class="form-group ">
																<label class="control-label col-md-3 " id="timefilterlabel">时间过滤</label>
																<div class="col-md-4">
																	<input type="checkbox" id="timefilter"
																		class="make-switch" data-on-color="primary"
																		data-off-color="info"
																		data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>"
																		data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
																</div>
																<div class="col-md-1 mt10">
																	<a class="popovers" data-container="body"
																		data-trigger="hover" data-placement="right"
																		data-content="按文件修改时间过滤文件,只同步指定时间内的文件">
																		<i class="fa fa-info-circle fa-lg"></i>
																	</a>
																</div>
															</div>
															<div class="form-group display-none timefilterdiv">
																<label class="control-label col-md-3 logtypelabel">过滤方式</label>
																<div class="col-md-4">
																	<select class="form-control select2me" id="timefiltertype">
																		<option value="1">按天过滤(同步最近几天)</option>
																		<option value="2">按月过滤(同步最近几月)</option>
            															<option value="3">按年过滤(同步最近几年)</option>
																	</select>
																</div>
															</div>
															<div class="form-group display-none timefilterdiv">
                                    							<label class="control-label col-md-3">过滤时间 </label>
                                    							<div class="col-md-3" >
                                    								<div class="timefiltervalue">
                                    		                        	<div class="input-group" >
                                    			                        	<input type="text" id="timefiltervalue" style="text-align: center;" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control" maxlength="5">
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
																<div class="col-md-1 mt10">
																</div>
																<div class="col-md-1 mt10">
																	<a class="popovers" data-container="body"
																		data-trigger="hover" data-placement="right"
																		data-content="备份系统只会同步配置的时间段内的数据">
																		<i class="fa fa-info-circle fa-lg"></i>
																	</a>
																</div>
                                    						</div>
                                    						<div class="form-group ">
																<label class="control-label col-md-3 " id="namefilterlabel">名称过滤</label>
																<div class="col-md-4">
																	<input type="checkbox" id="namefilter"
																		class="make-switch" data-on-color="primary"
																		data-off-color="info"
																		data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>"
																		data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
																</div>
																<div class="col-md-1 mt10">
																	<a class="popovers" data-container="body"
																		data-trigger="hover" data-placement="right"
																		data-content="按名称排除指定文件/文件夹,不同步">
																		<i class="fa fa-info-circle fa-lg"></i>
																	</a>
																</div>
															</div>
															<div class="form-group display-none namefilterdiv">
                                                    			<label class="control-label col-md-3"> 排除列表<span class="required">
                                                    			* </span>
                                                    			</label>
                                                    			<div class="col-md-4">
                                                    				<textarea class="form-control" id="namefilterlist" rows="4" placeholder="202?01,*02,*.txt,test?.*"></textarea>
                                                    				<div><span class="help-block ">
                                                    					排除指定名称文件/文件夹不同步,可以使用通配符?和*,?表示匹配一个字符,*表示匹配多个字符,多个匹配项使用逗号分隔
                                                    				</span></div>
                                                    			</div>
                                                    		</div>
														</div>
													</div>
												</div>
												
												<div class="tab-pane" id="tab_strategy">
													<div class="panel-body">
														<div class="col-md-12 ">
															<div class="form-group">
																<label class="control-label col-md-3 logtypelabel">时间策略</label>
																<div class="col-md-4">
																	<select class="form-control select2me" id="strategytype">
																		<option value="0">全天同步</option>
																		<option value="1">指定时间段同步</option>
																	</select>
																</div>
																<div class="col-md-1 mt10">
																	<a class="popovers" data-container="body"
																		data-trigger="hover" data-placement="right"
																		data-content="全天同步所有时间内都会进行同步,指定时间段同步只会在指定的时间段内同步数据">
																		<i class="fa fa-info-circle fa-lg"></i>
																	</a>
																</div>
															</div>
                                                    		<div class="form-group display-none" id="strategytimediv">
                												<label class="control-label col-md-3"> 起止时间<span
                													class="required"> * </span>
                												</label>
                												<div class="col-md-3">
                                        							<div class="input-group">
                                        								<input type="text" class="form-control timepicker timepicker-24 starttime">
                                        								<span class="input-group-btn">
                                        								<button class="btn default" type="button"><i class="fa fa-clock-o"></i></button>
                                        								</span>
                                        							</div>
                                        						</div>
                                        						<div class="col-md-3">
                                        							<div class="input-group">
                                        								<input type="text" class="form-control timepicker timepicker-24 endtime">
                                        								<span class="input-group-btn">
                                        								<button class="btn default" type="button"><i class="fa fa-clock-o"></i></button>
                                        								</span>
                                        							</div>
                                        						</div>
                											</div>
														</div>
													</div>
												</div>
												
												<div class="tab-pane" id="tab_history">
													<div class="panel-body">
														<div class="col-md-12 ">
															<div class="form-group">
                                    							<label class="control-label col-md-3" id="historytimelabel">保留时间 </label>
                                    							<div class="col-md-3" >
                                    								<div class="historytime">
                                    		                        	<div class="input-group" >
                                    			                        	<input type="text" id="historytime" style="text-align: center;" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control" maxlength="5">
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
                                    							<div class="col-md-1" style="margin: 15px 0 0 -20px;" id="historytimeunit">
																	天
																</div>
																<div class="col-md-1 mt10">
																	<a class="popovers" data-container="body"
																		data-trigger="hover" data-placement="right"
																		data-content="历史数据保留的时间,从生成历史文件的时间开始计算">
																		<i class="fa fa-info-circle fa-lg"></i>
																	</a>
																</div>
                                    						</div>
                                    						
                                    						<div class="form-group">
                                    							<label class="control-label col-md-3" id="historydellabel">每天删除文件保留 </label>
                                    							<div class="col-md-3" >
                                    								<div class="spinnerNum">
                                    		                        	<div class="input-group" >
                                    			                        	<input type="text" id="historydel" style="text-align: center;" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control" maxlength="5">
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
                                    							<div class="col-md-1" style="margin: 15px 0 0 -20px;" id="historydelunit">
																	个
																</div>
																<div class="col-md-1 mt10">
																	<a class="popovers" data-container="body"
																		data-trigger="hover" data-placement="right"
																		data-content="每天单个文件删除保留个数">
																		<i class="fa fa-info-circle fa-lg"></i>
																	</a>
																</div>
                                    						</div>
                                    						
                                    						<div class="form-group">
                                    							<label class="control-label col-md-3" id="historymodlabel">每天修改文件保留 </label>
                                    							<div class="col-md-3" >
                                    								<div class="spinnerNum">
                                    		                        	<div class="input-group" >
                                    			                        	<input type="text" id="historymod" style="text-align: center;" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control" maxlength="5">
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
                                    							<div class="col-md-1" style="margin: 15px 0 0 -20px;" id="historymodunit">
																	个
																</div>
																<div class="col-md-1 mt10">
																	<a class="popovers" data-container="body"
																		data-trigger="hover" data-placement="right"
																		data-content="每天单个文件修改保留个数">
																		<i class="fa fa-info-circle fa-lg"></i>
																	</a>
																</div>
                                    						</div>
                                    						<div class="form-group">
                                    							<label class="control-label col-md-3" id="historytimeintervallabel">修改文件间隔 </label>
                                    							<div class="col-md-3" >
                                    								<div class="historytimeinterval">
                                    		                        	<div class="input-group" >
                                    			                        	<input type="text" id="historytimeinterval" style="text-align: center;" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control" maxlength="5">
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
                                    							<div class="col-md-1" style="margin: 15px 0 0 -20px;" id="historytimeintervalunit">
																	秒
																</div>
																<div class="col-md-1 mt10">
																	<a class="popovers" data-container="body"
																		data-trigger="hover" data-placement="right"
																		data-content="判断一个文件修改间隔,在设置间隔时间内的文件修改后只保留一个历史数据">
																		<i class="fa fa-info-circle fa-lg"></i>
																	</a>
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
									<h4 class="form-section"></h4>
									<div class="form-group mb0">
										<label class="control-label col-md-3">生产主机:</label>
										<div class="col-md-7">
											<p class="form-control-static colorgreen producthostshow"></p>
										</div>
									</div>
									<div class="form-group mb0">
										<label class="control-label col-md-3">同步文件/文件夹:</label>
										<div class="col-md-7">
											<div class="filelisttext" id="filelist" style="margin-left:0;">
                                     
                               	 			</div>
										</div>
									</div>
									<h4 class="form-section"></h4>
									<div class="form-group mb0">
										<label class="control-label col-md-3">备份主机:</label>
										<div class="col-md-4">
											<p class="form-control-static colorgreen backuphostshow"></p>
										</div>
									</div>
									<div class="form-group mb0">
										<label class="control-label col-md-3">同步类型:</label>
										<div class="col-md-6">
											<p class="form-control-static colorgreen backuptypeshow"></p>
										</div>
									</div>
									<div class="form-group mb0" id="backupdirdiv">
										<label class="control-label col-md-3">同步数据目录:</label>
										<div class="col-md-6">
											<p class="form-control-static colorgreen backupdirshow"></p>
										</div>
									</div>
									<div class="form-group mb0 historyshowdiv">
										<label class="control-label col-md-3">历史数据目录:</label>
										<div class="col-md-6">
											<p class="form-control-static colorgreen historydirshow"></p>
										</div>
									</div>
									
									<h4 class="form-section"></h4>

									<div class="form-group mb0 ">
										<label class="control-label col-md-3">常规配置:</label>
										<div class="col-md-7">
											<p class="form-control-static colorgreen conventionshow"></p>
										</div>
									</div>
									<div class="form-group mb0 ">
										<label class="control-label col-md-3">防勒索:</label>
										<div class="col-md-7">
											<p class="form-control-static colorgreen ransomwareshow"></p>
										</div>
									</div>
									<div class="form-group mb0 ">
										<label class="control-label col-md-3">文件过滤:</label>
										<div class="col-md-7">
											<p class="form-control-static colorgreen filtershow"></p>
										</div>
									</div>
									<div class="form-group mb0 ">
										<label class="control-label col-md-3">时间策略:</label>
										<div class="col-md-7">
											<p class="form-control-static colorgreen strategyshow"></p>
										</div>
									</div>
									<div class="form-group mb0 historyshowdiv">
										<label class="control-label col-md-3">历史数据:</label>
										<div class="col-md-7">
											<p class="form-control-static colorgreen historyshow"></p>
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
									<a href="javascript:;" class="btn green-turquoise button-next next-btn-margin-left">
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
		<div id="modalransomware" class="modal xmodal fade form-horizontal" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
			<div class="modal-header ">
				<button type="button" class="close modalclosebtn" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="fa fa-search"></i> 扫描文件</h4>
			</div>
			<div class="modal-body">
    			<div class="form-group">
			        <label class="col-md-3 control-label">扫描状态</label>
                	<div class="col-md-8 pt7">
                		<label id="scanstatus"></label>
                	</div>
                </div>
                <div class="form-group">
			        <label class="col-md-3 control-label">扫描统计</label>
                	<div class="col-md-8 pt7">
                		<label id="scanstatistics"></label>
                	</div>
                </div>
                <div class="form-group">
			        <label class="col-md-3 control-label">历史扫描列表</label>
                	<div class="col-md-8 pt7">
                		<div class="scanfilelisttext" id="historyfilelist" >
                                     
                   		</div><span class="help-block">
                		历史扫描记录列表 </span>
                	</div>
                </div>
                <div class="form-group">
			        <label class="col-md-3 control-label">当前选择列表</label>
                	<div class="col-md-8 pt7">
                		<div class="scanfilelisttext" id="currentfilelist" >
                                     
                   		</div><span class="help-block">
                		当前选择文件/文件夹列表 </span>
                	</div>
                	
                </div>
                <div class="form-group">
			        <label class="col-md-3 control-label"></label>
                	<div class="col-md-8">
                		<button type="button"  class="btn btn-sm green-haze" id="startscan">开始扫描</button>
                		<button type="button"  class="btn btn-sm btn-default" id="stopscan">停止扫描</button>
                		
                		<span class="help-block">
                		启动扫描会按照当前选择文件/文件夹列表进行文件类型扫描,如果扫描时间过长可以关闭本窗口,系统会在后台自动完成扫描,待扫描完成后即可查看扫描结果<br>
                		停止扫描会停止所有正在扫描的文件/文件夹 </span>
                	</div>
                </div>
			</div>
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default modalclosebtn">关 闭</button>
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
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script src="./scripts/fs/filecdp.js" type="text/javascript"></script>
