<?php
include_once '../../tpl/permission.php';
?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet"
    type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link rel="stylesheet" type="text/css"
    href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css" />
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<!-- END PAGE LEVEL STYLES -->

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
    <span class="curent"><?php echo $LANG['UI_PLATFORM_OBS'] ?></span>
</h3>
<!-- END PAGE HEADER-->

<!-- BEGIN PAGE CONTENT-->
<div class="resource-manager-wrap" id="obs_content">
    <div class="resource-manager-wrap__header">
        <span>
            <i class="levelchild viconfont vicon-a-Instructionzhiling-01 me-10"></i>
            <span class="resource-manager-wrap__header__text"><?php echo $LANG['UI_PLATFORM_OBS'] ?></span>
        </span>
    </div>
    <div class="resource-manager-wrap__content"> 
        <div class="obs-manager-toolbar" id="obs_manager_toolbar">
            <div class="tool-left">
                <div class="tool-left-item <?php if (!in_array('p_obsmanager_delete', $_SESSION['permissionArr'])) {
                    echo 'display-none';
                } ?>">
                    <button type="button" id="deleteOBStorage" disabled
                        class="btn btn-toolbar-delete disabled">
                        <i class="viconfont vicon-a-Deleteshanchu1"></i>
                    </button>
                </div>
                <div class="tool-left-item">
                    <div class="search input-group">
                        <input id="search" class="currentSearch customSearch"
                            style="min-width:200px;padding-right:30px" autocomplete="off" type="text"
                            placeholder="<?php echo $LANG['UI_ONS_MANAGER_SEARCH_PLACEHOLDER'] ?>">
                        <div class="position0" style="width:auto;height:34px">
                            <button class="b-btn clear hide position0" id="obs_clear_search"><i
                                    class="icon-close-small"></i></button>
                        </div>
                        <div class="search-btn positionL0" style="width:auto;height:34px;">
                            <button id="obs_search" class="b-btn search-btn"><i
                                    class="icon-search"></i></button>
                        </div>
                    </div>
                </div>
                <div class="tool-left-item <?php if (!in_array('p_obsmanager_add', $_SESSION['permissionArr'])) {
                    echo 'display-none';
                } ?>" style="margin-right: 0;">
                    <button type="button" id="addOBStorage" class="btn-font btn-title"
                        style="margin-left: 0 !important">
                        <i class="viconfont vicon-ge_add_task me-5"></i>
                        <?php echo $LANG['UI_PUBLIC_ADDNEW'] ?>
                    </button>
                </div>
                <div class="tool-left-item <?php if (!in_array('p_obsmanager_edit', $_SESSION['permissionArr'])) {
                    echo 'display-none';
                } ?>" style="margin-right: 0;">
                    <button type="button" id="editOBStorage" class="btn-font btn-title"
                        style="margin-left: 0 !important">
                        <i class="viconfont vicon-a-Editbianji1 me-5"></i>
                        <?php echo $LANG['UI_PUBLIC_MODIFY'] ?>
                    </button>
                </div>
                <div class="tool-left-item <?php if (!in_array('p_obsmanager_licence', $_SESSION['permissionArr'])) {
                    echo 'display-none';
                } ?>" style="margin-right: 0;">
                    <button type="button" id="authOBStoage" class="btn-font btn-title"
                        style="margin-left: 0 !important;display:none">
                        <i class="viconfont vicon-biaogeshouquan me-5"></i>
                        <?php echo $LANG['UI_SETTINGS_AUTH'] ?>
                    </button>
                </div>
                <div class="tool-left-item <?php if (!in_array('p_obsmanager_refresh', $_SESSION['permissionArr'])) {
                    echo 'display-none';
                } ?>" style="margin-right: 0;">
                    <button type="button" id="autoRefreshInterval" class="btn-font btn-title"
                        style="margin-left: 0 !important">
                        <i class="viconfont vicon-ge_refresh me-5"></i>
                        <?php echo $LANG['UI_VCENTER_SET_AUTO_REFRESH'] ?>
                    </button>
                </div>
            </div>
            <div class="tool-right rightTool vin_toolbar">
                <button type="button" class="btn btn-sm green-haze" id="advanceSearchBtn">
                    <i class="fa fa-search"></i>
                    <span><?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH']; ?></span>
                </button>
                <div class="vin_btnToolbar"></div>
            </div>
        </div>

        <div id="current_searchDiv" class="searchDiv search-content display-none">
            <?php echo $LANG['UI_PUBLIC_SEARCH_CONDITION']; ?>
            <span class="searchContent"></span>
            <span class="clearSearch"><?php echo $LANG['UI_SEARCH_CLEAR'] ?></span>
        </div>

        <div class="table-container obs-manager-table-container">
            <table id="obstorage_table"></table>
        </div>

        <div class="alert alert-block alert-info fade in h-100px m0" id="obs_manager_tip">
            <button id="obs_manager_tip_close" type="button" class="close" data-dismiss="alert"></button>
            <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
            <ol class="alert-ol">
                <li>
                    <?php echo $LANG['UI_OBS_MANAGER_TIPS1'] ?>
                </li>
                <li>
                    <?php echo $LANG['UI_OBS_MANAGER_TIPS2'] ?>
                </li>
            </ol>
        </div>
    </div>
</div>
<!-- END PAGE CONTENT-->

<!-- BEGIN ADD OR EDIT OBS MODAL -->
<div id="obs_modal" class="modal xmodal fade form-horizontal " tabindex="-1" data-backdrop="static">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title" id="obs_modal_title" style="display: flex;align-items: center"></h4>
    </div>
    <div class="modal-body">
        <form action="#" id="obs_form" class="form-horizontal">
            <div class="form-group">
                <label
                    class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_CLOUD_VENDOR']; ?></label>
                <div class="col-md-7">
                    <select id="vendor" name="vendor" class="form-control">
                        <option value="0" selected><?php echo $LANG['UI_STORAGE_CLOUD_AMAZONE']; ?> S3
                        </option>
                        <option value="1"><?php echo $LANG['UI_STORAGE_CLOUD_VENDOR_ALI']; ?> OSS</option>
                        <option value="2"><?php echo $LANG['UI_STORAGE_CLOUD_VENDOR_TENCENT']; ?> COS
                        </option>
                        <option value="3"><?php echo $LANG['UI_STORAGE_CLOUD_VENDOR_HUAWEI']; ?> OBS
                        </option>
                        <option value="4">Ceph S3</option>
                        <option value="5">Wasabi</option>
                        <option value="6">MinIO</option>
                        <!-- <option value="7">微软 Azure</option> -->
                        <option value="8">Huawei OceanStor Pacific</option>
                        <option value="9"><?php echo $LANG['UI_STORAGE_CLOUD_VENDOR_OTHER']; ?></option>
                    </select>
                    <span class="help-block"><?php echo $LANG['UI_OBS_MANAGER_TIPS1']; ?> <span
                            class="other-obs-des display-none"><?php echo $LANG['UI_OBS_MANAGER_OTHER_OBS_DES']; ?></span></span>
                </div>
            </div>
            <div class="form-group from-group-aws">
                <label
                    class="control-label col-md-3"><?php echo $LANG['UI_OBS_MANAGER_ACCOUNT_SUBZONE']; ?></label>
                <div class="col-md-7">
                    <select id="account_subzone" name="accountSubzone" class="form-control">
                        <option value="1">AWS China</option>
                        <option value="2">AWS Global</option>
                    </select>
                </div>
            </div>
            <div class="form-group private-cloud">
                <label class="col-md-3 control-label">
                    <span class="required display-none">* </span>
                    <?php echo $LANG['UI_OBS_MANAGER_SERVER_ENDPOINT']; ?>
                </label>
                <div class="col-md-7">
                    <div class="input-icon right">
                        <i class="fa"></i>
                        <input type="text" class="form-control" maxlength="128" name="endpoint_override"
                            id="endpoint_override">
                    </div>
                    <span
                        class="help-block"><?php echo $LANG['UI_OBS_MANAGER_SERVER_ENDPOINT_TIP']; ?></span>
                </div>
            </div>
            <div class="form-group">
                <label
                    class="control-label col-md-3 form-group-label"><?php echo $LANG['UI_STORAGE_CLOUD_AWS_SSL_CERTIFICATE_VERIFICATION']; ?></label>
                <div class="col-md-7 form-group-content">
                    <input type="checkbox" id="sslConnect" data-size="small" class="make-switch"
                        data-on-color="primary" data-off-color="info"
                        data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                        data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                </div>
            </div>
            <div class="public-private-cloud">
                <div class="form-group">
                    <label class="col-md-3 control-label">
                        <span class="required">* </span>
                        <?php echo $LANG['UI_NAS_CLOUD_USERNAME'] ?>
                    </label>
                    <div class="col-md-7">
                        <div class="input-icon right">
                            <i class="fa"></i>
                            <input type="text" class="form-control" maxlength="128" name="admin_name"
                                id="admin_name" placeholder="access key id">
                        </div>
                        <span class="help-block"><?php echo $LANG['UI_OBS_MANAGER_ACCESS_KEY_ID']; ?></span>
                    </div>
                </div>
                <div class="form-group form-group-password">
                    <label class="col-md-3 control-label">
                        <span class="required" id="password_required">* </span>
                        <?php echo $LANG['UI_NAS_CLOUD_PASSWORD'] ?>
                    </label>
                    <div class="col-md-7">
                        <div class="input-icon right">
                            <i class="fa"></i>
                            <input type="password" autocomplete="off" class="form-control" maxlength="128"
                                name="password" id="password" placeholder="secret access key"
                                oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')">
                        </div>
                        <span
                            class="help-block"><?php echo $LANG['UI_OBS_MANAGER_ACCESS_KEY_TIP']; ?></span>
                    </div>
                </div>
            </div>

            <div class="form-group azure-cloud">
                <label class="col-md-3 control-label">
                    <span class="required">* </span>
                    <?php echo $LANG['UI_ARCHIVE_CLOUD_CONNEC_STR']; ?>
                </label>
                <div class="col-md-7">
                    <div class="input-icon right">
                        <i class="fa"></i>
                        <input type="text" class="form-control" maxlength="64" name="connect_str"
                            id="connect_str">
                    </div>
                </div>
            </div>

            <!-- APPID -->
            <!-- <div class="form-group cos-cloud">
                <label class="col-md-3 control-label">
                    appid
                </label>
                <div class="col-md-7">
                    <div class="input-icon right">
                        <i class="fa"></i>
                        <input type="text" class="form-control" maxlength="64" name="appid" id="appid">
                    </div>
                    <span class="help-block"><?php echo $LANG['UI_OBS_MANAGER_APPID_TIP']; ?></span>
                </div>
            </div> -->

            <div class="form-group">
                <label class="col-md-3 control-label">
                    <span class="required">* </span>
                    <?php echo $LANG['UI_PLATFORM_OBS_NAME']; ?>
                </label>
                <div class="col-md-7">
                    <div class="input-icon right">
                        <i class="fa"></i>
                        <input type="text" class="form-control" maxlength="64" name="nickname"
                            id="nickname">
                    </div>
                    <span class="help-block"><?php echo $LANG['UI_PLATFORM_OBS_NAME_TIP']; ?></span>
                </div>
            </div>

            <div class="form-group">
                <label
                    class="control-label col-md-3 form-group-label"><?php echo $LANG['UI_CLOUD_PLATFORM_ENGINE']; ?></label>
                <div class="col-md-7 form-group-content">
                    <input type="checkbox" id="appliance_agency_flag" data-size="small" class="make-switch"
                        data-on-color="primary" data-off-color="info"
                        data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                        data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                    <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true"
                        data-placement="right"
                        data-content="<?php echo $LANG['UI_OBS_MANAGER_APPLIANCE_TIP'] ?>">
                        <i class="viconfont vicon-tishi"></i>
                    </a>
                </div>
            </div>
            <div class="form-group display-hide applianceselectdiv">
                <label
                    class="control-label col-md-3 applianceselectlabel form-group-label"><?php echo $LANG['UI_HADOOP_SELECT_PROXY']; ?></label>
                <div class="col-md-7 content">
                    <select class="form-control" id="applianceSelect"></select>
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <button type="button" data-dismiss="modal"
            class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
        <button type="button" class="btn btn-primary"
            id="obs_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>
<!-- END ADD OR EDIT OBS MODAL -->

<!-- BEGIN OBS DETAIL DRAWER -->
<div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" style="width: 500px"
    aria-labelledby="obs_detail_drawer_title" aria-hidden="true" id="obs_detail_drawer">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header">
            <div class="drawer-title" id="obs_detail_drawer_title"
                style="display:flex;justify-content:space-between;align-items:center">
                <div class="drawer-title-left">
                    <i class="viconfont vicon-tenant-detail mr10"></i>
                    <span><?php echo $LANG['UI_OBS_MANAGER_DETAIL']; ?></span>
                </div>
                <div class="drawer-title-right drawer-close" data-dismiss="drawer" aria-label="Close">
                    <i class="viconfont vicon-guanbi"></i>
                </div>
            </div>
        </div>
        <div class="drawer-body">
            <div class="drawer-body-form">
                <div class="drawer-body-form-item">
                    <div class="drawer-body-form-item__label">
                        <?php echo $LANG['UI_STORAGE_CLOUD_VENDOR']; ?>：
                    </div>
                    <div class="drawer-body-form-item__content drawer-vendor">

                    </div>
                </div>
                <div class="drawer-body-form-item from-item-account-type">
                    <div class="drawer-body-form-item__label">
                        <?php echo $LANG['UI_OBS_MANAGER_ACCOUNT_SUBZONE']; ?>：
                    </div>
                    <div class="drawer-body-form-item__content drawer-account-type">

                    </div>
                </div>
                <div class="drawer-body-form-item drawer-private-cloud">
                    <div class="drawer-body-form-item__label">
                        <?php echo $LANG['UI_OBS_MANAGER_SERVER_ENDPOINT']; ?>：
                    </div>
                    <div class="drawer-body-form-item__content drawer-endpoint">

                    </div>
                </div>
                <div class="drawer-body-form-item">
                    <div class="drawer-body-form-item__label">
                        <?php echo $LANG['UI_STORAGE_CLOUD_AWS_SSL_CERTIFICATE_VERIFICATION']; ?>：
                    </div>
                    <div class="drawer-body-form-item__content drawer-ssl-flag">

                    </div>
                </div>
                <div class="drawer-body-form-item drawer-public-private-cloud">
                    <div class="drawer-body-form-item__label">
                        <?php echo $LANG['UI_STORAGE_CLOUD_USERNAME']; ?>：
                    </div>
                    <div class="drawer-body-form-item__content drawer-username">

                    </div>
                </div>
                <div class="drawer-body-form-item drawer-azure-cloud">
                    <div class="drawer-body-form-item__label">
                        <?php echo $LANG['UI_ARCHIVE_CLOUD_CONNEC_STR']; ?>：
                    </div>
                    <div class="drawer-body-form-item__content drawer-connect-str">

                    </div>
                </div>
                <div class="drawer-body-form-item">
                    <div class="drawer-body-form-item__label">
                        <?php echo $LANG['UI_CLIENT_REPORT_CREATE_TIME']; ?>：
                    </div>
                    <div class="drawer-body-form-item__content drawer-create-time">

                    </div>
                </div>
                <div class="drawer-body-form-item">
                    <div class="drawer-body-form-item__label">
                        <?php echo $LANG['UI_CLIENT_REPORT_ONLINE_STATUS']; ?>：
                    </div>
                    <div class="drawer-body-form-item__content drawer-status">

                    </div>
                </div>
                <div class="drawer-body-form-item">
                    <div class="drawer-body-form-item__label">
                        <?php echo $LANG['UI_CLOUD_PLATFORM_ENGINE']; ?>：
                    </div>
                    <div class="drawer-body-form-item__content appliance-agency-flag">

                    </div>
                </div>
                <div class="drawer-body-form-item appliance-agency-item">
                    <div class="drawer-body-form-item__label">
                        <?php echo $LANG['UI_OBS_MANAGER_SELECT_AGENT']; ?>：
                    </div>
                    <div class="drawer-body-form-item__content appliance-agency">

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END OBS DETAIL DRAWER -->

<!-- BEGIN AUTO REFRESH OBS MODAL -->
<div id="auto_refresh_obs_modal" class="modal xmodal fade form-horizontal " tabindex="-1"
    data-focus-on="input:first" data-backdrop="static">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title"><i class="viconfont vicon-ge_refresh"></i>
            <?php echo $LANG['UI_VCENTER_SET_AUTO_REFRESH']; ?>
        </h4>
    </div>
    <div class="modal-body">
        <form class="form auto-refresh-form">
            <div class="row">
                <label
                    class="control-label col-md-4"><?php echo $LANG['UI_OBS_MANAGER_REFRESH_INTERVAL']; ?>
                </label>
                <div class="col-md-5">
                    <div class="input-group obs-autorefresh-input-group">
                        <input type="text" id="refreshValue" class="spinner-input form-control input-sm" style="text-align: center;"
                            onkeyup="value=value.replace(/[^\d]/g,'')">
                        <div class="spinner-buttons input-group-btn">
                            <button type="button" class="btn spinner-up default" style="height: 34px">
                                <i class="fa fa-angle-up"></i>
                            </button>
                            <button type="button" class="btn spinner-down default" style="height: 34px">
                                <i class="fa fa-angle-down"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <label class="control-label col-md-2 left-padding"><?php echo $LANG['WEB_UTILS_MINUTE'] ?>
                </label>
            </div>

            <div class="row mt-10">
                <div class="col-md-12">
                    <div class="alert alert-block alert-info fade in" id="marktips">
                        <button type="button" class="close" data-dismiss="alert"></button>
                        <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong>
                        </h4>
                        <ol class="alert-ol">
                            <li>
                                <?php echo $LANG['UI_OBS_MANAGER_REFRESH_INTERVAL_TIPS1']; ?>
                            </li>
                            <li>
                                <?php echo $LANG['UI_OBS_MANAGER_REFRESH_INTERVAL_TIPS2']; ?>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <button type="button" data-dismiss="modal"
            class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
        <button type="button" class="btn btn-primary"
            id="auto_refresh_obs_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>
<!-- END AUTO REFRESH OBS MODAL -->

<!-- BEGIN ADVANCED SEARCH MODAL -->
<div id="advanced_search_modal" class="modal xmodal fade form-horizontal" tabindex="-1"
    data-backdrop="static">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title"><i class="fa fa-search"></i>
            <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?>
        </h4>
    </div>
    <div class="modal-body">
        <div class="portlet-body" style="height: auto">
            <!-- 云服务商 -->
            <div class="list-option">
                <div class="row">
                    <label
                        class="control-label col-md-4"><?php echo $LANG['']; ?><?php echo $LANG['UI_STORAGE_CLOUD_VENDOR']; ?>：
                    </label>
                    <div class="col-md-6">
                        <select id="advanced_search_vendor" name="vendor" class="form-control">
                            <option value=""><?php echo $LANG['UI_PUBLIC_ALL'] ?></option>
                            <option value="0">
                                <?php echo $LANG['']; ?><?php echo $LANG['UI_STORAGE_CLOUD_AMAZONE']; ?> S3
                            </option>
                            <option value="1">
                                <?php echo $LANG['']; ?><?php echo $LANG['UI_STORAGE_CLOUD_VENDOR_ALI']; ?>
                                OSS</option>
                            <option value="2">
                                <?php echo $LANG['']; ?><?php echo $LANG['UI_STORAGE_CLOUD_VENDOR_TENCENT']; ?>
                                COS</option>
                            <option value="3">
                                <?php echo $LANG['']; ?><?php echo $LANG['UI_STORAGE_CLOUD_VENDOR_HUAWEI']; ?>
                                OBS</option>
                            <option value="4">Ceph S3</option>
                            <option value="5">Wasabi</option>
                            <option value="6">MinIO</option>
                            <!-- <option value="7">微软 Azure</option> -->
                            <option value="8"><?php echo $LANG['UI_STORAGE_CLOUD_VENDOR_OTHER']; ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- 服务端终端节点 -->
            <div class="list-option">
                <div class="row">
                    <label
                        class="control-label col-md-4"><?php echo $LANG['UI_OBS_MANAGER_SERVER_ENDPOINT']; ?>:
                    </label>
                    <div class="col-md-6">
                        <div class="input-icon right">
                            <i class="fa"></i>
                            <input type="text" class="form-control" maxlength="64" name="endpoint_override"
                                id="advanced_search_endpoint_override">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 对象存储别名 -->
            <div class="list-option">
                <div class="row">
                    <label class="control-label col-md-4"><?php echo $LANG['UI_PLATFORM_OBS_NAME']; ?>:
                    </label>
                    <div class="col-md-6">
                        <div class="input-icon right">
                            <i class="fa"></i>
                            <input type="text" class="form-control" maxlength="64" name="nickname"
                                id="advanced_search_nickname">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 对象存储添加时间 -->
            <div class="list-option">
                <div class="row">
                    <label
                        class="control-label col-md-4"><?php echo $LANG['UI_OBS_MANAGER_CREATE_TIME']; ?>：
                    </label>
                    <div class="col-md-6 daterangepickerdiv">
                        <input type="text" id="advanced_search_daterangepicker" class="form-control"
                            autocomplete="off" readonly
                            style="cursor: pointer;background-color:transparent;color:#666">
                        <!-- 时间选择禁用手动输入 -->
                        <i class="viconfont vicon-ge_calendar"></i>
                    </div>
                </div>
            </div>

            <!-- 状态 -->
            <div class="list-option">
                <div class="row">
                    <label class="control-label col-md-4"><?php echo $LANG['UI_PUBLIC_STATUS'] ?>：
                    </label>
                    <div class="col-md-6">
                        <select class="form-control select2me" id="advanced_search_status">
                            <option value=""><?php echo $LANG['UI_PUBLIC_ALL'] ?></option>
                            <option value="1"><?php echo $LANG['WEB_PLATFORM_DES_NORMAL']; ?></option>
                            <option value="0"><?php echo $LANG['WEB_AGENT_STATUS_OFFLINE']; ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- 创建者 -->
            <div class="list-option">
                <div class="row">
                    <label class="control-label col-md-4"><?php echo $LANG['UI_JOB_CREATOR']; ?>:
                    </label>
                    <div class="col-md-6">
                        <div class="input-icon right">
                            <i class="fa"></i>
                            <input type="text" class="form-control" maxlength="64" name="creator"
                                id="advanced_search_creator">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 所有者 -->
            <div class="list-option">
                <div class="row">
                    <label class="control-label col-md-4"><?php echo $LANG['UI_CLIENT_OWNER']; ?>:
                    </label>
                    <div class="col-md-6">
                        <div class="input-icon right">
                            <i class="fa"></i>
                            <input type="text" class="form-control" maxlength="64" name="owner"
                                id="advanced_search_owner">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- modal-footer -->
    <div class="modal-footer">
        <button type="button" data-dismiss="modal"
            class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
        <button type="button" class="btn btn-primary"
            id="advanceSearchSubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>
<!-- END ADVANCED SEARCH MODAL -->

<!-- BEGIN AUTH MODAL -->
<div id="obsAuthModal" class="modal xmodal fade form-horizontal" tabindex="-1" data-focus-on="input:first"
    data-backdrop="static">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title"><i class="icon-badge"></i> <?php echo $LANG['UI_OBS_AUTH'] ?>
        </h4>
    </div>
    <div class="modal-body">
        <div class="portlet-body">
            <div class="form-group alldirdiv">
                <div class="col-md-12">
                    <!-- 添加授权 -->
                    <div class="btn-group mb-20">
                        <button type="button" id="addObsAuth" class="btn btn-sm green-haze">
                            <i class="viconfont vicon-ge_authorization2"></i>
                            <?php echo $LANG['UI_VCENTER_AUTH_ADD'] ?>
                        </button>
                    </div>
                    <!-- 取消授权 -->
                    <div class="btn-group mb-20">
                        <button type="button" id="cancelObsAuth" class="btn btn-sm green-haze">
                            <i class="viconfont vicon-guanbi"></i>
                            <?php echo $LANG['UI_VCENTER_AUTH_DELETE'] ?>
                        </button>
                    </div>
                    <span id="obsAuthDes" class="floatRight mb-20" style="margin-top: 7px;"></span>
                    <table id="auth_table"></table>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" data-dismiss="modal"
            class="btn btn-default"><?php echo $LANG['UI_PUBLIC_OFF_TWO'] ?></button>
    </div>
</div>
<!-- END AUTH MODAL -->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<?php
if ($_SESSION['language'] != "en-us") {
    //如果不是英文,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
        $_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js">
</script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
<script src="./assets/global/plugins/jsencrypt/jsencrypt.min.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/s3/obsmanager.js"></script>
<!-- END PAGE LEVEL PLUGINS -->