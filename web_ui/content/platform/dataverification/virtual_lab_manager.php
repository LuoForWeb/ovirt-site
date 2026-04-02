<?php include_once '../../../tpl/permission.php'; ?>
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>
<link href="./assets/global/plugins/datatables/extensions/Buttons/css/buttons.dataTables.css" rel="stylesheet" type="text/css"/>
<style>
    /*数据验证-虚拟演练室详情里的表格默认高度调整*/
    .tableDiv .fixed-table-body{
        height: revert !important;
    }
    .basicDiv{
        background: #FAFAFA;
        padding: 20px 16px;
        border-radius: 4px;
    }
</style>
<!-- BEGIN PAGE HEADER-->
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
	  <div class="portlet box blue-hoki" id='labContent'>
		<div class="portlet-title">
			<div class="caption">
				<i class="levelchild viconfont vicon-virtual_lab_manager"></i><?php echo $LANG['UI_VIRTUAL_LAB_MANAGER_LIST'] ?>
			</div>

		</div>

		<div class="portlet-body margin10">
            <div class="table-container">
                <div class="vin_toolbar" id="vin_lab_toolbar">
                    <div class="leftTool">
                    </div>

                    <div class="rightTool">
                        <div class="vin_btnToolbar">
                        </div>
                    </div>
                </div>
                <table id="lab_table">
                </table>
                <div class="alert alert-block alert-info fade in" id="marktips">
                    <button type="button" class="close" data-dismiss="alert"></button>
                    <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                    <ol class = "alert-ol">
                        <li>
                            <?php echo $LANG['UI_VIRTUAL_LAB_MANAGER_LIST_TIPS1'] ?>
                        </li>
                    </ol>
                </div>
            </div>

		</div>
	   </div>
	</div>

    <!-- BEGIN SHOW LAB DETAIL DRAWER -->
    <div class="drawer slide aws-drawer-width_en" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true" id="show_labdetail_drawer" style="width:600px;">
        <div class="drawer-content drawer-content-scrollable" role="document">
            <div class="drawer-header">
                <div class="drawer-title" id="nas_more_detail_drawer_title"  style="display:flex;justify-content:space-between;align-items:center">
                    <div class="drawer-title-left">
                        <i class="viconfont vicon-tenant-detail"></i>
                        <span><?php echo $LANG['UI_VIRTUAL_LAB_DETAILS'] ?></span>
                    </div>
                    <div class="drawer-title-right drawer-close" data-dismiss="drawer" aria-label="Close">
                        <i class="viconfont vicon-guanbi"></i>
                    </div>
                </div>
            </div>
            <div class="drawer-body config-detail-wrap config-detail-drawer-backup">
                <!-- 基本信息 -->
                <div class="strategy-group mt20">

                    <!-- BEGIN BASIC INFO -->
                    <div class="strategy-group__title">
                        <span class="decoration me-4"></span><span
                                class="strategy-group__title__text"><?php echo $LANG['UI_JOB_BASE_INFO'] ?></span>
                    </div>
                    <div class="strategy-group__form basicDiv">
                        <div class="strategy-group__form__item align-items-baseline">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_VIRTUAL_LAB_NAME'] ?>
                            </div>
                            <div class="strategy-group__form__item__value col-md-8" id="labName">
                            </div>
                        </div>
                        <div class="strategy-group__form__item align-items-baseline">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_PUBLIC_STATUS'] ?>
                            </div>
                            <div class="strategy-group__form__item__value col-md-8" id="labStatus">
                            </div>
                        </div>
                        <div class="strategy-group__form__item align-items-baseline backup_style">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_VERIFY_PROXY'] ?>
                            </div>
                            <div class="strategy-group__form__item__value col-md-8" id="proxy">
                            </div>
                        </div>
                        <div class="strategy-group__form__item align-items-baseline backup_style">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_PUBLIC_IP_ADDRESS'] ?>
                            </div>
                            <div class="strategy-group__form__item__value col-md-8" id="labIpaddr">
                            </div>
                        </div>
                        <div class="strategy-group__form__item align-items-baseline backup_style">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_SETTINGS_NETMASK'] ?>
                            </div>
                            <div class="strategy-group__form__item__value col-md-8" id="labNetmask">
                            </div>
                        </div>
                        <div class="strategy-group__form__item align-items-baseline backup_style">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_SETTINGS_GATEWAY'] ?>
                            </div>
                            <div class="strategy-group__form__item__value col-md-8" id="labGateway">
                            </div>
                        </div>
                        <div class="strategy-group__form__item align-items-baseline backup_style">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_VIRTUAL_LAB_PRODUCT_NETWORK'] ?>
                            </div>
                            <div class="strategy-group__form__item__value col-md-8" id="labNetwork">
                            </div>
                        </div>
                        <div class="strategy-group__form__item align-items-baseline backup_style">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_VIRTUAL_LAB_TARGET_STORAGE'] ?>
                            </div>
                            <div class="strategy-group__form__item__value col-md-8" id="labStorage">
                            </div>
                        </div>
                        <div class="strategy-group__form__item align-items-baseline backup_style">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_VCENTER_VC'] ?>
                            </div>
                            <div class="strategy-group__form__item__value col-md-8" id="vcenterName">
                            </div>
                        </div>
                        <div class="strategy-group__form__item align-items-baseline backup_style">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_VCENTER_HOST'] ?>
                            </div>
                            <div class="strategy-group__form__item__value col-md-8" id="hostName">
                            </div>
                        </div>

                    </div>
                    <!-- END BASIC INFO -->
                </div>
                <!-- BEGIN NETWORK INFO-->
                <div class="strategy-group tableDiv">

                    <!-- BEGIN VERIFICATION GROUP -->
                    <div class="">
                        <div class="strategy-group__title">
                            <span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_VIRTUAL_LAB_NETWORK_INFO'] ?></span>
                        </div>
                        <div class="strategy-group__form">
                            <!-- 生产网络 -->
                            <div class="mb15">
                                <table id="product_network_table">
                                </table>
                            </div>
                            <!-- 隔离网络 -->
                            <div class="mb15">
                                <table id="isolated_network_table">
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- END NETWORK INFO-->
                </div>
            </div>
            <div class="drawer-footer">
                <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-primary" id="addsubmit"><?php echo $LANG['UI_PUBLIC_YES']?></button>
            </div>
        </div>
    </div>
    <!-- END SHOW LAB DETAIL DRAWER -->

</div>

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/platform/dataverification/virtual_lab_manager.js"></script>
<!-- END PAGE LEVEL PLUGINS -->