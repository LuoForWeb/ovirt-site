<?php
include_once '../../tpl/permission.php';
global $CONF, $LANG;
$userAllPermission = $_SESSION['permission'];
$safeAdminPermission = $_SESSION['isThreePowers'] && $_SESSION['userLevel'] == $CONF['THREE_POWERS_USER']['safeadmin'];
$loadOldTableFlag = intval($_GET['load_old_table_flag']) ?? 0;
?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css"
      href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/jquery-file-upload/css/jquery.fileupload.css" rel="stylesheet"/>
<!--<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />-->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css"/>
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="./css/client/client_manager.css" type="text/css">
<?php
if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
    echo '<link href="./css/platform/lang/zh-cn.css" rel="stylesheet" type="text/css"/>';
} else {
    echo '<link href="./css/platform/lang/en-us.css" rel="stylesheet" type="text/css"/>';
}
?>
<style>
    .bootstrap-select .dropdown-menu {
        z-index: 99999 !important;
    }
</style>
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE CONTENT-->
<input type="text" hidden value="<?php echo $loadOldTableFlag ?>" id="loadOldTableFlag"/>
<div class="table-toolbar" id="clientlist">
    <div class="vin_toolbar mb-0" id="vin_client_toolbar">
        <div class="leftTool"></div>
        <div class="rightTool">
            <div class="customBtn3"></div>
            <div class="vin_client_btnToolbar"></div>
        </div>
    </div>
</div>

<div id="current_searchDiv" class="search-content searchDiv display-none">
    <?php echo $LANG['UI_PUBLIC_SEARCH_CONDITION']; ?>
    <span class="searchContent"></span>
    <span class="clearSearch"><?php echo $LANG['UI_SEARCH_CLEAR'] ?></span>
</div>

<div class="table-container reset-td-width client-manager-table-container">
    <table class="table" id="clientDatatable"></table>
</div>

<!-- 提示信息 -->
<div class="alert alert-block alert-info fade in mb-0" id="client_manager_tip">
    <button id="client_manager_tip_close" type="button" class="close" data-dismiss="alert"></button>
    <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
    <ol class="alert-ol">
        <li>
            <?php echo $LANG['UI_CLIENT_MANAGER_TIPS1'] ?>
        </li>
        <li>
            <?php echo $LANG['UI_CLIENT_MANAGER_TIPS3'] ?>
        </li>
        <li>
            <?php echo $LANG['UI_CLIENT_MANAGER_TIPS4'] ?>
        </li>
    </ol>
</div>

<!-- BEGIN ADD MODAL -->
<div id="addModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first"
     data-backdrop="static">
    <div class="modal-header portlet box blue-hoki">
        <div class="portlet-title">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            <ul class="nav nav-tabs floatl">
                <li class="addli active">
                    <a href="#adddiv" class="mt-0" data-toggle="tab">
                        <?php echo $LANG['UI_CLIENT_MANUAL_ADD'] ?>
                    </a>
                </li>
                <li class="deploymentli">
                    <a href="#deploymentdiv" class="mt-0" data-toggle="tab">
                        <?php echo $LANG['UI_CLIENT_REMOTE_DEPLOY'] ?>
                    </a>
                </li>

            </ul>
        </div>
    </div>
    <div class="modal-body ">
        <div class="portlet-body">
            <div class="tab-content row margin10">

                <div class="tab-pane active" id="adddiv">
                    <div class="col-md-12 alert alert-info ">
                        <button type="button" class="close" data-dismiss="alert"></button>
                        <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                        <ol class="alert-ol">
                            <li>
                                <?php echo $LANG['UI_CLIENT_MANUAL_ADD_TIPS1'] ?>
                            </li>
                            <li>
                                <?php echo $LANG['UI_CLIENT_MANUAL_ADD_TIPS2'] ?>
                            </li>
                            <li>
                                <?php echo $LANG['UI_CLIENT_MANUAL_ADD_TIPS3'] ?>
                            </li>
                        </ol>
                    </div>
                    <form action="#" id="addForm" class="form-horizontal">
                        <div class="form-group ">
                            <label class="col-md-3 control-label" for="agentType">
                                <?php echo $LANG['UI_CLIENT_AGENT_TYPE'] ?>
                            </label>
                            <div class="col-md-8">
                                <select class="form-control select2me" id="agentType">
                                    <option value="1"><?php echo $LANG['UI_JOB_CLIENT'] ?></option>
                                    <option value="4"><?php echo $LANG['UI_PLATFORM_APPLIANCE'] ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group ">
                            <label class="col-md-3 control-label" for="ipaddress1" id="ipaddress1Label">
                                <?php echo $LANG['UI_PUBLIC_IP_ADDRESS'] ?>
                            </label>
                            <div class="col-md-8">
                                <div class="input-icon right">
                                    <i class="fa"></i>
                                    <input type="text" class="form-control" maxlength="128" name="ipaddress"
                                           id="ipaddress1" placeholder="192.168.1.110">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="nickname1">
                                <?php echo $LANG['UI_VCENTER_RNAME'] ?>
                            </label>
                            <div class="col-md-8 ">
                                <div class="input-icon right">
                                    <i class="fa"></i>
                                    <input type="text" class="form-control" maxlength="64" name="nickname"
                                           id="nickname1">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3" for="connectport1">
                                <span id="clientConnectPortLabel"><?php echo $LANG['UI_CLIENT_COMMUNICATE_PORT'] ?></span>
                                <span id="proxyConnectPortLabel"><?php echo $LANG['UI_CLIEBT_COMMUNICATE_PORT_PROXY'] ?></span>
                            </label>
                            <div class="col-md-8">
                                <div class="input-icon right">
                                    <i class="fa"></i>
                                    <input type="text" class="form-control input-sm" maxlength="128" name="connectport"
                                           id="connectport1" oninput="this.value=this.value.replace(/[^\d]/g,'')">
                                </div>
                            </div>
                        </div>

                        <!-- 是否自动切换可用网络 -->
                        <div class="form-group manualAutoChangeNetworkFlagDiv">
                            <label class="col-md-3 control-label" for="manualAutoChangeNetworkFlag">
                                <?php echo $LANG['UI_CLIENT_AUTO_CHANGE_NETWORK_FLAG'] ?>
                            </label>
                            <div class="col-md-8 d-flex mt-4">
                                <input type="checkbox" id="manualAutoChangeNetworkFlag" class="make-switch"
                                       data-on-color="primary"
                                       data-off-color="info" data-size="small"
                                       data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                <a class="ml12 mt-2 popovers" data-container="body" data-trigger="hover"
                                   data-placement="right"
                                   data-content="<?php echo $LANG['UI_CLIENT_AUTO_CHANGE_NETWORK_FLAG_TIPS']; ?>">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                            </div>
                        </div>
                        <div class="form-group appliedVcentersDiv display-none">
                            <label class="control-label col-md-3">
                                <span><?php echo $LANG['UI_CLIENT_APPLY_VCENTERS'] ?></span>
                            </label>
                            <div class="col-md-8">
                                <select class="bootstrap-mutiple-select select2me selectpicker show-tick ignore"
                                        id="applied_vcenters" multiple data-live-search="true"
                                        data-actions-box="true">
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="tab-pane " id="deploymentdiv">
                    <div class="col-md-12 alert alert-info ">
                        <button type="button" class="close" data-dismiss="alert"></button>
                        <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                        <ol class="alert-ol">
                            <li>
                                <?php echo $LANG['UI_CLIENT_REMOTE_DEPLOY_TIPS1'] ?>
                            </li>
                            <li>
                                <?php echo $LANG['UI_CLIENT_REMOTE_DEPLOY_TIPS2'] ?>
                            </li>
                            <li>
                                <?php echo $LANG['UI_CLIENT_REMOTE_DEPLOY_TIPS3'] ?>
                            </li>
                            <li>
                                <?php echo $LANG['UI_CLIENT_REMOTE_DEPLOY_TIPS4'] ?>
                            </li>
                            <li>
                                <?php echo $LANG['UI_CLIENT_REMOTE_DEPLOY_TIPS5'] ?>
                            </li>
                        </ol>
                    </div>

                    <form action="#" id="clientForm" class="form-horizontal">
                        <div class="form-group addTypeDiv">
                            <label class="col-md-3 control-label" for="addType">
                                <?php echo $LANG['UI_CLIENT_ADD_THE_WAY'] ?>
                            </label>
                            <div class="col-md-8">
                                <select class="form-control select2me" id="addType">
                                    <option value="1"><?php echo $LANG['UI_CLIENT_ADD_SIMPLE'] ?></option>
                                    <option value="2"><?php echo $LANG['UI_CLIENT_ADD_BATCH'] ?></option>
                                </select>
                                <span class="help-block">
                                    </span>
                            </div>
                        </div>
                        <div class="form-group simpleDiv">
                            <label class="col-md-3 control-label" for="agentTypeRemote">
                                <?php echo $LANG['UI_CLIENT_AGENT_TYPE'] ?>
                            </label>
                            <div class="col-md-8">
                                <select class="form-control select2me" id="agentTypeRemote" disabled>
                                    <option value="1"><?php echo $LANG['UI_CLIENT_AGENT_TYPE_CLIENT'] ?></option>
                                    <option value="4"><?php echo $LANG['UI_CLIENT_AGENT_TYPE_TRANSPORT'] ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group simpleDiv">
                            <label class="col-md-3 control-label" for="deployOsType">
                                <?php echo $LANG['UI_LOGIN_AGENT_FILE_SYSTEM'] ?>
                            </label>
                            <div class="col-md-8">
                                <select class="form-control select2me ossystem_select" id="deployOsType">
                                </select>
                            </div>
                        </div>
                        <div class="form-group fsagent display-hide">
                            <label class="col-md-3 control-label" for="deployFilesystemType">
                                <?php echo $LANG['UI_LOGIN_AGENT_VERSION_ARC'] ?>
                            </label>
                            <div class="col-md-8">
                                <select class="form-control select2me filesystem_select" id="deployFilesystemType">
                                </select>
                            </div>
                        </div>
                        <div class="form-group simpleDiv">
                            <label class="col-md-3 control-label" for="ipaddress">
                                <?php echo $LANG['UI_PUBLIC_IP_ADDRESS'] ?>
                            </label>
                            <div class="col-md-8">
                                <div class="input-icon right">
                                    <i class="fa"></i>
                                    <input type="text" class="form-control" maxlength="128" name="ipaddress"
                                           id="ipaddress" placeholder="192.168.1.110">
                                </div>
                            </div>
                        </div>
                        <div class="form-group simpleDiv">
                            <label class="col-md-3 control-label" for="adminname">
                                <?php echo $LANG['UI_CLIENT_CONNECT_USERNAME'] ?>
                            </label>
                            <div class="col-md-8">
                                <div class="input-icon right">
                                    <i class="fa"></i>
                                    <input type="text" class="form-control" maxlength="64" name="adminname"
                                           id="adminname">
                                    <div><span class="help-block ">
                                    <?php echo $LANG['UI_CLIENT_CONNECT_USERNAME_TIPS'] ?>
                                    </span></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group simpleDiv">
                            <label class="col-md-3 control-label" for="password">
                                <?php echo $LANG['UI_CLIENT_CONNECT_PASSWORD'] ?>
                            </label>
                            <div class="col-md-8 ">
                                <div class="input-icon right">
                                    <i class="fa"></i>
                                    <input type="password" autocomplete="off" class="form-control" maxlength="64"
                                           name="password" id="password"
                                           oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')">
                                    <div><span class="help-block ">
                                    <?php echo $LANG['UI_CLIENT_CONNECT_PASSWORD_TIPS'] ?>
                                    </span></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group simpleDiv">
                            <label class="col-md-3 control-label" for="nickname">
                                <?php echo $LANG['UI_VCENTER_RNAME'] ?>
                            </label>
                            <div class="col-md-8 ">
                                <div class="input-icon right">
                                    <i class="fa"></i>
                                    <input type="text" class="form-control" maxlength="64" name="nickname"
                                           id="nickname">
                                </div>
                            </div>
                        </div>

                        <div class="form-group display-none">
                            <label class="control-label col-md-3" for="clientport">
                                <span class=""><?php echo $LANG['UI_CLIENT_TRANSFER_PORT'] ?></span>
                            </label>
                            <div class="col-md-8">
                                <div class="input-icon right">
                                    <i class="fa"></i>
                                    <input type="text" class="form-control input-sm" maxlength="128" name="clientport"
                                           id="clientport">
                                </div>
                            </div>
                        </div>
                        <div class="form-group display-none configDiv">
                            <label class="control-label col-md-3" for="connectType">
                                <?php echo $LANG['UI_CLIENT_COMMUNICATION'] ?>
                            </label>
                            <div class="col-md-8">
                                <select class="form-control select2me" id="connectType">
                                    <option value="2"><?php echo $LANG['UI_CLIENT_CLIENT_TO_SERVER'] ?></option>
                                    <option value="1"><?php echo $LANG['UI_CLIENT_SERVER_TO_CLIENT'] ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group display-none configDiv">
                            <label class="control-label col-md-3" for="connectport">
                                <span class="display-none clientlabel"><?php echo $LANG['UI_CLIENT_COMMUNICATE_PORT'] ?></span>
                                <span class="serverlabel"><?php echo $LANG['UI_CLIENT_SERVER_COMMUNICATE_PORT'] ?></span>
                            </label>
                            <div class="col-md-8">
                                <div class="input-icon right">
                                    <i class="fa"></i>
                                    <input type="text" class="form-control input-sm" maxlength="128" name="connectport"
                                           id="connectport" oninput="this.value=this.value.replace(/[^\d]/g,'')">
                                    <div class="portshowDiv"><span class="help-block ">
                                    <?php echo $LANG['UI_CLIENT_MODIFY_PORT_TIPS'] ?>
                                    </span></div>
                                </div>
                            </div>
                        </div>
                        <!-- 服务器通讯IP -->
                        <div class="form-group display-none configDiv addServerIpDiv">
                            <label class="control-label col-md-3" for="addServerIp">
                                <?php echo $LANG['UI_CLIENT_SERVER_ADDRESS'] ?>
                            </label>
                            <div class="col-md-8">
                                <select class="form-control select2me" id="addServerIp"></select>
                            </div>
                        </div>
                        <!-- 是否自动切换可用网络 -->
                        <div class="form-group display-none configDiv autoChangeNetworkFlagDiv">
                            <label class="col-md-3 control-label" for="autoChangeNetworkFlag">
                                <?php echo $LANG['UI_CLIENT_AUTO_CHANGE_NETWORK_FLAG'] ?>
                            </label>
                            <div class="col-md-8 d-flex mt-4">
                                <input type="checkbox" id="autoChangeNetworkFlag" class="make-switch"
                                       data-on-color="primary"
                                       data-off-color="info" data-size="small"
                                       data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                <a class="ml12 mt-2 popovers" data-container="body" data-trigger="hover"
                                   data-placement="right"
                                   data-content="<?php echo $LANG['UI_CLIENT_AUTO_CHANGE_NETWORK_FLAG_TIPS']; ?>">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                            </div>
                        </div>

                        <div class="form-group display-none">
                            <label class="col-md-3 control-label"><?php echo $LANG['UI_CLIENT_INSTALL_MODULE'] ?></label>
                            <div class="col-md-8">
                                <div class="">
                                    <label class="checkbox-inline pl0"><input type="checkbox" class="icheck icheckbox"
                                                                              id="agent_file" checked="checked"
                                                                              disabled>
                                        <a><i class="fa fa-file"></i></a> <?php echo $LANG['UI_CLIENT_TIMING_BACKUP'] ?>
                                    </label>
                                </div>
                                <div class="">
                                    <label class="checkbox-inline pl0"><input type="checkbox" class="icheck icheckbox"
                                                                              id="agent_cdp">
                                        <a><i class="fa fa-database"></i></a> <?php echo $LANG['UI_CLIENT_CDP_BACKUP'] ?>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- 批量远程部署的上传文件 -->
                        <div class="form-group batchDiv display-none">
                            <label class="col-md-3 control-label"><?php echo $LANG['UI_CLIENT_ADD_BATCH'] ?></label>
                            <div class="col-md-8">
                                <div class="btn-group" id="uploadDiv">
                                    <span class="btn green-haze fileinput-button">
                                        <i class="fa fa-upload"></i>
                                        <span>
                                            <?php echo $LANG['WEB_SYSTEM_UPLOAD_FILE'] ?></span>
                                        <input type="file" name="files"
                                               title="<?php echo $LANG['UI_CLIENT_NOT_UPLOAD_FILE_TIPS'] ?>">
                                    </span>
                                </div>
                                <div class="mt10">
                                    <a href="javascript:;"
                                       id="downloadTemp"><?php echo $LANG['UI_CLIENT_TEMPLATE'] ?></a><?php echo $LANG['UI_CLIENT_UPLOAD_TIPS'] ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group display-none batchTableDiv">
                            <div class="col-md-offset-1 col-md-10 table-container ">
                                <table class="table table-hover lh2" id="clientBatchTable">
                                    <thead>
                                    <tr role="row" class="heading">
                                        <th width="40%">
                                            <?php echo $LANG['UI_PUBLIC_IP_ADDRESS'] ?>
                                        </th>
                                        <th width="30%">
                                            <?php echo $LANG['UI_LOGIN_AGENT_FILE_SYSTEM'] ?>
                                        </th>
                                        <th width="30%">
                                            <?php echo $LANG['UI_PUBLIC_STATUS'] ?>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
        <button type="button" class="btn btn-primary" id="addsubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>
<!-- END ADD MODAL -->

<!-- BEGIN UPGRADE MODAL -->
<div id="upgradeModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first"
     data-backdrop="static">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">
            <i class="viconfont vicon-shengji"></i>
            <span class="text"><?php echo $LANG['UI_CLIENT_UPGRADE_CLIENT'] ?></span>
        </h4>
    </div>
    <div class="modal-body">
        <div class="portlet-body">
            <div class="tab-content">
                <!-- 升级代理 -->
                <div class="form-group">
                    <div class="table-container reset-td-width" id="upgradeClientTableWrapper">
                        <table class="table" id="upgradeClientTable"></table>
                    </div>
                </div>
                <div class="alert alert-block alert-info fade in mb-0">
                    <button type="button" class="close" data-dismiss="alert"></button>
                    <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                    <ol class="alert-ol">
                        <li>
                            <?php echo $LANG['UI_CLIENT_UPGRADE_TIPS1'] ?>
                        </li>
                        <li>
                            <?php echo $LANG['UI_CLIENT_UPGRADE_TIPS2'] ?>
                        </li>
                        <li>
                            <?php echo $LANG['UI_CLIENT_UPGRADE_TIPS4'] ?>
                        </li>
                        <li>
                            <?php echo $LANG['UI_CLIENT_UPGRADE_TIPS5'] ?>
                        </li>
                        <li>
                            <?php echo $LANG['UI_CLIENT_UPGRADE_TIPS6'] ?>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- modal-footer -->
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
        <button type="button" class="btn btn-primary" id="upgradesubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>
<!-- END UPGRADE MODAL -->
<!-- BEGIN DOWNLOAD MODAL -->
<div id="downloadModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first"
     data-backdrop="static">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">
            <i class="viconfont vicon-xiazai"></i>
            <span class="text"><?php echo $LANG['UI_CLIENT_DOWNLOAD_CLIENT'] ?></span>
        </h4>
    </div>
    <div class="modal-body">
        <div class="portlet-body">
            <div class="list-option mt-0">
                <div class="row">
                    <!-- 操作系统-->
                    <div class="form-group col-md-8">
                        <label class="control-label col-md-4" for="downloadOsType">
                            <?php echo $LANG['UI_LOGIN_AGENT_FILE_SYSTEM'] ?>
                        </label>
                        <div class="col-md-8">
                            <select class="form-control select2me ossystem_select" id="downloadOsType">
                            </select>
                        </div>
                    </div>
                    <!--版本架构-->
                    <div class="fsagent display-hide form-group col-md-8">
                        <label class="control-label col-md-4" for="downloadFilesystemType">
                            <?php echo $LANG['UI_LOGIN_AGENT_VERSION_ARC'] ?>
                        </label>
                        <div class="col-md-8">
                            <select class="form-control select2me filesystem_select" id="downloadFilesystemType">
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <button type="button" id="download" class="btn btn-sm green-haze">
                            <i class="downLoad viconfont vicon-download">
                            </i>
                            <?php echo $LANG['UI_LOGIN_AGENT_DOWNLOAD'] ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- END DOWNLOAD MODAL -->
<!-- BEGIN EDIT MODAL -->
<div id="editModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first"
     data-backdrop="static">
    <div class="modal-header ">
        <input id="edit_client_uuid" class="display-none"/>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title" id="editClientTitle">
            <i class="viconfont vicon-xiugai"></i>
            <span class="text"><?php echo $LANG['UI_CLIENT_MODIFY_CLIENT'] ?></span>
        </h4>
        <h4 class="modal-title" id="editProxyTitle">
            <i class="viconfont vicon-xiugai"></i>
            <span class="text"><?php echo $LANG['UI_CLIENT_MODIFY_PROXY'] ?></span>
        </h4>
    </div>
    <div class="modal-body">
        <form action="#" id="editForm" class="form-horizontal">
            <div class="form-group ">
                <label class="col-md-3 control-label" for="ipaddress2" id="ipaddress2Label">
                    <?php echo $LANG['UI_PUBLIC_IP_ADDRESS'] ?>
                </label>
                <div class="col-md-8">
                    <div class="input-icon right">
                        <i class="fa"></i>
                        <input type="text" class="form-control" maxlength="128" name="ipaddress"
                               id="ipaddress2" placeholder="192.168.1.110">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label" for="nickname2">
                    <?php echo $LANG['UI_VCENTER_RNAME'] ?>
                </label>
                <div class="col-md-8 ">
                    <div class="input-icon right">
                        <i class="fa"></i>
                        <input type="text" class="form-control" maxlength="64" name="nickname" id="nickname2">
                    </div>
                </div>
            </div>
            <div class="form-group clientportDiv">
                <label class="control-label col-md-3" for="connectport2">
                    <span class="display-none clientlabel"><?php echo $LANG['UI_CLIENT_COMMUNICATE_PORT'] ?></span>
                    <span class="display-none clientlabelproxy"><?php echo $LANG['UI_CLIEBT_COMMUNICATE_PORT_PROXY'] ?></span>
                    <span class="serverlabel"><?php echo $LANG['UI_CLIENT_SERVER_COMMUNICATE_PORT'] ?></span>
                </label>
                <div class="col-md-8">
                    <div class="input-icon right">
                        <i class="fa"></i>
                        <input type="text" class="form-control input-sm" maxlength="128" name="connectport"
                               id="connectport2" oninput="this.value=this.value.replace(/[^\d]/g,'')">
                        <div>
                            <span id="clientEditConnectPortTips"
                                  class="help-block "><?php echo $LANG['UI_CLIENT_MODIFY_PORT_TIPS'] ?></span>
                            <span id="proxyEditConnectPortTips"
                                  class="help-block "><?php echo $LANG['UI_CLIENT_MODIFY_PROXY_PORT_TIPS'] ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 是否自动切换可用网络 -->
            <div class="form-group editAutoChangeNetworkFlagDiv">
                <label class="col-md-3 control-label" for="editAutoChangeNetworkFlag">
                    <?php echo $LANG['UI_CLIENT_AUTO_CHANGE_NETWORK_FLAG'] ?>
                </label>
                <div class="col-md-8 d-flex mt-4">
                    <input type="checkbox" id="editAutoChangeNetworkFlag" class="make-switch" data-on-color="primary"
                           data-off-color="info" data-size="small"
                           data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                    <a class="ml12 mt-2 popovers" data-container="body" data-trigger="hover"
                       data-placement="right"
                       data-content="<?php echo $LANG['UI_CLIENT_AUTO_CHANGE_NETWORK_FLAG_TIPS']; ?>">
                        <i class="viconfont vicon-tishi"></i>
                    </a>
                </div>
            </div>

            <div class="form-group editAppliedVcentersDiv display-none">
                <label class="control-label col-md-3">
                    <span><?php echo $LANG['UI_CLIENT_APPLY_VCENTERS'] ?></span>
                </label>
                <div class="col-md-8">
                    <select class="bootstrap-mutiple-select select2me selectpicker show-tick ignore"
                            id="applied_vcenters_edit" multiple data-live-search="true"
                            data-actions-box="true">
                    </select>
                </div>
            </div>
        </form>
    </div>

    <!-- modal-footer -->
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
        <button type="button" class="btn btn-primary" id="editsubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>

</div>
<!-- END EDIT MODAL -->
<!-- BEGIN AGENT CONFIG MODAL -->
<div id="agentModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first"
     data-backdrop="static">
    <div class="modal-header">
        <input id="agent_uuid" class="display-none">
        <input id="net_model" class="display-none">
        <input id="port" class="display-none">
        <input id="agent_nickname" class="display-none">
        <input id="auto_change_network" class="display-none">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">
            <i class="viconfont vicon-ge_configuration"></i>
            <span class="text"><?php echo $LANG['UI_CLIENT_AGENT_CONFIG'] ?></span>
        </h4>
    </div>
    <div class="modal-body">
        <div class="form-group pt50">
            <label class="control-label col-md-3" for="ipaddr">
                <span class="required">* </span>
                <?php echo $LANG['UI_APPLIANCE_IP'] ?>
            </label>
            <div class="col-md-8">
                <div class="input-icon right">
                    <i class="fa"></i>
                    <input type="text" maxlength="128" class="form-control" id="ipaddr" name="ipaddr"
                           placeholder="192.168.1.100" disabled/>
                    <div><span class="help-block "><?php echo $LANG['UI_APPLIANCE_ADD_IP_TIPS'] ?></span></div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-md-3" for="dnslist">
                <span class="required">* </span>
                <?php echo $LANG['UI_SETTINGS_DNS_SETTING'] ?>
            </label>
            <div class="col-md-8">
                <textarea class="form-control" id="dnslist" rows="8"
                          placeholder="192.168.1.110  example.com"></textarea>
                <div>
                    <span class="help-block ">
                        <?php echo $LANG['UI_SETTINGS_DNS_SETTING_TIPS'] ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="form-group configAppliedVcentersDiv">
            <label class="control-label col-md-3">
                <span><?php echo $LANG['UI_CLIENT_APPLY_VCENTERS'] ?></span>
            </label>
            <div class="col-md-8">
                <select class="bootstrap-mutiple-select select2me selectpicker show-tick ignore"
                        id="applied_vcenters_config" multiple data-live-search="true"
                        data-actions-box="true">
                </select>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
        <button type="button" class="btn btn-primary"
                id="agentConfigSubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>
<!-- END AGENT CONFIG MODAL -->

<!-- BEGIN ADVANCE SEARCH MODAL -->
<div id="advanceSearchModal" class="modal xmodal fade form-horizontal" tabindex="-1" data-backdrop="static">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">
            <i class="viconfont vicon-gaojisousuo1"></i>
            <span class="text"><?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?></span>
        </h4>
    </div>
    <div class="modal-body">
        <div class="portlet-body" style="height: auto"> <!-- 这里会撑开父元素，子元素有除了content之外的，不能用100% -->
            <!-- 客户端添加时间 -->
            <div class="list-option">
                <div class="row">
                    <label class="control-label col-md-4" for="advanceSearchDateRangePicker">
                        <?php echo $LANG['UI_CLIENT_ADD_TIME'] ?> ：
                    </label>
                    <div class="col-md-5 daterangepickerdiv">
                        <input type="text" id="advanceSearchDateRangePicker" class="form-control" autocomplete="off"
                               readonly style="cursor: pointer"><!-- 时间选择禁用手动输入 -->
                        <i class="viconfont vicon-ge_calendar"></i>
                    </div>
                </div>
            </div>

            <!-- IP地址 -->
            <div class="list-option">
                <div class="row">
                    <label class="control-label col-md-4" for="advanceSearchIp">
                        <?php echo $LANG['UI_CLIENT_IP_ADDRESS'] ?> ：
                    </label>
                    <div class="col-md-5">
                        <input style="width:323px; height:34px;" id="advanceSearchIp" type="text"
                               maxlength="64" class="table-group-action-input form-control input-inline input input-sm"
                               aria-controls="example">
                    </div>
                </div>
            </div>

            <!-- 主机名 -->
            <div class="list-option">
                <div class="row">
                    <label class="control-label col-md-4" for="advanceSearchHostname">
                        <?php echo $LANG['UI_CLIENT_HOST_NAME'] ?> ：
                    </label>
                    <div class="col-md-5">
                        <input style="width:323px; height:34px;" id="advanceSearchHostname" type="text"
                               maxlength="64" class="table-group-action-input form-control input-inline input input-sm"
                               aria-controls="example">
                    </div>
                </div>
            </div>

            <!-- 别名 -->
            <div class="list-option">
                <div class="row">
                    <label class="control-label col-md-4" for="advanceSearchAlias">
                        <?php echo $LANG['UI_CLIENT_ALIAS'] ?> ：
                    </label>
                    <div class="col-md-5">
                        <input style="width:323px; height:34px;" id="advanceSearchAlias" type="text"
                               maxlength="64" class="table-group-action-input form-control input-inline input input-sm"
                               aria-controls="example">
                    </div>
                </div>
            </div>

            <!-- 操作系统 -->
            <div class="list-option">
                <div class="row">
                    <label class="control-label col-md-4" for="advanceSearchOsVersion">
                        <?php echo $LANG['UI_PUBLIC_OS'] ?> ：
                    </label>
                    <div class="col-md-5">
                        <input style="width:323px; height:34px;" id="advanceSearchOsVersion" type="text"
                               maxlength="64" class="table-group-action-input form-control input-inline input input-sm"
                               aria-controls="example">
                    </div>
                </div>
            </div>

            <!-- 状态 -->
            <div class="list-option">
                <div class="row">
                    <label class="control-label col-md-4" for="advanceSearchStatus">
                        <?php echo $LANG['UI_PUBLIC_STATUS'] ?> ：
                    </label>
                    <div class="col-md-5">
                        <select class="form-control select2me" id="advanceSearchStatus">
                            <option value=""><?php echo $LANG['UI_PUBLIC_ALL'] ?></option>
                            <?php
                            $onlineStatusEnum = [
                                1 => 'WEB_AGENT_STATUS_ONLINE',
                                2 => 'WEB_AGENT_STATUS_OFFLINE',
                            ];
                            $deployStatusEnum = [
                                1 => 'WEB_CLIENT_DEPLOYING',
                                2 => 'WEB_CLIENT_DEPLOY_SUCCESS',
                                3 => 'WEB_CLIENT_DEPLOY_FAILED',
                                4 => 'WEB_CLIENT_UPGRADING',
                                5 => 'WEB_CLIENT_UPGRADE_SUCCESS',
                                6 => 'WEB_CLIENT_UPGRADE_FAILED',
                                7 => 'WEB_CLIENT_INVALID_CODE',
                            ];
                            foreach ($deployStatusEnum as $_status => $_value) {
                                foreach ($onlineStatusEnum as $status => $value) {
                                    if ($value == 'WEB_AGENT_STATUS_ONLINE' && $_value == 'WEB_CLIENT_DEPLOY_FAILED') {
                                        continue;
                                    }
                                    echo "<option value=\"$status-$_status\">$LANG[$value]($LANG[$_value])</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- 应用名 -->
            <div class="list-option">
                <div class="row">
                    <label class="control-label col-md-4" for="advanceSearchAppName">
                        <?php echo $LANG['UI_CLIENT_APP_NAME'] ?> ：
                    </label>
                    <div class="col-md-5">
                        <input style="width:323px; height:34px;" id="advanceSearchAppName" type="text"
                               maxlength="64" class="table-group-action-input form-control input-inline input input-sm"
                               aria-controls="example">
                    </div>
                </div>
            </div>

            <!-- 所有者 -->
            <div class="list-option">
                <div class="row">
                    <label class="control-label col-md-4" for="advanceSearchOwner">
                        <?php echo $LANG['UI_CLIENT_OWNER'] ?> ：
                    </label>
                    <div class="col-md-5">
                        <input style="width:323px; height:34px;" id="advanceSearchOwner" type="text"
                               maxlength="64" class="table-group-action-input form-control input-inline input input-sm"
                               aria-controls="example">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- modal-footer -->
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
        <button type="button" class="btn btn-primary"
                id="advanceSearchSubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>
<!-- END ADVANCE SEARCH MODAL -->
<!-- END PAGE CONTENT-->

<!-- BEGIN INSTALL DRIVER MODAL -->
<div id="installDriverModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first"
     data-backdrop="static">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">
            <i class="viconfont vicon-a-Hunting-gearcongdongzhuangzhi"></i>
            <span class="text"><?php echo $LANG['UI_CLIENT_INSTALL_DRIVER'] ?></span>
        </h4>
    </div>
    <div class="modal-body">
        <div class="portlet-body">
            <div class="tab-content">
                <!-- 安装驱动 -->
                <div class="form-group">
                    <div class="table-container reset-td-width" id="installDriverTableWrapper">
                        <table class="table" id="installDriverTable"></table>
                    </div>
                </div>
                <div class="alert alert-block alert-info fade in mb-0">
                    <button type="button" class="close" data-dismiss="alert"></button>
                    <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                    <ol class="alert-ol">
                        <li><?php echo $LANG['UI_CLIENT_INSTALL_DRIVER_TIPS1'] ?></li>
                        <li>
                            <?php echo $LANG['UI_CLIENT_INSTALL_DRIVER_TIPS2'] ?>
                            <a id="toDriverManager"><?php echo $LANG['UI_CLIENT_INSTALL_DRIVER_TIPS3'] ?></a>
                            <?php echo $LANG['UI_CLIENT_INSTALL_DRIVER_TIPS4'] ?>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- modal-footer -->
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
        <button type="button" class="btn btn-primary"
                id="installDriverSubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>
<!-- END INSTALL DRIVER MODAL -->

<!-- BEGIN DRAWER -->

<!-- BEGIN DETAILS DRAWER -->
<div id="detailsDrawer" class="drawer slide " data-placement="right" tabindex="-1" role="dialog">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <!-- header -->
        <div class="drawer-header">
            <h4 class="drawer-title" id="detailClientTitle">
                <span>
                    <i class="viconfont vicon-tenant-detail"></i>
                </span>
                <span class="text"><?php echo $LANG['UI_CLIENT_DETAIL'] ?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close ">
                    <i class="viconfont vicon-guanbi"></i>
                </span>
            </h4>
            <h4 class="drawer-title" id="detailProxyTitle">
                <span>
                    <i class="viconfont vicon-tenant-detail"></i>
                </span>
                <span class="text"><?php echo $LANG['UI_CLIENT_PROXY_DETAIL'] ?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close ">
                    <i class="viconfont vicon-guanbi"></i>
                </span>
            </h4>
        </div>

        <!-- body -->
        <div class="drawer-body">
            <!-- 基本信息 -->
            <div class="section-title">
                <span class="decoration section-title__decoration"></span>
                <span class="section-title__name"><?php echo $LANG['UI_PUBLIC_BASE_INFO'] ?></span>
            </div>
            <div class="section-wrapper">
                <!-- 添加IP/连接IP -->
                <div class="section-item">
                    <div class="col-md-4 name">
                        <?php echo $LANG['UI_CLIENT_ADD_IP'] . '/' . $LANG['UI_CLIENT_CONNECT_IP'] ?>
                    </div>
                    <div class="col-md-8 value" id="detailIp"></div>
                </div>
                <!-- 主机名/别名 -->
                <div class="section-item">
                    <div class="col-md-4 name"><?php echo $LANG['UI_CLIENT_HOST_NICKNAME'] ?></div>
                    <div class="col-md-8 value" id="detailHostname"></div>
                </div>
                <!-- 操作系统 -->
                <div class="section-item">
                    <div class="col-md-4 name"><?php echo $LANG['WEB_OS_HOST'] ?></div>
                    <div class="col-md-8 value" id="detailOs"></div>
                </div>
                <!-- 类型 -->
                <div class="section-item">
                    <div class="col-md-4 name"><?php echo $LANG['UI_PUBLIC_TYPE'] ?></div>
                    <div class="col-md-8 value" id="detailAgentType"></div>
                </div>
                <!-- 应用配置 -->
                <div class="section-item">
                    <div class="col-md-4 name"><?php echo $LANG['UI_CLIENT_APPLICATION_CONFIG'] ?></div>
                    <div class="col-md-8 value" id="detailapp"></div>
                </div>
                <!-- 传输代理资源池 -->
                <div class="section-item" id="detailAgentPoolDiv">
                    <div class="col-md-4 name"><?php echo $LANG['UI_AGENT_POOL'] ?></div>
                    <div class="col-md-8 value" id="detailAgentPool"></div>
                </div>
                <!-- 客户端版本 -->
                <div class="section-item">
                    <div class="col-md-4 name"
                         id="detailClientVersion"><?php echo $LANG['UI_CLIENT_VERSION'] ?></div>
                    <div class="col-md-4 name"
                         id="detailProxyVersion"><?php echo $LANG['UI_CLIENT_PROXY_VERSION'] ?></div>
                    <div class="col-md-8 value" id="detailVersion"></div>
                </div>
                <!-- 添加时间 -->
                <div class="section-item">
                    <div class="col-md-4 name"><?php echo $LANG['UI_CLIENT_ADD_TIME'] ?></div>
                    <div class="col-md-8 value" id="detailCreatetime"></div>
                </div>
                <!-- 状态 -->
                <div class="section-item">
                    <div class="col-md-4 name"><?php echo $LANG['UI_PUBLIC_STATUS'] ?></div>
                    <div class="col-md-8 value" id="detailStatus"></div>
                </div>
                <!-- 创建者 -->
                <div class="section-item">
                    <div class="col-md-4 name"><?php echo $LANG['UI_JOB_CREATOR'] ?></div>
                    <div class="col-md-8 value" id="detailCreator"></div>
                </div>
                <!-- 所有者 -->
                <div class="section-item">
                    <div class="col-md-4 name"><?php echo $LANG['UI_CLIENT_OWNER'] ?></div>
                    <div class="col-md-8 value" id="detailOwner"></div>
                </div>
                <!-- 通信模式 -->
                <div class="section-item">
                    <div class="col-md-4 name"><?php echo $LANG['UI_CLIENT_COMMUNICATION'] ?></div>
                    <div class="col-md-8 value" id="detailMode"></div>
                </div>
                <!-- 通信端口 -->
                <div class="section-item detailportDiv">
                    <div class="col-md-4 name clientportdiv"><?php echo $LANG['UI_CLIENT_COMMUNICATE_PORT'] ?></div>
                    <div class="col-md-4 name proxyportdiv"><?php echo $LANG['UI_CLIEBT_COMMUNICATE_PORT_PROXY'] ?></div>
                    <div class="col-md-4 name serverportdiv"><?php echo $LANG['UI_CLIENT_SERVER_COMMUNICATE_PORT'] ?></div>
                    <div class="col-md-8 value" id="detailClientport"></div>
                </div>
                <!-- 自动切换可用网络 -->
                <div class="section-item detailAutoChangeNetworkFlagDiv">
                    <div class="col-md-4 name"><?php echo $LANG['UI_CLIENT_AUTO_CHANGE_NETWORK_FLAG'] ?></div>
                    <div class="col-md-8 value" id="detailAutoChangeNetworkFlag"></div>
                </div>
                <!-- 时区 -->
                <div class="section-item">
                    <div class="col-md-4 name"><?php echo $LANG['UI_CLIENT_TIMEZONE'] ?></div>
                    <div class="col-md-8 value" id="detailTimezone"></div>
                </div>
            </div>
            <!-- 网卡信息 -->
            <div class="section-title">
                <span class="decoration section-title__decoration"></span>
                <span class="section-title__name"><?php echo $LANG['UI_CLIENT_NET_MSG'] ?></span>
            </div>
            <div class="table-container reset-td-width" id="networkTableWrapper">
                <table class="table" id="networkTable2"></table>
            </div>
        </div>

        <!-- footer -->
        <div class="drawer-footer">
            <button type="button" class="btn default cancel" data-dismiss="drawer"
                    aria-label="Close"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
        </div>
    </div>
</div>
<!-- END DETAILS DRAWER -->

<!-- END DRAWER -->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<?php
if ($_SESSION['language'] != "en-us") {
    //如果不是英文,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
        $_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.js"></script>
<script type="text/javascript"
        src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script src="./assets/global/plugins/jquery-file-upload/js/vendor/jquery.ui.widget.js"></script>
<script src="./assets/global/plugins/jquery-file-upload/js/jquery.fileupload.js"></script>

<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
<script type="text/javascript" src="./scripts/client/client_manager.js"></script>
<!-- END PAGE LEVEL PLUGINS -->