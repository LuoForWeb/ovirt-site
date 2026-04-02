<?php include_once '../../../tpl/permission.php'; ?>
<!-- BEGIN PAGE CONTENT-->
<div class="row row-manager">
    <div class="col-md-12 col-manager">
        <!-- BEGIN VALIDATION STATES-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="viconfont vicon-a-yonghubiaogeyong fs18"></i><?php echo $LANG['UI_USER_SELF_INFO'] ?>
                </div>
            </div>
            <div class="portlet-body margin10">
                <div class="user-info-card">
                    <div class="inline-block user-info-name-card">
                        <span class="username_content"></span>
                    </div>
                    <div class="user-info-common inline-block user-info-color-label-card">
                        <?php echo $LANG['UI_MICROSOFT365_CATEGORY']?>：
                    </div>
                    <div class="user-info-common inline-block user-info-color-value-card me-32">
                        <span id="usertype_content"></span>
                    </div>
                    <div class="user-info-common inline-block user-info-color-label-card">
                        <?php echo $LANG['WEB_HISTORY_LOGIN_TIME'];?>：
                    </div>
                    <div class="user-info-common inline-block user-info-color-value-card me-32">
                        <span id="last_login_time_content"></span>
                    </div>
                    <div class="user-info-common inline-block user-info-color-label-card">
                        <?php echo $LANG['UI_PLATFORM_SAFETY_ROLE'];?>：
                    </div>
                    <div class="user-info-common inline-block user-info-color-value-card">
                        <span class="role_str"></span>
                    </div>
                </div>
                <div class="">
                    <div class="col-md-6 pl0">
                        <div class="user-name-info-card flex-center">
                            <div class="col-md-11 pl0">
                                <div class="user-info-title user-info-color-label-card mb-8 fs12"><?php echo $LANG['WEB_KUBE_CLUSTER_USERNAME'];?></div>
                                <div class="user-info-common user-info-color-value-card">
                                    <span class="username_content"></span>
                                </div>
                            </div>
                            <div class="col-md-2 user-info-edit-card">
                                <a href="javascript:void(0)" id="edit_username" class="colorgreen" data-toggle="drawer" data-target="#drawer-username"><?php echo $LANG['UI_PUBLIC_MODIFY'];?></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 pl0">
                        <div class="user-tel-info-card flex-center">
                            <div class="col-md-11 pl0">
                                <div class="user-info-title user-info-color-label-card mb-8 fs12"><?php echo $LANG['UI_USER_PHONE'];?></div>
                                <div class="user-info-common user-info-color-value-card">
                                    <span id="number"></span>
                                </div>
                            </div>
                            <div class="col-md-2 user-info-edit-card">
                                <a href="javascript:void(0)" id="set_number" class="colorgreen" data-toggle="drawer" data-target="#drawer-number">
                                    <span class="set_number_des"></span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <!--登录密码-->
                <div class="">
                    <div class="col-md-6 pl0">
                        <div class="user-password-info-card flex-center">
                            <div class="col-md-11 pl0">
                                <div class="user-info-title user-info-color-label-card mb-8 fs12"><?php echo $LANG['UI_CLIENT_CONNECT_PASSWORD'];?></div>
                                <div class="user-info-common user-info-color-value-card">
                                    <input name="password" type="password" style="border: none;outline: none;" readonly>
                                </div>
                            </div>
                            <div class="col-md-2 user-info-edit-card">
                                <a href="javascript:void(0)" id="edit_password" class="colorgreen" data-toggle="drawer" data-target="#drawer-password"><?php echo $LANG['UI_PUBLIC_MODIFY'];?></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 pl0">
                        <div class="user-tel-info-card flex-center">
                            <div class="col-md-11 pl0">
                                <div class="user-info-title user-info-color-label-card mb-8 fs12"><?php echo $LANG['UI_USER_EMAIL_ADDRESS'];?></div>
                                <div class="user-info-common user-info-color-value-card">
                                    <span id="email_address"></span>
                                </div>
                            </div>
                            <div class="col-md-2 user-info-edit-card">
                                <a href="javascript:void(0)" id="edit_email" class="colorgreen" data-toggle="drawer" data-target="#drawer-email">
                                    <span class="set_email_des"></span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <!--独立密码-->
                <div class="">
                    <div class="col-md-6 pl0" style="<?php if ($CONF['SYSTEM_INFO']['vendor'] != $CONF['VENDOR_LIST']['gmp']) {echo 'display:none;';} ?>">
                        <div class="user-custome-password-info-card flex-center">
                            <div class="col-md-11 pl0">
                                <div class="user-info-title user-info-color-label-card mb-8 fs12">
                                    <span class="pr10"><?php echo $LANG['UI_USER_CUSTOM_PASSWORD'];?></span>
                                    <a class="popovers" data-container="body" data-trigger="hover"
                                       data-placement="right"
                                       data-content="<?php echo $LANG['UI_USER_CUSTOM_PASSWORD_TIPS'];?>">
                                        <i class="viconfont vicon-tishi"></i>
                                    </a>
                                </div>
                                <div class="user-info-common user-info-color-value-card">
                                    <input name="custome_password" type="text" style="border: none;outline: none;" readonly>
                                </div>
                            </div>
                            <div class="col-md-2 user-info-edit-card">
                                <a href="javascript:void(0)" id="set_custome_password" class="colorgreen" data-toggle="drawer" data-target="#drawer-custome-password">
                                    <span class="set_custome_password_des"></span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 pl0">
                        <div class="user-accesskey-info-card">
                            <div class="col-md-11 pl0">
                                <div class="user-info-title user-info-color-label-card mb-4 fs12">
                                    <span class="pr10">Access Key</span>
                                    <a class="popovers" data-container="body" data-trigger="hover"
                                       data-placement="right"
                                       data-content="<?php echo $LANG['UI_USER_INFO_ACCESS_KEY_TIPS'] ?>">
                                        <i class="viconfont vicon-tishi"></i>
                                    </a>
                                </div>
                                <div class="user-info-common user-info-color-value-card">
                                    <span id="auth_code" class="pr10"></span>
                                    <a class="btn pd0 colorgray auth_code_btn" href="javascript:;" data-clipboard-target="#auth_code" title="<?php echo $LANG['UI_PUBLIC_COPY'] ?>">
                                        <i class="viconfont vicon-a-Copyfuzhi"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group display-hide"><!-- bug#19925屏蔽多语言切换，如果再次打开，去除此注释@JackC -->
                    <label class="control-label col-md-3"><?php echo $LANG['UI_USER_LANG'] ?> <span
                                class="required">
									</span>
                    </label>
                    <div class="col-md-6">
                        <select class="form-control select2me" id="langtype" name="langtype">
                        </select>
                    </div>
                </div>
            </div>
            <!-- END VALIDATION STATES-->
        </div>
    </div>
    <!--  修改用户名  -->
    <div style="width: 800px;" class="drawer slide" data-placement="right" tabindex="-1" role="dialog"
         aria-labelledby="drawer-1-title" aria-hidden="true" id="drawer-username">
        <div class="drawer-content drawer-content-scrollable" role="document">
            <div class="drawer-header">
                <h4 class="userinfo-drawer-title drawer-title fs16 pl0" id="drawer-1-title">
                    <span id="drawer-1-title-1">
                        <i class="viconfont vicon-a-Editbianji mr4"></i>
                    </span>
                    <span style="vertical-align: top" id="drawer-1-title-2">
                        <?php echo $LANG['UI_USER_EDIT_USERNAME'];?>
                    </span>
                    <span data-dismiss="drawer" aria-label="Close" class="drawer-close ">
                        <i class="viconfont vicon-guanbi"></i>
                    </span>
                </h4>
            </div>
            <div class="drawer-body">
                <!-- BEGIN FORM-->
                <form action="#" id="form_sample_1" class="form-horizontal">
                    <div class="form-body">
                        <div class="form-group">
                            <label class="control-label col-md-3">
                                <span class="required">* </span>
                                <?php echo $LANG['UI_USER_NEW_USERNAME'];?>
                            </label>
                            <div class="col-md-7">
                                <div class="input-icon right">
                                    <i class="fa"></i>
                                    <input type="text" maxlength="64" class="form-control" name="new_username" placeholder="<?php echo $LANG['UI_USER_INPUT_NEW_USERNAME'];?>" aria-describedby="username-error" aria-invalid="false">
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
                            <button type="button" id="username_submit" class="btn green-haze btn-confirm">
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
    <!--  设置联系电话  -->
    <div style="width: 800px;" class="drawer slide" data-placement="right" tabindex="-1" role="dialog"
         aria-labelledby="drawer-2-title" aria-hidden="true" id="drawer-number">
        <div class="drawer-content drawer-content-scrollable" role="document">
            <div class="drawer-header">
                <h4 class="userinfo-drawer-title drawer-title fs16 pl0 mt-0" id="drawer-2-title">
                    <span id="drawer-2-title-1">
                        <i class="viconfont telnumber_title_icon mr4"></i>
                    </span>
                    <span style="vertical-align: top" id="drawer-2-title-2" class="telnumber_des">
                    </span>
                    <span data-dismiss="drawer" aria-label="Close" class="drawer-close ">
                        <i class="viconfont vicon-guanbi"></i>
                    </span>
                </h4>
            </div>
            <div class="drawer-body">
                <!-- BEGIN FORM-->
                <form action="#" id="form_sample_2" class="form-horizontal">
                    <div class="form-body">
                        <div class="form-group">
                            <label class="control-label col-md-3">
                                <?php echo $LANG['UI_USER_PHONE'];?>
                            </label>
                            <div class="col-md-7">
                                <div class="input-icon right">
                                    <i class="fa"></i>
                                    <input type="tel" maxlength="64" class="form-control" name="number" placeholder="<?php echo $LANG['UI_SETTINGS_NOTICE_INPUT_PHONE'];?>" aria-describedby="number-error" aria-invalid="false">
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
                            <button type="button" id="number_submit" class="btn green-haze btn-confirm">
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
    <!--  修改登录密码  -->
    <div style="width: 800px;" class="drawer slide" data-placement="right" tabindex="-1" role="dialog"
         aria-labelledby="drawer-3-title" aria-hidden="true" id="drawer-password">
        <div class="drawer-content drawer-content-scrollable" role="document">
            <div class="drawer-header">
                <h4 class="userinfo-drawer-title drawer-title fs16 pl0 mt-0" id="drawer-3-title">
                    <span id="drawer-3-title-1">
                        <i class="viconfont vicon-a-Editbianji mr4"></i>
                    </span>
                    <span style="vertical-align: top" id="drawer-3-title-2">
                        <?php echo $LANG['UI_USER_EDIT_PASSWORD'];?>
                    </span>
                    <span data-dismiss="drawer" aria-label="Close" class="drawer-close ">
                        <i class="viconfont vicon-guanbi"></i>
                    </span>
                </h4>
            </div>
            <div class="drawer-body">
                <!-- BEGIN FORM-->
                <form action="#" id="form_sample_3" class="form-horizontal">
                    <div class="form-body">
                        <div class="form-group">
                            <label class="control-label col-md-3">
                                <span class="required">* </span>
                                <?php echo $LANG['UI_USER_OLD_PASS'];?>
                            </label>
                            <div class="col-md-7">
                                <div class="input-icon right">
                                    <i class="fa"></i>
                                    <input type="password" maxlength="64" class="form-control" name="old_password" placeholder="<?php echo $LANG['UI_RESETPWD_ENTER_OLD_PASSWORD'] ?>" aria-describedby="old_password-error" aria-invalid="false">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3">
                                <span class="required">* </span>
                                <?php echo $LANG['UI_USER_NEW_PASS'];?>
                            </label>
                            <div class="col-md-7">
                                <div class="input-icon right">
                                    <i class="fa"></i>
                                    <input type="password" maxlength="64" class="form-control" name="new_password" placeholder="<?php echo $LANG['UI_RESETPWD_ENTER_NEWPASSWORD'];?>" aria-describedby="new_password-error" aria-invalid="false" autocomplete="off" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3">
                                <span class="required">* </span>
                                <?php echo $LANG['UI_USER_CONFIRM_PASS'];?>
                            </label>
                            <div class="col-md-7">
                                <div class="input-icon right">
                                    <i class="fa"></i>
                                    <input type="password" maxlength="64" class="form-control" name="confirm_new_password" placeholder="<?php echo $LANG['UI_RESETPWD_REENTER_NEWPASSWORD'];?>" aria-describedby="confirm_new_password-error" aria-invalid="false" autocomplete="off" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')">
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
                            <button type="button" id="password_submit" class="btn green-haze btn-confirm">
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
    <!--  修改邮箱 -->
    <div style="width: 800px;" class="drawer slide" data-placement="right" tabindex="-1" role="dialog"
         aria-labelledby="drawer-4-title" aria-hidden="true" id="drawer-email">
        <div class="drawer-content drawer-content-scrollable" role="document">
            <div class="drawer-header">
                <h4 class="userinfo-drawer-title drawer-title fs16 pl0 mt-0" id="drawer-4-title">
                    <span id="drawer-4-title-1">
                        <i class="viconfont mr4 email_title_icon"></i>
                    </span>
                    <span style="vertical-align: top" id="drawer-4-title-2" class="email_des">
                    </span>
                    <span data-dismiss="drawer" aria-label="Close" class="drawer-close ">
                        <i class="viconfont vicon-guanbi"></i>
                    </span>
                </h4>
            </div>
            <div class="drawer-body">
                <!-- BEGIN FORM-->
                <form action="#" id="form_sample_4" class="form-horizontal">
                    <div class="form-body">
                        <div class="form-group">
                            <label class="control-label col-md-3">
                                <?php echo $LANG['UI_USER_EMAIL_ADDRESS'];?>
                            </label>
                            <div class="col-md-7">
                                <div class="input-icon right">
                                    <i class="fa"></i>
                                    <input type="email" maxlength="64" class="form-control" name="email" placeholder="<?php echo $LANG['UI_LOGIN_TIP_EMAILADDRESS'];?>" aria-describedby="email-error" aria-invalid="false">
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
                            <button type="button" id="email_submit" class="btn green-haze btn-confirm">
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
    <!--  设置独立密码  -->
    <div style="width: 800px;" class="drawer slide" data-placement="right" tabindex="-1" role="dialog"
         aria-labelledby="drawer-5-title" aria-hidden="true" id="drawer-custome-password">
        <div class="drawer-content drawer-content-scrollable" role="document">
            <div class="drawer-header">
                <h4 class="userinfo-drawer-title drawer-title fs16 pl0 mt-0" id="drawer-5-title">
                    <span id="drawer-5-title-1">
                        <i class="viconfont custome_password_title_icon mr4"></i>
                    </span>
                    <span style="vertical-align: top" id="drawer-5-title-2" class="custome_password_des">
                    </span>
                    <span data-dismiss="drawer" aria-label="Close" class="drawer-close ">
                        <i class="viconfont vicon-guanbi"></i>
                    </span>
                </h4>
            </div>
            <div class="drawer-body">
                <!-- BEGIN FORM-->
                <form action="#" id="form_sample_5" class="form-horizontal">
                    <div class="form-body">
                        <div class="form-group old_custome_password_div">
                            <label class="control-label col-md-3">
                                <span class="required">* </span>
                                <?php echo $LANG['UI_USER_OLD_CUSTOME_PASSWORD'];?>
                            </label>
                            <div class="col-md-7">
                                <div class="input-icon right">
                                    <i class="fa"></i>
                                    <input type="password" maxlength="64" class="form-control" name="old_custome_password" placeholder="<?php echo $LANG['UI_USER_INPUT_OLD_CUSTOME_PASSWORD'];?>" aria-describedby="custome_password-error" aria-invalid="false" autocomplete="off" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3">
                                <span class="required">* </span>
                                <?php echo $LANG['UI_USER_NEW_CUSTOME_PASSWORD'];?>
                            </label>
                            <div class="col-md-7">
                                <div class="input-icon right">
                                    <i class="fa"></i>
                                    <input type="password" maxlength="64" class="form-control" name="new_custome_password" placeholder="请输入新独立密码" aria-describedby="custome_password-error" aria-invalid="false" autocomplete="off" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3">
                                <span class="required">* </span>
                                <?php echo $LANG['UI_USER_CONFIRM_NEW_CUSTOME_PASSWORD'];?>
                            </label>
                            <div class="col-md-7">
                                <div class="input-icon right">
                                    <i class="fa"></i>
                                    <input type="password" maxlength="64" class="form-control" name="confirm_custome_password" placeholder="<?php echo $LANG['UI_USER_INPUT_NEW_CUSTOME_PASSWORD_AGAIN'];?>" aria-describedby="confirm_custome_password-error" aria-invalid="false" autocomplete="off" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')">
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
                            <button type="button" id="custome_password_submit" class="btn green-haze btn-confirm">
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
</div>
<!-- END PAGE CONTENT-->
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jsencrypt/jsencrypt.min.js" type="text/javascript"></script>
<?php
if ($_SESSION['language'] != "en-us") {
    //如果不是英文,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
        $_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script src="./scripts/platform/users/userinfo.js" type="text/javascript"></script>