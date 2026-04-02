<?php
include_once '../../tpl/permission.php';
include_once '../../content/platform/public/bs_table.php';
global $LANG;
?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" href="./css/driver/driver_manager.css" />
<link rel="stylesheet" href="./css/platform/common_delete.css" />
<!-- END PAGE LEVEL STYLES -->
 <!-- BEGIN PAGE HEADER -->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="backup_manager" href="./content/platform/backup/backupresources.php">
            <span><?php echo $LANG['UI_PLATFORM_BACKUP_RESOURCE'] ?></span>
        </a>
    </li>
    <span>
        >
    </span>
    <span class="curent"><?php echo $LANG['WEB_DRIVER_MANAGER'] ?></span>
</h3>
<!-- END PAGE HEADER -->
<!-- BEGIN PAGE CONTENT-->
<div class="resource-manager-wrap" id="driverContent">
    <div class="resource-manager-wrap__header">
        <span>
            <i class="levelchild viconfont vicon-a-Hunting-gearcongdongzhuangzhi me-10"></i>
            <span class="resource-manager-wrap__header__text"><?php echo $LANG['WEB_DRIVER_MANAGER'] ?></span>
        </span>
    </div>
    <div class="resource-manager-wrap__content">
        <div class="table-container reset-td-width">
            <div class="vin_toolbar" id="vinDriverToolbar">
                <div class="leftTool">
                </div>
                <div class="rightTool">
                    <div class="vin_btnToolbar">
                    </div>
                </div>
            </div>
            <table class="table" id="driverTable"></table>
        </div>
    </div>
</div>
<!-- END PAGE CONTENT-->

<!-- BEGIN MODAL -->
<!-- END MODAL -->

<!-- BEGIN DRAWER -->

<!-- BEGIN ADD/EDIT DRIVER DRAWER -->
<div class="drawer slide " data-placement="right" tabindex="-1" role="dialog" id="addDriverDrawer" data-backdrop="static">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header">
            <h3 class="drawer-title mt-0">
                <span class="icon__wrapper"><i class="viconfont vicon-biaogetianjia icon"></i></span>
                <span class="title" style="vertical-align: top"><?php echo $LANG['UI_DRIVER_ADD'] ?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close ">
                    <i class="viconfont vicon-guanbi"></i>
                </span>
            </h3>
        </div>
        <div class="drawer-body">
            <!-- BEGIN FORM-->
            <form action="#" id="addDriverForm" class="form-horizontal" data-lang="" data-type="" data-uuid="">
                <div class="form-body">
                    <!-- 错误提示 -->
                    <div class="alert alert-danger display-hide">
                        <button type="button" class="close" data-close="alert"></button>
                        <span class="message"></span>
                    </div>

                    <!-- 适用操作系统 -->
                    <div class="form-group flex-items-center">
                        <label for="driverOsType" class="control-label drawer-item-label">
                            <?php echo $LANG['UI_DRIVER_ADD_OS_TYPE'] ?>
                        </label>
                        <div class="drawer-item-content">
                            <div class="radio-group" id="driverOsType">
                                <label value="2" class="active radio-group__item with-svg me-20">
                                    <img src="./img/platform/windows-68.svg" value="2" />
                                </label>
                                <label value="1" class="radio-group__item with-svg me-20">
                                    <img src="./img/platform/linux-68.svg" value="1" />
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- 上传驱动 -->
                    <div class="form-group">
                        <label class="control-label drawer-item-label">
                            <?php echo $LANG['UI_DRIVER_ADD_UPLOAD'] ?>
                        </label>
                        <div class="drawer-item-content d-flex">
                            <button id="uploadDriverBtn" class="btn green-haze" type="button">
                                <i class="viconfont vicon-shangchuan"></i>
                                <?php echo $LANG['UI_DRIVER_ADD_UPLOAD_BTN'] ?>
                            </button>
                            <a class="popovers ml12 pt7" data-container="body" data-trigger="hover" data-html="true"
                               data-placement="right" data-content="<?php echo $LANG['UI_DRIVER_ADD_UPLOAD_TIPS'] ?>">
                                <i class="viconfont vicon-tishi"></i>
                            </a>
                        </div>
                    </div>

                    <!-- 上传驱动-结果 -->
                    <div class="form-group upload-driver-result-div">
                        <label class="drawer-item-label"></label>
                        <div class="drawer-item-content">
                            <div id="uploadDriverResult" class="dropzone"></div>
                        </div>
                    </div>

                    <!-- 自动获取的配置 -->
                    <!-- 驱动名称 -->
                    <div class="form-group autoAcquireConfig">
                        <label for="driverName" class="control-label drawer-item-label">
                            <?php echo $LANG['UI_DRIVER_ADD_NAME'] ?>
                        </label>
                        <div class="drawer-item-content">
                            <input type="text" class="form-control" readonly id="driverName" />
                        </div>
                    </div>

                    <!-- 驱动类别 -->
                    <div class="form-group autoAcquireConfig driverHardwareTypeDiv">
                        <label for="driverHardwareType" class="control-label drawer-item-label">
                            <?php echo $LANG['UI_DRIVER_ADD_TYPE'] ?>
                        </label>
                        <div class="drawer-item-content">
                            <input type="text" class="form-control" readonly id="driverHardwareType" />
                        </div>
                    </div>

                    <!-- 制造商 -->
                    <div class="form-group autoAcquireConfig">
                        <label for="driverProvider" class="control-label drawer-item-label">
                            <?php echo $LANG['UI_DRIVER_ADD_PROVIDER'] ?>
                        </label>
                        <div class="drawer-item-content">
                            <input type="text" class="form-control" readonly id="driverProvider" />
                        </div>
                    </div>

                    <!-- 驱动日期 -->
                    <div class="form-group autoAcquireConfig driverDateDiv">
                        <label for="driverDate" class="control-label drawer-item-label">
                            <?php echo $LANG['UI_DRIVER_ADD_DATE'] ?>
                        </label>
                        <div class="drawer-item-content">
                            <input type="text" class="form-control" readonly id="driverDate" />
                        </div>
                    </div>

                    <!-- 驱动版本 -->
                    <div class="form-group autoAcquireConfig">
                        <label for="driverVersion" class="control-label drawer-item-label">
                            <?php echo $LANG['UI_DRIVER_ADD_VERSION'] ?>
                        </label>
                        <div class="drawer-item-content">
                            <input type="text" class="form-control" readonly id="driverVersion" />
                        </div>
                    </div>

                    <!-- 备注 -->
                    <div class="form-group">
                        <label for="driverRemark" class="control-label drawer-item-label">
                            <?php echo $LANG['UI_DRIVER_ADD_REMARK'] ?>
                        </label>
                        <div class="drawer-item-content">
                            <input type="text" class="form-control" id="driverRemark" />
                        </div>
                    </div>
                </div>
            </form>
            <!-- END FORM-->
        </div>
        <!-- footer -->
        <div class="drawer-footer">
            <div class="form-actions">
                <button type="button" class="btn default cancel">
                    <?php echo $LANG['UI_PUBLIC_NO'] ?>
                </button>
                <button type="button" class="btn green-haze btn-confirm">
                    <?php echo $LANG['UI_PUBLIC_YES'] ?>
                </button>
            </div>
        </div>
    </div>
</div>
<!-- END ADD/EDIT DRIVER DRAWER -->

<!-- BEGIN DETAIL DRIVER DRAWER -->
<div class="drawer slide " data-placement="right" tabindex="-1" role="dialog" id="driverDetailDrawer">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header">
            <h3 class="drawer-title mt-0">
                <span class="icon__wrapper"><i class="viconfont vicon-tenant-detail icon"></i></span>
                <span class="title" style="vertical-align: top"><?php echo $LANG['UI_DRIVER_DETAIL'] ?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close ">
                    <i class="viconfont vicon-guanbi"></i>
                </span>
            </h3>
        </div>
        <div class="drawer-body">
            <!-- BEGIN FORM-->
            <form action="#" id="driverDetailForm" class="form-horizontal">
                <div class="form-body">
                    <!-- 详情预览 -->
                    <div class="form-group detail-preview-div">
                        <div class="row">
                            <!-- 驱动名称 -->
                            <div class="form-group detail-preview__item">
                                <label class="control-label detail-preview__item-label"><?php echo $LANG['UI_DRIVER_DETAIL_NAME'] ?>:</label>
                                <div class="detail-preview__item-content">
                                    <span class="text" id="detailDriverName"></span>
                                </div>
                            </div>

                            <!-- 适用系统 -->
                            <div class="form-group detail-preview__item">
                                <label class="control-label detail-preview__item-label"><?php echo $LANG['UI_DRIVER_DETAIL_OS_TYPE'] ?>:</label>
                                <div class="detail-preview__item-content">
                                    <span class="img">
                                        <img id="detailOsImg" src="./img/platform/windows-24.svg"  alt=""/>
                                    </span>
                                    <span class="text" id="detailOsName"></span>
                                </div>
                            </div>

                            <!-- 驱动版本 -->
                            <div class="form-group detail-preview__item">
                                <label class="control-label detail-preview__item-label"><?php echo $LANG['UI_DRIVER_DETAIL_VERSION'] ?>:</label>
                                <div class="detail-preview__item-content">
                                    <span class="text" id="detailDriverVersion"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 驱动硬件信息 -->
                    <div class="form-group detail-table-div">
                        <div class="table-container reset-td-width">
                            <table class="table" id="driverDetailTable"></table>
                        </div>
                    </div>

                    <!-- 提示信息 -->
                    <div class="alert alert-block alert-info fade in">
                        <button type="button" class="close" data-dismiss="alert"></button>
                        <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                        <ol class="alert-ol">
                            <li><?php echo $LANG['UI_DRIVER_DETAIL_TIPS1'] ?></li>
                            <li><?php echo $LANG['UI_DRIVER_DETAIL_TIPS2'] ?></li>
                            <li><?php echo $LANG['UI_DRIVER_DETAIL_TIPS3'] ?></li>
                            <li><?php echo $LANG['UI_DRIVER_DETAIL_TIPS4'] ?></li>
                        </ol>
                    </div>
                </div>
            </form>
            <!-- END FORM-->
        </div>
        <!-- footer -->
        <div class="drawer-footer">
            <div class="form-actions">
                <button type="button" class="btn default cancel">
                    <?php echo $LANG['UI_PUBLIC_NO'] ?>
                </button>
                <button type="button" class="btn green-haze btn-confirm">
                    <?php echo $LANG['UI_PUBLIC_YES'] ?>
                </button>
            </div>
        </div>
    </div>
</div>
<!-- END DETAIL DRIVER DRAWER -->

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

<script src="./scripts/public/initComponents.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/driver/driver_manager.js"></script>
<!-- END PAGE LEVEL PLUGINS -->