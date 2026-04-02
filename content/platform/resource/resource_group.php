<?php include_once '../../../tpl/permission.php'; ?>

<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>
<link href="./assets/global/plugins/datatables/extensions/Buttons/css/buttons.dataTables.css" rel="stylesheet" type="text/css"/>
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<!-- BEGIN PAGE HEADER -->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="backup_manager" href="./content/platform/backup/backupresources.php">
            <span><?php echo $LANG['UI_PLATFORM_BACKUP_RESOURCE'] ?></span>
        </a>
    </li>
    <span>
        >
    </span>
    <span class="curent"><?php echo $LANG['UI_RESOURCE_GROUP_LIST'] ?></span>
</h3>
<!-- END PAGE HEADER -->
<!-- BEGIN PAGE CONTENT-->
<div class="resource-manager-wrap" id="resource_group_div">
    <div class="resource-manager-wrap__header">
        <span>
            <i class="viconfont vicon-a-Cube-fourmofang"></i>
            <span class="resource-manager-wrap__header__text"><?php echo $LANG['UI_RESOURCE_GROUP_LIST'] ?></span>
        </span>
    </div>
    <div class="resource-manager-wrap__content">
        <div class="table-toolbar-wrapper vin_toolbar" id="vin_current_toolbar">
            <div class="table-toolbar-wrapper__left leftTool" style="display: flex;">
            </div>
        </div>
        <div class="table-toolbar-wrapper__right rightTool">
            <div class="table-actions-wrapper page-right">
            </div>
            <div class="vin_btnToolbar"></div>
        </div>

        <div class="table-container">
            <table id="resource_table"></table>
        </div>
    </div>
</div>

<!-- BEGIN MODAL DELETE-->
<div id="deletemodaldiv" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title"><i class="viconfont vicon-ge_delete"></i> <?php echo $LANG['UI_GLOBAL_STRATEGY_DELETE'] ?></h4>
    </div>
    <div class="modal-body">
        <div class="portlet-body">
            <div class="alert alert-warning">
                <button type="button" class="close" data-dismiss="alert"></button>
                <ul class="alert-ul">
                    <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                    <li>
                        <i class="viconfont vicon-ge_summary"></i>
                        <?php echo $LANG['UI_GLOBAL_STRATEGY_DELETE_TIPS1'] ?>
                    </li>
                </ul>
            </div>
        </div>
        <div class="row static-info">
            <div class="col-md-11">
                <ul id="strategy_tree" class="ztree bd1de5  tree_div"></ul>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
        <button type="button" class="btn btn-primary" id="delete_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>
<!--修改-->
<div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true" id="drawer-edit" style="width: 600px;">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header ">
            <h4 class="drawer-title" id="drawer-1-title" style="vertical-align: top">
                <i class="editStrategy" style="padding-top: 3px"></i>
                <span style="vertical-align: top"><?php echo $LANG['UI_RESOURCE_GROUP_MODIFY'] ?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i class="viconfont vicon-guanbi"></i></span>
            </h4>

        </div>
        <div class="drawer-body">
            <div class="form-group mt30">
                <label class="col-md-3"><?php echo $LANG['UI_RESOURCE_GROUP_NAME'] ?><span class="required" style="color: red">
                * </span>
                </label>
                <div class="col-md-8">
                    <div class="input-icon right">
                        <i class="fa"></i>
                        <input type="text" maxlength="128" class="form-control" name="resourcegroupname" id="resourcegroupname"/>
                        <div><span class="help-block ">
                        </span></div>
                    </div>
                </div>
            </div>
            <div class="form-group" >
                <label class="mt30 col-md-3"><?php echo $LANG['UI_RESOURCE_GROUP_TYPE'] ?><span class="required" style="color: red">
                * </span>
                </label>
                <div class="col-md-8 mt30">
                    <div class="input-icon right">
                        <i class="fa"></i>
                        <select  class="form-control" id="editSelect">
                            <?php if (in_array('vmprotect', $_SESSION['permission'] ?? [])) {
                                echo '<option value="3">' . $LANG['BILLING_VM'] . '</option>';
                            }
                            ; ?>

                            <?php if (in_array('storage_lanfree', $_SESSION['permission'] ?? [])) {
                                echo '<option value="10">' . $LANG['UI_JOB_CLIENT'] . '</option>';
                            }
                            ; ?>

                            <?php if (in_array('node_manager', $_SESSION['permission'] ?? [])) {
                                echo '<option value="7">' . $LANG['UI_JOB_BACKUP_NODE'] . '</option>';
                            }
                            ; ?>

                            <?php if (in_array('storage_manager', $_SESSION['permission'] ?? [])) {
                                echo '<option selected value="8">' . $LANG['UI_PLATFORM_STORAGE_RESOURCE'] . '</option>';
                            }
                            ; ?>

                            <?php if (in_array('agent_manager', $_SESSION['permission'] ?? [])) {
                                echo '<option value="2">' . $LANG['UI_PLATFORM_VM_APPLIANCE'] . '</option>';
                            }
                            ; ?>

                            <?php if (in_array('office365_protect', $_SESSION['permission'] ?? [])) {
                                echo '<option value="56">' . $LANG['UI_PLATFORM_OFFICE365_ORGANIZATION'] . '</option>';
                            }
                            ; ?>

                            <?php if (in_array('nas_protect', $_SESSION['permission'] ?? [])) {
                                echo '<option value="57">' . $LANG['UI_NAS_DEVICE_NAME'] . '</option>';
                            }
                            ; ?>

                            <?php if (in_array('cloud_platform', $_SESSION['permission'] ?? [])) {
                                echo '<option value="58">' . $LANG['UI_PLATFORM_VM_CLOUD_PLATFORM'] . '</option>';
                            }
                            ; ?>

                            <?php if (in_array('obs_protect', $_SESSION['permission'] ?? [])) {
                                echo '<option value="59">' . $LANG['UI_PLATFORM_OBS'] . '</option>';
                            }
                            ; ?>

                            <?php if (in_array('hadoop_protect', $_SESSION['permission'] ?? [])) {
                                echo '<option value="60">' . $LANG['WEB_HADOOP_CLUSTER'] . '</option>';
                            }
                            ; ?>

                            <?php if (in_array('prcloud_protect', $_SESSION['permission'] ?? [])) {
                                echo '<option value="61">' . $LANG['UI_PLATFORM_VM_PRIVATE_CLOUD_PLATFORM'] . '</option>';
                            }
                            ; ?>
                            <?php if (in_array('k8s_cluster', $_SESSION['permission'] ?? [])) {
                                echo '<option value="62">' . $LANG['WEB_K8S_CLUSTER_PROTECT'] . '</option>';
                            }
                            ; ?>
                        </select>
                        <div><span class="help-block ">
                        </span></div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 mt30">&emsp;&emsp;<?php echo $LANG['UI_PUBLIC_REMARK'] ?>
                </label>
                <div class="col-md-8 mt30">
                    <div class="input-icon right">
                        <i class="fa"></i>
                        <textarea class="form-control" id="description" name="description" rows="6" placeholder="<?php echo $LANG['UI_RESOURCE_GROUP_DETAIL_INFO'] ?>"></textarea>
                        <div><span class="help-block ">
                        </span></div>
                    </div>
                </div>
            </div>
            <!--表格-->
            <div class="form-group mt30">
                <label class="col-md-4 mt30"><?php echo $LANG['UI_CLIENT_APP_SELECT_RES'] ?>
                </label>
                <div class="mt30">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="portlet-body" id="">
                            </div>
                            <div class="table-container";>
                                <div class="table-toolbar-wrapper vin_toolbar" id="vin_select_resource_toolbar">
                                    <div class="table-toolbar-wrapper__left leftTool" style="display: flex;">
                                    </div>
                                    <div class="col-md-8">
                                        <select class="form-control select2me display-none" id="edtype" name="edtype" style="width:235px; height:34px;">
                                        </select>
                                        <select class="form-control select2me display-none" id="edPrivatetype" name="edPrivatetype" style="width:235px; height:34px;">
                                        </select>
                                        <select class="form-control select2me display-none" id="edPublictype" name="edPublictype" style="width:235px; height:34px;">
                                        </select>
                                    </div>

                                </div>
                                <div class="table-toolbar-wrapper__right">
                                    <div class="table-actions-wrapper page-right">
                                    </div>
                                </div>
                                <table id="editReport">
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="drawer-footer">
            <button type="button" class="btn btn-primary" id="modify_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
            <button type="button" data-dismiss="drawer" aria-label="Close" id="modify_close" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
        </div>
    </div>
</div>

<!--添加-->
<div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true" id="drawer-1" style="width: 600px;">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header ">
            <h4 class="drawer-title" id="drawer-1-title" style="vertical-align: top">
                <i class="addNewTask" style="padding-top: 3px"></i>
                <span style="vertical-align: top"><?php echo $LANG['UI_RESOURCE_GROUP_ADD'] ?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i class="viconfont vicon-guanbi"></i></span>
            </h4>

        </div>
        <div class="drawer-body">
            <div class="form-group mt30">
                <label class="col-md-3"><?php echo $LANG['UI_RESOURCE_GROUP_NAME'] ?><span class="required" style="color: red">
                * </span>
                </label>
                <div class="col-md-8">
                    <div class="input-icon right">
                        <input type="text" maxlength="128" class="form-control" name="resourcegroupname" id="addName"/>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class=" col-md-3 mt30"><?php echo $LANG['UI_RESOURCE_GROUP_TYPE'] ?><span class="required" style="color: red">
                * </span>
                </label>
                <div class="col-md-8 mt30">
                    <div class="input-icon right">
                        <select  class="form-control" id="addSelect">
                            <?php if (in_array('vmprotect', $_SESSION['permission'] ?? [])) {
                                echo '<option value="3">' . $LANG['BILLING_VM'] . '</option>';
                            }
                            ; ?>

                            <?php if (in_array('storage_lanfree', $_SESSION['permission'] ?? [])) {
                                echo '<option value="10">' . $LANG['UI_JOB_CLIENT'] . '</option>';
                            }
                            ; ?>

                            <?php if (in_array('node_manager', $_SESSION['permission'] ?? [])) {
                                echo '<option value="7">' . $LANG['UI_JOB_BACKUP_NODE'] . '</option>';
                            }
                            ; ?>

                            <?php if (in_array('storage_manager', $_SESSION['permission'] ?? [])) {
                                echo '<option selected value="8">' . $LANG['UI_PLATFORM_STORAGE_RESOURCE'] . '</option>';
                            }
                            ; ?>

                            <?php if (in_array('agent_manager', $_SESSION['permission'] ?? [])) {
                                echo '<option value="2">' . $LANG['UI_PLATFORM_VM_APPLIANCE'] . '</option>';
                            }
                            ; ?>

                            <?php if (in_array('office365_protect', $_SESSION['permission'] ?? [])) {
                                echo '<option value="56">' . $LANG['UI_PLATFORM_OFFICE365_ORGANIZATION'] . '</option>';
                            }
                            ; ?>

                            <?php if (in_array('nas_protect', $_SESSION['permission'] ?? [])) {
                                echo '<option value="57">' . $LANG['UI_NAS_DEVICE_NAME'] . '</option>';
                            }
                            ; ?>

                            <?php if (in_array('cloud_platform', $_SESSION['permission'] ?? [])) {
                                echo '<option value="58">' . $LANG['UI_PLATFORM_VM_CLOUD_PLATFORM'] . '</option>';
                            }
                            ; ?>

                            <?php if (in_array('obs_protect', $_SESSION['permission'] ?? [])) {
                                echo '<option value="59">' . $LANG['UI_PLATFORM_OBS'] . '</option>';
                            }
                            ; ?>

                            <?php if (in_array('hadoop_protect', $_SESSION['permission'] ?? [])) {
                                echo '<option value="60">' . $LANG['WEB_HADOOP_CLUSTER'] . '</option>';
                            }
                            ; ?>

                            <?php if (in_array('prcloud_protect', $_SESSION['permission'] ?? [])) {
                                echo '<option value="61">' . $LANG['UI_PLATFORM_VM_PRIVATE_CLOUD_PLATFORM'] . '</option>';
                            }
                            ; ?>
                            <?php if (in_array('k8s_cluster', $_SESSION['permission'] ?? [])) {
                                echo '<option value="62">' . $LANG['WEB_K8S_CLUSTER_PROTECT'] . '</option>';
                            }
                            ; ?>

                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class=" col-md-3 mt30">&emsp;&emsp;<?php echo $LANG['UI_PUBLIC_REMARK'] ?>
                </label>
                <div class="col-md-8 mt30">
                    <div class="input-icon right">
                        <i class="fa"></i>
                        <textarea class="form-control" id="addDescription" name="description" rows="6" placeholder="<?php echo $LANG['UI_RESOURCE_GROUP_DETAIL_INFO'] ?>"></textarea>
                        <div><span class="help-block ">
                        </span></div>
                    </div>
                </div>
            </div>
            <!--表格-->
            <div class="form-group mt30">
                <label class=" col-md-3 mt30"><?php echo $LANG['UI_STORAGE_SELECT_RES'] ?>
                </label>
                <div class=" mt30">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="portlet-body" id="">
                            </div>
                            <div class="table-container">
                                <div class="table-toolbar-wrapper vin_toolbar" id="vin_select_resource_toolbar1">
                                    <div class="table-toolbar-wrapper__left leftTool" style="display: flex;">
                                    </div>
                                    <div class="col-md-8">
                                        <select class="form-control select2me display-none" id="vmtype" name="vmtype" style="width:235px; height:34px;">
                                        </select>
                                        <select class="form-control select2me display-none" id="vmPrivatetype" name="vmPrivatetype" style="width:235px; height:34px;">
                                        </select>
                                        <select class="form-control select2me display-none" id="vmPublictype" name="vmPublictype" style="width:235px; height:34px;">
                                        </select>
                                    </div>
                                </div>
                                <div class="table-toolbar-wrapper__right rightTool">
                                    <div class="table-actions-wrapper page-right vin_btnToolbar1">
                                    </div>
                                </div>
                                <table id="backupReport">
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="drawer-footer">
            <button type="button" class="btn btn-primary" id="add_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
            <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
        </div>
    </div>
</div>

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/platform/resource/resource_group.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
