<?php include_once '../../../tpl/permission.php';?>
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/component/driver_check.css" />
<link rel="stylesheet" type="text/css" href="./scripts/components/filter/css/filter.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/component/safe_virus.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/tree-grid/jquery.treegrid.css" />
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="appgroup" href="./content/platform/dataverification/appgroup.php">
            <?php echo $LANG['UI_VERIFY_APP_GROUP_LIST'];?>
        </a>
    </li>
    <span>></span>
    <span class="curent"><?php if(empty($_GET['uuid'])) {echo $LANG['UI_PUBLIC_ADDNEW'];} else{ echo $LANG['UI_PUBLIC_MODIFY'];}?></span>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row row-manager">
    <div class="col-md-12 col-manager">
        <input id="appgroup_uuid" value="<?php echo $_GET['uuid']; ?>" class="display-none"></input>
        <input id="oem_version" value="<?php echo $CONF['SYSTEM_INFO']['vendor'];?>" class="display-none"></input>
        <!-- BEGIN VALIDATION STATES-->
        <div class="portlet box blue-hoki" id="appgroupContent">
            <div class="portlet-title">
                <div class="caption">
                    <i class="icon-user-follow"></i><?php if(empty($_GET['uuid'])) {echo $LANG['UI_VERIFY_APP_GROUP_ADD'];} else{ echo $LANG['UI_VERIFY_APP_GROUP_MODIFY'];}?>
                </div>
            </div>
            <div class="portlet-body form">
                <!-- BEGIN FORM-->
                <form action="#" id="form_sample_2" class="form-horizontal">
                    <input type="password" autocomplete="new-password" hidden>
                    <div class="form-body">
                        <div class="alert alert-danger display-hide">
                            <button type="button" class="close" data-close="alert"></button>
                            <?php echo $LANG['UI_USER_BASE_INFO_TIPS']?>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-4"><?php echo $LANG['UI_VERIFY_APP_GROUP_NAME'];?> <span class="required">
							* </span>
                            </label>
                            <div class="col-md-4">
                                <div class="input-icon right">
                                    <i class="fa"></i>
                                    <input type="text" maxlength="64"  class="form-control" id="appgroupname" name="name"/>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 "><?php echo $LANG['UI_COPY_STORAGE_SELECT'];?></label>
                            <div class="col-md-4">
                                <select class="form-control select2me" id="storagetypeselect" >

                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-4"><?php echo $LANG['UI_VERIFY_OBJECT'];?>
                    			</span>
                            </label>
                            <div class="col-md-6" >
                                <div id="verify_filter_wrapper" class="position-relative"></div>
                                <div class="bd1de5 treeDiv " style="height: 300px;overflow-y: auto;">
                                    <ul id="object_tree" class="ztree"></ul>
                                </div>

                                <div class="alert alert-block alert-info fade in display-hide" id="noModule">
                                    <button type="button" class="close" data-dismiss="alert"></button>
                                    <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS']; ?></strong></h4>
                                    <ol class="alert-ol">
                                        <li>
                                            <?php echo $LANG['UI_VERIFY_APP_GROUP_SELECT_MODULE_TIPS']; ?>
                                        </li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <div class="form-group objectDiv display-hide">
                            <label class="control-label col-md-4"> <?php echo $LANG['UI_VERIFY_APP_GROUP_SELECT_OBJECT_LIST']; ?>
                    			</span>
                            </label>
                            <div class="col-md-6" >
                                <div >
                                    <ul class="feeds accordion" id="objectList">
                                    </ul>
                                </div>
                                <div><span class="help-block ">
                                </span></div>
                            </div>
                        </div>
                        <div id="driverCheck_vm"></div>

                    </div>
                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-offset-6 col-md-6">
                                <button type="button" id="cancel" class="btn default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
                                <button type="button" id="addsubmit" class="btn green-haze btn-confirm <?php if(!empty($_GET['uuid'])) {echo "display-hide";}?>"><?php echo $LANG['UI_PUBLIC_YES']?></button>
                                <button type="button" id="editsubmit" class="btn green-haze btn-confirm <?php if(empty($_GET['uuid'])) {echo "display-hide";}?>"><?php echo $LANG['UI_PUBLIC_YES']?></button>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- END FORM-->
            </div>
        </div>
    </div>
        <!-- END VALIDATION STATES-->

    <!-- BEGIN ADD DRAWER -->
    <div class="drawer slide" data-placement="right" tabindex="-1" role="dialog"
         aria-labelledby="drawer-1-title" aria-hidden="true" id="select_point_drawer" style="width: 800px;">
        <div class="drawer-content drawer-content-scrollable" role="document">
            <div class="drawer-header">
                <input id="object_uuid" type="text" class="display-hide"></input>
                <input id="src_task_uuid" type="text" class="display-hide"></input>
                <h4 class="drawer-title">
                    <span id="object_name">
                    </span>
                    <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i
                                class="viconfont vicon-guanbi"></i></span>
                </h4>
            </div>
            <div class="drawer-body">
                <div class="form-body form-horizontal">
                    <div class="form-group mt30">
                        <label class="control-label col-md-2 "><?php echo $LANG['UI_VERIFY_APP_GROUP_SELECT_POINT']; ?></label>
                        <div class="col-md-8">
                            <select class="form-control select2me width364 inline-block" id="pointType" >
                                <option value="0"><?php echo $LANG['UI_VERIFY_APP_GROUP_NEWEST_POINT']; ?></option>
                                <option value="1"><?php echo $LANG['UI_VERIFY_APP_GROUP_ASSIGN_POINT']; ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group cdptimeDiv display-hide">
                        <label class="control-label col-md-2 "><?php echo $LANG['UI_VERIFY_APP_GROUP_SELECT_POINT'] ?></label>
                        <div class="col-md-8">
                            <select class="form-control select2me width364 inline-block" id="cdptimerange" >
                            </select>
                            <a class="ml10 popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_CM_CDP_BACKUP_SET_TIPS'] ?>">
                                <i class="viconfont vicon-tishi"></i>
                            </a>
                        </div>
                    </div>

                    <div class="form-group cdptimeDiv display-hide">
                        <label class="control-label  col-md-2"><?php echo $LANG['UI_PLATFORM_CHOOSE_POINT'] ?></label>
                        <div class="col-md-4 form-group-timepoint ">
                            <div class="input-group date backupsettimepointview" >
                                <input type="text" style="display:none;">
                                <input type="text" size="16" id="" class="form-control input-sm selecttimepoint">
                                <span class="input-group-btn">
                                    <!-- <button class="btn default input-sm-vol-cdp" id="resetRecoveryTimepoint" type="button"><i class="fa fa-times"></i></button> -->
                                    <button class="btn default date-set input-sm-vol-cdp" type="button"><i class="viconfont vicon-ge_calendar"></i></button>
                                </span>
                            </div>
                        </div>
                        <div id="timePointValidity" class="control-label col-md-1" style="padding:10px 0;"><?php echo $LANG['UI_DB_CDP_BACKUP_VALID_TIME_POINT_TYPE'];?></div>
                    </div>

                    <div class="form-group pointTableDiv display-hide">
                        <div class="table-container">
                            <div class="vin_toolbar" id="vin_point_toolbar">
                                <div class="leftTool">
                                </div>

                                <div class="rightTool">
                                    <div class="vin_btnToolbar">
                                    </div>
                                </div>
                            </div>
                            <table id="point_table">
                            </table>
                        </div>

                    </div>
                </div>


            </div>
            <!-- footer -->
            <div class="drawer-footer">
                <div class="drawer-footer-div" style="float: right;">
                    <a href="javascript:;" class="btn default mr10" data-dismiss="drawer" aria-label="Close" >
                        <?php echo $LANG['UI_PUBLIC_NO'] ?>
                    </a>
                    <a href="javascript:;" class="btn green-haze mr10" data-dismiss="drawer" id="select_point_submit">
                        <?php echo $LANG['UI_PUBLIC_YES'] ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- END ADD DRAWER -->

</div>
<!-- END PAGE CONTENT-->
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/sortable/Sortable.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/tree-grid/jquery.treegrid.js"></script>
<script type="text/javascript" src="./assets/global/plugins/tree-grid/bootstrap-table-treegrid.js"></script>
<script type="text/javascript" src="./scripts/components/filter/js/filter.js"></script>
<script type="text/javascript" src="./scripts/platform/component/driver_check.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_virus_detection.js"></script>
<script type="text/javascript" src="./scripts/plugins/path-tree-selector.js"></script>
<script type="text/javascript" src="./scripts/platform/dataverification/add_appgroup.js"></script>
