<?php include_once '../../../tpl/permission.php';?>
<?php include_once  '../public/bs_table.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>

<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link rel="stylesheet" type="text/css" href="./scripts/plugins/popModal/popModal.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/daterangepicker/daterangepicker.css"/>
<!-- END PAGE LEVEL STYLES -->

<!-- BEGIN PAGE CONTENT-->
<div class="row" id="vm-data">
    <div class="col-md-12">
        <!-- Begin: life time stats -->
        <div class="portlet box blue-hoki">
            <div class="portlet-title ">
                <div class="caption">
                    <i class="levelchild viconfont vicon-a-Cameraxiangji"></i><?php echo $LANG['UI_INSTANT_POINTS'];?>
                </div>
            </div>
            <div class="portlet-body mlr10">
                <div class="row">
                    <div class="col-md-4">
                        <div class="portlet ">
                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="viconfont vicon-ge_time_point"></i><?php echo $LANG['UI_INSTANT_POINTS'];?>
                                </div>
                            </div>
                            <div class="portlet-body batchDeleteDiv">
                                <div class="btn-group">
                                    <button type="button" id="allDelete" class="btn btn-sm green-haze">
                                        <i class="viconfont vicon-ge_delete"></i>
                                    </button>
                                </div>

                                <div id="nodeCheck" style="margin-top:10px;margin-bottom:10px;">
                                    <select class="bs-select dataBorder" style="width:100%;height:34px;padding-left: 12px" data-show-subtext="true" id="storageselect" >
                                    </select>
                                </div>

                                <div class="vcenter-tree">
                                    <p id="markStr"></p>
                                    <div class="alert alert-block alert-info fade in display-hide"  id="nopointtips">
                                        <button type="button" class="close" data-dismiss="alert"></button>
                                        <ul class="alert-ul">
                                            <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS']?>:</strong>
                                            <li>
                                                <?php echo $LANG['UI_DATA_NODATA_TIPS']?>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="alert alert-block alert-info fade in display-hide"  id="nosearchtips">
                                        <button type="button" class="close" data-dismiss="alert"></button>
                                        <ul class="alert-ul">
                                            <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS']?>:</strong>
                                            <li>
                                                <?php echo $LANG['UI_DATA_NOSEARCH_TIPS']?>
                                            </li>
                                        </ul>
                                    </div>

                                    <ul id="vcenter_tree" class="ztree ztree-fa tree-scroll bd1de5" style="height: 90%;"></ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="portlet ">
                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="viconfont vicon-a-View-listxiangqingliebiao"></i><?php echo $LANG['UI_DATA_BACKUP_POINT']?>
                                    <span style="margin-left: 10px;color:#4ad1cd;font-size:14px;" id="vmdataUrl"></span>
                                </div>
                            </div>
                            <div class="alert alert-block alert-info fade in"  id="tabletips">
                                <button type="button" class="close" data-dismiss="alert"></button>
                                <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS']?></strong></h4>
                                <ol class = "alert-ol">
                                    <li>
                                        <?php echo $LANG['UI_DATA_VM_TIPS1']?>
                                    </li>
                                    <li>
                                        <?php echo $LANG['UI_DATA_VM_TIPS2']?>
                                    </li>
                                    <li>
                                        <?php echo $LANG['UI_DATA_VM_TIPS3']?>
                                    </li>
                                    <li>
                                        <?php echo $LANG['UI_DATA_VM_TIPS4']?>
                                    </li>
                                </ol>
                            </div>
                            <div class="portlet-body display-none" id="vmtable">
                                <!-- search -->
                                <div class="table-toolbar-wrapper">
                                    <div class="table-toolbar-wrapper__left">
                                    </div>
                                    <div class="table-toolbar-wrapper__right">
                                        <div class="table-actions-wrapper page-right">
											<span>
											</span>
                                            <button type="button" id="searchAll" class="btn green-haze">
                                                <i class="fa fa-search"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div id="searchDiv" class="searchDiv display-none"><?php echo $LANG['UI_PUBLIC_SEARCH_CONDITION'] ?> <span class="searchContent"></span><span class="clearSearch"><?php echo $LANG['UI_SEARCH_CLEAR'] ?></span> </div>
                                    </div>
                                </div>
                                <div class="table-container">
                                    <table id="datatable"></table>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End: life time stats -->
        <!-- BEGIN MODAL -->
        <div id="searchmodal" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
            <input id="vcenteruuid" class="display-none"></input>
            <div class="modal-header ">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title"><i class="fa fa-search"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH']?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="portlet-body">

                    <!-- 时间点范围 -->
                    <div class="list-option" id="startTimeDiv">
                        <div class="row">
                            <label class="control-label col-md-3"><?php echo $LANG['UI_DATA_TIMEPOINT_RANGE']?> ：
                            </label>
                            <div class="col-md-6 daterangepickerdiv" >
                                <input type="text" id="daterangepickerVmData" class="form-control" autocomplete="off">
                                <i class="viconfont vicon-ge_calendar"></i>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- modal-footer -->
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
                <button type="button"class="btn btn-primary" id="serach_submit"><?php echo $LANG['UI_PUBLIC_YES']?></button>
            </div>
        </div>
        <!-- END MODAL -->

    </div>
</div>
<!-- END PAGE CONTENT-->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<?php
if($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw"){
    //如果不是英文,加载语言包
    echo '<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>';
}
?>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
<script type="text/javascript" src="./scripts/plugins/popModal/popModal.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<input type="hidden" id="subtype" value="<?php echo $_GET['subtype'];?>">
<?php
if ($_GET['recovery_type'] != 'agent') {
    // 虚拟化
    echo '<script type="text/javascript" src="./scripts/platform/recovery/points.js"></script>';
} else {
    // 整机
    echo '<script type="text/javascript" src="./scripts/platform/recovery/agent_points.js"></script>';
}
?>
<!-- END PAGE LEVEL PLUGINS -->
