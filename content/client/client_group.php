<?php
include_once '../../tpl/permission.php';
global $LANG;
?>
<!-- BEGIN PAGE LEVEL STYLES -->

<link rel="stylesheet" type="text/css"
      href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./css/client/client_group.css" />
<!-- END PAGE LEVEL STYLES -->

<!-- BEGIN PAGE CONTENT-->
<div class="col-md-3 client-group-wrap">
    <div class="client-group-wrap__header">
        <div class="btn-group dropdown-wrapper" id="organMenuWrapper">
            <button type="button" class="btn btn-primary dropdown-toggle btn-dropdown-operate"
                    type="button" data-toggle="dropdown" data-hover="dropdown" data-delay="1000"
                    data-close-others="true">
                <?php echo $LANG['UI_CLIENT_GROUP_MANAGER_BUTTON'] ?> <i class="fa fa-angle-down"></i>
            </button>
            <ul class="dropdown-menu" role="menu" id="organMenu">
                <li id="addGroup" class="<?php if (!in_array('p_client_group_add', $_SESSION['permissionArr']))
                    echo 'display-none' ?>">
                    <button class="btn dropdown-menu__item" type="button"><?php echo $LANG['UI_PUBLIC_ADD'] ?></button>
                </li>
                <li id="editGroup" class="<?php if (!in_array('p_client_group_modify', $_SESSION['permissionArr']))
                    echo 'display-none' ?>">
                    <button class="btn dropdown-menu__item"
                            type="button"><?php echo $LANG['UI_PUBLIC_MODIFY'] ?></button>
                </li>
                <li id="deleteGroup" class="<?php if (!in_array('p_client_group_delete', $_SESSION['permissionArr']))
                    echo 'display-none' ?>">
                    <button class="btn dropdown-menu__item"
                            type="button"><?php echo $LANG['UI_PUBLIC_DELETE'] ?></button>
                </li>
            </ul>
        </div>
    </div>
    <div class="client-group-wrap__content">
        <ul id="group_tree" class="ztree tree_div_vcde"></ul>
    </div>
</div>

<div class="col-md-9 client-group-table-wrap">
    <div class="client-group-table-wrap__header">
        <div class="table-toolbar">
            <div class="vin_toolbar mb-0" id="vin_client_toolbar2">
                <div class="leftTool">
                    <div class="btn-group dropdown-wrapper" id="moveMenuWrapper">
                        <button type="button" class="btn dropdown-toggle btn-primary btn-dropdown-operate"
                                type="button" data-toggle="dropdown" data-hover="dropdown" data-delay="1000"
                                data-close-others="true">
                            <?php echo $LANG['UI_CLIENT_MANAGER'] ?> <i class="fa fa-angle-down"></i>
                        </button>
                        <ul class="dropdown-menu" role="menu" id="moveMenu">
                            <li id="moveClient"
                                class="<?php if (!in_array('p_client_group_add_to_group', $_SESSION['permissionArr']))
                                    echo 'display-none' ?>">
                                <button class="btn dropdown-menu__item"
                                        type="button"><?php echo $LANG['UI_CLIENT_GROUP_MOVE_TO'] ?></button>
                            </li>
                            <li id="removeClient"
                                class="<?php if (!in_array('p_client_group_delete_from_group', $_SESSION['permissionArr']))
                                    echo 'display-none' ?>">
                                <button class="btn dropdown-menu__item"
                                        type="button"><?php echo $LANG['UI_CLIENT_GROUP_REMOVE_FROM'] ?></button>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="rightTool">
                    <div class="table-actions-wrapper page-right">
                        <input type="search" maxlength="128"
                               class="searchinput table-group-action-input form-control input-inline input"
                               placeholder="<?php echo $LANG['UI_SEARCH_HOST_NAME_IP'] ?>" aria-controls="example">
                        <input id="searchbtn" type="button" class="btn btn-search"
                               value="<?php echo $LANG['UI_PUBLIC_SEARCH'] ?>">
                    </div>
                    <div class="vin_btnToolbar2"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="table-container reset-td-width">
        <table class="table" id="clientTable2"></table>
    </div>

    <div class="alert alert-block alert-info fade in h-100px m0" id="client_group_table_tip">
        <button id="client_group_table_tip_close" type="button" class="close" data-dismiss="alert"></button>
        <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
        <ol class="alert-ol">
            <li>
                <?php echo $LANG['UI_CLIENT_GROUP_MANAGER_TIPS1'] ?>
            </li>
            <li>
                <?php echo $LANG['UI_CLIENT_GROUP_MANAGER_TIPS2'] ?>
            </li>
        </ol>
    </div>
</div>
<!-- END PAGE CONTENT-->

<!-- BEGIN MODAL -->
<div id="groupModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first"
     data-backdrop="static">
    <input id="groupuuid" class="display-none"></input>
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title addDiv">
            <i class="viconfont vicon-client_groups"></i>
            <span class="text"><?php echo $LANG['WEB_AGENT_GROUP_ADD'] ?></span>
        </h4>

        <h4 class="modal-title editDiv display-none">
            <i class="viconfont vicon-client_groups"></i>
            <span class="text"><?php echo $LANG['WEB_AGENT_GROUP_EDIT'] ?></span>
        </h4>
    </div>
    <div class="modal-body">
        <div class="portlet-body">
            <form action="#" id="clientGroupForm" class="form-horizontal">
                <div class="form-group">
                    <label class="col-md-3 control-label "><?php echo $LANG['UI_CLIENT_GROUP_NAME'] ?></label>
                    <div class="col-md-8">
                        <div class="input-icon right">
                            <i class="fa"></i>
                            <input id="groupName" name="groupname" type="text" maxlength="64"
                                   class="form-control input-sm">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label "><?php echo $LANG['UI_PUBLIC_REMARK'] ?></label>
                    <div class="col-md-8">
                        <div class="input-icon right">
                            <i class="fa"></i>
                            <input id="remark" name="remark" type="text" maxlength="1024" class="form-control input-sm">
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>

    <!-- modal-footer -->
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
        <button type="button" class="btn btn-primary addDiv"
                id="add_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
        <button type="button" class="btn btn-primary editDiv display-none"
                id="edit_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>
<!-- END MODAL -->
<!-- BEGIN MOVE MODAL -->
<div id="moveClientModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first"
     data-backdrop="static">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">
            <i class="viconfont vicon-client_groups"></i>
            <span class="text"><?php echo $LANG['UI_CLIENT_GROUP_MOVE_CLIENT_TO'] ?></span>
        </h4>

    </div>
    <div class="modal-body">
        <div class="portlet-body">
            <div class="form-group">
                <label class="col-md-3 control-label "><?php echo $LANG['UI_CLIENT_GROUP_SELECT'] ?></label>
                <div class="col-md-8">
                    <select class="form-control select2me" id="groupList">
                    </select>
                </div>
            </div>

        </div>
    </div>

    <!-- modal-footer -->
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
        <button type="button" class="btn btn-primary" id="move_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>
<!-- END MOVE MODAL -->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/additional-methods.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/client/client_group.js"></script>
<!-- END PAGE LEVEL PLUGINS -->