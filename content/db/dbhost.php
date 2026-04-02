<?php include_once '../../tpl/permission.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
		<!-- Begin: life time stats -->
		<div class="portlet box blue-hoki" id="hostcontent">
			<div class="portlet-title">
				<div class="caption">
					<i class="levelchild viconfont vicon-dbhost"></i>主机管理
				</div>
			</div>
			<div class="portlet-body mlr10">
				<div class="table-toolbar-wrapper">
					<div class="table-toolbar-wrapper__left">
						<div class="btn-group">
							<button type="button" id="add" class="btn table-toolbar-btn">
							<i class="viconfont vicon-ge_add_task"></i> <?php echo $LANG['UI_PUBLIC_ADD'] ?>
							</button>
						</div>
						<div class="btn-group">
							<button type="button" id="edit" class="btn table-toolbar-btn">
							<i class="viconfont vicon-ge_modify"></i> <?php echo $LANG['UI_PUBLIC_MODIFY'] ?>
							</button>
						</div>
						<div class="btn-group">
							<button type="button" id="delete" class="btn table-toolbar-btn">
							<i class="viconfont vicon-ge_delete"></i> <?php echo $LANG['UI_PUBLIC_DELETE'] ?>
							</button>
						</div>
						<div class="btn-group">
							<button type="button" id="download" class="btn table-toolbar-btn">
							<i class="viconfont vicon-ge_download"></i> 下载日志
							</button>
						</div>
					</div>
					<div class="table-toolbar-wrapper__right">
						<div class="table-actions-wrapper page-right" >
							<span>
							</span>
							<input type="search" maxlength="128" class="searchinput table-group-action-input form-control input-inline input" placeholder="按主机名或IP地址搜索" aria-controls="example">
							<input id="searchbtn" type="button" class="btn btn-search" value="<?php echo $LANG['UI_PUBLIC_SEARCH'] ?>">
						</div>
					</div>
				</div>
				
				<div class="table-container">
					<table class="table table-striped table-hover" id="datatable">
					<thead>
					<tr role="row" class="heading">
						<th width="3%">
							<input type="checkbox" class="group-checkable">
						</th>
						<th width="15%">
							 主机名
						</th>
						<th width="10%">
							<?php echo $LANG['UI_PUBLIC_IP_ADDRESS'] ?>
						</th>
						<th width="15%">
							操作系统
						</th>
						<th width="10%">
							类型
						</th>
						<th width="10%">
							授权
						</th>
						<th width="15%">
							添加时间
						</th>
						<th width="12%">
							状态
						</th>
						<th width="10%">
							用户
						</th>
					</tr>
					</thead>
					<tbody>
					</tbody>
					</table>
				</div>
			</div>
		</div>
		<!-- End: life time stats -->
		
		<!-- BEGIN MODAL -->
		<div id="modaldivadd" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="viconfont vicon-ge_add_task"></i> 添加主机</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label col-md-3">主机类型
					</label>
					<div class="col-md-8">
						<select class="form-control select2me" id="hosttype">
							<option value="1">生产主机</option>
							<option value="2">备份主机</option>
						</select>
						<div><span class="help-block ">
							请按用途选择您需要添加的主机:生产主机是运行数据库业务的主机,备份主机是作为备份使用的主机
						</span></div>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">IP地址/名称<span class="required"> * </span></label>
					<div class="col-md-8">
						<input type="text" class="form-control" maxlength="128" placeholder="192.168.1.110" id="hostip">
						<span class="help-block">
						请输入主机的IP地址或域名 </span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?php echo $LANG['UI_AGENT_ADD_AUTH'] ?></label>
					<div class="col-md-8">
						<div class="col-md-6 display-none" style="padding: 5px 0">
							<label class="checkbox-inline pl0"><input type="checkbox" class="icheck" id="file"> 
							<a><img src="../../img/platform/module-file.svg" alt=""></a> 文件实时同步 </label>
						</div>
						<div class="col-md-12" style="padding: 5px 0">
							<label class="checkbox-inline pl0"><input type="checkbox" class="icheck" id="database"> 
							<a><img src="../../img/platform/module-db.svg" alt=""></a> 数据库实时备份 </label>
						</div>
						<span class="help-block">
						对主机进行授权,授权后才能进行备份操作 </span>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
				<button type="button" class="btn btn-primary" id="addsubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
			</div>
		</div>
		<!-- END MODAL -->
		
		<!-- BEGIN MODAL -->
		<div id="modaldivedit" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="viconfont vicon-ge_modify"></i> 修改主机</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label col-md-3">主机类型
					</label>
					<div class="col-md-8">
						<input type="text" class="form-control display-none" maxlength="128" id="ehostuuid">
						<select class="form-control select2me" disabled="disabled" id="ehosttype">
							<option value="1">生产主机</option>
							<option value="2">备份主机</option>
						</select>
						<div><span class="help-block ">
							请按用途选择您需要添加的主机:生产主机是运行数据库业务的主机,备份主机是作为备份/同步使用的主机
						</span></div>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">IP地址/名称<span class="required"> * </span></label>
					<div class="col-md-8">
						<input type="text" class="form-control" maxlength="128" placeholder="192.168.1.110" id="ehostip">
						<span class="help-block">
						请输入主机的IP地址或域名 </span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label"><?php echo $LANG['UI_AGENT_ADD_AUTH'] ?></label>
					<div class="col-md-8">
						<div class="col-md-6 display-none" style="padding: 5px 0">
							<label class="checkbox-inline pl0"><input type="checkbox" class="icheck" id="efile"> 
							<a><i class="fa fa-file"></i></a> 文件实时同步 </label>
						</div>
						<div class="col-md-12" style="padding: 5px 0">
							<label class="checkbox-inline pl0"><input type="checkbox" class="icheck" id="edatabase"> 
							<a><i class="fa fa-database"></i></a> 数据库实时备份 </label>
						</div>
						<span class="help-block">
						<?php echo $LANG['UI_AGENT_ADD_AUTH_TIPS'] ?> </span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">主机名<span class="required"> * </span></label>
					<div class="col-md-8">
						<input type="text" class="form-control" maxlength="128" placeholder="主机名称" id="ehostname">
						<span class="help-block">
						可以自定义主机名称 </span>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
				<button type="button" class="btn btn-primary" id="editsubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
			</div>
		</div>
		<!-- END MODAL -->
		
	</div>
</div>
<!-- END PAGE CONTENT-->
	

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/db/dbhost.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	