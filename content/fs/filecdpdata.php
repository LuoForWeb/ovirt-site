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
<!--<h3 class="page-title">-->
<!--文件实时同步数据 <small> 查看实时同步的文件详情</small>-->
<!--</h3>-->
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
		<!-- Begin: life time stats -->
		<div class="portlet box blue-hoki">
			<div class="portlet-title">
				<div class="caption">
					<i class=" levelchild viconfont vicon-filecdpdata"></i> 文件实时同步数据
				</div>
			</div>
			<div class="portlet-body mlr10">
			     <div class="row">
			         <div class="col-md-4">
			             <div class="portlet">
			                <div class="portlet-title">
                				<div class="caption">
                					<i class="viconfont vicon-ge_backup_host"></i>备份主机和文件
                				</div>
                			</div>
                			<div class="portlet-body">
                				<div id="nodeCheck" style="margin-top:10px;margin-bottom:10px;">
                                    <div class="input-icon">
                    				    <input type="text" maxlength="128" placeholder="按主机,任务名搜索..." 
                    				    style="padding-left: 13px;" class="form-control " id="searchfs"/>
                    		        </div>	                   	
                                </div>
                                
                			    <div class="vcenter-tree" >
									<div class="alert alert-block alert-info mt10 fade in display-hide" id="nopointtips">
										<button type="button" class="close" data-dismiss="alert"></button>
										<ul class="alert-ul">
											<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS']?>:</strong>
											<li>
												<?php echo $LANG['UI_DATA_NODATA_TIPS']?>
											</li>
										</ul>
									</div>
        							<ul id="cdpfstree" style="border: 1px solid #e5e5e5;" class="ztree"></ul>
								</div>
                			</div>
			             </div>
			         </div>
			         <div class="col-md-8">
			             <div class="portlet ">
			                <div class="portlet-title">
                				<div class="caption">
                					<i class="viconfont vicon-ge_backup_file"></i>同步文件详情
                					<span style="margin-left: 10px;color:#4ad1cd;font-size:14px;" id="fsdataUrl"></span>
                				</div>
                			</div>
							<div class="alert alert-block alert-info fade in" id="tabletips">
								<button type="button" class="close" data-dismiss="alert"></button>
								<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS']?></strong></h4>
								<ol class = "alert-ol">
									<li>
										展开左边的主机和目录树
									</li>
									<li>
										选择要查看的文件
									</li>
								</ol>
							</div>
                			<div class="portlet-body display-none" id="fileinfodiv">
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
    											 任务名称:
    										</div>
    										<div class="col-md-10 value" id="taskName" style="word-break:break-word;">
    										</div>
    									</div>
    									<div class="row static-info" style="margin: 10px 0;">
    										<div class="col-md-2 name">
    											 文件名称:
    										</div>
    										<div class="col-md-10 value" id="fileName" style="word-break:break-word;">
    										</div>
    									</div>
    									<div class="row static-info" style="margin: 10px 0;">
    										<div class="col-md-2 name">
    											 文件大小:
    										</div>
    										<div class="col-md-10 value" id="fileSize" style="word-break:break-word;">
    										</div>
    									</div>
    									<div class="row static-info" style="margin: 10px 0;">
    										<div class="col-md-2 name">
    											 修改时间:
    										</div>
    										<div class="col-md-10 value" id="modifyTime" style="word-break:break-word;">
    										</div>
    									</div>
    									<div class="row static-info" id="fullPathDiv" style="margin: 10px 0;">
    										<div class="col-md-2 name">
    											 全路径:
    										</div>
    										<div class="col-md-10 value" id="fullPath" style="word-break:break-word;">
    										</div>
    									</div>
    									<div class="row static-info" style="margin: 10px 0;">
    										<div class="col-md-2 name">
    											 下载文件:
    										</div>
    										<div class="col-md-3" style="padding-top: 5px">
                								<div class="btn-group">
                									<button type="button" id="downloadFile" class="btn btn-sm green-haze ">
                										<i class="viconfont vicon-ge_download"></i>下载
                									</button>
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
<script type="text/javascript" src="./scripts/fs/filecdpdata.js"></script>

<!-- END PAGE LEVEL PLUGINS -->	