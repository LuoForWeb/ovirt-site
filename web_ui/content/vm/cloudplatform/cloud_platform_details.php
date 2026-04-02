<?php include_once '../../../tpl/permission.php'; ?>
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->

<link rel="stylesheet" type="text/css"
      href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>
<link href="./assets/global/plugins/datatables/extensions/Buttons/css/buttons.dataTables.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/daterangepicker/daterangepicker.css"/>
<!-- END PAGE LEVEL STYLES -->
<style>
    .bootstrap-select .dropdown-toggle:focus {
        outline: none !important;
    }
</style>
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
    <li>
        <?php
        if ('private' == $_GET['cloudType']) {
            echo '<a class="ajaxify" name="infrastructure" href="./content/vm/cloudplatform/cloud_platform_manager.php?cloudType=private">'
                . $LANG['UI_PLATFORM_VM_PRIVATE_CLOUD_PLATFORM'] . '</a>';
        } else {
            echo '<a class="ajaxify" name="infrastructure" href="./content/vm/cloudplatform/cloud_platform_manager.php?cloudType=public">'
                . $LANG['UI_PLATFORM_VM_CLOUD_PLATFORM'] . '</a>';
        }
        ?>
    </li>
    <span> > </span>
    <span class="curent"><?php echo $LANG['UI_CLOUD_PLATFORM_VC_DETAILS'] ?></span>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row" id="cloud-platform">
    <div class="col-md-12">
        <input id="vcuuid" value="<?php echo $_GET['vcuuid']; ?>" class="display-none"></input>
        <input id="pageUri" value="<?php echo $_SERVER['REQUEST_URI']; ?>" class="display-none"></input>
        <!-- 类型：public/private -->
        <input id="cloudType" value="<?php echo $_GET['cloudType']; ?>" class="display-none">
        <!-- Begin: life time stats -->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="levelchild viconfont <?php echo 'private' == $_GET['cloudType'] ? 'vicon-cloud_platform' : 'vicon-yunpingtai' ?>"></i>
                    <?php echo $LANG['UI_CLOUD_PLATFORM_VC_DETAILS'] ?>
                </div>
            </div>
            <div class="portlet-body">
                    <div class="row tab-pane__row">
                        <div class="col-md-3 tab-pane__row__source">
                            <div class="src-wrap">
                                <div class="src-wrap__title">
                                    <i class="viconfont vicon-cloud_platform"></i><?php echo $LANG['UI_CLOUD_PLATFORM_VC'] ?>
                                </div>
                                <div class="src-wrap__content">
                                    <div class="src-wrap__content__search">
                                        <input id="vmshowtype" value="1" class="display-none">
                                    </div>
                                    <div class="src-wrap__content__ztree">
                                        <div class="three_tree">
                                            <ul id="vm_tree_hc" class="ztree bd1de5  tree_div_vcde "></ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-9 VMList-div">
                            <div class="src-wrap">
                                <div class="src-wrap__title">
                                    <i class="viconfont vicon-ge_vm"></i>
                                    <?php echo $LANG['UI_CLOUD_PLATFORM_INSTANCE'] ?>
                                </div>
                                <div class="src-wrap__content">
                                    <div class="alert alert-block alert-info fade in" id="tabletips">
                                        <button type="button" class="close" data-dismiss="alert"></button>
                                        <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong>
                                        </h4>
                                        <ol class="alert-ol">
                                            <li>
                                                <?php echo $LANG['UI_CLOUD_PLATFORM_DETAILS_TIPS1'] ?>
                                            </li>
                                            <li>
                                                <?php echo $LANG['UI_CLOUD_PLATFORM_DETAILS_TIPS2'] ?>
                                            </li>
                                        </ol>
                                    </div>
                                    <div class="display-none" id="vmtable">
                                        <div class="table-toolbar">
                                            <div class="vin_toolbar" id="vm-report-toolbar">
                                                <div class="leftTool" style="display: flex;">
                                                    <div class="search input-group mr12">
                                                        <input type="search" maxlength="128" id="search" class="searchinput customSearch"
                                                               autocomplete="off"
                                                               style="padding-right:32px;min-width: 220px;"
                                                               placeholder="<?php echo $LANG['UI_OVERVIEW_PUBLIC_CLOUD_INSTANCE_SEARCH_TIPS'] ?>">
                                                        <div class="position0" style="width:auto;height:34px">
                                                            <button class="b-btn clear hide position0" id="clearSearchBtn">
                                                                <i class="icon-close-small"></i></button>
                                                        </div>
                                                        <div class="search-btn positionL0" style="width:auto;height:34px;">
                                                            <button class="b-btn search-btn" id="searchbtn"><i class="icon-search"></i></button>
                                                        </div>
                                                    </div>
                                                    <?php if (
                                                        'private' == $_GET['cloudType'] && in_array("p_cloud_platform_private_vm_overview_vmoperate", $_SESSION['permissionArr'])
                                                        || 'public' == $_GET['cloudType'] && in_array("p_cloud_platform_vm_overview_vmoperate", $_SESSION['permissionArr'])
                                                    ) { ?>
                                                            <div class="btn-group">
                                                                <button type="button" id="addtojob" class="btn table-toolbar-btn">
                                                                    <i class="viconfont vicon-biaogetianjia"></i> <?php echo $LANG['UI_VM_REPORT_ADD_TO_BACKUP'] ?>
                                                                </button>
                                                            </div>
                                                            <div class="btn-group">
                                                                <button type="button" id="createjob" class="btn table-toolbar-btn">
                                                                    <i class="viconfont vicon-xinjianbeifen1" style="font-weight: bold"></i> <?php echo $LANG['UI_VM_REPORT_CREATE_JOB'] ?>
                                                                </button>
                                                            </div>
                                                    <?php } ?>
                                                </div>
                                                <div class="rightTool" style="display: flex;justify-content: flex-end">
                                                    <button type="button" id="searchAll" class="btn btn-primary adv_btn brr2 p-lr8 list">
                                                        <i class="adv-search"></i>
                                                        <span style="color: #FFFFFF"><?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?></span>
                                                    </button>
                                                    <div class="vm_report_toolbar ml5">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div id="searchDiv" class="searchDiv display-none">
                                                    <?php echo $LANG['UI_PUBLIC_SEARCH_CONDITION'] ?>
                                                    <span class="searchContent"></span>
                                                    <span class="clearSearch"><?php echo $LANG['UI_SEARCH_CLEAR'] ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="table-container">
                                            <table id="vm_table" data-export-types="['excel','csv']"></table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
        <!-- End: life time stats -->

        <!-- BEGIN MODAL -->
        <div id="modaldiv" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first"
             data-backdrop="static">
            <input id="vmuuid" class="display-none"></input>
            <input id="vcenteruuid" class="display-none"></input>
            <div class="modal-header ">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title"><i
                            class="viconfont vicon-ge_add_task"></i> <?php echo $LANG['WEB_VM_VCENTER_ADD_VM_TO_TASK'] ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="form-group vmnameDiv">
                    <label class="col-md-3 control-label "><?php echo $LANG['UI_CLOUD_PLATFORM_MACHINE_NAME'] ?></label>
                    <div class="col-md-8 margintop10" id="vmname">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label"><?php echo $LANG['UI_CLOUD_PLATFORM_ADD_TO_TASK'] ?><span
                                class="required"> * </span></label>
                    <div class="col-md-8">
                        <select class="bootstrap-mutiple-select select2me selectpicker show-tick ignore" id="task" data-live-search="true">
                        </select>
                        <span class="help-block">
                        <?php echo $LANG['UI_CLOUD_PLATFORM_ADD_TO_TASK_TIPS'] ?> </span>
                    </div>
                </div>
                <div class="alert alert-danger display-none" id="modaltips">
                    <button type="button" class="close" data-dismiss="alert"></button>
                    <ul class="alert-ul">
                        <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                        <li>
                            <?php echo $LANG['UI_CLOUD_PLATFORM_ADD_TO_TASK_WARNING_TIPS'] ?>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal"
                        class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
                <button type="button" class="btn btn-primary" id="submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
            </div>
        </div>
        <!-- END MODAL -->

        <!-- BEGIN SEARCH MODAL -->
        <div id="searchmodal" class="modal xmodal fade form-horizontal " tabindex="-1"  data-backdrop="static">
            <div class="modal-header ">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title"><i class="fa fa-search"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="portlet-body">

                    <!-- 上次备份时间范围 -->
                    <div class="list-option" id="startTimeDiv">
                        <div class="row">
                            <label class="control-label col-md-2"><?php echo $LANG['UI_VM_REPORT_LAST_TIME'] ?> :
                            </label>
                            <div class="col-md-6 daterangepickerdiv">
                                <input type="text" id="daterangepickerVmReport" class="form-control" autocomplete="off">
                                <i class="viconfont vicon-ge_calendar"></i>
                            </div>
                        </div>
                    </div>

                    <div class="list-option">
                        <div class="row">
                            <!-- 实例名 -->
                            <label class="control-label col-md-2"><?php echo $LANG['UI_OVERVIEW_PUBLIC_CLOUD_INSTANCE_NAME'] ?> :
                            </label>
                            <div class="col-md-4">
                                <input style="width:235px; height:34px;" id="vmName" type="text" maxlength="64" class="table-group-action-input form-control input-inline input input-sm" aria-controls="example">
                            </div>

                            <!-- 实例IP -->
                            <label class="control-label col-md-2"><?php echo $LANG['UI_OVERVIEW_PUBLIC_CLOUD_INSTANCE_IP'] ?> :
                            </label>
                            <div class="col-md-4">
                                <input style="width:235px; height:34px;" id="vmIP" type="text" maxlength="64" class="table-group-action-input form-control input-inline input input-sm" aria-controls="example">
                            </div>
                        </div>
                    </div>

                    <div class="list-option">
                        <div class="row">
                            <!-- 备份状态 -->
                            <label class="control-label col-md-2"><?php echo $LANG['UI_VM_REPORT_BACKUP_STATUS'] ?> :
                            </label>
                            <div class="col-md-4">
                                <select class="form-control select2me" id="backupFlag">
                                    <option value="0"><?php echo $LANG['UI_PUBLIC_ALL'] ?></option>
                                    <option value="1"><?php echo $LANG['UI_VM_REPORT_IN_BACKUP'] ?></option>
                                    <option value="2"><?php echo $LANG['UI_VM_REPORT_NOIN_BACKUP'] ?></option>
                                </select>
                            </div>

                            <!-- 备份任务名 -->
                            <div id="taskNameDiv">
                                <label class="control-label col-md-2"><?php echo $LANG['UI_VM_REPORT_BACKUP_TASK'] ?> :
                                </label>
                                <div class="col-md-4">
                                    <input style="width:235px; height:34px;" id="taskName" type="text" maxlength="64" class="table-group-action-input form-control input-inline input input-sm" aria-controls="example">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- modal-footer -->
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
                <button type="button" class="btn btn-primary" id="serach_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
            </div>
        </div>
        <!-- END SEARCH MODAL -->
    </div>
</div>
<!-- END PAGE CONTENT-->


<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript"
        src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>

<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>

<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/vm/cloudplatform/cloud_platform_details.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	