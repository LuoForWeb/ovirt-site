<?php
include_once '../../tpl/permission.php';
// $userAllPermission = $_SESSION['permission'];
include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php';
?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link href="./assets/global/plugins/jquery-file-upload/css/jquery.fileupload.css" rel="stylesheet"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
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
    <span class="curent"><?php echo $LANG['UI_PLATFORM_MICROSOFT365'] ?></span>
</h3>
<!-- END PAGE HEADER-->

<!-- BEGIN PAGE CONTENT-->
<div class="resource-manager-wrap" id="m365-content-organization">
    <div class="resource-manager-wrap__header">
        <span>
            <i class="levelchild viconfont vicon-zuzhi me-10"></i>
            <span class="resource-manager-wrap__header__text"><?php echo $LANG['WEB_M365_ORGANIZATION_MANAGE'] ?></span>
        </span>
    </div>
    <div class="resource-manager-wrap__content m365-table">
        <div class="table-toolbar">
            <div class="vin_toolbar" id="vin_m365_toolbar">
                <div class="leftTool"></div>
                <div class="rightTool">
                    <div class="vin_btnToolbar"></div>
                </div>
            </div>
        </div>

        <div class="table-container m365-table-contanier">
            <table class="table" id="table"></table>
        </div>

        <div class="alert alert-block alert-info fade in h-150px m0" id="m365_tip">
            <button id="m365_tip_close" type="button" class="close" data-dismiss="alert"></button>
            <h4 class="alert-heading">
                <strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong>
            </h4>
            <ol class = "alert-ol">
                <li>
                    <?php echo $LANG['WEB_M365_ORGANIZATION_TIP_ONE'] ?>
                </li>
                <li>
                    <?php echo $LANG['WEB_M365_ORGANIZATION_TIP_TWO'] ?>
                </li>
                <li>
                    <?php echo $LANG['WEB_M365_ORGANIZATION_TIP_THREE'] ?>
                </li>
                <li>
                    <?php echo $LANG['WEB_M365_ORGANIZATION_TIP_FOUR'] ?>
                </li>
            </ol>
        </div>
    </div>
</div>

<!-- END PAGE CONTENT-->
 <!--添加模态框开始-->
<div id="m365AddModal" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
    <div id="uuid" class="display-none"></div>
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title add-title">
            <i class="viconfont vicon-ge_add_task mr8"></i> <?php echo $LANG['WEB_M365_COMMON_OP_CODE_ADD_ORGANIZATION'] ?>
        </h4>
        <h4 class="modal-title edit-title">
            <i class="viconfont vicon-a-Editbianji mr8"></i><?php echo $LANG['WEB_M365_COMMON_OP_CODE_EDIT_ORGANIZATION'] ?>
        </h4>
    </div>
    <div class="modal-body">
        <div class="portlet-body form">
            <form action="#" class="form-horizontal" id="submit_form" method="POST" style="height:100%;">
            <div class="form-wizard" style="height: 100%;">
                <div class="form-body" style="padding:0 20px;">
                    <ul class="nav nav-pills nav-justified steps" style="margin-bottom:10px;height:10%;">
                        <li>
                            <a href="#tab1" data-toggle="tab" class="step">
                                <span class="number"> 1 </span>
                                <span class="desc">
                                    <?php echo $LANG['UI_MICROSOFT365_AUTHENTY_METHOD'] ?><i class="fa fa-check"></i>
                                </span>
                            </a>
                        </li>
                        <li>
                            <a href="#tab2" data-toggle="tab" class="step">
                                <span class="number"> 2 </span>
                                <span class="desc">
                                    <?php echo $LANG['UI_MICROSOFT365_CONFIGURE_AUTHENTY_PARAMS'] ?><i class="fa fa-check"></i>
                                </span>
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content" style="height:90%;overflow-x:hidden;overflow-y:auto;">
                        <div class="tab-pane active" id="tab1">
                            <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="control-label col-md-4">
                                                <?php echo $LANG['UI_MICROSOFT365_SELECT_REGION'] ?>
                                            </label>
                                            <div class="col-md-6">
                                                <select class="form-control select2me" id="regionType" >
                                                    <option value="1"><?php echo $LANG['UI_MICROSOFT365_INTERNAL_VERSION'] ?></option>
<!--                                                                        <option value="2">中国版</option>-->
                                                    <option value="100"><?php echo $LANG['UI_MICROSOFT365_LOCAL_VERSION'] ?></option>
                                                </select>
                                            </div>
                                        </div>
                                            <div class="form-group">
                                            <label class="control-label col-md-4">
                                                <?php echo $LANG['UI_MICROSOFT365_SELECT_TYPE'] ?>
                                            </label>
                                            <div class="col-md-6">
                                                <select class="form-control select2me" id="organizationType" >
                                                    <option value="1">Exchange Online</option>
                                                    <option value="2" class="display-none">Exchange Server</option>
                                                </select>
                                            </div>
                                            </div>
                                            <div class="form-group conectionTypeDiv">
                                            <label class="control-label col-md-4">
                                                <?php echo $LANG['UI_MICROSOFT365_SELECT_ORGANIZATION_METHOD'] ?>
                                            </label>
                                            <div class="col-md-6">
                                                <select class="form-control select2me" id="conectionType" >
                                                    <option value="1"><?php echo $LANG['UI_MICROSOFT365_AUTO_REGIST_AZUREAD'] ?></option>
                                                    <option value="2"><?php echo $LANG['UI_MICROSOFT365_USE_EXIST_AZUREAD'] ?></option>
                                                </select>
                                            </div>
                                            <a class="popovers ml8" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_MICROSOFT365_SELECT_ORGANIZATION_METHOD_TIP'] ?>" data-original-title="" title="">
                                                <i class="viconfont vicon-tishi fa-lg"></i>
                                            </a>
                                            </div>
                                            <div class="form-group authenticationDiv">
                                            <label class="control-label col-md-4">
                                                <?php echo $LANG['UI_MICROSOFT365_AUTHENTY_METHOD'] ?>
                                            </label>
                                            <div class="col-md-6">
                                                <select class="form-control select2me" id="authentication" >
                                                    <option value="1"><?php echo $LANG['UI_MICROSOFT365_TRADITION_AUTHENTY'] ?></option>
                                                    <option value="2"><?php echo $LANG['UI_MICROSOFT365_CERTIFY_AUTHENTY'] ?></option>
                                                </select>
                                            </div>
                                            <a class="popovers ml8" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_MICROSOFT365_AUTHENTY_METHOD_TIP'] ?>" data-original-title="" title="">
                                                <i class="viconfont vicon-tishi fa-lg"></i>
                                            </a>
                                            </div>
                                            <!-- 跳过证书认证 -->
                                            <div class="form-group skip-cert-auth-form">
                                                <label class="control-label col-md-4">
                                                    <?php echo $LANG['WEB_M365_SKIP_CERT_VERIFY'] ?>
                                                </label>
                                                <div class="col-md-1 mt4 mr15">
                                                    <input type="checkbox" id="skip_cert_auth_flag" class="make-switch" data-on-color="primary" data-off-color="info" data-size="small" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                </div>
                                                <a class="popovers ml8" data-container="body" style="margin: -3px 0 0 0;display: inline-block;" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['WEB_M365_SKIP_CERT_VERIFY_TIPS'] ?>" data-original-title="" title="">
                                                    <i class="viconfont vicon-tishi fa-lg"></i>
                                                </a>
                                            </div>
                                            <!--提示信息-->
                                            <div class="alert alert-block alert-info fade in" id="marktips" style="margin: 32px 0 0 0;">
                                                <button type="button" class="close" data-dismiss="alert"></button>
                                                <ul class="alert-ul">
                                                    <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                                                    <li>
                                                    <?php echo $LANG['UI_MICROSOFT365_AZUREAD_TIP'] ?>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="tab2">
                                <div class="row">
                                    <div class="col-md-12">
<!--                                                            online开始-->
                                        <div class="form-group ADnameDiv">
                                            <label class="control-label col-md-4">
                                                <?php echo $LANG['UI_MICROSOFT365_AZUREAD_APP_NAME'] ?>
                                            </label>
                                            <div class="col-md-6">
                                                <input type="text" maxlength="128" placeholder="<?php echo $LANG['WEB_M365_AD_NAME_TIPS'] ?>" class="form-control" id="ADname">
                                            </div>
                                        </div>
<!--                                                            用户名-->
                                        <div class="form-group usernameDiv display-none">
                                            <label class="control-label col-md-4">
                                                <?php echo $LANG['UI_MICROSOFT365_USER_NAME'] ?>
                                            </label>
                                            <div class="col-md-6">
                                                <input type="text" maxlength="128" placeholder="admin@7c5l6x.onmicrosoft.com" class="form-control" id="userName">
                                                <span class="serverDes"> <?php echo $LANG['UI_MICROSOFT365_USER_NAME_DES'] ?></span>
                                            </div>
                                        </div>
<!--                                                            租户ID-->
                                        <div class="form-group tenantIdDiv display-none">
                                            <label class="control-label col-md-4">
                                                <?php echo $LANG['UI_MICROSOFT365_TENANT_ID'] ?>
                                            </label>
                                            <div class="col-md-6">
                                                <input type="text" maxlength="128" placeholder="4es27f3e-c232-45a7-91d7-3eabbd74a764" class="form-control" id="tenantId">
                                            </div>
                                        </div>
<!--                                                            Azure AD应用程序ID-->
                                        <div class="form-group AzureIdDiv display-none">
                                            <label class="control-label col-md-4">
                                                <?php echo $LANG['UI_MICROSOFT365_AZUREAD_APP_ID'] ?>
                                            </label>
                                            <div class="col-md-6">
                                                <input type="text" maxlength="128" placeholder="a5b7ac8d-266b-4efc-89f4-d73c97c64894" class="form-control" id="AzureId">
                                                <span class="serverDes"><?php echo $LANG['UI_MICROSOFT365_AZUREAD_APP_ID_DES'] ?></span>
                                            </div>
                                        </div>
<!--                                                            Azure AD应用程序密码-->
                                        <div class="form-group AzurePswDiv display-none">
                                            <label class="control-label col-md-4">
                                                <?php echo $LANG['UI_MICROSOFT365_AZUREAD_APP_PASSWORD'] ?>
                                            </label>
                                            <div class="col-md-6">
                                                <input type="password" maxlength="128" placeholder="a5b7ac8d-266b-4efc-89f4-d73c97c64894" class="form-control" id="AzurePsw">
                                                <span class="serverDes"><?php echo $LANG['UI_MICROSOFT365_AZUREAD_APP_ID_DES'] ?></span>
                                            </div>
                                        </div>
<!--                                                            选择证书类型-->
                                        <div class="form-group certSelectDiv display-none">
                                            <label class="control-label col-md-4">
                                                <?php echo $LANG['UI_MICROSOFT365_SELECT_CERTIFICATE_TYPE'] ?>
                                            </label>
                                            <div class="col-md-6">
                                                <select class="form-control" id="certSelect" >
                                                    <option value="1"><?php echo $LANG['UI_MICROSOFT365_GENERATE_SIGN_CERTIFICATE'] ?></option>
                                                    <option value="2"><?php echo $LANG['UI_MICROSOFT365_IMPORT_PFX_CERTIFICATE'] ?></option>
                                                </select>
                                            </div>
                                            <a class="popovers ml8" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_MICROSOFT365_SELECT_CERTIFICATE_TYPE_TIP'] ?>" data-original-title="" title="">
                                                <i class="viconfont vicon-tishi fa-lg"></i>
                                            </a>
                                        </div>
                                        <div class="form-group pfxCertDiv display-none">
                                            <label class="control-label col-md-4">
                                                <?php echo $LANG['UI_MICROSOFT365_PFX_CERTIFICATE_TYPE'] ?>
                                            </label>
                                            <div class="col-md-6">
                                                <input type="file" id="pfxCert" accept=".pfx">
                                                <div id="fileList"></div>
                                                <label for="pfxCert" class="lookBtn"><?php echo $LANG['UI_MICROSOFT365_BROWSE'] ?></label>
                                            </div>
                                        </div>
                                        <div class="form-group pfxCertPswDiv display-none">
                                            <label class="control-label col-md-4">
                                                <?php echo $LANG['UI_MICROSOFT365_PFX_CERTIFICATE_PASSWORD'] ?>
                                            </label>
                                            <div class="col-md-6">
                                                <input type="password" maxlength="128" class="form-control" id="pfxCertPsw">
                                            </div>
                                            <a class="popovers ml8" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_MICROSOFT365_PFX_CERTIFICATE_PASSWORD_TIP'] ?>" data-original-title="" title="">
                                                <i class="viconfont vicon-tishi fa-lg"></i>
                                            </a>
                                        </div>
<!--                                                            online结束-->
<!--                                                            server开始-->
                                        <div class="form-group ADdomainDiv display-none">
                                            <label class="control-label col-md-4">
                                                <?php echo $LANG['UI_MICROSOFT365_PC_FULL_NAME'] ?>
                                            </label>
                                            <div class="col-md-6">
                                                <input type="text" maxlength="128" class="form-control" id="ADdomain">
                                                <span class="serverDes"><?php echo $LANG['UI_MICROSOFT365_PC_FULL_NAME_TIP'] ?></span>
                                            </div>
                                        </div>
                                        <div class="form-group managerNameDiv display-none">
                                            <label class="control-label col-md-4">
                                                <?php echo $LANG['UI_MICROSOFT365_ADMINISTRATOR_ACCOUNT'] ?>
                                            </label>
                                            <div class="col-md-6">
                                                <input type="text" maxlength="128" class="form-control" id="managerName">
                                                <span class="serverDes"><?php echo $LANG['UI_MICROSOFT365_ADMINISTRATOR_ACCOUNT_TIP'] ?></span>
                                            </div>
                                        </div>
                                        <div class="form-group managerPwdDiv display-none">
                                            <label class="control-label col-md-4">
                                                <?php echo $LANG['UI_MICROSOFT365_ADMINISTRATOR_PASSWORD'] ?>
                                            </label>
                                            <div class="col-md-6">
                                                <input type="password" maxlength="128" class="form-control" id="managerPwd">
                                                <span class="serverDes"><?php echo $LANG['UI_MICROSOFT365_ADMINISTRATOR_PASSWORD_TIP'] ?></span>
                                            </div>
                                        </div>
                                        <div class="form-group agentConnectDiv display-none">
                                            <label class="control-label col-md-4">
                                                <?php echo $LANG['UI_MICROSOFT365_AGENT_CONNECT'] ?>
                                            </label>
                                            <div class="col-md-6">
                                                <select id="agentConnect" name="" class="bootstrap-mutiple-select selectpicker show-tick" multiple data-dropup-auto="false" data-size="5"></select>
                                            </div>
                                            <a class="popovers ml8" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content=" <?php echo $LANG['UI_MICROSOFT365_AGENT_CONNECT_TIP'] ?>" data-original-title="" title="">
                                                <i class="viconfont vicon-tishi fa-lg"></i>
                                            </a>
                                        </div>
                                        <div class="form-group nicknameDiv">
                                            <label class="control-label col-md-4">
                                                <?php echo $LANG['UI_MICROSOFT365_ORGANIZATION_NICK_NAME'] ?>
                                            </label>
                                            <div class="col-md-6">
                                                <input type="text" maxlength="128" class="form-control" id="nickname">
                                            </div>
                                        </div>
<!--                                                            server结束-->
                                        <!--提示信息-->
                                            <div class="alert alert-block alert-info fade in display-none" id="marktips1" style="margin: 32px 0 0 0;">
                                                <button type="button" class="close" data-dismiss="alert"></button>
                                                <ul class="alert-ul">
                                                    <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                                                    <li>
                                                    <?php echo $LANG['UI_MICROSOFT365_MARK_TIP1'] ?><a href="https://portal.azure.com/" target="_blank"><strong><?php echo $LANG['UI_MICROSOFT365_MARK_TIP2'] ?></strong></a><?php echo $LANG['UI_MICROSOFT365_MARK_TIP3'] ?>
                                                    </li>
                                                </ul>
                                            </div>
                                        <!--提示信息-->
                                        <div class="alert alert-block alert-info fade in display-none" id="marktips2" style="margin-bottom:0;">
                                            <button type="button" class="close" data-dismiss="alert"></button>
                                            <h4 class="alert-heading">
                                                <strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong>
                                            </h4>
                                            <ol class = "alert-ol">
                                                <li>
                                                    <?php echo $LANG['UI_MICROSOFT365_MARK_TIP1'] ?><a href="https://portal.azure.com/" target="_blank"><strong><?php echo $LANG['UI_MICROSOFT365_MARK_TIP2'] ?></strong></a><?php echo $LANG['UI_MICROSOFT365_MARK_TIP3'] ?>
                                                </li>
                                                <li>
                                                    <?php echo $LANG['UI_MICROSOFT365_MARK_TIP4'] ?><strong><?php echo $LANG['UI_MICROSOFT365_MARK_TIP5'] ?></strong>
                                                </li>
                                            </ol>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- modal-footer -->
    <div class="modal-footer" style="margin-top: 22px">
        <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
        <button type="button" class="btn default button-previous" ><?php echo $LANG['UI_PUBLIC_PREV_STEP'] ?></button>
        <button type="button" class="btn green-turquoise button-next " ><?php echo $LANG['UI_PUBLIC_NEXT_STEP'] ?></button>
        <button type="button" class="btn green-haze button-submit" id="add_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
        <button type="button" class="btn green-haze button-submit display-none" id="edit_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>
<!--添加模态框结束-->

<!--验证身份信息模态框开始-->
<div id="addVerify" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title add-title">
            <i class="viconfont vicon-ge_add_task mr8"></i><?php echo $LANG['UI_MICROSOFT365_ADD_ORGANIZATION_AUTHENTY'] ?>
        </h4>
        <h4 class="modal-title edit-title">
            <i class="viconfont vicon-a-Editbianji mr8"></i><?php echo $LANG['UI_MICROSOFT365_EDIT_ORGANIZATION_AUTHENTY'] ?>
        </h4>
    </div>
    <div class="modal-body" style="padding: 20px;">
        <form action="#" id="verifyForm" class="form-horizontal">
            <span style="font-size: 16px;"><?php echo $LANG['UI_MICROSOFT365_LOGIN_MICROSOFT365'] ?></span>
            <div class="verify-content">
                <div class="verify-box">
                    <div><?php echo $LANG['UI_MICROSOFT365_LOGIN_ACCOUNT_TIP'] ?></div>
                    <div><?php echo $LANG['UI_MICROSOFT365_COPY_CODE_VERIFY'] ?></div>
                    <input type="text" id="vertifyCode" disabled="disabled">
                    <i class="viconfont vicon-a-Redozhongxin"></i>
                    <div class="copy-btn-group">
                        <span class="copy">
                                <i class="viconfont vicon-fuzhi" style="margin-right: 3px;"></i><?php echo $LANG['UI_MICROSOFT365_COPY'] ?>
                        </span>
                        <div class="copy-tips display-none"><?php echo $LANG['UI_MICROSOFT365_COPY_SUCCESS'] ?></div>
                    </div>
                    <a href="https://microsoft.com/devicelogin" class="verifyLink c0FBF98" target="_blank"><?php echo $LANG['UI_MICROSOFT365_COPY_CODE_TIP'] ?></a>
                </div>
                <!--验证码请求中-->
                <div class="vertify-waiting">
                    <i class="viconfont vicon-shalou c0FBF98"></i><?php echo $LANG['UI_MICROSOFT365_WAIT_LOGIN'] ?>
                </div>
                <!--验证码请求超时-->
                <div class="vertify-outtime display-none">
                    <i class="viconfont vicon-shalou c0FBF98"></i><?php echo $LANG['UI_MICROSOFT365_VERIFY_OUTTIME_TIP'] ?>
                </div>
                <!--验证码请求成功-->
                <div class="vertify-success display-none"></div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_CANCEL'] ?></button>
        <button type="button" class="btn green-haze button-submit" id="verify_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>
<!--验证身份信息模态框结束-->

<!--自动刷新时间模态框开始-->
<div id="refreshModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">
            <i class="viconfont vicon-a-Group2 mr8"></i><?php echo $LANG['UI_MICROSOFT365_AUTO_REFRESH_TIME_CONFIGURATION'] ?>
        </h4>
    </div>
    <div class="modal-body">
        <div class="form-group">
            <label class="control-label col-md-4">
                <?php echo $LANG['UI_MICROSOFT365_AUTO_REFRESH_TIME_INTERVAL'] ?>
            </label>
            <div class="col-md-6">
                    <input type="number" id="timeConfig" class=" form-control input-sm" maxlength="4">
            </div>
            <div class="col-md-1 pd0" style="line-height: 34px"> <?php echo $LANG['WEB_UTILS_MINUTE'] ?></div>
        </div>
        <!--提示信息-->
        <div class="alert alert-block alert-info fade in">
            <button type="button" class="close" data-dismiss="alert"></button>
            <h4 class="alert-heading">
                <strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong>
            </h4>
            <ol class = "alert-ol">
                <li>
                    <?php echo $LANG['UI_MICROSOFT365_AUTO_REFRESH_TIME_INTERVAL_TIP1'] ?>
                </li>
                <li>
                    <?php echo $LANG['UI_MICROSOFT365_AUTO_REFRESH_TIME_INTERVAL_TIP2'] ?>
                </li>
            </ol>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_CANCEL'] ?></button>
        <button type="button" class="btn green-haze button-submit" id="configSubmmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>
<!--自动刷新时间模态框结束-->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<?php
if ($_SESSION['language'] != 'en-us') {
    //如果不是英文,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
        $_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>

<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script src="./assets/global/plugins/jquery-file-upload/js/vendor/jquery.ui.widget.js"></script>
<script src="./assets/global/plugins/jquery-file-upload/js/jquery.fileupload.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>

<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/exchange/exchange_organization.js"></script>
<!-- END PAGE LEVEL PLUGINS --> 
