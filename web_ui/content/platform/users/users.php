<?php include_once '../../../tpl/permission.php'; ?>
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"
    xmlns="http://www.w3.org/1999/html" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<!-- <link rel="stylesheet" type="text/css"
    href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css" /> -->


<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.css" rel="stylesheet" type="text/css" />
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="safety" href="./content/platform/users/safety_manager.php">
            <span>
                <?php echo $LANG['UI_PLATFORM_SAFETY'] ?>
            </span>
        </a>
    </li>
    <span>></span>
    <span class="curent">
        <?php echo $LANG['UI_PLATFORM_SAFETY_USER'] ?>
    </span>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
    <div class="col-md-12">
        <!-- Begin: life time stats -->
        <div class="portlet box blue-hoki" id="userMangerDiv">
            <div class="portlet-title">
                <div class="caption">
                     <i class="viconfont vicon-a-yonghubiaogeyong"></i>
                    <?php echo $LANG['UI_USER_LIST'] ?>
                </div>
            </div>
            <div class="portlet-body margin10">

                <div class="table-container">
                    <div class="vin_toolbar" id="vin_user_toolbar">
                        <div class="leftTool">
                        </div>

                        <div class="rightTool">
                            <div class="vin_btnToolbar">
                            </div>
                        </div>
                    </div>
                    <table id="user_table">
                    </table>
                    <div class="alert alert-block alert-info fade in" id="marktips">
                        <button type="button" class="close" data-dismiss="alert"></button>
                        <ul class="alert-ul">
                            <strong class="alert-ul-head">
                                <?php echo $LANG['UI_PUBLIC_TIPS'] ?>:
                            </strong>
                            <li>
                                <?php echo $LANG['UI_PLATFORM_USR_CLICK_USERROLE_NAME_CAT_INFO'] ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- End: life time stats -->

        <!-- BEGIN ADD DRAWER -->
        <div style="width: 600px;z-index: 10051;" class="drawer slide" data-placement="right" tabindex="-1" role="dialog"
            aria-labelledby="drawer-1-title" aria-hidden="true" id="add_user_drawer">
            <div class="drawer-content drawer-content-scrollable" role="document">
                <div class="drawer-header">
                    <h4 class="drawer-title" id="drawer-1-title">
                        <span id="drawer-1-title-1"><i class="viconfont vicon-danchuangtianjia1"></i></span>
                        <span style="vertical-align: top" id="drawer-1-title-2">
                            <?php echo $LANG['WEB_USERS_ADD_USER'] ?>
                        </span>
                        <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i
                                class="viconfont vicon-guanbi"></i></span>
                    </h4>
                </div>
                <div class="drawer-body">

                    <!-- BEGIN FORM-->
                    <form action="#" id="form_sample_2" class="form-horizontal">
                        <input type="password" autocomplete="new-password" hidden>
                        <div class="form-body">
                            <div class="alert alert-danger display-hide">
                                <button type="button" class="close" data-close="alert"></button>
                                <?php echo $LANG['UI_USER_BASE_INFO_TIPS'] ?>
                            </div>

                            <div class="form-group" style="margin-top: 15px;margin-left: -100px;">
                                <label class="control-label col-md-4 user-width40_en">
                                    <span class="required">
                                        * </span>
                                    <?php echo $LANG['UI_USER_TYPE'] ?>
                                </label>
                                <div class="col-md-8 user-width60_en">
                                    <select class="form-control select2me" id="usertype" name="usertype">
                                        <option value="1">
                                            <?php echo $LANG['UI_USER_LOCATION'] ?>
                                        </option>
                                        <option value="2">
                                            <?php echo $LANG['UI_USER_EXTERNAL'] ?>
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group domainDiv display-none" style="margin-left: -100px;">
                                <label class="control-label col-md-4 user-width40_en">
                                    <span class="required">
                                        * </span>
                                    <?php echo $LANG['UI_USER_EXTERNAL_PROVIDER'] ?>
                                </label>
                                <div class="col-md-4 user-width60_en">
                                    <select class="form-control select2me" id="domainlist" name="domainlist">
                                    </select>
                                </div>
                            </div>

                            <div class="form-group" style="margin-left: -100px;">
                                <label class="control-label col-md-4 user-width40_en">
                                    <span class="required">
                                        * </span>
                                    <?php echo $LANG['UI_LOGIN_USERNAME'] ?>
                                </label>
                                <div class="col-md-8 user-width60_en">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input type="text" maxlength="64" class="form-control" id="name" name="name" />
                                    </div>
                                </div>
                            </div>
                            <div class="form-group locationDiv" style="margin-left: -100px;">
                                <label class="control-label col-md-4 user-width40_en">
                                    <span class="required">
                                        * </span>
                                    <?php echo $LANG['UI_LOGIN_PASSWORD'] ?>
                                </label>
                                <div class="col-md-8 user-width60_en">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input type="password" autocomplete="off" maxlength="32" class="form-control"
                                            id="password" name="password"
                                            oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')" />
                                    </div>
                                </div>
                            </div>
                            <div class="form-group locationDiv" style="margin-left: -100px;">
                                <label class="control-label col-md-4 user-width40_en">
                                    <span class="required">
                                        * </span>
                                    <?php echo $LANG['UI_USER_CONFIRM'] ?>
                                </label>
                                <div class="col-md-8 user-width60_en">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input type="password" autocomplete="off" maxlength="32" class="form-control"
                                            name="rpassword"
                                            oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')" />
                                    </div>
                                </div>
                            </div>
                            <div class="form-group" style="margin-left: -100px;">
                                <label class="control-label col-md-4 user-width40_en">
                                    <span class="required">
                                    </span>
                                    <?php echo $LANG['UI_USER_EMAIL'] ?>
                                </label>
                                <div class="col-md-8 user-width60_en">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input type="text" maxlength="128" class="form-control" name="email" />
                                    </div>
                                </div>
                            </div>
                            <div class="form-group" style="margin-left: -100px;">
                                <label class="control-label col-md-4 user-width40_en">
                                    <span class="required">
                                    </span>
                                    <?php echo $LANG['UI_USER_PHONE'] ?>
                                </label>
                                <div class="col-md-8 user-width60_en">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input type="text" maxlength="20" class="form-control" name="number" />
                                    </div>
                                </div>
                            </div>
                            <div id="customePassword_div" class="form-group" style="margin-left: -100px;<?php if ($CONF['SYSTEM_INFO']['vendor'] != $CONF['VENDOR_LIST']['gmp']) {
                                echo 'display:none;';
                            } ?>">
                                <label class="control-label col-md-4 user-width40_en">
                                    <span class="required">
                                    </span>
                                    <?php echo $LANG['UI_USER_CUSTOM_PASSWORD_TITLE']; ?>
                                </label>
                                <div class="col-md-3">
                                    <button id="resetCustomePassword" class="btn btn-sm green-haze" type="button"><?php echo $LANG['UI_LOGIN_BUTTON_PASSWORD'];?></button>
                                </div>
                            </div>
                            <div class="form-group" style="margin-left: -100px;<?php if ($CONF['SYSTEM_INFO']['vendor'] != $CONF['VENDOR_LIST']['gmp']) {
                                echo 'display:none;';
                            } ?>">
                                <label class="control-label col-md-4 user-width40_en">
                                    <span class="required">
                                    </span>
                                    <?php echo $LANG['UI_USER_POSITION']; ?>
                                </label>
                                <div class="col-md-8 user-width60_en">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input type="text" maxlength="20" class="form-control" name="position" />
                                    </div>
                                </div>
                            </div>


                            <!--<div class="form-group tenantSelectDiv <?php if (!empty($_SESSION['tenantuuid'])) {
                                echo "display-none";
                            } ?> ">
                            <label class="control-label col-md-4"><?php echo $LANG['UI_PLATFORM_TENANT'] ?><span class="required">
                            * </span>
                            </label>
                            <div class="col-md-4">
                                <div class="input-icon right">
                                    <i class="fa"></i>
                                    <select class="form-control select2me" id="user_Tenant" name="userTenant">
                                    </select>
                                    <div><span class="help-block ">
                                    <?php echo $LANG['UI_USER_SELECT_TENANT_TIPS'] ?>
                                    </span></div>
                                </div>
                            </div>
                        </div> -->

                            <div class="form-group <?php if (($_SESSION['isThreePowers']) || !in_array('global_observer', $_SESSION['permissionArr'])) {
                                echo "display-none";
                            } ?>" id="div_global_auth" style="margin-left: -100px;">
                                <label class="control-label col-md-4 user-width40_en" style="margin-top:-6px;"><?php echo $LANG['UI_PLATFORM_GLOBAL_OBSERBER']?></label>
                                <div class="col-md-8 user-width60_en">
                                    <input type="checkbox"
                                           id="choose_global_flag"
                                           class="make-switch" data-on-color="primary"
                                           data-size="small" data-off-color="info"
                                           data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                    <div><span class="help-block ">
									<?php echo $LANG['UI_PLATFORM_GLOBAL_USER_TIPS']?>
									</span></div>
                                </div>
                            </div>

                            <div class="form-group <?php if (($_SESSION['isThreePowers'])) {
                                echo "display-none";
                            } ?>"
                                style="margin-left: -100px;">
                                <label class="control-label col-md-4 user-width40_en">
                                    <span class="required">
                                    </span>
                                    <?php echo $LANG['UI_PLATFORM_SAFETY_ROLE'] ?>
                                </label>
                                <div class="col-md-8 user-width60_en" id="muti-user">
                                    <div class="input-icon right">
                                        <select id="user_Role" name="userGroupRole"
                                            class="selectpicker show-tick bootstrap-mutiple-select" multiple data-live-search="true"
                                            data-actions-box="true">
                                        </select>
                                        <div><span class="help-block ">
                                                <?php echo $LANG['UI_USER_SELECT_ROLE_TIPS'] ?>
                                            </span></div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group <?php if (($_SESSION['isThreePowers'])) {
                                echo "display-none";
                            } ?>"
                                style="margin-left: -100px;">
                                <label class="control-label col-md-4 user-width40_en">
                                    <span class="required">
                                    </span>
                                    <?php echo $LANG['UI_PLATFORM_SAFETY_USER_GROUP'] ?>
                                </label>
                                <div class="col-md-8 user-width60_en" id="muti-user">
                                    <div class="input-icon right">
                                        <select id="user_Group" name="usergroup"
                                            class="selectpicker show-tick bootstrap-mutiple-select" multiple data-live-search="true"
                                            data-actions-box="true">
                                        </select>
                                        <div><span class="help-block ">
                                                <?php echo $LANG['UI_USER_SELECT_USER_GROUP_TIPS'] ?>
                                            </span></div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group" style="margin-left: -100px;">
                                <label class="control-label col-md-4 user-width40_en">
                                    <span class="required">* </span>
                                    <?php echo $LANG['UI_USER_BACKUP_STORAGE_CAPACITY'] ?>

                                </label>
                                <div class="col-md-4 user-width30_en">
                                    <select id="storageMode" class="form-control">
                                        <option value="1">
                                            <?php echo $LANG['UI_USER_UNLIMITED_CAPACITY'] ?>
                                        </option>
                                        <option value="2">
                                            <?php echo $LANG['UI_USER_CUSTOM_CAPACITY'] ?>
                                        </option>
                                    </select>
                                    <div id="custom" style="margin-top: 15px; display:inline-flex;">
                                        <div class="input-icon">
                                            <div id="spinnerNum" style="width: 140px;">
                                                <div class="input-group spinner-group" style="width:140px;">
                                                    <input type="text" id="spinnerNumInput" style="text-align: center;"
                                                        onkeyup="value=value.replace(/^(0+)|[^\d]+/g,'')"
                                                        class="spinner-input form-control" maxlength="3">
                                                    <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                        <button type="button" class="btn spinner-up default">
                                                            <i class="fa fa-angle-up"></i>
                                                        </button>
                                                        <button type="button" class="btn spinner-down default">
                                                            <i class="fa fa-angle-down"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="maxNum">
                                                <span id="maxNum" style="color: #737373;">
                                                    <?php echo $LANG['UI_USER_TOTAL_STORAGE'] ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="">
                                            <select id="unit"
                                                style="width: 80px; margin-left: 10px;height: 33px;text-align:center;border-color: #E6E6E6;outline: none;">
                                                <option value="1">MB</option>
                                                <option value="2">GB</option>
                                                <option value="3">TB</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1" style="margin-top: 10px;">
                                    <a class="popovers" data-container="body" data-trigger="hover"
                                        data-placement="right" data-content="<?php echo $LANG['UI_USER_QUOTA'] ?>">
                                        <i class="viconfont vicon-tishi"></i>
                                    </a>
                                </div>

                            </div>

                            <div class="form-group" style="margin-left: -100px;" id="force-edit-pwd">
                                <div class="control-label col-md-4 user-width40_en">
                                    <?php echo $LANG['UI_USER_FORCE_CHANGE_PASSWORD'] ?>
                                </div>
                                <div>
                                    <div class="col-md-4 user-width60_en">
                                        <input type="checkbox" id="editPassWord" class="make-switch"
                                            data-on-color="primary" data-off-color="info" data-size="small"
                                            data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                            data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                        <a class="popovers ml15" data-container="body" data-trigger="hover"
                                            data-placement="right"
                                            data-content="<?php echo $LANG['UI_PALTFORM_FIRST_CHANGE_PASSWORD'] ?>;">
                                            <i class="viconfont vicon-tishi"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group <?php if (!in_array('p_safety_user_manager_allocation', $_SESSION['permissionArr'])) {
                                echo "display-none";
                            } ?>"
                                id="concat_users" style="margin-left: -100px;">
                                <div class="control-label col-md-4 user-width40_en">
                                    <?php echo $LANG['WEB_USERS_CONCAT_USERS'] ?>
                                </div>
                                <div>
                                    <div class="col-md-4 user-width60_en">
                                        <input type="checkbox" id="concatUsers" class="make-switch"
                                            data-on-color="primary" data-off-color="info" data-size="small"
                                            data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                            data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                        <a class="popovers ml15" data-container="body" data-trigger="hover"
                                            data-placement="right"
                                            data-content="<?php echo $LANG['UI_USER_RESOURCE_ALLOCATION_CONFIRM'] ?>;">
                                            <i class="viconfont vicon-tishi"></i>
                                        </a>
                                    </div>

                                </div>
                            </div>

                            <div id="manage_user_div"
                                class="<?php if (!in_array('p_safety_user_manager_allocation', $_SESSION['permissionArr'])) {
                                    echo "display-none";
                                } ?>">
                                <div class="form-group " style="margin-left: -100px;">
                                    <label class="control-label col-md-4 user-width40_en">
                                        <?php echo $LANG['UI_USER_MANAGER_SELECT'] ?> <span class="required">
                                        </span>
                                    </label>
                                    <div class="col-md-8 user-width60_en">
                                        <select class="form-control select2me" id="manager" name="manager">
                                        </select>
                                    </div>
                                </div>

                                <!-- 选择权限 -->
                                <div class="form-group" style="margin-left: -100px;" style="margin-top: 15px;">
                                    <label class="control-label col-md-4 user-width40_en">
                                        <?php echo $LANG['UI_USER_PERMISSION_SELECT'] ?>
                                    </label>
                                    <div class="col-md-8 user-width60_en">
                                        <div class="table-container">
                                            <table id="select_manager_permission">
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </form>
                    <!-- END FORM-->
                </div>
                <!-- footer -->
                <div class="drawer-footer">
                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-6" style="float: right;padding-right: 10px;">
                                <button type="button" id="submit" class="btn green-haze btn-confirm">
                                    <?php echo $LANG['UI_PUBLIC_YES'] ?>
                                </button>
                                <button type="button" class="btn default cancel">
                                    <?php echo $LANG['UI_PUBLIC_NO'] ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END ADD DRAWER -->

        <!-- BEGIN USER_VIEW MODAL -->
        <div id="user_info_modal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first"
            data-backdrop="static">
            <div class="modal-header ">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title" style="display: flex;align-items: center;">
                    <svg width="12" height="18" style="margin-right: 11px;" viewBox="0 0 12 18" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M3.75 7.5C3.33579 7.5 3 7.83579 3 8.25C3 8.66421 3.33579 9 3.75 9H8.25C8.66421 9 9 8.66421 9 8.25C9 7.83579 8.66421 7.5 8.25 7.5H3.75Z"
                            fill="#333333" />
                        <path
                            d="M3 11.25C3 10.8358 3.33579 10.5 3.75 10.5H8.25C8.66421 10.5 9 10.8358 9 11.25C9 11.6642 8.66421 12 8.25 12H3.75C3.33579 12 3 11.6642 3 11.25Z"
                            fill="#333333" />
                        <path
                            d="M3.75 4.5C3.33579 4.5 3 4.83579 3 5.25C3 5.66421 3.33579 6 3.75 6H8.25C8.66421 6 9 5.66421 9 5.25C9 4.83579 8.66421 4.5 8.25 4.5H3.75Z"
                            fill="#333333" />
                        <path
                            d="M1.5 0.75C0.671554 0.75 0 1.42158 0 2.25V16.5C0 16.7809 0.15701 17.0383 0.406814 17.1669C0.656617 17.2954 0.957318 17.2736 1.18593 17.1103L3.375 15.5467L5.56407 17.1103C5.82484 17.2966 6.17516 17.2966 6.43593 17.1103L8.625 15.5467L10.8141 17.1103C11.0427 17.2736 11.3434 17.2954 11.5932 17.1669C11.843 17.0383 12 16.7809 12 16.5V2.25C12 1.42158 11.3284 0.75 10.5 0.75H1.5ZM1.5 2.25H10.5V15.0426L9.06093 14.0147C8.80016 13.8284 8.44984 13.8284 8.18907 14.0147L6 15.5783L3.81093 14.0147C3.55016 13.8284 3.19984 13.8284 2.93907 14.0147L1.5 15.0426V2.25Z"
                            fill="#333333" />
                    </svg>
                    <?php echo $LANG['UI_RESOURCE_GROUP_RELATED_INFO'] ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="tabbable-custom max-height500">
                    <ul class="nav nav-tabs ">
                        <li id="usergrouptab" class="active">
                            <a href="#resource_tab" data-toggle="tab" aria-expanded="false">
                                <i class="viconfont vicon-a-Benzbenchi"></i>
                                <?php echo $LANG['UI_TENANT_RESOURCE'] ?>
                            </a>
                        </li>
                        <li class="display-none" id="resourcegrouptab">
                            <a href="#resourcegroup_tab" data-toggle="tab" aria-expanded="false">
                                <i class="viconfont vicon-resource_group"></i>
                                <?php echo $LANG['UI_PLATFORM_RESOURCE_GROUP'] ?>
                            </a>
                        </li>
                        <li class="display-none" id="permissiontab">
                            <a href="#permission_tab" data-toggle="tab" aria-expanded="false">
                                <i class="viconfont vicon-a-Equalizerjunhengqi"></i>
                                <?php echo $LANG['UI_ROLE_PERMISSION'] ?>
                            </a>
                        </li>

                    </ul>
                    <div class="tab-content" style="padding: 16px 20px;">
                        <div class="tab-pane active" id="resource_tab">
                            <div class="table-container">
                                <div class="vin_toolbar" id="vin_resource_toolbar">
                                    <div class="leftTool">
                                        <div class='customBtn1'></div>
                                    </div>

                                    <div class="rightTool">
                                        <div class="vin_btnToolbar">
                                        </div>
                                    </div>
                                </div>
                                <table id="resource_table">

                                </table>
                            </div>
                        </div>

                        <div class="tab-pane" id="resourcegroup_tab">
                            <div class="table-container">
                                <div class="vin_toolbar" id="vin_resource_group_toolbar">
                                    <div class="leftTool">
                                        <div class='customBtn1'></div>
                                    </div>

                                    <div class="rightTool">
                                        <div class="vin_btnToolbar">
                                        </div>
                                    </div>
                                </div>
                                <table id="resourcegroup_table">
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane" id="permission_tab">
                            <div class="organ_trees">
                                <ul id="permission_tree" class="ztree bd1de5 ztree-fa"
                                    style="height: 315px;overflow-y:auto;"></ul>
                                <div class="alert alert-block alert-info fade in" id="nodatatips">
                                    <button type="button" class="close" data-dismiss="alert"></button>
                                    <ul class="alert-ul">
                                        <strong class="alert-ul-head">
                                            <?php echo $LANG['UI_PUBLIC_TIPS'] ?>:
                                        </strong>
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
                <button type="button" data-dismiss="modal" class="btn btn-default">
                    <?php echo $LANG['UI_PUBLIC_CLOSE'] ?>
                </button>
            </div>
        </div>
        <!-- END USER_VIEW MODAL -->

        <!-- BEGIN ALLOCATION MODAL -->
        <div id="resource_allocation_modal" class="modal xmodal fade form-horizontal " tabindex="-1"
            data-focus-on="input:first" data-backdrop="static">
            <div class="modal-header ">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title"><i class="viconfont vicon-a-Pie-twojindu2"></i>
                    <?php echo $LANG['UI_RESOURCE_GROUP_ALLOCATION_RESOURCE'] ?>
                </h4>
            </div>
            <div class="modal-body">
                <!-- 选择资源 -->
                <div class="form-group" style="margin-top: 15px;margin-left: -100px;">
                    <label class="control-label col-md-3">
                        <?php echo $LANG['UI_STORAGE_SELECT_RES'] ?>
                    </label>
                    <div class="col-md-9">
                        <select class="form-control select2me" id="select_resource" name="">
                            <option value="1">
                                <?php echo $LANG['UI_TENANT_RESOURCE'] ?>
                            </option>
                            <option value="2">
                                <?php echo $LANG['UI_PLATFORM_RESOURCE_GROUP'] ?>
                            </option>
                        </select>
                    </div>
                </div>

                <!-- 资源类型 -->
                <div class="form-group" style="margin-top: 15px;margin-left: -100px;">
                    <label class="control-label col-md-3">
                        <?php echo $LANG['WEB_USERS_RESOURCE_TYPE'] ?>
                    </label>
                    <div class="col-md-9">
                        <select class="form-control select2me" id="resource_type" name="usertype">
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

                <!-- 选择资源/组 -->
                <div class="form-group" style="margin-top: 15px;margin-left: -100px;">
                    <label class="control-label col-md-3">
                        <?php echo $LANG['UI_STORAGE_SELECT_RES_GROUP'] ?>
                    </label>
                    <div class="col-md-9">
                        <div class="table-container">
                            <div class="vin_toolbar" id="vin_select_resource_toolbar">
                                <div class="leftTool">
                                </div>
                                <div class="col-md-8">
                                    <select class="form-control select2me display-none" id="vmtype" name="vmtype" style="width:235px; height:34px;">
                                    </select>
                                    <select class="form-control select2me display-none" id="vmPrivatetype" name="vmPrivatetype" style="width:235px; height:34px;">
                                    </select>
                                    <select class="form-control select2me display-none" id="vmPublictype" name="vmPublictype" style="width:235px; height:34px;">
                                    </select>
                                </div>


                                <div class="rightTool">
                                    <div class="vin_btnToolbar">
                                    </div>
                                </div>
                            </div>
                            <table id="select_resource_table">
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <div class="form-actions">
                    <div class="row">
                        <div class="col-md-7" style="float: right;padding-right: 20px;">
                            <button type="button" class="btn default cancel">
                                <?php echo $LANG['UI_PUBLIC_NO'] ?>
                            </button>
                            <button type="button" class="btn green-haze btn-confirm add_submit">
                                <?php echo $LANG['UI_PUBLIC_YES'] ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- END ALLOCATION MODAL -->

        <!-- BEGIN TRANSFER MODAL -->
        <div id="resource_transfer_modal" class="modal xmodal fade form-horizontal " tabindex="-1"
            data-focus-on="input:first" data-backdrop="static">
            <div class="modal-header ">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title"><i class="viconfont vicon-a-Nested-arrowsqiantaojiantou"></i>
                    <?php echo $LANG['UI_STORAGE_TRANSFER'] ?>
                </h4>
            </div>
            <div class="modal-body">
                <!-- 选择接收用户 -->
                <div class="form-group" style="margin-top: 15px;margin-left: -100px;">
                    <label class="control-label col-md-4">
                        <?php echo $LANG['UI_STORAGE_SELECT_RECEIVE'] ?>
                    </label>
                    <div class="col-md-7">
                        <select class="form-control select2me" id="select_receive_user" name="">

                        </select>
                    </div>
                </div>

                <!-- 选择资源 -->
                <div class="form-group" style="margin-top: 15px;margin-left: -100px;">
                    <label class="control-label col-md-4">
                        <?php echo $LANG['UI_STORAGE_SELECT_RES'] ?>
                    </label>
                    <div class="col-md-7">
                        <div id="resource_div" class="checkbox-area" style="padding: 5px 0;">
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <div class="form-actions">
                    <div class="row">
                        <div class="col-md-7" style="float:right;padding-right:20px;">
                            <button type="button" class="btn default cancel">
                                <?php echo $LANG['UI_PUBLIC_NO'] ?>
                            </button>
                            <button type="button" class="btn green-haze btn-confirm add_submit">
                                <?php echo $LANG['UI_PUBLIC_YES'] ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- END TRANSFER MODAL -->

        <!-- BEGIN MANAGER_ALLOC MODAL -->
        <div id="manager_allocation_modal" class="modal xmodal fade form-horizontal " tabindex="-1"
            data-focus-on="input:first" data-backdrop="static">
            <div class="modal-header ">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title"><i class="viconfont vicon-a-People-bottom-cardrenxiangkapianxia"></i>
                    <?php echo $LANG['UI_USER_MANAGER_ALLOCATION'] ?>
                </h4>
            </div>
            <div class="modal-body">
                <!-- 选择管理用户 -->
                <div class="form-group resource-ml80_en" style="margin-top: 15px;margin-left: -100px;">
                    <label class="control-label col-md-4">
                        <?php echo $LANG['UI_USER_MANAGER_SELECT'] ?>
                    </label>
                    <div class="col-md-7">
                        <select class="form-control select2me" id="select_manager_user" name="">

                        </select>
                    </div>
                </div>

                <!-- 选择权限 -->
                <div class="form-group resource-ml80_en" style="margin-top: 15px;margin-left: -100px;">
                    <label class="control-label col-md-4">
                        <?php echo $LANG['UI_USER_PERMISSION_SELECT'] ?>
                    </label>
                    <div class="col-md-7">
                        <div class="table-container">
                            <table id="select_permission">
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <div class="form-actions">
                    <div class="row">
                        <div class="col-md-7" style="float:right;padding-right:20px;">
                            <button type="button" class="btn default cancel">
                                <?php echo $LANG['UI_PUBLIC_NO'] ?>
                            </button>
                            <button type="button" class="btn green-haze btn-confirm add_submit">
                                <?php echo $LANG['UI_PUBLIC_YES'] ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- END MANAGER_ALLOC MODAL -->

        <!-- BEGIN ROLE_ALLOC MODAL -->
        <div id="role_allocation_modal" class="modal xmodal fade form-horizontal " tabindex="-1"
            data-focus-on="input:first" data-backdrop="static">
            <div class="modal-header ">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title"><i class="viconfont vicon-fenfa"></i>
                    <?php echo $LANG['UI_USER_AUTH_MANAGE'] ?>
                </h4>
            </div>
            <div class="modal-body">
                <!-- 选择角色 -->
                <div class="form-group" style="margin-top: 15px;margin-left: -100px;">
                    <label class="control-label col-md-4">
                        <?php echo $LANG['UI_PLATFORM_SAFETY_ROLE'] ?>
                    </label>
                    <div class="col-md-8">
                        <select class="form-control select2me" id="select_role_user" name="">

                        </select>
                    </div>
                </div>

                <!-- 选择权限 -->
                <div class="form-group" style="margin-top: 15px;margin-left: -100px;">
                    <label class="control-label col-md-4">
                        <?php echo $LANG['UI_USER_PERMISSION_SELECT'] ?>
                    </label>
                    <div class="col-md-8">
                        <div class="table-container">
                            <ul id="permissionTree" class="ztree bd1de5  tree_div ztree-fa"
                                style="height:289px;overflow-y:scroll;"></ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <div class="form-actions">
                    <div class="row">
                        <div class="col-md-offset-6 col-md-7">
                            <button type="button" class="btn default cancel">
                                <?php echo $LANG['UI_PUBLIC_NO'] ?>
                            </button>
                            <button type="button" class="btn green-haze btn-confirm add_submit">
                                <?php echo $LANG['UI_PUBLIC_YES'] ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- END ROLE_ALLOC MODAL -->

        <!-- BEGIN RESET_CUSTOMEPASSWORD MODAL -->
        <div id="reset_customepassword_modal" class="modal xmodal fade form-horizontal" tabindex="-1" data-backdrop="static">
            <div class="modal-header ">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title"><i class="viconfont vicon-Frame15"></i>
                    <?php echo $LANG['UI_USER_RESET_SAFE_PWD_SUCCESS'];?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="portlet-body">
                    <div class="col-md-12" style="margin-top: 16px;">
                        <div class="col-md-2 share-title">
                            <?php echo $LANG['UI_NAS_CLOUD_USERNAME']?>
                        </div>
                        <div class="input-icon col-md-10" id="user_name">
                        </div>
                    </div>
                    <div class="col-md-12" style="margin-top: 16px;">
                        <div class="col-md-2 share-title">
                            <?php echo $LANG['UI_USER_CUSTOM_PASSWORD']?>
                        </div>
                        <div class="input-icon col-md-10" id="custome_password">
                        </div>
                    </div>
                </div>
            </div>
            <!-- modal-footer -->
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn green-haze" id="close_reset_customepassword_modal"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
            </div>
        </div>
        <!-- END RESET_CUSTOMEPASSWORD MODAL -->

    </div>
</div>
<!-- END PAGE CONTENT-->

<input type="hidden" id="is_three_powers" value="<?php echo $_SESSION['isThreePowers']; ?>" />
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<!-- <script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script> -->
<!-- <script type="text/javascript"
    src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script> -->
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jsencrypt/jsencrypt.min.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/platform/users/users.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<?php
if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
    //如果是中文简体|中文繁体,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
        $_SESSION['language'] . '.js" type="text/javascript"></script>';
} else if ($_SESSION['language'] != "en-us" && $_SESSION['language'] != "zh-cn" && $_SESSION['language'] != "zh-tw") {
    //如果不是英文|中文简体|中文繁体,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages_' .
        $_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<!-- <script src="./scripts/libs/md5.js" type="text/javascript"></script> -->
<!-- <script src="./scripts/platform/users/add_user.js" type="text/javascript"></script> -->