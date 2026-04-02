<?php include_once '../../../tpl/permission.php'; ?>
<?php include_once '../../../content/platform/public/bs_table.php'; ?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<!-- BEGIN PAGE LEVEL STYLES -->
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet"
      type="text/css" />
<link href="./content/platform/resource/css/tape.css" rel="stylesheet" type="text/css" />
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<div class="row" id="tage_data_div">
    <div class="portlet box blue-hoki" id='authdiv'>
        <div class="col-md-3" id="left-ztee">
            <div>
                <select id="module_type" class="form-control">
                </select>
            </div>
            <div class="input-with-icon" style="margin-bottom: 5px;">
                <i class="icon-search search-trigger" style="right: unset;top: unset; cursor: pointer;"></i>
                <input type="text" id="object_name" class="form-control"
                       placeholder="<?php echo $LANG['UI_TAPE_SEARCH_OBJECT'] ?>">
            </div>
            <div style="flex: 1; min-height: 0;">
                <ul id="recoverFileTree" class="ztree"><ul>
            </div>
        </div>
        <div class="col-md-9">
            <!-- BEGIN TAB PORTLET-->
            <div class="portlet-body">
                <!-- BEGIN PAGE CONTENT-->
                <div class="row">
                    <div class="col-md-12">
                        <!-- Begin: life time stats grey-cararra -->
                        <div class="portlet-body m-10" id="tape_monitor">
                            <div class="vin_toolbar" id="vin_data_toolbar">
                                <div class="leftTool">
                                </div>

                                <div class="rightTool">
                                    <div class="vin_btnToolbar2">
                                    </div>

                                </div>
                            </div>
                            <table id="tape_data_table"></table>
                        </div>
                    </div>
                </div>
                <!-- </div> -->
            </div>

            <!-- END TAB PORTLET-->
        </div>
    </div>
</div>
<!-- END PAGE CONTENT-->

<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script src="./scripts/platform/resource/tape_data.js"></script>