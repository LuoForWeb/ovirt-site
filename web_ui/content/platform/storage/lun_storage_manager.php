<?php include_once '../../../tpl/permission.php'; ?>
<?php include_once '../../../content/platform/public/bs_table.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->

<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet"
      type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet"
      type="text/css" />
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css"
      href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />

<?php
$userAllPermission = $_SESSION['permission'];

$tabNameArr = array('storage_manager_list', 'storage_pool');
$activeClassArr = array();  //active类
$displayArr = array();      //是否显示

$setFlag = false;
foreach ($tabNameArr as $key => $value) {
    if (in_array($value, $userAllPermission)) {
        //如果有权限
        $displayArr[$value] = "";
        if (!$setFlag) {
            $activeClassArr[$value] = " active ";
            $setFlag = true;
        } else {
            $activeClassArr[$value] = "";
        }
    } else {
        //如果没有权限
        $activeClassArr[$value] = "";
        $displayArr[$value] = "displaynone";
    }
}
?>

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
    <span class="curent"><?php echo $LANG['UI_PRODUCTION_STORAGE_NAME'] ?></span>
</h3>
<!-- END PAGE HEADER-->

<!-- BEGIN PAGE CONTENT-->
<div class="resource-manager-wrap">
    <div class="resource-manager-wrap__header">
        <span>
            <i class="viconfont vicon-shengchancunchu1 me-10"></i>
            <span class="resource-manager-wrap__header__text"><?php echo $LANG['UI_PRODUCTION_STORAGE_NAME'] ?></span>
        </span>
    </div>
    <div class="resource-manager-wrap__content">
        <div class="table-toolbar">
            <div class="vin_toolbar" id="vin_lun_storage_toolbar">
                <div class="leftTool"></div>
                <div class="rightTool">
                    <div class="vin_btnToolbar"></div>
                </div>
            </div>
        </div>

        <div class="table-container lun-storage-table-container">
            <table id="lun_storage_table"></table>
        </div>
    </div>
</div>
<!-- END PAGE CONTENT -->


<!-- BEGIN ADD DRAWER -->
<div style="width: 600px;" class="drawer slide" data-placement="right" tabindex="-1" role="dialog"
     aria-labelledby="drawer-1-title" id="add_storage_drawer">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header">
            <h4 class="drawer-title" id="drawer-1-title"><i class="viconfont vicon-danchuangtianjia1"></i>
                <?php echo $LANG['UI_PUBLIC_ADDNEW'] ?>
            </h4>
            <span data-dismiss="drawer" aria-label="Close" class="drawer-close " style="margin-top: -17px;">
                    <i class="viconfont vicon-guanbi"></i></span>
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
                        <label class="control-label col-md-4 width45_en">
                            <?php echo $LANG['UI_LUN_STORAGE_TYPE'] ?> <span class="required">
                                * </span>
                        </label>
                        <div class="col-md-8 width55_en">
                            <select class="form-control select2me" id="storage_type" name="storage_type">
                                <option value="14">
                                    HUAWEI OceanStor
                                </option>
                                <option value="15">
                                    HUAWEI Fusion Storage
                                </option>
                                <option value="17">
                                    Inspur HF18000G6
                                </option>
                            </select>
                            <div>
                                <span class="help-block ">
                                    <?php echo $LANG['UI_REPORT_STORAGE_TYPE'] ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-left: -100px;" id="ip_div">
                        <label class="control-label col-md-4 width45_en">
                            <?php echo $LANG['UI_STORAGE_IP_OR_DOMAIN'] ?> <span class="required">
                                * </span>
                        </label>
                        <div class="col-md-8 width55_en">
                            <div class="input-icon right">
                                <i class="fa"></i>
                                <input type="text" maxlength="64" class="form-control" id="ip" name="ip" />
                                <div>
                                    <span class="help-block ">
                                        <?php echo $LANG['UI_LUN_STORAGE_IP_TIPS'] ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 存储别名 -->
                    <div class="form-group" style="margin-left: -100px;">
                        <label class="control-label col-md-4 width45_en">
                            <?php echo $LANG['UI_STORAGE_NAME'] ?> <span class="required">
                                * </span>
                        </label>
                        <div class="col-md-8 width55_en">
                            <div class="input-icon right">
                                <i class="fa"></i>
                                <input type="text" maxlength="64" class="form-control" id="storage_name"
                                       name="storage_name" />
                                <span class="help-block ">
                                    <?php echo $LANG['UI_STORAGE_RNAME'] ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- 用户名 -->
                    <div class="form-group" style="margin-left: -100px;" id="user_div">
                        <label class="control-label col-md-4 width45_en">
                            <?php echo $LANG['UI_LOGIN_USERNAME'] ?> <span class="required">
                                * </span>
                        </label>
                        <div class="col-md-8 width55_en">
                            <div class="input-icon right">
                                <i class="fa"></i>
                                <input type="text" maxlength="64" class="form-control" id="name" name="name" />
                                <span class="help-block ">
                                    <?php echo $LANG['UI_LUN_STORAGE_LOGIN_NAME'] ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- 密码 -->
                    <div class="form-group locationDiv" style="margin-left: -100px;" id="pwd_div">
                        <label class="control-label col-md-4 width45_en">
                            <?php echo $LANG['UI_LOGIN_PASSWORD'] ?><span class="required">
                                * </span>
                        </label>
                        <div class="col-md-8 width55_en">
                            <div class="input-icon right">
                                <i class="fa"></i>
                                <input type="password" autocomplete="off" maxlength="64" class="form-control"
                                       id="password" name="password"
                                       oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')" />
                                <span class="help-block ">
                                    <?php echo $LANG['UI_LUN_STORAGE_LOGIN_PASSWORD'] ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- 节点 -->
                    <div class="form-group protocol-div" id="nodeDiv" style="margin-top: 15px;margin-left: -100px;">
                        <label class="control-label col-md-4 width45_en">
                            <?php echo $LANG['UI_NODE_IPADDR'] ?>
                        </label>
                        <div class="col-md-8 width55_en">
                            <select id="node_select" name="node_select"
                                    class="bootstrap-mutiple-select selectpicker show-tick" multiple data-live-search="true"
                                    data-actions-box="true">
                            </select>
                        </div>
                    </div>

                    <!-- 选择网络协议 -->
                    <div class="form-group protocol-div" style="margin-top: 15px;margin-left: -100px;">
                        <label class="control-label col-md-4 width45_en">
                            <?php echo $LANG['UI_LUN_STORAGE_NETWORK_PROTOCOL'] ?>
                        </label>
                        <div class="col-md-8 width55_en">
                            <select class="form-control select2me" id="protocol_type" name="protocol_type">
                                <option value="0">
                                    <?php echo $LANG['UI_PUBLIC_SELECT'] ?>
                                </option>
                                <option value="1">
                                    iSCSI
                                </option>
                                <option value="2">
                                    FC
                                </option>
                                <option value="3" class="display-none">
                                    VBS
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- 验证启动器 -->
                    <div class="form-group check-div ml200_en" style="margin-left: 120px;">
                        <div class="control-label col-md-8">
                        </div>
                        <div>
                            <div class="col-md-12" style="margin-top: 4px;">
                                <a id="check">
                                    <?php echo $LANG['UI_LUN_STORAGE_CHECK_INITIATOR'] ?>
                                </a>
                                <span style="color: red;" id="check_error"></span>
                            </div>
                        </div>
                    </div>

                    <!-- chap认证 -->
                    <div class="chap-div display-hide">
                        <div class="form-group" style="margin-left: -100px;">
                            <div class="control-label col-md-4 width45_en">
                                <?php echo $LANG['UI_LUN_STORAGE_CHAP_AUTH'] ?>
                            </div>
                            <div>
                                <div class="col-md-4" style="margin-top: 5px;">
                                    <input type="checkbox" id="chap_auth" class="make-switch" data-on-color="primary"
                                           data-off-color="info" data-size="small"
                                           data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                    <a class="popovers ml15" data-container="body" data-trigger="hover"
                                       data-placement="right" data-content="<?php echo $LANG['UI_LUN_CHAP_TIPS'] ?>;">
                                        <i class="viconfont vicon-tishi"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="chap-input-div display-hide">
                            <!-- discovery认证 -->
                            <div class="form-group" style="margin-left: -100px;">
                                <div class="control-label col-md-4 width45_en">
                                    <?php echo $LANG['UI_LUN_STORAGE_DISCOVERY_AUTH'] ?>
                                </div>
                                <div>
                                    <div class="col-md-4" style="margin-top: 5px;">
                                        <input type="checkbox" id="discovery_auth" class="make-switch"
                                               data-on-color="primary" data-off-color="info" data-size="small"
                                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                        <a class="popovers ml15" data-container="body" data-trigger="hover"
                                           data-placement="right"
                                           data-content="<?php echo $LANG['UI_LUN_CHAP_TIPS'] ?>;">
                                            <i class="viconfont vicon-tishi"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <!-- 名称 -->
                            <div class="form-group" style="margin-left: -100px;">
                                <label class="control-label col-md-4 width45_en">
                                    <?php echo $LANG['UI_CHAP_NAME'] ?> <span class="required">
                                        * </span>
                                </label>
                                <div class="col-md-7 width55_en">
                                    <div class="input-icon right mr15">
                                        <i class="fa"></i>
                                        <input type="text" class="form-control" maxlength="223" id="chap_name" name="chap_name" />
                                    </div>
                                    <a class="popovers ml15" data-container="body" data-trigger="hover"
                                       style="position: absolute;right: 0;top: 8px;" data-placement="left"
                                       data-content="<?php echo $LANG['UI_LUN_STORAGE_CHAP_NAME_TIP'] ?>"
                                       data-original-title="" title="">
                                        <i class="viconfont vicon-tishi"></i>
                                    </a>
                                </div>
                            </div>
                            <!-- 密码 -->
                            <div class="form-group locationDiv" style="margin-left: -100px;">
                                <label class="control-label col-md-4 width45_en">
                                    <?php echo $LANG['UI_LOGIN_PASSWORD'] ?><span class="required">
                                        * </span>
                                </label>
                                <div class="col-md-7 width55_en">
                                    <div class="input-icon right mr15">
                                        <i class="fa"></i>
                                        <input type="password" autocomplete="off" class="form-control"
                                               oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')"
                                               id="chap_password" name="chap_password" />
                                    </div>
                                    <a class="popovers ml15" data-container="body" data-trigger="hover"
                                       style="position: absolute;right: 0;top: 8px;" data-placement="left"
                                       data-content="<?php echo $LANG['UI_LUN_STORAGE_CHAP_PASSWORD_TIP'] ?>"
                                       data-original-title="" title="">
                                        <i class="viconfont vicon-tishi"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 存储快照 -->
                    <div class="form-group" style="margin-left: -100px;">
                        <div class="control-label col-md-4 width45_en">
                            <?php echo $LANG['UI_LUN_STORAGE_SNAPSHOT'] ?>
                        </div>
                        <div>
                            <div class="col-md-4 width55_en" style="margin-top: 5px;">
                                <input type="checkbox" id="storage_snap" class="make-switch" data-on-color="primary"
                                       data-off-color="info" data-size="small"
                                       data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                <a class="popovers ml15" data-container="body" data-trigger="hover"
                                   data-placement="right"
                                   data-content="<?php echo $LANG['UI_LUN_STORAGE_SNAP_TIPS'] ?>;">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- 快照请求频率 -->
                    <div class="form-group" style="margin-left: -100px;" id="storage_snap_div">
                        <div class="control-label col-md-4 width45_en" style="margin-top: 15px;">
                            <?php echo $LANG['UI_LUN_STORAGE_SNAPSHOT_FREQUENCY'] ?>
                        </div>
                        <div>
                            <div class="col-md-4">
                                <div style="margin-top: 15px; display:inline-flex;">
                                    <div class="input-icon">
                                        <div id="snapNum" style="width: 140px;">
                                            <div class="input-group spinner-group" style="width:140px;">
                                                <input type="text" id="snapNumInput" style="text-align: center;"
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
                                        <span style="position: absolute;right: -40px;top: 7px;" class="r100_en">
                                            <?php echo $LANG['UI_LUN_STORAGE_COUNT_PER_SECOND'] ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 同步存储 -->
                    <div class="form-group" style="margin-left: -100px;">
                        <div class="control-label col-md-4 width45_en">
                            <?php echo $LANG['UI_LUN_STORAGE_SYNC'] ?>
                        </div>
                        <div>
                            <div class="col-md-4" style="margin-top: 4px;">
                                <input type="checkbox" id="sync_freq" class="make-switch" data-on-color="primary"
                                       data-off-color="info" data-size="small"
                                       data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                <a class="popovers ml15" data-container="body" data-trigger="hover"
                                   data-placement="right"
                                   data-content="<?php echo $LANG['UI_LUN_STORAGE_SYNC_TIPS'] ?>;">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- 同步时间间隔 -->
                    <div class="form-group" style="margin-left: -100px;" id="sync_div">
                        <div class="control-label col-md-4 width45_en" style="margin-top: 15px;">
                            <?php echo $LANG['UI_LUN_STORAGE_SYNC_FREQUENCY'] ?>
                        </div>
                        <div id="custom" class="col-md-4" style="margin-top: 15px; display:inline-flex;">
                            <div class="input-icon">
                                <div id="syncTimeNum" style="width: 140px;">
                                    <div class="input-group spinner-group" style="width:140px;">
                                        <input type="text" id="syncNumInput" style="text-align: center;"
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
                                <span style="position: absolute;right: -30px;top: 7px;" class="r75_en">
                                    <?php echo $LANG['WEB_UTILS_MINUTE'] ?>
                                </span>
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
                    <div class="col-md-offset-6 col-md-7">
                        <button type="button" style="margin-right: -7px;" id=""
                                class="btn green-haze btn-confirm submit">
                            <?php echo $LANG['UI_PUBLIC_YES'] ?>
                        </button>
                        <button type="button" id="" class="btn default cancel">
                            <?php echo $LANG['UI_PUBLIC_NO'] ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END ADD DRAWER -->


<!-- BEGIN MODAL DELETE-->
<div id="lunmodaldiv" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first"
     data-backdrop="static">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal"></button>
        <h4 class="modal-title"><i class="viconfont vicon-ge_delete"></i>
            <?php echo $LANG['UI_STORAGE_DELETE'] ?></h4>
    </div>
    <div class="modal-body">
        <div class="portlet-body" id="childrendiv">
            <div class="alert alert-warning">
                <button type="button" class="close" data-dismiss="alert"></button>
                <ul class="alert-ul">
                    <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                    <li>
                        <i class="fa fa-info-circle "></i>
                        <?php echo $LANG['UI_STORAGE_DELETE_TIP_TITLE'] ?>
                        <?php echo $LANG['UI_STORAGE_DELETE_TIP_CONTENT'] ?>
                    </li>
                </ul>
            </div>
        </div>
        <div class="row static-info">
            <div class="col-md-4 name textalignr">
                <?php echo $LANG['UI_STORAGE_DELETE_POINT_COUNT'] ?>:
            </div>
            <div class="col-md-8 value" id="timepointcount">
            </div>
        </div>
        <div class="row static-info">
            <div class="col-md-4 name textalignr">
                <?php echo $LANG['UI_STORAGE_DELETE_BACKUP_SIZE'] ?>:
            </div>
            <div class="col-md-8 value" id="timepointsize">
            </div>
        </div>
        <div class="row static-info">
            <div class="col-md-4 name textalignr">
                <?php echo $LANG['UI_STORAGE_DELETE_TASK_COUNT'] ?>:
            </div>
            <div class="col-md-8 value" id="taskcount">
            </div>
        </div>
        <div class="row static-info">
            <div class="col-md-4 name textalignr">
                <?php echo $LANG['UI_JOB_RNAME'] ?>:
            </div>
            <div class="col-md-8 value" id="taskname" style="max-height:150px;overflow-y:scroll;">
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
        <button type="button" class="btn btn-primary" id="delsubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>
<!-- END MODAL -->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<?php
if ($_SESSION['language'] != "en-us") {
    //如果不是英文,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
        $_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/fuelux/js/spinner.min.js"></script>
<script src="./scripts/libs/base64.js" type="text/javascript"></script>
<script type="text/javascript"
        src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/platform/storage/lun_storage_manager.js"></script>