<?php include_once '../../tpl/permission.php';?>
<!-- BEGIN PAGE LEVEL STYLES -->

<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<!-- END PAGE LEVEL STYLES -->
<style type="text/css">
.table-scrollable{border: 0;}
.dataTables_scrollBody {
    border-bottom: 0 solid #ddd !important;
}
</style>
<!-- BEGIN PAGE HEADER-->
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
		<!-- Begin: life time stats -->
		<div class="portlet box blue-hoki">
			<div class="portlet-title">
				<div class="caption">
					<i class="levelchild viconfont vicon-db_protect"></i> 数据库备份数据
				</div>
			</div>
			<div class="portlet-body mlr10">
			     <div class="row">
			         <div class="col-md-4">
			             <div class="portlet" id="">
			                <div class="portlet-title">
                				<div class="caption">
                					<i class="viconfont vicon-ge_backup_host"></i>备份主机和数据库
                				</div>
                			</div>
                			<div class="portlet-body">
                				<div id="nodeCheck" style="margin-top:10px;margin-bottom:10px;">
                                    <div class="input-icon">
                    				    <input type="text" maxlength="128" placeholder="按主机,实例,数据库名称搜索..." 
                    				    style="padding-left: 13px;" class="form-control " id="searchdb"/>
                    		        </div>	                   	
                                </div>
                                
                			    <div class="vcenter-tree" >
        							<div class="alert alert-block alert-info  mt10 fade in display-hide"  id="nopointtips">
        								<button type="button" class="close" data-dismiss="alert"></button>
										<ul class="alert-ul">
                                            <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS']?>:</strong>
                                            <li>
                                               <?php echo $LANG['UI_DATA_NODATA_TIPS']?>
                                            </li>
                                        </ul>
        							</div>
        							<ul id="cdpdbtree" style="border: 1px solid #e5e5e5;" class="ztree"></ul>
								</div>
                			</div>
			             </div>
			         </div>
			         <div class="col-md-8">
			             <div class="portlet ">
			                <div class="portlet-title">
                				<div class="caption">
                					<i class="viconfont vicon-ge_backup_database"></i>备份数据库
                					<span style="margin-left: 10px;color:#4ad1cd;font-size:14px;" id="fsdataUrl"></span>
                				</div>
                			</div>
                			<div class="alert alert-block alert-info fade in"  id="tabletips">
								<button type="button" class="close" data-dismiss="alert"></button>
								<h4 class="alert-heading"><strong>提示</strong></h4>
								<ol class = "alert-ol">
									<li>
									展开左边的主机和数据库树
									</li>
									<li>
									选择要查看的数据库
									</li>
								</ol>
							</div>
                			<div class="portlet-body display-none" id="databaseinfodiv">
                			    <div class="portlet " style="border: 1px solid #e5e5e5;">
                			            <div class="row static-info" style="margin: 10px 0;">
    										<div class="col-md-2 name">
    											 备份主机:
    										</div>
    										<div class="col-md-10 value" id="standbyhostName" style="word-break:break-word;">
    										</div>
    									</div>
                			            <div class="row static-info" style="margin: 10px 0;">
    										<div class="col-md-2 name">
    											 生产主机:
    										</div>
    										<div class="col-md-10 value" id="producthostName" style="word-break:break-word;">
    										</div>
    									</div>
										<div class="row static-info" style="margin: 10px 0;">
    										<div class="col-md-2 name">
    											 备份数据:
    										</div>
    										<div class="col-md-2 value" id="backupsize" style="word-break:break-word;">
    										</div>
    										<div class="col-md-2 name">
    											 历史数据:
    										</div>
    										<div class="col-md-2 value" id="hissize" style="word-break:break-word;">
    										</div>
    										<div class="col-md-2 name">
    											 接管数据:
    										</div>
    										<div class="col-md-2 value" id="takeoversize" style="word-break:break-word;">
    										</div>
    									</div>
    									
    									<div class="row static-info" style="margin: 10px 0;">
    										<div class="col-md-2 name">
    											 可用时间段:
    										</div>
    										<div class="col-md-7 value" style="word-break:break-word;">
    											<select class="form-control select2me" name="timeperiod"  id="timeperiod">
												</select>
    										</div>
    									</div>
    									<div class="row static-info" style="margin: 10px 0;">
    										<div class="col-md-2 name">
    											 时间点详情:
    										</div>
    										<div class="col-md-4">
                                				<div class="input-group date form_datetime">
                                					<input type="text" size="16"  name="timeinput" class="form-control">
                                					<span class="input-group-btn">
                                					<button class="btn default date-set" type="button"><i class="viconfont vicon-ge_calendar"></i></button>
                                					</span>
                                				</div>
                                				<div><span class="help-block ">
                                					请填写或选择时间点后扫描详情
                                				</span></div>
                                			</div>
                                			<div class="col-md-3" style="padding-top: 5px">
                								<div class="btn-group">
                									<button type="button" id="scantimepoint" class="btn btn-sm green-haze">扫描时间点详情</button>
                								</div>
                							</div>
    									</div>
                			    </div>
                			    
                			    <div class="table-container display-none" id="cdptimepointdiv">
                					<table class="table table-striped table-bordered table-hover" id="timepoint">
                					<thead>
                					<tr role="row" class="heading">
                						<th width="10%">
                							编号
                						</th>
                						<th width="90%">
                							时间点
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
		<!-- End: life time stats -->
	</div>
</div>
<!-- END PAGE CONTENT-->
	

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/db/dbdata.js"></script>

<!-- END PAGE LEVEL PLUGINS -->	