<?php include_once '../../../../tpl/permission.php'; ?>
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="portlet-body pding20" id="autobaklist">
	<div class="data_list-container">
		<div class="vin_toolbar" id="autoBackTableToolbar">
			<div class="leftTool">
				<div class="flex_center">
					<?php
					if (in_array("p_rc_autobak_list_download", $_SESSION['permissionArr'])) {
						echo '<button class="btn b-btn viconfont vicon-ge_download brr2 mr12" id="downloadFile"></button>';
					}
					if (in_array("p_rc_autobak_list_delete", $_SESSION['permissionArr'])) {
						echo '<button class="btn b-btn viconfont vicon-a-Deleteshanchu1 brr2 mr12" id="deletePoint"></button>';
					}
					?>
				</div>
				<div class="search input-group mr12">
					<input type="search" maxlength="128" class="searchInput searchFileName customSearch" autocomplete="off"
						style="padding-right:32px" maxlength="64" type="text"
						placeholder="<?php echo $LANG['UI_PLATFORM_SETTING_SEARCH_BY_FILENAME'] ?>">
					<div class="position0" style="width:auto;height:34px">
						<button class="b-btn auto_back_clear clear hide position0"><i
								class="icon-close-small"></i></button>
					</div>
					<div class="search-btn positionL0" style="width:auto;height:34px;"  id="search_auto_btn">
						<button class="b-btn search-btn"><i class="icon-search"></i></button>
					</div>
				</div>
				<div class="btn-group">
					<button type="button" id="refreshTable" class="dropdown-toggle btn-font btn-title btn-whitespace" style="width: auto;">
						<i class="viconfont vicon-biaogeshuaxin mr5"></i><?php echo $LANG['UI_PUBLIC_TOOLS_RELOAD'] ?>
					</button>
				</div>
			</div>
			<div class="rightTool">
				<div class="vin_autoBackTableToolbar">
				</div>
			</div>
		</div>
		<div class="table-container">
			<table id="bakDataTable"></table>
			<div class="alert alert-block alert-info fade in" id="marktips">
				<button type="button" class="close" data-dismiss="alert"></button>
				<ul class="alert-ul">
					<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
					<li>
						<?php echo $LANG['UI_BR_AUTOBAK_LIST_TIP'] ?>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="./scripts/platform/settings/settingstab/system_br_autobak_list.js"></script>