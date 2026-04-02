<?php include_once '../../../../tpl/permission.php';?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link href="./assets/global/plugins/jquery-file-upload/css/jquery.fileupload.css" rel="stylesheet"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/web-uploader/webuploader.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/timeline.css" />
<!-- BEGIN PAGE HEADER-->
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="setting_manager" href="./content/platform/settings/setting_manager.php" >
            <span><?php echo $LANG['UI_PLATFORM_SYSTEM_SET']?></span>
        </a>
    </li>
    <span>></span>
    <li>
        <a class="ajaxify" name="setting_manager" href="./content/platform/settings/settingstab/system_upgrade.php" >
            <span><?php echo $LANG['UI_PLATFORM_SYSTEM_UPDATE']?></span>
        </a>
    </li>
    <span>></span>
    <span class="curent"><?php echo $LANG['WEB_SETTINGS_UPDATE_ONLINE']?></span>
</h3>


<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
    <div class="col-md-12">
      <div class="portlet box blue-hoki" id='DownloadMangerDiv'>
		<div class="portlet-title">
			<div class="caption">
				<i class="iconfont icon-zuhu"></i><?php echo $LANG['WEB_SETTINGS_UPDATE_ONLINE']?>
			</div>
			
		</div>

		<div class="portlet-body">
			<div class="table-container">
				<table class="table table-striped table-bordered table-hover lh2" id="OnlineTable">
					<thead>
						<tr role="row" class="heading">
							<th width="5%">
								<input type="checkbox" class="group-checkable">
							</th>
							<th width="35%">
							   	<?php echo $LANG['UI_SETTINGS_UPDATE_PATCH_NAME']?>
							</th>
							<th width="15%">
								<?php echo $LANG['UI_SETTINGS_UPDATE_PATCH_SIZE']?>
							</th>
							<th width="20%">
								<?php echo $LANG['WEB_SETTINGS_UPDATE_PUT_TIME']?>
							</th>
							<th width="10%">
								<?php echo $LANG['UI_PUBLIC_STATUS']?>
							</th>
							<th width="10%">
								<?php echo $LANG['UI_PUBLIC_DETAIL']?>
							</th>
						</tr>
					</thead>
					<tbody>
					
					</tbody>
				</table>
				
				<div class="alert alert-block alert-info fade in"  id="marktips">
					<button type="button" class="close" data-dismiss="alert"></button>
					<ul class="alert-ul">
						<strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS']?>:</strong>
						<li>
							<?php echo $LANG['UI_SETTINGS_UPDATE_MANAGE_TIPS']?>
						</li>
					</ul>
				</div>
			</div>
		
		</div>
       </div> 
    </div>
    </div>

<!-- BEGIN MODAL -->
<div id="detailsModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
	<div class="modal-header ">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
		<h4 class="modal-title"><i class="glyphicon glyphicon-circle-arrow-up"></i> <?php echo $LANG['WEB_SETTINGS_UPDATE_DETAILS']?>
				</h4>
			</div>
			<div class="modal-body" style="max-height:700px;">
    			<div class="portlet-body">
                    <ul class="timeLine" style="width: 1200px;">
                    </ul>
                   
                </div>
			</div>
			
			<!-- modal-footer -->
	<div class="modal-footer">
		<button type="button"  data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_CLOSE']?></button>
	</div>
</div>	
<!-- END MODAL -->
<!-- BEGIN MODAL -->
<div id="hintModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
	<div class="modal-header ">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
		<h4 class="modal-title"><i class="glyphicon glyphicon-circle-arrow-up"></i> <?php echo $LANG['UI_PUBLIC_TIPS']?>
				</h4>
			</div>
			<div class="modal-body">
    			<div class="portlet-body">
    				<div class="row list-option">
    					<div id="hintContent" class="col-md-12" style="text-align: center;">
    					</div>
    				</div>
    				
				</div>
			</div>
			
			<!-- modal-footer -->
	<div class="modal-footer">
		<button type="button"  data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_CLOSE']?></button>
	</div>
</div>	
<!-- END MODAL -->
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<?php 
if($_SESSION['language'] != "en-us"){
    //如果不是英文,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' . 
         $_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script src="./scripts/libs/base64.js" type="text/javascript"></script>
<!-- END PAGE LEVEL PLUGINS -->	
<script src="./assets/global/plugins/web-uploader/webuploader.min.js"></script>
<script src="./assets/global/plugins/jquery-file-upload/js/vendor/jquery.ui.widget.js"></script>
<script src="./assets/global/plugins/jquery-file-upload/js/jquery.fileupload.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>

<script type="text/javascript" src="./scripts/platform/settings/settingstab/checkupdate.js"></script>