<?php include_once '../../../tpl/permission.php'; ?>
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
    <?php echo $LANG['UI_USER_MODIFY_PASS'] ?>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row row-manager">
    <div class="col-md-12 col-manager">
        <!-- BEGIN VALIDATION STATES-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="levelchild viconfont vicon-ge_modify"></i><?php echo $LANG['UI_USER_MODIFY_PASS'] ?>
                </div>
            </div>
            <div class="portlet-body form">
                <!-- BEGIN FORM-->
                <form action="#" id="editpass" class="form-horizontal">
                    <input type="password" autocomplete="new-password" hidden>
                    <div class="form-body-wrapper">
                        <div class="form-body wp-50 hp-100 overflow-visible width80p_en">
                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo $LANG['UI_USER_OLD_PASS'] ?> <span
                                        class="required">
                                        * </span>
                                </label>
                                <div class="col-md-6">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input type="password" autocomplete="off" maxlength="32" class="form-control"
                                            id="oldpass" name="oldpass"
                                            oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')" />
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo $LANG['UI_USER_NEW_PASS'] ?> <span
                                        class="required">
                                        * </span>
                                </label>
                                <div class="col-md-6">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input type="password" autocomplete="off" maxlength="32" class="form-control"
                                            id="password" name="password"
                                            oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')" />
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo $LANG['UI_USER_CONFIRM_PASS'] ?> <span
                                        class="required">
                                        * </span>
                                </label>
                                <div class="col-md-6">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input type="password" autocomplete="off" maxlength="32" class="form-control"
                                            name="rpassword"
                                            oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions flex-items-center justify-content-center">
                        <div class="wp-50">
                            <label class="control-label col-md-3"></label>
                            <div class="col-md-6">
                                <button type="button" id="cancel"
                                    class="btn default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
                                <button type="button" id="addsubmit"
                                    class="btn green-haze btn-confirm"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- END FORM-->
            </div>
        </div>
        <!-- END VALIDATION STATES-->
    </div>
</div>
<!-- END PAGE CONTENT-->
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
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
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script src="./scripts/platform/users/edit_password.js" type="text/javascript"></script>