<?php include_once '../../../tpl/permission.php'; ?>
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>
<style>
    #objectList .panel-body .row{
        margin: 0 0 12px 0;
    }
</style>
<!-- BEGIN PAGE HEADER-->
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
	  <div class="portlet box blue-hoki" id='appGroupContent'>
		<div class="portlet-title">
			<div class="caption">
				<i class="levelchild viconfont vicon-yingyong"></i><?php echo $LANG['UI_VERIFY_APP_GROUP_LIST']; ?>
			</div>

		</div>

		<div class="portlet-body margin10">
            <div class="table-container">
                <div class="vin_toolbar" id="vin_appgroup_toolbar">
                    <div class="leftTool">
                    </div>

                    <div class="rightTool">
                        <div class="vin_btnToolbar">
                        </div>
                    </div>
                </div>
                <table id="appgroup_table">
                </table>
                <div class="alert alert-block alert-info fade in" id="marktips">
                    <button type="button" class="close" data-dismiss="alert"></button>
                    <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS']; ?></strong></h4>
                    <ol class = "alert-ol">
                        <li>
                            <?php echo $LANG['UI_VERIFY_APP_GROUP_LIST_TIPS']; ?>
                        </li>
                    </ol>
                </div>
            </div>

		</div>
	   </div>
	</div>

    <!-- BEGIN DETAIL DRAWER -->
    <div class="drawer slide" data-placement="right" tabindex="-1" role="dialog"
         aria-labelledby="drawer-1-title" aria-hidden="true" id="appgroup_detail_drawer" style="width: 800px;">
        <div class="drawer-content drawer-content-scrollable" role="document">
            <div class="drawer-header">
                <h4 class="drawer-title">
                   <i class="viconfont vicon-yingyong"></i>
                   <span id="appgroupname">
                    </span>
                    <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i
                                class="viconfont vicon-guanbi"></i></span>
                </h4>
            </div>
            <div class="drawer-body">
                <div class="row static-info">
                    <div class="col-md-12 value" >
                        <ul class="feeds pd10 accordion" id="objectList">
                        </ul>
                    </div>
                </div>
            </div>
            <!-- footer -->
            <div class="drawer-footer">
                <div class="drawer-footer-div">
                    <a href="javascript:;" class="btn default mr10 floatr" data-dismiss="drawer" aria-label="Close" >
                        <?php echo $LANG['UI_PUBLIC_OFF_TWO'] ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- END ADD DRAWER -->

</div>

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/platform/dataverification/appgroup.js"></script>
<!-- END PAGE LEVEL PLUGINS -->