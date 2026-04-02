<?php include_once '../../../tpl/permission.php'; ?>
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>


<!-- BEGIN PAGE LEVEL STYLES -->
<!-- <link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet"
    type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" /> -->
<!-- <link rel="stylesheet" type="text/css"
    href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
<link rel="stylesheet" type="text/css"
    href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" /> -->
<link rel="stylesheet" type="text/css" href="./scripts/plugins/ztree/css/metroStyle/metroStyle.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<!-- <link rel="stylesheet" type="text/css" href="./scripts/plugins/popModal/popModal.css" /> -->
<!-- <link rel="stylesheet" type="text/css" href="./assets/global/plugins/daterangepicker/daterangepicker.css" /> -->
<!-- END PAGE LEVEL STYLES -->

<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li><a class="ajaxify" name="infrastructure" href=" ./content/platform/storage/lun_storage_manager.php">
            <?php echo $LANG['UI_LUN_STORAGE_LIST']; ?>
        </a></li>
    <span> > </span>
    <span class="curent">
        <?php echo $LANG['UI_LUN_STORAGE_LIST_DETAIL'] ?>
    </span>
</h3>

<!-- <div class="row hp100"> -->
<!-- BEGIN PAGE CONTENT-->
<div class="row" style="height: 95%;">
    <div class="col-md-12" style="height: 100%;">
        <!-- Begin: life time stats -->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="levelchild viconfont vicon-a-Tapecidai"></i><?php echo $LANG['UI_LUN_STORAGE_LIST_DETAIL'] ?>
                </div>
            </div>
            <div class="portlet-body mlr10">
                <div class="row">
                    <div class="col-md-4">
                        <div class="portlet">
                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="viconfont vicon-a-Chart-graphguanxitu"></i><?php echo $LANG['UI_LUN_STORAGE_LIST'] ?>
                                </div>
                            </div>
                            <div class="portlet-body">
                                <div class="vcenter-tree" style="border: 1px solid #E6E6E6;padding: 8px 8px;">
                                    <!-- <div class="input-icon width100p">
                                        <input type="text" maxlength="128"
                                            placeholder="<?php echo $LANG['UI_COPY_SEARCH_AS_KEYWORD'] ?>"
                                            class="form-control" id="search_storage" />
                                    </div> -->
                                    <!-- <p id="markStr"></p>
                                    <div class="alert alert-block alert-info fade in display-hide" id="nopointtips">
                                        <button type="button" class="close" data-dismiss="alert"></button>
                                        <ul class="alert-ul">
                                            <strong class="alert-ul-head">
                                                <?php echo $LANG['UI_PUBLIC_TIPS'] ?>:
                                            </strong>
                                            <li>
                                                <?php echo $LANG['UI_DATA_NODATA_TIPS'] ?>
                                            </li>
                                        </ul>

                                    </div>
                                    <div class="alert alert-block alert-info fade in display-hide" id="nosearchtips">
                                        <button type="button" class="close" data-dismiss="alert"></button>
                                        <ul class="alert-ul">
                                            <strong class="alert-ul-head">
                                                <?php echo $LANG['UI_PUBLIC_TIPS'] ?>:
                                            </strong>
                                            <li>
                                                <?php echo $LANG['UI_DATA_NOSEARCH_TIPS'] ?>
                                            </li>
                                        </ul>
                                    </div> -->
                                    <ul id="lun_storage_tree" class="ztree ztree-fa tree-scroll"
                                        style="padding: 11px 20px;">
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="portlet ">
                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="viconfont vicon-a-View-listxiangqingliebiao"></i><?php echo $LANG['UI_LUN_STORAGE_SNAP_LIST'] ?>
                                    <span style="margin-left: 10px;color:#4ad1cd;font-size:14px;"
                                          id="storage_data_url"></span>
                                </div>
                            </div>
                            <div class="table-container hp-100" id="lun_table_div">
                                <div class="vin_toolbar" id="vin_lun_toolbar">
                                    <div class="leftTool">
                                        <div class='customBtn1'></div>
                                    </div>
                                    <div class="rightTool">
                                        <div class="vin_btnToolbar">
                                        </div>
                                    </div>
                                </div>
                                <!-- <table id="lun_detail_table"></table> -->

                                <table id="lun_table"></table>

                            </div>

                        </div>
                    </div>
                    <div class="bottom-drawer display-hide col-md-12" id="drag-box">
                        <div class="data-list" id="drag-top"></div>
                        <div id="resize_div" class="resize-line"></div>
                        <!-- <div class="vin_toolbar" id="vin_lun_toolbar">
                            <div class="leftTool">
                                <div class='customBtn1'></div>
                            </div>
                            <div class="rightTool">
                                <div class="vin_btnToolbar">
                                </div>
                            </div>
                        </div> -->
                        <div class="table-container hp-100">
                            <!-- <table id="lun_detail_table"></table> -->
                            <table id="lun_snap_table"></table>
                        </div>
                        <div class="data-list target-list display-hide" id="drag-down"></div>
                    </div>

                </div>
            </div>
        </div>
        <!-- End: life time stats -->

    </div>
    <!-- END PAGE CONTENT-->
</div>

<input type="hidden" id="storage_type" value="<?php echo $_GET['type'];?>">
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<!-- <script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script> -->
<!-- <script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script> -->
<!-- <script type="text/javascript"
    src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script> -->
<!-- <script type="text/javascript"
    src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script> -->

<!-- <script type="text/javascript" src="./scripts/plugins/datatable.js"></script> -->
<!-- <script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script> -->
<!-- <script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script> -->
<!-- <script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script> -->
<!-- <script type="text/javascript" src="./scripts/plugins/popModal/popModal.js"></script> -->
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/platform/storage/lun_storage_detail.js"></script>
<!-- END PAGE LEVEL PLUGINS -->