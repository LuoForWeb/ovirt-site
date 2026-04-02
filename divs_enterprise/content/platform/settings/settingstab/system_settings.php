<?php include_once '../../../../tpl/permission.php'; ?>
<link href="./assets/global/plugins/jquery-file-upload/css/jquery.fileupload.css" rel="stylesheet"/>

<head>
    <style>
        .upload-div:hover{
            cursor: pointer;
        }

        .settings-span{
            width: auto;
            height: auto;
            display: inline-block;
            line-height: 34px;
            border: 1px dashed #E6E6E6;
            padding: 0 8px;
        }

        .close-btn{
            position: absolute;
            color: #E6E6E6;
            top: -4px;
        }
        .close-btn:hover{
            cursor: pointer;
            color: #333;
        }
    </style>
</head>
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="setting_manager" href="./content/platform/settings/setting_manager.php">
            <span><?php echo $LANG['UI_PLATFORM_SYSTEM_SET'] ?></span>
        </a>
    </li>
    <span>></span>
    <span class="curent">个性化配置</span>
</h3>
<div class="row row-manager">
    <div class="col-md-12 col-manager">
        <!-- BEGIN TAB PORTLET-->
        <div class="portlet box blue-hoki" id=''>
            <div class="portlet-title">
                <div class="caption">
                    <i class="viconfont vicon-setting_manager"></i>个性化配置
                </div>
            </div>
            <div class="portlet-body form">
                <!-- BEGIN FORM-->
                <form action="#" class="form-horizontal" id="settingform">
                    <div class="form-body-wrapper">
                        <div class="form-body wp-50 hp-100 overflow-visible">
                            <div class="form-group">
                                <label class="control-label col-md-3">系统自定义名称
                                </label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="custom_name" aria-invalid="false">
                                    <div class="help-block">自定义系统名，展示在首页导航栏上</div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3">自定义logo
                                </label>
                                <div class="col-md-6 item-logo">
                                    <div class="draggable-s item-logo item-items mt5" id="item_logo" data-field="item_logo">
                                        <span class="margin-r--15 settings-span" title="">
                                            <label class="upload-div">
                                                <span class="upload-flag"><i
                                                        class="viconfont vicon-shangchuan"></i>上传logo</span>
                                                <input type="file" accept="image/*" name="files" id="diy_logo_value"
                                                    style="display:none;">
                                                <input type="hidden" id="diy_logo_value_url" value="">
                                            </label>
                                        </span>
                                        <span class="close-btn"><i class="viconfont vicon-a-Close-oneguanbi-copy"></i></span>
                                    </div>
                                    <!-- 选择图片文件：<input type="file" name="image"> -->
                                    <div class="help-block">自定义图片，在登陆页面左上展示，尺寸大小建议：150x50 px</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions flex-items-center justify-content-center">
                        <div class="wp-50">
                            <label class="control-label col-md-3"></label>
                            <div class="col-md-6">
                                <button type="button" class="btn btn-default mr5 cancel-btn">
                                    <?php echo $LANG['UI_PUBLIC_CANCEL'] ?> </button>
                                <button type="button" id="submit" class="btn btn-primary">
                                    <?php echo $LANG['UI_PUBLIC_CONFIRM'] ?> </button>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- END FORM-->
            </div>
        </div>
    </div>
</div>

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<?php
if ($_SESSION['language'] != "en-us") {
    //如果不是英文,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
        $_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script src="./scripts/libs/base64.js" type="text/javascript"></script>
<script src="./assets/global/plugins/jquery-file-upload/js/jquery.fileupload.js"></script>
<script type="text/javascript" src="./scripts/platform/settings/settingstab/system_settings.js"></script>
<!-- END PAGE LEVEL PLUGINS -->