<?php include_once '../../../tpl/permission.php'; ?>
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
	<li>
		<a class="ajaxify" name="infrastructure" href="./content/platform/resource/infrastructure.php">
			<span><?php echo $LANG['UI_PLATFORM_INFRASTRUCTURE'] ?></span>
		</a>
	</li>
	<span>
		>
	</span>
	<span class="curent"><?php echo 'private' == $_GET['cloudType'] ? $LANG['UI_PLATFORM_VM_PRIVATE_CLOUD_PLATFORM'] : $LANG['UI_PLATFORM_VM_CLOUD_PLATFORM'] ?></span>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="resource-manager-wrap" id="cloud-platform-content">
	<!-- 类型：public/private -->
	<input id="cloudType" value="<?php echo $_GET['cloudType']; ?>" class="display-none">
	<!-- Begin: life time stats -->
	<div class="resource-manager-wrap__header">
		<span>
			<i class="levelchild viconfont <?php echo 'private' == $_GET['cloudType'] ? 'vicon-cloud_platform' : 'vicon-yunpingtai'; ?>"></i>
			<span class="resource-manager-wrap__header__text">
				<?php echo 'private' == $_GET['cloudType'] ? $LANG['UI_PLATFORM_VM_PRIVATE_CLOUD_PLATFORM'] : $LANG['UI_PLATFORM_VM_CLOUD_PLATFORM'] ?>
			</span>
		</span>
	</div>
	<div class="resource-manager-wrap__content">
		<div class="table-toolbar-wrapper">
			<div class="table-toolbar-wrapper__left">
				<?php
				if (in_array("p_cloud_platform_manager_delete", $_SESSION['permissionArr'])) {
					// 删除
					echo '<div style="cursor: not-allowed">
							<button type="button" id="delete" class="btn viconfont vicon-a-Deleteshanchu1 b-btn brr2 mr12 exch-forbid-event">
							</button>
						</div>';
				}
				?>
				<div class="search input-group mr12">
					<input type="search" maxlength="128" id="searchInput" class="searchinput customSearch"
							autocomplete="off"
							style="padding-right:32px;min-width: 220px;"
							placeholder="<?php echo $LANG['UI_SEARCH_CLOUD_PLATFORM_NICKNAME'] ?>">
					<div class="position0" style="width:auto;height:34px">
						<button class="b-btn clear hide position0" id="clearSearchBtn">
							<i class="icon-close-small"></i></button>
					</div>
					<div class="search-btn positionL0" style="width:auto;height:34px;">
						<button class="b-btn search-btn" id="searchbtn"><i class="icon-search"></i></button>
					</div>
				</div>
				<?php
				if (in_array("p_cloud_platform_manager_add", $_SESSION['permissionArr'])) {
					// 新建
					echo '<div class="btn-group">
								<button type="button" id="add" class="btn table-toolbar-btn">
									<i class="viconfont vicon-biaogetianjia"></i> ' . $LANG['UI_PUBLIC_ADDNEW'] . '
								</button>
							</div>';
				}
				if (in_array("p_cloud_platform_manager_edit", $_SESSION['permissionArr'])) {
					// 修改
					echo '<div class="btn-group">
								<button type="button" id="edit" class="btn table-toolbar-btn">
									<i class="viconfont vicon-xiugai"></i>' . $LANG['UI_PUBLIC_MODIFY'] . '
								</button>
							</div>';
				}
				if (in_array("p_cloud_platform_manager_refresh", $_SESSION['permissionArr'])) {
					// 自动刷新
					echo '<div class="btn-group">
								<button type="button" id="refreshInterval" class="btn table-toolbar-btn">
									<i class="viconfont vicon-ge_refresh" style="font-weight: bold"></i> ' . $LANG['UI_CLOUD_PLATFORM_SET_AUTO_REFRESH'] . '
								</button>
							</div>';
				}

				?>


			</div>
			<div class="table-toolbar-wrapper__right">
				<div class="table-actions-wrapper page-right" >
						<span>
						</span>
<!--							<input id="searchInput" type="search" maxlength="128" class="searchinput table-group-action-input form-control input-inline input" placeholder="--><?php //echo $LANG['UI_SEARCH_CLOUD_PLATFORM_NICKNAME'] ?><!--" aria-controls="example">-->
<!--							<input id="searchbtn" type="button" class="btn btn-search" value="--><?php //echo $LANG['UI_PUBLIC_SEARCH'] ?><!--">-->
					<button type="button" id="searchAll" class="btn btn-primary adv_btn brr2 p-lr8 list">
						<i class="adv-search"></i>
						<span style="color: #FFFFFF"><?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?></span>
					</button>
				</div>
			</div>

<!--					<div class="row">-->
<!--						<div class="col-md-12" >-->
<!--							<div id="searchDiv" class="searchDiv display-none">--><?php //echo $LANG['UI_PUBLIC_SEARCH_CONDITION'] ?><!-- <span style="color:#5b9bd1;" class="searchContent"></span><span class="clearSearch">--><?php //echo $LANG['UI_SEARCH_CLEAR'] ?><!--</span> </div>-->
<!--						</div>-->
<!--					</div>-->
		</div>

		<div id="searchDiv" class="search-content searchDiv display-none">
			<?php echo $LANG['UI_PUBLIC_SEARCH_CONDITION'] ?> 
			<span style="color:#5b9bd1;" class="searchContent"></span>
			<span class="clearSearch"><?php echo $LANG['UI_SEARCH_CLEAR'] ?></span> 
		</div>

		<div class="table-container vcenter-manager-table-container">
			<table id="datatable" class="table-hover">
			</table>
		</div>

		<div class="alert alert-block alert-info fade in h-50px m0" id="cloud_platform_table_tip">
			<button type="button" class="close" data-dismiss="alert" id="cloud_platform_table_tip_close"></button>
			<ul class="alert-ul">
				<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
				<li>
					<?php echo 'private' == $_GET['cloudType'] ? $LANG['UI_CLOUD_PLATFORM_TABLE_TIP1'] : $LANG['UI_CLOUD_PLATFORM_TABLE_TIP2'] ?>
				</li>
			</ul>
		</div>
	</div>
</div>
<!-- End: life time stats -->
<!-- END PAGE CONTENT-->

<!-- BEGIN MODAL -->
<div id="modalauthhost" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
	<input id="vcenteruuid" class="display-none"></input>
	<div class="modal-header ">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
		<h4 class="modal-title"><i class="icon-badge"></i> <?php echo $LANG['UI_CLOUD_PLATFORM_AUTH_HOST'] ?></h4>
	</div>
	<div class="modal-body">
		<div class="portlet-body">
			<div class="table-toolbar">
				<div class="row">
					<div class="col-md-12">
						<div class="btn-group">
							<button type="button" id="deletelisence" class="btn btn-sm green-haze">
								<i class="glyphicon glyphicon-remove"></i> <?php echo $LANG['UI_CLOUD_PLATFORM_AUTH_DELETE'] ?>
							</button>
						</div>
						<div class="btn-group">
							<button type="button" id="addlisence" class="btn btn-sm green-haze">
							<i class="viconfont vicon-ge_authorization2"></i> <?php echo $LANG['UI_CLOUD_PLATFORM_AUTH_ADD'] ?>
							</button>
						</div>

						<div class="btn-group" id="vmlisenceinfo" style="color: #ff5714;margin-left: 50px;">
						</div>
					</div>
				</div>
			</div>
			<div class="table-container">
				<table class="mt15" id="hostdatatable">
				</table>

				<?php if ('private' == $_GET['cloudType']) { ?>
																				<div class="alert alert-block  alert-info fade in" id="marktips">
																					<button type="button" class="close" data-dismiss="alert"></button>
																					<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
																					<ol class="alert-ol">
																						<li><?php echo $LANG['UI_CLOUD_PLATFORM_HOST_AUTH_TIPS1'] ?></li>
																						<li><?php echo $LANG['UI_CLOUD_PLATFORM_HOST_AUTH_TIPS2'] ?></li>
																					</ol>
																				</div>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
<!-- END MODAL -->

<!-- BEGIN SEARCH MODAL -->
<div id="searchmodal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
	<div class="modal-header ">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
		<h4 class="modal-title"><i class="fa fa-search"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?>
		</h4>
	</div>
	<div class="modal-body">
		<div class="portlet-body" style="height: auto">
            <div class="list-option">
                <div class="row">
                    <!-- 虚拟化类型 -->
                    <label class="control-label col-md-4"><?php echo $LANG['UI_CLOUD_PLATFORM_TYPE'] ?> :
                    </label>
                    <div class="col-md-8">
                        <select class="form-control select2me" id="vmtype" name="vmtype" style="width:235px; height:34px;">
                        </select>
                    </div>
                </div>
            </div>
			<div class="list-option">
				<div class="row">
					<!-- 虚拟化中心 -->
					<label class="control-label col-md-4"><?php echo 'private' == $_GET['cloudType'] ? $LANG['UI_PUBLIC_IP_ADDRESS_OR_DOMAIN'] : $LANG['UI_PUBLIC_ACCOUNT'] ?> :
					</label>
					<div class="col-md-8">
						<input style="width:235px; height:34px;" id="vcenterIp" type="text" maxlength="64" class="table-group-action-input form-control input-inline input input-sm" aria-controls="example">
					</div>
				</div>
			</div>
			<div class="list-option">
				<div class="row">
					<!-- 别名 -->
					<label class="control-label col-md-4"><?php echo $LANG['UI_CLOUD_PLATFORM_RNAME'] ?> :
					</label>
					<div class="col-md-8">
						<input style="width:235px; height:34px;" id="nickName" type="text" maxlength="64" class="table-group-action-input form-control input-inline input input-sm" aria-controls="example">
					</div>
				</div>
			</div>
			<div class="list-option">
				<div class="row">
					<!-- 创建用户名 -->
					<label class="control-label col-md-4"><?php echo $LANG['UI_CLOUD_PLATFORM_ADD_USER'] ?> :
					</label>
					<div class="col-md-8">
						<input style="width:235px; height:34px;" id="userName" type="text" maxlength="64" class="table-group-action-input form-control input-inline input input-sm" aria-controls="example">
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- modal-footer -->
	<div class="modal-footer">
		<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
		<button type="button"class="btn btn-primary" id="serach_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
	</div>
</div>
<!-- END SEARCH MODAL -->
	
<!-- start refresh modal -->
<div id="refreshModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
	<div class="modal-header ">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
		<h4 class="modal-title"><i class="viconfont vicon-ge_refresh" style="margin-right: 8px;"></i><?php echo $LANG['UI_CLOUD_PLATFORM_REFRESH_SET']; ?>
		</h4>
	</div>
	<div class="modal-body">
		<div class="portlet-body">
			<div class="list-option">
				<div class="row">
					<!-- 虚拟化中心 -->
					<label class="control-label col-md-5"><?php echo $LANG['UI_CLOUD_PLATFORM_REFRESH_DESCRIPTION']; ?>
					</label>
					<div class="col-md-5">
						<div class="input-group spinner-group">
							<input type="text" id="refreshValue" style="text-align: center;" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control" maxlength="2" onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}" onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'0')}else{this.value=this.value.replace(/\D/g,'')}">
							<div class="spinner-buttons input-group-btn spinner-group-btn">
								<button type="button" class="btn spinner-up default input-sm">
									<i class="fa fa-angle-up"></i>
								</button>
								<button type="button" class="btn spinner-down default input-sm">
									<i class="fa fa-angle-down"></i>
								</button>
							</div>
						</div>
					</div>
					<label class="control-label col-md-2 left-padding"><?php echo $LANG['WEB_UTILS_MINUTE'] ?>
					</label>
				</div>



				</div>
			</div>
				<div class="list-option">
					<div class="row">
						<div class="col-md-12">
						<div class="alert alert-block alert-info fade in" id="marktips">
							<button type="button" class="close" data-dismiss="alert"></button>
							<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
							<ol class="alert-ol">
								<li>
									<?php echo $LANG['UI_CLOUD_PLATFORM_REFRESH_SET_HITE1']; ?>
								</li>
								<li>
									<?php echo $LANG['UI_CLOUD_PLATFORM_REFRESH_SET_HITE2']; ?>
								</li>
							</ol>
						</div>
					</div>
					</div>
			</div>
	</div>

	<!-- modal-footer -->
	<div class="modal-footer">
		<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
		<button type="button"class="btn btn-primary" id="refreshTime"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
	</div>
</div>
<!-- end refresh modal -->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/vm/cloudplatform/cloud_platform_manager.js"></script>
<!-- END PAGE LEVEL PLUGINS -->