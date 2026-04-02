<?php include_once '../../tpl/permission.php';?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<style type="text/css">
.table-scrollable{overflow: auto;max-height: 258px; }
</style>
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
		<div class="portlet box blue-hoki" id="recovercontent">
			<div class="portlet-title">
				<div class="caption">
					<i class="levelchild viconfont vicon-dbdataRecovery"></i><?php echo $LANG['UI_BACKUP_NEW_TASK']?>
				</div>
			</div>
			<div class="portlet-body form">
				<form action="#" id="dbrecoveryform" class="form-horizontal">
				    <div class="form-body min-height480">
						<div class="form-group pt50">
							<label class="control-label col-md-3"> 选择恢复源<span
								class="required"> * </span>
							</label>
							<div class="col-md-4 ">
								<div>
									<select class="form-control select2me" name="standbyhost" id="standbyhost">
									</select>
									<div>
										<span class="help-block "> 选择备份数据所在的主机 </span>
									</div>
								</div>
							</div>
						</div>
						
						<div class="form-group display-none sinstancediv">
							<label class="control-label col-md-3"> 选择备份源<span
								class="required"> * </span>
							</label>
							<div class="col-md-4 ">
								<div>
									<select class="form-control select2me" name="producthost" id="producthost">
									</select>
									<div>
										<span class="help-block "> 选择从哪台生产主机备份的数据 </span>
									</div>
								</div>
							</div>
						</div>
						
						<div class="form-group display-none sinstancediv">
							<label class="control-label col-md-3"> 选择数据库实例<span
								class="required"> * </span>
							</label>
							<div class="col-md-4 ">
								<div>
									<select class="form-control select2me" name="dbinstance" id="dbinstance">
									</select>
									<div>
										<span class="help-block "> 选择要恢复的数据库实例 </span>
									</div>
								</div>
							</div>
						</div>
						
						<div class="form-group display-none sdatabasediv">
							<label class="control-label col-md-3"> 选择数据库<span
								class="required"> * </span>
							</label>
							<div class="col-md-4 ">
								<div>
									<select class="form-control select2me" name="dbselect"  id="dbselect">
									</select>
									<div>
										<span class="help-block "> 选择需要恢复的数据库</span>
									</div>
								</div>
							</div>
						</div>
						
						<div class="form-group display-none stimeperioddiv">
							<label class="control-label col-md-3"> 选择时间段<span
								class="required"> * </span>
							</label>
							<div class="col-md-4 ">
								<div>
									<select class="form-control select2me" name="timeperiod"  id="timeperiod">
									</select>
									<div>
										<span class="help-block "> 选择需要恢复的时间段</span>
									</div>
								</div>
							</div>
						</div>
						
						<div class="form-group display-none stimepointdiv">
                			<label class="control-label col-md-3">选择恢复点 <span 
                			class="required"> * </span>
                			</label>
                			<div class="col-md-4">
                				<div class="input-group date form_datetime">
                					<input type="text" size="16"  name="timeinput" class="form-control">
                					<span class="input-group-btn">
                					<button class="btn default date-set" type="button"><i class="viconfont vicon-ge_calendar"></i></button>
                					</span>
                				</div>
                				<div><span class="help-block ">
                					<?php echo $LANG['UI_SETTINGS_TIME_SET_TIP']?>
                				</span></div>
                			</div>
                			<div class="col-md-3" style="padding-top: 5px">
								<div class="btn-group">
									<button type="button" id="scantimepoint" class="btn btn-sm green-haze">扫描时间点详情</button>
								</div>
							</div>
                		</div>
                		
                		
                		<div class="form-group display-none scanshowdiv">
    							<label class="control-label col-md-3">时间点信息<span class="required">
    							* </span>
    							</label>
    							<div class="col-md-4">
    								<div class="table-container">
                    					<table class="table table-striped table-bordered table-hover" 
                    					id="timepoint" >
                    					<thead>
                    					<tr role="row" class="heading">
                    						<th width="10%">
                    							<input type="checkbox" class="group-checkable disabled" disabled >
                    						</th>
                    						<th width="20%">
                    							 编号
                    						</th>
                    						<th width="60%">
                    							 时间点
                    						</th>
                    					</tr>
                    					</thead>
                    					<tbody>
                    					</tbody>
                    					</table>
                    				</div>
                    				<div><span class="help-block ">
                    					请选择一个时间点进行恢复
                    				</span></div>
    							</div>
    						</div>
                		
                		<div class="form-group  display-none scanshowdiv">
							<label class="control-label col-md-3"> 恢复目标<span
								class="required"> * </span>
							</label>
							<div class="col-md-4 ">
								<div>
									<select class="form-control select2me" name="recoveryhost"  id="recoveryhost">
									</select>
									<div>
										<span class="help-block "> 选择主机作为恢复目的地 </span>
									</div>
								</div>
							</div>
						</div>
						
						<div class="form-group display-none attachdbdiv">
                			<label class="control-label col-md-3">附加数据库
                			</label>
                			<div class="col-md-4">
                				<input type="checkbox" id="attachdb"  class="make-switch" data-on-color="primary" data-off-color="info" 
                				data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>" 
                				data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
                				<div><span class="help-block ">
                					恢复完成后自动将数据库文件附加到数据库
                				</span></div>
                			</div>
                		</div>
						
						<div class="form-group  display-none dinstancediv">
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
												style="padding-right: 20px; margin-bottom: 0;"><input
													type="checkbox" id=""
													data-checkbox="icheckbox_square-blue" data-mode="1"
													class="icheck">实例名称</label></td>
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
								<span class="help-block ">请填写连接数据库实例的用户名和密码,完成后测试连接</span>
							</div>
							<div class="col-md-offset-3 col-md-3">
								<div class="btn-group">
									<button type="button" id="testconnect" class="btn btn-sm green-haze">测试连接</button>
								</div>
							</div>
						</div>
						
						<div class="form-group display-none" id="jobNamediv">
							<label class="control-label col-md-3"><?php echo $LANG['UI_JOB_RNAME']?><span class="required">
							* </span>
							</label>
							<div class="col-md-4">
								<div class="input-icon right ">
									<i class="fa"></i>
									<input type="text" maxlength="128" class="form-control" name="jobname" id="jobname"/>
									<div><span class="help-block "><?php echo $LANG['UI_JOB_RNAME_TIPS']?></span></div>
								</div>
							</div>
						</div>
						
					</div>
					<div class="form-actions pt50">
						<div class="row">
							<div class="col-md-offset-3 col-md-4">
								<button type="button" id="cancelbtn" class="btn default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
								<button type="button" disabled id="submitbtn" class="btn green-haze"><?php echo $LANG['UI_PUBLIC_YES']?></button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<?php 
if($_SESSION['language'] != "en-us"){
    //如果不是英文,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' . 
         $_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script src="./scripts/db/dbrecovery.js" type="text/javascript"></script>