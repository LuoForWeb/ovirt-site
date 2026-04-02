<?php
include_once '../../../tpl/permission.php';
include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php';
?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<!-- BEGIN PAGE HEADER-->

<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
    <div class="col-md-12">
        <div class="portlet box blue-hoki" id='tenantMangerDiv'>
            <div class="portlet-title">
                <div class="caption">
                    <i class="levelchild viconfont vicon-tenant"></i><?php echo $LANG['UI_TENANT_LIST'] ?>
                </div>
            </div>
            <div class="portlet-body mlr10">
                <div class="table-container tenant-table">
                    <div class="vin_toolbar" id="tenant_manager_toolbar">
                        <div class="leftTool">
                        </div>
                        <div class="rightTool">
                            <div class="vin_btnToolbar">
                            </div>
                        </div>
                    </div>
                    <table id="tenant_table"></table>
                </div>
                <div class="alert alert-block alert-info fade in" id="marktips">
                    <button type="button" class="close" data-dismiss="alert"></button>
                    <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                    <ol class = "alert-ol">
                        <li>
                            <?php echo $LANG['WEB_TENANT_LIST_TIPS1'] ?>
                        </li>
                        <li>
                            <?php echo $LANG['WEB_TENANT_LIST_TIPS2'] ?>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- BEGIN MODAL -->
    <div id="deleteModal" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            <h4 class="modal-title"><?php echo $LANG['UI_TENANT_DELETE_INFO_TIPS'] ?></h4>
        </div>
        <div class="modal-body">
            <p><?php echo $LANG['UI_TENANT_DELETE_RESULT'] ?>:</p>
            <div class="changelog-list"  style="height: 300px; overflow-y:auto; border: 1px solid #ddd; background: #fff; padding: 20px;">
                <ul id="info" class="feeds">
                </ul>
            </div>
        </div>

        <!-- modal-footer -->
        <div class="modal-footer">
            <button type="button" data-dismiss="modal" class="btn btn-default" id="cancelModal"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
        </div>
    </div>
    <!-- END MODAL -->
    <!-- drawer开始 -->
    <div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true" id="tenantDrawer">
        <div class="drawer-content drawer-content-scrollable" role="document">
            <div class="drawer-header">
                <h4 class="drawer-title" id="drawer-1-title">
                    <span class="title-des"></span>
                    <span data-dismiss="drawer" aria-label="Close" class="drawer-close"><i class="viconfont vicon-guanbi"></i></span>
                    <input type="text" class="display-none" id="tenant_uuid">
                    <input type="text" class="display-none" id="user_uuid">
                </h4>
            </div>
            <div class="drawer-body pd0" style="overflow-y: hidden;">
                <form action="#" class="form-horizontal" id="submit_form" method="POST" style="height:100%;">
                    <div class="form-wizard" style="height: 100%;">
                        <div class="form-body pd0"  style="height: calc(100% - 48px);">
                            <ul class="nav nav-pills nav-justified steps" style="margin: 20px 0;">
                                <li class="active">
                                    <a href="#tab1" data-toggle="tab" class="step" aria-expanded="true">
                                        <span class="number"> 1 </span>
                                        <span class="desc"><?php echo $LANG['UI_TENANT_ADD'] ?><i class="fa fa-check"></i></span>
                                    </a>
                                </li>
                                <li class="">
                                    <a href="#tab2" data-toggle="tab" class="step" aria-expanded="false">
                                        <span class="number"> 2 </span>
                                        <span class="desc"><?php echo $LANG['WEB_TENANT_BAK_RECOVERY_CONFIG'] ?><i class="fa fa-check"></i></span>
                                    </a>
                                </li>
                                <li class="">
                                    <a href="#tab3" data-toggle="tab" class="step" aria-expanded="false">
                                        <span class="number"> 3 </span>
                                        <span class="desc"><?php echo $LANG['WEB_TENANT_AUTH_CONFIG'] ?><i class="fa fa-check"></i></span>
                                    </a>
                                </li>
                            </ul>
                            <div class="tab-content" style="padding: 0 20px; height: calc(100% - 32px);overflow: auto;">
                                <div class="tab-pane active" id="tab1">
                                    <div class="form-group">
                                        <label class="control-label col-md-3">
                                            <span class="required">* </span><?php echo $LANG['UI_TENANT_NAME'] ?>
                                        </label>
                                        <div class="col-md-8">
                                            <div class="input-icon right">
                                                <i class="fa"></i>
                                                <input type="text" maxlength="128" class="form-control" id="tenant_name" name="tenant_name">
                                                <span class="add-tenant-des"><?php echo $LANG['UI_TENANT_INPUT_RULE'] ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-3">
                                            <?php echo $LANG['UI_PUBLIC_REMARK'] ?>
                                        </label>
                                        <div class="col-md-8">
                                            <div class="input-icon right">
                                                <i class="fa"></i>
                                                <input type="text" maxlength="128" class="form-control" id="remarks" name="remarks">
                                                <span class="add-tenant-des"><?php echo $LANG['WEB_TENANT_REMARK_DES'] ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-3">
                                            <span class="required">* </span><?php echo $LANG['UI_TENANT_ADMIN_ACCOUNT'] ?>
                                        </label>
                                        <div class="col-md-8">
                                            <div class="input-icon right">
                                                <i class="fa"></i>
                                                <input type="text" maxlength="128" class="form-control" id="user_name" name="user_name">
                                                <span class="add-tenant-des"><?php echo $LANG['UI_TENANT_ADMIN_ACCOUNT_TIPS'] ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-3">
                                            <span class="required">* </span><?php echo $LANG['UI_TENANT_ADMIN_PASSWORD'] ?>
                                        </label>
                                        <div class="col-md-8">
                                            <div class="input-icon right">
                                                <i class="fa"></i>
                                                <input type="password" maxlength="128" class="form-control" id="admin_password" name="admin_password" autocomplete="off">
                                                <span class="add-tenant-des"><?php echo $LANG['UI_TENANT_ADMIN_PASSWORD_TIPS'] ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-3">
                                            <span class="required">* </span><?php echo $LANG['UI_TENANT_ADMIN_CONFIRM_PASSWORD'] ?>
                                        </label>
                                        <div class="col-md-8">
                                            <div class="input-icon right">
                                                <i class="fa"></i>
                                                <input type="password" maxlength="128" class="form-control" id="confirm_password" name="confirm_password" autocomplete="off">
                                                <span class="add-tenant-des"><?php echo $LANG['UI_TENANT_ADMIN_CONFIRM_PASSWORD_TIPS'] ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-3">
                                            <?php echo $LANG['UI_TENANT_ADMIN_EMAIL'] ?>
                                        </label>
                                        <div class="col-md-8">
                                            <div class="input-icon right">
                                                <i class="fa"></i>
                                                <input type="text" maxlength="128" class="form-control" id="admin_email" name="email">
                                                <span class="add-tenant-des"><?php echo $LANG['UI_TENANT_ADMIN_EMAIL_TIPS'] ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="tab2">
                                    <div class="form-group">
                                        <label class="control-label col-md-3">
                                            <?php echo $LANG['UI_DATACENTER_BACKUP_STORAGE'] ?>
                                        </label>
                                        <div class="col-md-8">
                                            <select id="selectStorage" name="" class="bootstrap-mutiple-select selectpicker show-tick" multiple></select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-3">
                                        </label>
                                        <div class="col-md-8">
                                            <div class="alert alert-block alert-info fade in">
                                                <button type="button" class="close" data-dismiss="alert"></button>
                                                <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                                <ol class="alert-ol">
                                                    <li>
                                                        <?php echo $LANG['UI_TENANT_STORAGE_TIPS1'] ?>
                                                    </li>
                                                    <li>
                                                        <?php echo $LANG['UI_TENANT_STORAGE_TIPS2'] ?>
                                                    </li>
                                                </ol>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="tab3">
                                    <div class="form-group">
                                        <div class="auth-way-div">
                                            <?php echo $LANG['UI_PLATFORM_TENANT_AUTOR_WAY'] ?>:
                                            <span id="authMode"></span>
                                        </div>
                                    </div>
                                    <!-- 按容量-->
                                    <div class="quota-content">
                                        <div class="tenant-title"><span class="green-line"></span><?php echo $LANG['WEB_SYSTEM_LICENSE_CAPACITY_AUTH'] ?></div>
                                        <!-- 普通容量模式 -->
                                        <div class="normal-quota-div grey-ground display-none">
                                            <div class="form-group">
                                                <label class="control-label col-md-3">
                                                    <?php echo $LANG['WEB_TENANT_CAPACITY_MODE'] ?>
                                                </label>
                                                <div class="col-md-6">
                                                    <select class="form-control select2me normal-capacity-mode">
                                                        <option value="0"><?php echo $LANG['UI_SETTINGS_AUTH_UNLIMITED'] ?></option>
                                                        <option value="1"><?php echo $LANG['UI_TENANT_QUOTA_APPOINT'] ?></option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group normal-diy-capacity display-none mb0">
                                                <label class="control-label col-md-3">
                                                    <?php echo $LANG['UI_TENANT_QUOTA_SIZE'] ?>
                                                </label>
                                                <div class="col-md-8">
                                                    <div style="display:inline-flex;">
                                                        <div class="input-group spinner-group normal-quota-input-div">
                                                            <input type="text" id="normal-quota-input" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" maxlength="3">
                                                            <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                <button type="button" class="btn spinner-up default input-sm">
                                                                    <i class="fa fa-angle-up"></i>
                                                                </button>
                                                                <button type="button" class="btn spinner-down default input-sm">
                                                                    <i class="fa fa-angle-down"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div class="">
                                                            <select id="normal-quota-unit" class="form-control" style="margin-left: 10px;">
                                                                <option value="MB">MB</option>
                                                                <option value="GB">GB</option>
                                                                <option value="TB">TB</option>
                                                                <option value="PB">PB</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="help-block"><?php echo $LANG['WEB_TENANT_FREE_CAPACITY'] ?><span class="normal-free-size"></span></div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- 定时容量模式 -->
                                        <div class="time-quota-div grey-ground display-none">
                                            <div class="form-group">
                                                <label class="control-label col-md-3">
                                                    <?php echo $LANG['UI_TENANT_TIME_STORAGE_MODE'] ?>
                                                </label>
                                                <div class="col-md-6">
                                                    <select class="form-control select2me time-capacity-mode">
                                                        <option value="0"><?php echo $LANG['UI_SETTINGS_AUTH_UNLIMITED'] ?></option>
                                                        <option value="1"><?php echo $LANG['UI_TENANT_QUOTA_APPOINT'] ?></option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group time-diy-capacity display-none mb0">
                                                <label class="control-label col-md-3">
                                                    <?php echo $LANG['UI_TENANT_TIME_QUOTA'] ?>
                                                </label>
                                                <div class="col-md-8">
                                                    <div style="display:inline-flex;">
                                                        <div class="input-group spinner-group time-quota-input-div">
                                                            <input type="text" id="time-quota-input" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" maxlength="3">
                                                            <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                <button type="button" class="btn spinner-up default input-sm">
                                                                    <i class="fa fa-angle-up"></i>
                                                                </button>
                                                                <button type="button" class="btn spinner-down default input-sm">
                                                                    <i class="fa fa-angle-down"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div class="">
                                                            <select id="time-quota-unit" class="form-control" style="margin-left: 10px;">
                                                                <option value="MB">MB</option>
                                                                <option value="GB">GB</option>
                                                                <option value="TB">TB</option>
                                                                <option value="PB">PB</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="help-block"><?php echo $LANG['WEB_TENANT_FREE_CAPACITY'] ?><span class="time-free-size"></span></div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- 实时容量模式 -->
                                        <div class="real-time-quota-div grey-ground display-none">
                                            <div class="form-group">
                                                <label class="control-label col-md-3">
                                                    <?php echo $LANG['UI_TENANT_REAL_TIME_STORAGE_MODE'] ?>
                                                </label>
                                                <div class="col-md-6">
                                                    <select class="form-control select2me real-time-capacity-mode">
                                                        <option value="0"><?php echo $LANG['UI_SETTINGS_AUTH_UNLIMITED'] ?></option>
                                                        <option value="1"><?php echo $LANG['UI_TENANT_QUOTA_APPOINT'] ?></option>
                                                    </select>
                                                </div>
                                            </div>
                                            <!-- <div class="form-group real-time-diy-capacity display-none mb0">
                                                <label class="control-label col-md-3">
                                                    <?php echo $LANG['UI_TENANT_REAL_TIME_QUOTA'] ?>
                                                </label>
                                                <div class="col-md-8">
                                                    <div style="display:inline-flex;">
                                                        <div class="input-group spinner-group real-time-quota-input-div">
                                                            <input type="text" id="real-time-quota-input" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" maxlength="3">
                                                            <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                <button type="button" class="btn spinner-up default input-sm">
                                                                    <i class="fa fa-angle-up"></i>
                                                                </button>
                                                                <button type="button" class="btn spinner-down default input-sm">
                                                                    <i class="fa fa-angle-down"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div class="">
                                                            <select id="real-time-quota-unit" class="form-control" style="margin-left: 10px;">
                                                                <option value="MB">MB</option>
                                                                <option value="GB">GB</option>
                                                                <option value="TB">TB</option>
                                                                <option value="PB">PB</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="help-block"><?php echo $LANG['WEB_TENANT_FREE_CAPACITY'] ?><span class="real-time-free-size"></span></div>
                                                </div>
                                            </div> -->
                                        </div>
                                        <!-- 文件系列容量模式 -->
                                        <div class="file-quota-div grey-ground display-none">
                                            <div class="form-group">
                                                <label class="control-label col-md-3">
                                                    <?php echo $LANG['WEB_TENANT_CAPACITY_MODE'] ?>
                                                </label>
                                                <div class="col-md-6">
                                                    <select class="form-control select2me file-capacity-mode">
                                                        <option value="0"><?php echo $LANG['UI_SETTINGS_AUTH_UNLIMITED'] ?></option>
                                                        <option value="1"><?php echo $LANG['UI_TENANT_QUOTA_APPOINT'] ?></option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group file-diy-capacity display-none mb0">
                                                <label class="control-label col-md-3">
                                                    <?php echo $LANG['UI_TENANT_FILE_QUOTA'] ?>
                                                </label>
                                                <div class="col-md-8">
                                                    <div style="display:inline-flex;">
                                                        <div class="input-group spinner-group file-quota-input-div">
                                                            <input type="text" id="file-quota-input" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" maxlength="3">
                                                            <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                <button type="button" class="btn spinner-up default input-sm">
                                                                    <i class="fa fa-angle-up"></i>
                                                                </button>
                                                                <button type="button" class="btn spinner-down default input-sm">
                                                                    <i class="fa fa-angle-down"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div class="">
                                                            <select id="file-quota-unit" class="form-control" style="margin-left: 10px;">
                                                                <option value="MB">MB</option>
                                                                <option value="GB">GB</option>
                                                                <option value="TB">TB</option>
                                                                <option value="PB">PB</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="help-block"><?php echo $LANG['WEB_TENANT_FREE_CAPACITY'] ?><span class="file-free-size"></span></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--按数量-->
                                    <div class="tenant-title"><span class="green-line"></span><?php echo $LANG['WEB_SYSTEM_LICENSE_NUM_AUTH'] ?></div>
                                    <div class="grey-ground">
                                    <?php
                                        if (in_array("vmprotect", $_SESSION['permission'])) {
                                            echo '<div class="form-group display-none num-div">
                                                <label class="control-label col-md-3 vm-label">' . $LANG['UI_SETTINGS_AUTH_VM_NUM'] . '</label>
                                                <div class="col-md-4">
                                                    <div class="input-group spinner-group vmNumDiv">
                                                        <input type="text" id="vmNum" onkeyup="value=value.replace(/[^\d]/g,\'\')" class="spinner-input form-control input-sm" maxlength="3">
                                                        <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                            <button type="button" class="btn spinner-up default input-sm">
                                                                <i class="fa fa-angle-up"></i>
                                                            </button>
                                                            <button type="button" class="btn spinner-down default input-sm">
                                                                <i class="fa fa-angle-down"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <span class="add-tenant-des">' . $LANG['WEB_TENANT_FREE_NUM'] . '<span class="vm-free-num"></span></span>
                                                </div>
                                            </div>';
                                        }
                                        // 文件、数据库、整机为客户端数量授权
                                        if (in_array("filebackup", $_SESSION['permission']) || in_array("db_protect", $_SESSION['permission']) || in_array("osbackup", $_SESSION['permission']) || in_array("complete_machine", $_SESSION['permission'])) {
                                            echo '<div class="form-group display-none agent-mudule num-div">
                                                        <label class="control-label col-md-3">' . $LANG['UI_JOB_CLIENT'] . '</label>
                                                        <div class="col-md-4">
                                                            <div class="input-group spinner-group agentNumDiv">
                                                                <input type="text" id="agentNum" onkeyup="value=value.replace(/[^\d]/g,\'\')" class="spinner-input form-control input-sm" maxlength="3">
                                                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                    <button type="button" class="btn spinner-up default input-sm">
                                                                        <i class="fa fa-angle-up"></i>
                                                                    </button>
                                                                    <button type="button" class="btn spinner-down default input-sm">
                                                                        <i class="fa fa-angle-down"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <span class="add-tenant-des">' . $LANG['WEB_TENANT_FREE_NUM'] . '<span class="agent-free-num"></span></span>
                                                        </div>
                                                    </div>';
                                        }

                                        if (in_array("awsprotect", $_SESSION['permission'])) {
                                            echo '<div class="form-group display-none num-div">
                                                <label class="control-label col-md-3 aws-label">' . $LANG['WEB_TENANT_AWS_NUM'] . '</label>
                                                <div class="col-md-4">
                                                    <div class="input-group spinner-group awsNumDiv">
                                                        <input type="text" id="awsNum" onkeyup="value=value.replace(/[^\d]/g,\'\')" class="spinner-input form-control input-sm" maxlength="3">
                                                        <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                            <button type="button" class="btn spinner-up default input-sm">
                                                                <i class="fa fa-angle-up"></i>
                                                            </button>
                                                            <button type="button" class="btn spinner-down default input-sm">
                                                                <i class="fa fa-angle-down"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <span class="add-tenant-des">' . $LANG['WEB_TENANT_FREE_NUM'] . '<span class="aws-free-num"></span></span>
                                                </div>
                                            </div>';
                                        }
                                        if (in_array("prcloud_protect", $_SESSION['permission'])) {
                                            echo '<div class="form-group display-none num-div">
                                                <label class="control-label col-md-3 ops-label">' . $LANG['WEB_TENANT_OPS_NUM'] . '</label>
                                                <div class="col-md-4">
                                                    <div class="input-group spinner-group opsNumDiv">
                                                        <input type="text" id="opsNum" onkeyup="value=value.replace(/[^\d]/g,\'\')" class="spinner-input form-control input-sm" maxlength="3">
                                                        <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                            <button type="button" class="btn spinner-up default input-sm">
                                                                <i class="fa fa-angle-up"></i>
                                                            </button>
                                                            <button type="button" class="btn spinner-down default input-sm">
                                                                <i class="fa fa-angle-down"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <span class="add-tenant-des">' . $LANG['WEB_TENANT_FREE_NUM'] . '<span class="ops-free-num"></span></span>
                                                </div>
                                            </div>';
                                        }
                                        if (in_array("filebackup", $_SESSION['permission'])) {
                                            echo '<div class="form-group display-none num-div file-div file-mudule">
                                                         <label class="control-label col-md-3">' . $LANG['WEB_PLATFORM_DES_FS'] . '</label>
                                                         <div class="col-md-4">
                                                             <div class="input-group spinner-group fileNumDiv">
                                                                 <input type="text" id="fileNum" onkeyup="value=value.replace(/[^\d]/g,\'\')" class="spinner-input form-control input-sm" maxlength="3">
                                                                 <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                     <button type="button" class="btn spinner-up default input-sm">
                                                                         <i class="fa fa-angle-up"></i>
                                                                     </button>
                                                                     <button type="button" class="btn spinner-down default input-sm">
                                                                         <i class="fa fa-angle-down"></i>
                                                                     </button>
                                                                 </div>
                                                             </div>
                                                             <span class="add-tenant-des">' . $LANG['WEB_TENANT_FREE_NUM'] . '<span class="file-free-num"></span></span>
                                                         </div>
                                                     </div>';
                                        }
                                        if (in_array("db_protect", $_SESSION['permission'])) {
                                            echo '<div class="form-group display-none num-div db-div">
                                                       <label class="control-label col-md-3">' . $LANG['WEB_PLATFORM_DES_DB'] . '</label>
                                                       <div class="col-md-4">
                                                           <div class="input-group spinner-group dbNumDiv">
                                                               <input type="text" id="dbNum" onkeyup="value=value.replace(/[^\d]/g,\'\')" class="spinner-input form-control input-sm" maxlength="3">
                                                               <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                   <button type="button" class="btn spinner-up default input-sm">
                                                                       <i class="fa fa-angle-up"></i>
                                                                   </button>
                                                                   <button type="button" class="btn spinner-down default input-sm">
                                                                       <i class="fa fa-angle-down"></i>
                                                                   </button>
                                                               </div>
                                                           </div>
                                                           <span class="add-tenant-des">' . $LANG['WEB_TENANT_FREE_NUM'] . '<span class="db-free-num"></span></span>
                                                       </div>
                                                   </div>';
                                        }
                                        if (in_array("osbackup", $_SESSION['permission']) || in_array("complete_machine", $_SESSION['permission'])) {
                                            echo '<div class="form-group display-none num-div machine-div">
                                                        <label class="control-label col-md-3">' . $LANG['UI_COMPLETE_MACHINE'] . '</label>
                                                        <div class="col-md-4">
                                                            <div class="input-group spinner-group osNumDiv">
                                                                <input type="text" id="osNum" onkeyup="value=value.replace(/[^\d]/g,\'\')" class="spinner-input form-control input-sm" maxlength="3">
                                                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                    <button type="button" class="btn spinner-up default input-sm">
                                                                        <i class="fa fa-angle-up"></i>
                                                                    </button>
                                                                    <button type="button" class="btn spinner-down default input-sm">
                                                                        <i class="fa fa-angle-down"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <span class="add-tenant-des">' . $LANG['WEB_TENANT_FREE_NUM'] . '<span class="os-free-num"></span></span>
                                                        </div>
                                                    </div>';
                                        }
                                        if (in_array("nas_protect", $_SESSION['permission'])) {
                                            echo '<div class="form-group display-none num-div file-mudule">
                                                           <label class="control-label col-md-3">' . $LANG['UI_PLATFORM_NAS_DEVICE'] . '</label>
                                                           <div class="col-md-4">
                                                               <div class="input-group spinner-group nasNumDiv">
                                                                   <input type="text" id="nasNum" onkeyup="value=value.replace(/[^\d]/g,\'\')" class="spinner-input form-control input-sm" maxlength="3">
                                                                   <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                       <button type="button" class="btn spinner-up default input-sm">
                                                                           <i class="fa fa-angle-up"></i>
                                                                       </button>
                                                                       <button type="button" class="btn spinner-down default input-sm">
                                                                           <i class="fa fa-angle-down"></i>
                                                                       </button>
                                                                   </div>
                                                               </div>
                                                               <span class="add-tenant-des">' . $LANG['WEB_TENANT_FREE_NUM'] . '<span class="nas-free-num"></span></span>
                                                           </div>
                                                       </div>';
                                        }
                                        if (in_array("office365_protect", $_SESSION['permission'])) {
                                            echo '<div class="form-group display-none num-div">
                                                        <label class="control-label col-md-3">' . $LANG['WEB_TENANT_M365_USER'] . '</label>
                                                        <div class="col-md-4">
                                                            <div class="input-group spinner-group m365NumDiv">
                                                                <input type="text" id="m365Num" onkeyup="value=value.replace(/[^\d]/g,\'\')" class="spinner-input form-control input-sm" maxlength="3">
                                                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                    <button type="button" class="btn spinner-up default input-sm">
                                                                        <i class="fa fa-angle-up"></i>
                                                                    </button>
                                                                    <button type="button" class="btn spinner-down default input-sm">
                                                                        <i class="fa fa-angle-down"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <span class="add-tenant-des">' . $LANG['WEB_TENANT_FREE_NUM'] . '<span class="m365-free-num"></span></span>
                                                        </div>
                                                    </div>';
                                        }
                                        if (in_array("office365_protect", $_SESSION['permission'])) {
                                            echo '<div class="form-group display-none num-div">
                                                        <label class="control-label col-md-3">' . $LANG['WEB_TENANT_M365_USER2'] . '</label>
                                                        <div class="col-md-4">
                                                            <div class="input-group spinner-group m365OnlineNumDiv">
                                                                <input type="text" id="m365OnlineNum" onkeyup="value=value.replace(/[^\d]/g,\'\')" class="spinner-input form-control input-sm" maxlength="3">
                                                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                    <button type="button" class="btn spinner-up default input-sm">
                                                                        <i class="fa fa-angle-up"></i>
                                                                    </button>
                                                                    <button type="button" class="btn spinner-down default input-sm">
                                                                        <i class="fa fa-angle-down"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <span class="add-tenant-des">' . $LANG['WEB_TENANT_FREE_NUM'] . '<span class="m365Online-free-num"></span></span>
                                                        </div>
                                                    </div>';
                                        }
                                        if (in_array("hadoop_protect", $_SESSION['permission'])) {
                                            echo '<div class="form-group display-none num-div file-mudule">
                                                    <label class="control-label col-md-3">' . $LANG['WEB_HADOOP_CLUSTER'] . '</label>
                                                    <div class="col-md-4">
                                                        <div class="input-group spinner-group hadoopNumDiv">
                                                            <input type="text" id="hadoopNum" onkeyup="value=value.replace(/[^\d]/g,\'\')" class="spinner-input form-control input-sm" maxlength="3">
                                                            <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                <button type="button" class="btn spinner-up default input-sm">
                                                                    <i class="fa fa-angle-up"></i>
                                                                </button>
                                                                <button type="button" class="btn spinner-down default input-sm">
                                                                    <i class="fa fa-angle-down"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <span class="add-tenant-des">' . $LANG['WEB_TENANT_FREE_NUM'] . '<span class="hadoop-free-num"></span></span>
                                                    </div>
                                                </div>';
                                        }
                                        if (in_array("obs_protect", $_SESSION['permission'])) {
                                            echo '<div class="form-group display-none num-div file-mudule">
                                                        <label class="control-label col-md-3">' . $LANG['WEB_PLATFORM_DES_OBS'] . '</label>
                                                        <div class="col-md-4">
                                                            <div class="input-group spinner-group obsNumDiv">
                                                                <input type="text" id="obsNum" onkeyup="value=value.replace(/[^\d]/g,\'\')" class="spinner-input form-control input-sm" maxlength="3">
                                                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                    <button type="button" class="btn spinner-up default input-sm">
                                                                        <i class="fa fa-angle-up"></i>
                                                                    </button>
                                                                    <button type="button" class="btn spinner-down default input-sm">
                                                                        <i class="fa fa-angle-down"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <span class="add-tenant-des">' . $LANG['WEB_TENANT_FREE_NUM'] . '<span class="obs-free-num"></span></span>
                                                        </div>
                                                    </div>';
                                        }
                                        if (in_array("k8s_protect", $_SESSION['permission'])) {
                                            echo '<div class="form-group display-none num-div">
                                                        <label class="control-label col-md-3">Kubernetes</label>
                                                        <div class="col-md-4">
                                                            <div class="input-group spinner-group k8sNumDiv">
                                                                <input type="text" id="k8sNum" onkeyup="value=value.replace(/[^\d]/g,\'\')" class="spinner-input form-control input-sm" maxlength="3">
                                                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                    <button type="button" class="btn spinner-up default input-sm">
                                                                        <i class="fa fa-angle-up"></i>
                                                                    </button>
                                                                    <button type="button" class="btn spinner-down default input-sm">
                                                                        <i class="fa fa-angle-down"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <span class="add-tenant-des">' . $LANG['WEB_TENANT_FREE_NUM'] . '<span class="k8s-free-num"></span></span>
                                                        </div>
                                                    </div>';
                                        }
                                        if (in_array("machine_copy", $_SESSION['permission']) || in_array("vol_cdp_copy", $_SESSION['permission'])) {
                                            echo '<div class="form-group display-none copy-mudule num-div">
                                                        <label class="control-label col-md-3">' . $LANG['WEB_MACHINE_COPY_PROTECT'] . '</label>
                                                        <div class="col-md-4">
                                                            <div class="input-group spinner-group machinecopyNumDiv">
                                                                <input type="text" id="machinecopyNum" onkeyup="value=value.replace(/[^\d]/g,\'\')" class="spinner-input form-control input-sm" maxlength="3">
                                                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                    <button type="button" class="btn spinner-up default input-sm">
                                                                        <i class="fa fa-angle-up"></i>
                                                                    </button>
                                                                    <button type="button" class="btn spinner-down default input-sm">
                                                                        <i class="fa fa-angle-down"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <span class="add-tenant-des">' . $LANG['WEB_TENANT_FREE_NUM'] . '<span class="machinecopy-free-num"></span></span>
                                                        </div>
                                                    </div>';
                                        }
                                        if (in_array("file_copy_protect", $_SESSION['permission'])) {
                                            echo '<div class="form-group display-none copy-mudule num-div">
                                                        <label class="control-label col-md-3">' . $LANG['UI_FILE_COPY'] . '</label>
                                                        <div class="col-md-4">
                                                            <div class="input-group spinner-group filecopyNumDiv">
                                                                <input type="text" id="filecopyNum" onkeyup="value=value.replace(/[^\d]/g,\'\')" class="spinner-input form-control input-sm" maxlength="3">
                                                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                    <button type="button" class="btn spinner-up default input-sm">
                                                                        <i class="fa fa-angle-up"></i>
                                                                    </button>
                                                                    <button type="button" class="btn spinner-down default input-sm">
                                                                        <i class="fa fa-angle-down"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <span class="add-tenant-des">' . $LANG['WEB_TENANT_FREE_NUM'] . '<span class="filecopy-free-num"></span></span>
                                                        </div>
                                                    </div>';
                                        }
                                        if (in_array("file_copy_protect", $_SESSION['permission'])) {
                                            echo '<div class="form-group display-none copy-mudule num-div">
                                                        <label class="control-label col-md-3">' . $LANG['UI_FILE_COPY_NAS'] . '</label>
                                                        <div class="col-md-4">
                                                            <div class="input-group spinner-group nascopyNumDiv">
                                                                <input type="text" id="nascopyNum" onkeyup="value=value.replace(/[^\d]/g,\'\')" class="spinner-input form-control input-sm" maxlength="3">
                                                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                    <button type="button" class="btn spinner-up default input-sm">
                                                                        <i class="fa fa-angle-up"></i>
                                                                    </button>
                                                                    <button type="button" class="btn spinner-down default input-sm">
                                                                        <i class="fa fa-angle-down"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <span class="add-tenant-des">' . $LANG['WEB_TENANT_FREE_NUM'] . '<span class="nascopy-free-num"></span></span>
                                                        </div>
                                                    </div>';
                                        }
                                        if (in_array("dbcdpcopy", $_SESSION['permission'])) {
                                            echo '<div class="form-group display-none copy-mudule num-div">
                                                        <label class="control-label col-md-3">' . $LANG['UI_DB_CDP_COPY'] . '</label>
                                                        <div class="col-md-4">
                                                            <div class="input-group spinner-group dbcdpcopyNumDiv">
                                                                <input type="text" id="dbcdpcopyNum" onkeyup="value=value.replace(/[^\d]/g,\'\')" class="spinner-input form-control input-sm" maxlength="3">
                                                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                    <button type="button" class="btn spinner-up default input-sm">
                                                                        <i class="fa fa-angle-up"></i>
                                                                    </button>
                                                                    <button type="button" class="btn spinner-down default input-sm">
                                                                        <i class="fa fa-angle-down"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <span class="add-tenant-des">' . $LANG['WEB_TENANT_FREE_NUM'] . '<span class="dbcdpcopy-free-num"></span></span>
                                                        </div>
                                                    </div>';
                                        }
                                    ?>
                                    </div> 
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="drawer-footer">
                <div class="tenant-foot">
                    <button type="button" class="btn default cancel mr8" data-dismiss="drawer" aria-label="Close"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
                    <button type="button" class="btn default button-previous mr8" ><?php echo $LANG['UI_PUBLIC_PREV_STEP'] ?></button>
                    <button type="button" class="btn green-turquoise button-next mr8" ><?php echo $LANG['UI_PUBLIC_NEXT_STEP'] ?></button>
                    <button type="button" class="btn green-haze button-submit mr8" id="add_submit"><?php echo $LANG['UI_PUBLIC_SUBMIT'] ?></button>
                    <button type="button" class="btn green-haze button-submit mr8 display-none" id="edit_submit" ><?php echo $LANG['UI_PUBLIC_SUBMIT'] ?></button>
                </div>
            </div>
        </div>
        <!-- drawer结束 -->
        <!-- 详情drawer开始 -->
        <div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true" id="tenantDetail">
            <div class="drawer-content drawer-content-scrollable" role="document">
                <div class="drawer-header">
                    <h4 class="drawer-title" id="drawer-1-title">
                        <i class="viconfont vicon-tenant-detail mr8"></i><?php echo $LANG['WEB_TENANT_DETAIL'] ?>
                        <span class="detail-tenant-name"></span>
                        <span data-dismiss="drawer" aria-label="Close" class="drawer-close"><i class="viconfont vicon-guanbi"></i></span>
                    </h4>
                </div>
                <div class="drawer-body">
                    <div class="tenant-title"><span class="green-line"></span><?php echo $LANG['UI_TENANT_RESOURCE'] ?></div>
                    <div class="card-box">
                        <div class="col-md-4 card-parent display-none card-parent-storage">
                            <div class="card">
                                <div class="viconfont vicon-beifencunchu1 card-icon"></div>
                                <span class="detail-storage-label"><?php echo $LANG['UI_PUBLIC_CAPACITY'] ?></span>
                                <p class="detail-storage"></p>
                            </div>
                        </div>
                        <?php
                        if (in_array("vmprotect", $_SESSION['permission'])) {
                            echo '<div class="col-md-4 card-parent display-none num-div">
                                   <div class="card">
                                       <div class="viconfont vicon-vcenter_manager card-icon"></div>
                                       <span class="detail-vm-label">'. $LANG['WEB_PLATFORM_DES_VM'] .'</span>
                                       <p class="detail-vm"></p>
                                   </div>
                               </div>';
                        }
                        if (in_array("fileprotect", $_SESSION['permission']) || in_array("db_protect", $_SESSION['permission']) || in_array("osbackup", $_SESSION['permission']) || in_array("complete_machine", $_SESSION['permission'])) {
                            echo '<div class="col-md-4 card-parent display-none agent-card-detail">
                                   <div class="card">
                                       <div class="viconfont vicon-client card-icon"></div>
                                       <span class="detail-agent-label">'. $LANG['UI_JOB_CLIENT'] .'</span>
                                       <p class="detail-agent"></p>
                                   </div>
                               </div>';
                        }
                        if (in_array("awsprotect", $_SESSION['permission'])) {
                            echo '<div class="col-md-4 card-parent display-none num-div">
                                   <div class="card">
                                       <div class="viconfont vicon-overview-plubic-cloud card-icon"></div>
                                       <span class="detail-aws-label">' . $LANG['WEB_TENANT_AWS_NUM'] . '</span>
                                       <p class="detail-aws"></p>
                                   </div>
                               </div>';
                        }
                        if (in_array("prcloud_protect", $_SESSION['permission'])) {
                            echo '<div class="col-md-4 card-parent display-none num-div">
                                   <div class="card">
                                       <div class="viconfont vicon-overview-private-cloud card-icon"></div>
                                       <span class="detail-ops-label">' . $LANG['WEB_TENANT_OPS_NUM'] . '</span>
                                       <p class="detail-ops"></p>
                                   </div>
                               </div>';
                        }
                        if (in_array("fileprotect", $_SESSION['permission'])) {
                            echo '<div class="col-md-4 card-parent display-none num-div file-mudule">
                                      <div class="card">
                                          <div class="viconfont vicon-fileprotect card-icon"></div>
                                          <span>' . $LANG['WEB_PLATFORM_DES_FS'] . '</span>
                                          <p class="detail-file"></p>
                                      </div>
                                  </div>';
                        }
                        if (in_array("db_protect", $_SESSION['permission'])) {
                            echo ' <div class="col-md-4 card-parent display-none num-div db-div">
                                       <div class="card">
                                           <div class="viconfont vicon-shujuku card-icon"></div>
                                           <span>' . $LANG['WEB_PLATFORM_DES_DB'] . '</span>
                                           <p class="detail-db"></p>
                                       </div>
                                   </div>';
                        }
                        if (in_array("osbackup", $_SESSION['permission']) || in_array("complete_machine", $_SESSION['permission'])) {
                            echo '  <div class="col-md-4 card-parent display-none num-div machine-div">
                                        <div class="card">
                                            <div class="viconfont vicon-os_protect card-icon"></div>
                                            <span>' . $LANG['UI_COMPLETE_MACHINE'] . '</span>
                                            <p class="detail-os"></p>
                                        </div>
                                    </div>';
                        }
                        if (in_array("nas_protect", $_SESSION['permission'])) {
                            echo ' <div class="col-md-4 card-parent display-none num-div file-mudule">
                                       <div class="card">
                                           <div class="viconfont vicon-nasmanager card-icon"></div>
                                           <span>' . $LANG['UI_PLATFORM_NAS_DEVICE'] . '</span>
                                           <p class="detail-nas"></p>
                                       </div>
                                   </div>';
                        }
                        if (in_array("office365_protect", $_SESSION['permission'])) {
                            echo '<div class="col-md-4 card-parent display-none num-div">
                                       <div class="card">
                                           <div class="viconfont vicon-windows-view card-icon"></div>
                                           <span>' . $LANG['WEB_TENANT_M365_USER'] . '</span>
                                           <p class="detail-m365"></p>
                                       </div>
                                   </div>';
                        }
                        if (in_array("office365_protect", $_SESSION['permission'])) {
                            echo '<div class="col-md-4 card-parent display-none num-div">
                                       <div class="card">
                                           <div class="viconfont vicon-windows-view card-icon"></div>
                                           <span>' . $LANG['WEB_TENANT_M365_USER2'] . '</span>
                                           <p class="detail-m365-online"></p>
                                       </div>
                                   </div>';
                        }
                        if (in_array("hadoop_protect", $_SESSION['permission'])) {
                            echo '<div class="col-md-4 card-parent display-none num-div file-mudule">
                                      <div class="card">
                                          <div class="viconfont vicon-hadoop card-icon"></div>
                                          <span>' . $LANG['WEB_HADOOP_CLUSTER'] . '</span>
                                          <p class="detail-hadoop"></p>
                                      </div>
                                  </div>';
                        }
                        if (in_array("obs_protect", $_SESSION['permission'])) {
                            echo '<div class="col-md-4 card-parent display-none num-div file-mudule">
                                       <div class="card">
                                           <div class="viconfont vicon-obs card-icon"></div>
                                           <span>' . $LANG['WEB_PLATFORM_DES_OBS'] . '</span>
                                           <p class="detail-obs"></p>
                                       </div>
                                   </div>';
                        }
                        if (in_array("k8s_protect", $_SESSION['permission'])) {
                            echo '<div class="col-md-4 card-parent display-none num-div">
                                       <div class="card">
                                           <div class="viconfont vicon-overciew-k8s card-icon"></div>
                                           <span>Kubernetes</span>
                                           <p class="detail-k8s"></p>
                                       </div>
                                   </div>';
                        }
                        if (in_array("machine_copy", $_SESSION['permission']) || in_array("vol_cdp_copy", $_SESSION['permission'])) {
                            echo '<div class="col-md-4 card-parent">
                                       <div class="card">
                                           <div class="viconfont vicon-overview-complete-machine card-icon"></div>
                                           <span>' . $LANG['WEB_MACHINE_COPY_PROTECT'] . '</span>
                                           <p class="detail-machinecopy"></p>
                                       </div>
                                   </div>';
                        }
                        if (in_array("file_copy_protect", $_SESSION['permission'])) {
                            echo '<div class="col-md-4 card-parent">
                                       <div class="card">
                                           <div class="viconfont vicon-fuzhiliebiao card-icon"></div>
                                           <span>' . $LANG['UI_FILE_COPY'] . '</span>
                                           <p class="detail-filecopy"></p>
                                       </div>
                                   </div>';
                        }
                        if (in_array("file_copy_protect", $_SESSION['permission'])) {
                            echo '<div class="col-md-4 card-parent">
                                       <div class="card">
                                           <div class="viconfont vicon-overview-nas card-icon"></div>
                                           <span>' . $LANG['UI_FILE_COPY_NAS'] . '</span>
                                           <p class="detail-nascopy"></p>
                                       </div>
                                   </div>';
                        }
                        if (in_array("dbcdpcopy", $_SESSION['permission'])) {
                            echo '<div class="col-md-4 card-parent">
                                       <div class="card">
                                           <div class="viconfont vicon-tongbu card-icon"></div>
                                           <span>' . $LANG['UI_DB_CDP_COPY'] . '</span>
                                           <p class="detail-dbcdpcopy"></p>
                                       </div>
                                   </div>';
                        }
                        ?>
                    </div>
                    <!-- <div class="col-md-12 recover-div">
                        <div class="viconfont vicon-zhuji card-icon"></div>
                        <span><?php echo $LANG['UI_VOL_CDP_RECOVER_TARGET_MACHINE'] ?></span>
                        <div class="recovery-display"></div>
                    </div> -->
                    <div class="col-md-12 storage-div">
                        <div class="viconfont vicon-cunchuku card-icon"></div>
                        <span><?php echo $LANG['UI_DATACENTER_BACKUP_STORAGE'] ?></span>
                        <div class="storage-display"></div>
                    </div>
                    <div class="col-md-12 pd0 title-user tenant-title"><span class="green-line"></span><?php echo $LANG['UI_PLATFORM_USER'] ?></div>
                    <div class="table-container col-md-12 pd0">
                        <table id="user_table"></table>
                    </div>
                </div>
                <div class="drawer-footer">
                    <div class="tenant-foot">
                        <a href="javascript:;" class="btn default mr10" data-dismiss="drawer" aria-label="Close" >
                            <?php echo $LANG['UI_PUBLIC_CLOSE'] ?>
                        </a>
                    </div>
                </div>
            </div>
            <!-- 详情drawer结束 -->
    </div>

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/platform/tenant/tenant_manager.js"></script>
<!-- END PAGE LEVEL PLUGINS -->