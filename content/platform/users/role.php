<?php include_once '../../../tpl/permission.php'; ?>
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->

<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>
<link href="./assets/global/plugins/datatables/extensions/Buttons/css/buttons.dataTables.css" rel="stylesheet" type="text/css"/>

<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="safety" href="./content/platform/users/safety_manager.php">
            <span><?php echo $LANG['UI_PLATFORM_SAFETY'] ?></span>
        </a>
    </li>
    <span>></span>
    <span class="curent"><?php echo $LANG['UI_PLATFORM_SAFETY_ROLE'] ?></span>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
    <div class="col-md-12">
        <div class="portlet box blue-hoki" id="roleDiv">
            <div class="portlet-title">
                <div class="caption">
                    <i class="iconfont icon-jiaose"></i><?php echo $LANG['UI_ROLE_MANAGE_LIST'] ?>
                </div>
            </div>
            <div class="portlet-body margin10">
                <div class="table-container">
                    <div class="vin_toolbar" id="vin_role_toolbar">
                        <div class="leftTool">
                            <div class="customBtn1">
                                <div style="cursor: not-allowed">
                                    <?php
                                    if (in_array("p_safety_role_delete", $_SESSION['permissionArr'])) {
                                        // 删除
                                        echo '<div style="cursor:not-allowed;"><button class="btn viconfont vicon-a-Deleteshanchu1 b-btn brr2 mr12 exch-forbid-event" id="delete_role"></button></div>';
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="customBtn2">

                                <?php
                                if (in_array("p_safety_role_add", $_SESSION['permissionArr'])) {
                                    // 新建
                                    echo '<button type="button" id="add_role" class="btn table-toolbar-btn">
                                        <i class="viconfont vicon-biaogetianjia mr4"></i> <span>' . $LANG['UI_PUBLIC_ADD'] . '</span></button>';
                                }
                                if (in_array("p_safety_role_edit", $_SESSION['permissionArr'])) {
                                    // 修改
                                    echo '<button type="button" id="edit_role" class="btn table-toolbar-btn">
                                        <i class="viconfont vicon-xiugai mr4"></i> <span>' . $LANG['UI_PUBLIC_MODIFY'] . '</span></button>';
                                }
                                if (in_array("p_safety_role_enable", $_SESSION['permissionArr'])) {
                                    // 启用
                                    echo '<button type="button" id="unlock" class="btn table-toolbar-btn">
                                        <i class="viconfont vicon-a-Unlockjiesuo-011 mr4"></i> <span>' . $LANG['UI_TENANT_BUTTON_ENABLE'] . '</span></button>';
                                }
                                if (in_array("p_safety_role_disable", $_SESSION['permissionArr'])) {
                                    // 禁用
                                    echo '<button type="button" id="lock" class="btn table-toolbar-btn">
                                        <i class="viconfont vicon-a-Unlockjiesuo-011 mr4"></i> <span>' . $LANG['UI_TENANT_BUTTON_DISABLE'] . '</span></button>';
                                }
                                if (in_array("p_safety_role_allot", $_SESSION['permissionArr'])) {
                                    // 分配用户
                                    echo '<button type="button" id="allocation" class="btn table-toolbar-btn">
                                        <i class="viconfont vicon-a-Unlockjiesuo-011 mr4"></i> <span>' . $LANG['UI_ROLE_ALLOC_USER'] . '</span></button>';
                                }
                                ?>
                            </div>
                        </div>

                        <div class="rightTool">
                            <div class="vin_btnToolbar">
                            </div>
                        </div>
                    </div>
                    <table id="role_table">
                    </table>
                    <div class="alert alert-block alert-info fade in mt-10" id="marktips">
                        <button type="button" class="close" data-dismiss="alert"></button>
                        <ul class="alert-ul">
                            <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                            <li>
                                <?php echo $LANG['UI_PLATFORM_USR_CLICK_NAME_CAT_INFO'] ?>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>

        <!-- BEGIN MODAL -->
        <div id="allocationModal" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
            <input id="roleuuid" class="display-none"></input>
            <div class="modal-header ">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title"><?php echo $LANG['UI_ROLE_ALLOC_USER'] ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="tabbable-custom">
                    <ul class="nav nav-tabs ">
                        <li class="active">
                            <a class="allocationHref" href="#allousertab" data-toggle="tab" aria-expanded="false" id="allocationUser">
                                <i class="viconfont vicon-pt_setting_user"></i> <?php echo $LANG['UI_ROLE_USER'] ?> </a>
                        </li>
                        <li class="">
                            <a class="allocationHref" href="#allousergrouptab" data-toggle="tab" aria-expanded="false" id="allocationUsergroup">
                                <i class="iconfont icon-usergroup "></i> <?php echo $LANG['UI_PLATFORM_SAFETY_USER_GROUP'] ?> </a>
                        </li>
                    </ul>
                    <div class="tab-content" style="height: 300px;">
                        <div class="tab-pane active" id="allousertab">
                            <form action="#" class="form-horizontal">
                                <div class="form-group row">
                                    <div class="col-md-2 control-label" style="padding: 0;">
                                        <?php echo $LANG['UI_ROLE_NAME'] ?>:
                                    </div>
                                    <div class="col-md-8">
                                        <span id="userRoleName"></span>
                                    </div>
                                </div>
                                <div class="form-group row ">
                                    <div class="col-md-2 control-label">
                                        <?php echo $LANG['UI_PLATFORM_SAFETY_USER'] ?>:
                                    </div>
                                    <div class="col-md-8">
                                        <select class="selectpicker show-tick bootstrap-mutiple-select" multiple data-live-search="true" data-actions-box="true" data-size="3" id="selectUser">

                                        </select>
                                    </div>
                                </div>
                                <div class="alert alert-block alert-info fade in ml10 mr10" id="marktips" style="margin-top: 60px;">
                                    <button type="button" class="close" data-dismiss="alert"></button>
                                    <h4 class="alert-heading">
                                        <strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong>
                                    </h4>
                                    <p><?php echo $LANG['UI_ROLE_ALLOC_USER_TIP1'];?></p>
                                    <div class="alert-line"></div>
                                    <ol class="alert-ol">
                                        <li>
                                            <?php echo $LANG['UI_ROLE_ALLOC_USER_TIP2'];?>
                                        </li>
                                        <li>
                                            <?php echo $LANG['UI_ROLE_ALLOC_USER_TIP3'];?>
                                        </li>
                                    </ol>
                                </div>
                            </form>

                        </div>
                        <div class="tab-pane" id="allousergrouptab">
                            <form action="#" class="form-horizontal">
                                <div class="form-group row">
                                    <div class="col-md-2 control-label" style="padding: 0;">
                                        <?php echo $LANG['UI_ROLE_NAME'] ?>:
                                    </div>
                                    <div class="col-md-8">
                                        <span id="usergroupRoleName"></span>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-2 control-label">
                                        <?php echo $LANG['UI_PLATFORM_SAFETY_USER_GROUP'] ?>:
                                    </div>
                                    <div class="col-md-8">
                                        <select class="selectpicker show-tick bootstrap-mutiple-select" multiple data-live-search="true" data-actions-box="true" data-size="3" id="selectUsergroup">

                                        </select>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- modal-footer -->
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
                <button type="button"class="btn btn-primary" id="userSubmit"><?php echo $LANG['UI_ROLE_ALLOCATION_TO_USER'] ?></button>
                <button type="button"class="btn btn-primary display-none" id="userGroupSubmit"><?php echo $LANG['UI_ROLE_ALLOCATION_TO_USER_GROUP'] ?></button>
            </div>
        </div>
        <!-- END MODAL -->

        <!-- BEGIN MODAL -->
        <div id="rolePermissionDiv" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
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
                        <li id="usergrouptab" class="display-none">
                            <a href="#usergroup_tab" data-toggle="tab" aria-expanded="false">
                                <i class="iconfont icon-usergroup"></i> <?php echo $LANG['UI_PLATFORM_SAFETY_USER_GROUP'] ?></a>
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
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane" id="usergroup_tab">
                            <div class="table-container">
                                <table class="table table-striped table-bordered table-hover" id="usergroup_table">
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane" id="permission_tab">
                            <div class="organ_trees">
                                <ul id="permission_tree" class="ztree bd1de5 ztree-fa" style="height: 315px;overflow-y:auto;"></ul>
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

<?php include_once 'role_drawer.php'; ?>

<!-- END PAGE CONTENT-->
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./scripts/platform/users/role.js"></script>
<!-- END PAGE LEVEL PLUGINS -->