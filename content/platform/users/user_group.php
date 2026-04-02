<?php include_once '../../../tpl/permission.php'; ?>
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>

<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="safety" href="./content/platform/users/safety_manager.php">
            <span><?php echo $LANG['UI_PLATFORM_SAFETY'] ?></span>
        </a>
    </li>
    <span>></span>
    <span class="curent"><?php echo $LANG['UI_PLATFORM_SAFETY_USER_GROUP'] ?></span>
</h3>

<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
    <div class="col-md-12">
        <div class="portlet box blue-hoki" id='authdiv'>
            <div class="portlet-title">
                <div class="caption">
                    <i class="iconfont icon-usergroup"></i><?php echo $LANG['UI_USER_GROUP_MANAGE_LIST'] ?>
                </div>

            </div>

            <div class="portlet-body margin10">

                <div class="table-container">
                    <div class="vin_toolbar" id="vin_usergroup_toolbar">
                        <div class="leftTool">
                            <div class="customBtn1">
                                <div style="cursor: not-allowed">
                                    <?php
                                    if (in_array("p_safety_usergroup_delete", $_SESSION['permissionArr'])) {
                                        // 删除
                                        echo '<div style="cursor:not-allowed;"><button class="btn viconfont vicon-a-Deleteshanchu1 b-btn brr2 mr12 exch-forbid-event" id="delete_usergroup"></button></div>';
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="customBtn2">
                                <?php
                                if (in_array("p_safety_usergroup_add", $_SESSION['permissionArr'])) {
                                    // 新建
                                    echo '<button type="button" id="add_usergroup" class="btn table-toolbar-btn">
                                        <i class="viconfont vicon-biaogetianjia mr4"></i> <span>' . $LANG['UI_PUBLIC_ADDNEW'] . '</span></button>';
                                }
                                if (in_array("p_safety_usergroup_edit", $_SESSION['permissionArr'])) {
                                    // 修改
                                    echo '<button type="button" id="edit_usergroup" class="btn table-toolbar-btn">
                                        <i class="viconfont vicon-xiugai mr4"></i> <span>' . $LANG['UI_PUBLIC_MODIFY'] . '</span></button>';
                                }
                                if (in_array("p_safety_usergroup_enable", $_SESSION['permissionArr'])) {
                                    // 启用
                                    echo '<button type="button" id="unlock" class="btn table-toolbar-btn">
                                        <i class="viconfont vicon-a-Unlockjiesuo-0111 mr4"></i> <span>' . $LANG['UI_TENANT_BUTTON_ENABLE'] . '</span></button>';
                                }
                                if (in_array("p_safety_usergroup_disable", $_SESSION['permissionArr'])) {
                                    // 禁用
                                    echo '<button type="button" id="lock" class="btn table-toolbar-btn">
                                        <i class="viconfont vicon-a-Unlockjiesuo-011 mr4"></i> <span>' . $LANG['UI_TENANT_BUTTON_DISABLE'] . '</span></button>';
                                }
                                ?>
                            </div>
                        </div>

                        <div class="rightTool">
                            <div class="vin_btnToolbar">
                            </div>
                        </div>
                    </div>
                    <table id="usergroup_table">
                    </table>
                    <div class="alert alert-block alert-info fade in mt-10" id="marktips">
                        <button type="button" class="close" data-dismiss="alert"></button>
                        <ul class="alert-ul">
                            <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                            <li>
                                <?php echo $LANG['UI_PLATFORM_USR_CLICK_GROUP_NAME_CAT_INFO'] ?>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
        <!-- BEGIN MODAL -->
        <div id="userPermissionDiv" class="modal xmodal fade form-horizontal " style="top:40%" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
            <div class="modal-header ">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title" ><i class="viconfont vicon-ge_summary"></i> <?php echo $LANG['UI_RESOURCE_GROUP_RELATED_INFO'] ?></h4>
            </div>
            <div class="modal-body">
                <div class="tabbable-custom max-height500">
                    <ul class="nav nav-tabs ">
                        <li id="usertab" class="active">
                            <a href="#user_tab" data-toggle="tab" aria-expanded="false">
                                <i class="viconfont vicon-pt_setting_user"></i> <?php echo $LANG['WEB_USERS_USER'] ?></a>
                        </li>
                        <li class="display-none" id="roletab">
                            <a href="#role_tab" data-toggle="tab" aria-expanded="false">
                                <i class="iconfont icon-jiaose"></i> <?php echo $LANG['UI_PLATFORM_SAFETY_ROLE'] ?> </a>
                        </li>
                        <li class="display-none" id="resourcegrouptab">
                            <a href="#resourcegroup_tab" data-toggle="tab" aria-expanded="false">
                                <i class="viconfont vicon-resource_group"></i> <?php echo $LANG['UI_PLATFORM_RESOURCE_GROUP'] ?> </a>
                        </li>
                        <li class="display-none" id="permissiontab">
                            <a href="#permission_tab" data-toggle="tab" aria-expanded="false">
                                <i class="viconfont vicon-ge_permission"></i> <?php echo $LANG['UI_ROLE_PERMISSION'] ?> </a>
                        </li>

                    </ul>
                    <div class="tab-content ">
                        <div class="tab-pane active" id="user_tab">
                            <div class="table-container">
                                <table class="table table-striped table-bordered table-hover" id="user_table">
                                    <thead>
                                    <tr role="row" class="heading">
                                        <th width="10%">
                                            <?php echo $LANG['UI_PUBLIC_NUMBER'] ?>
                                        </th>
                                        <th width="90%">
                                            <?php echo $LANG['UI_DOMAIN_SERVER_USER_NAME'] ?>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane" id="role_tab">
                            <div class="table-container">
                                <table class="table table-striped table-bordered table-hover" id="role_table">
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane" id="resourcegroup_tab">
                            <div class="table-container">
                                <table class="table table-striped table-bordered table-hover" id="resourcegroup_table">
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane" id="permission_tab">
                            <div class="organ_trees">
                                <ul id="permission_tree" class="ztree bd1de5 ztree-fa" style="height: 400px;overflow-y:auto;"></ul>
                                <div class="alert alert-block alert-info fade in" id="nodatatips">
                                    <button type="button" class="close" data-dismiss="alert"></button>
                                    <ul class="alert-ul">
                                        <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                                        <li>
                                            <?php echo $LANG['UI_PLATFORM_USR_NO_PERMISSION'] ?>
                                        </li>
                                    </ul>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
            </div>
        </div>
        <!-- END MODAL -->
    </div>
</div>

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/platform/users/usergroup.js"></script>
<!-- END PAGE LEVEL PLUGINS -->