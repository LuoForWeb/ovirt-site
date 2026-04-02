<?php
include_once '../../../tpl/permission.php';
include_once '../../../content/platform/public/bs_table.php';
?>

<!-- BEGIN PAGE LEVEL STYLES -->
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet"
      type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css"
      href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
<link rel="stylesheet" type="text/css"
      href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./scripts/plugins/popModal/popModal.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/daterangepicker/daterangepicker.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link href="./content/platform/resource/css/tapeManagement.css" rel="stylesheet" type="text/css" />
<!-- END PAGE LEVEL STYLES -->
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
<script type="text/javascript" src="./scripts/libs/md5.js" type="text/javascript"></script>

<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="storage_manager" href="./content/platform/storage/storagedevice.php">
            <span><?php echo $LANG['UI_PLATFORM_STORAGE'] ?></span>
        </a>
    </li>
    <span>></span>
    <span class="curent"><?php echo $LANG['UI_STORAGE_TYPE10'] ?></span>
</h3>

<!-- END PAGE HEADER-->
<?php
$userAllPermission = $_SESSION['permission'];

$tabNameArr = array('tape_device', 'tape_task', 'tape_manage_data');
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
<!-- BEGIN PAGE CONTENT-->
<div class="vinchin-wrapper tape-equipment-wrapper">
    <div class="vinchin-wrapper__tabs">
        <ul class="nav nav-tabs nav-line-tabs">
            <li id="deviceLi" class="nav-item <?php echo $activeClassArr['tape_device'] ?> <?php echo $displayArr['tape_device'] ?>" >
                <a href="#devicediv" class="nav-link" data-toggle="tab">
                    <i class="levelchild viconfont vicon-a-Tapecidai"></i><?php echo $LANG['UI_TAPE_EQUIPMENT'] ?> </a>
            </li>
            <li id="jobLi" class="nav-item <?php echo $activeClassArr['tape_task'] ?> <?php echo $displayArr['tape_task'] ?>" >
                <a href="#jobdiv" class="nav-link" data-toggle="tab">
                    <i class="levelchild viconfont vicon-a-Type-drivecidai"></i>  <?php echo $LANG['UI_TAPE_JOB'] ?> </a>
            </li>
            <li id="dataLi" class="nav-item <?php echo $activeClassArr['tape_manage_data'] ?> <?php echo $displayArr['tape_manage_data'] ?>" >
                <a href="#datadiv" class="nav-link" data-toggle="tab">
                    <i class="levelchild viconfont vicon-shujuguanli"></i>  <?php echo $LANG['UI_TAPE_DATA'] ?> </a>
            </li>
        </ul>
        <div class="tab-content hover-scroll-y">
            <div class="tab-pane <?php echo $activeClassArr['tape_device'] ?>" id="devicediv">
                <?php
                if (empty($displayArr['tape_device'])) {
                    // 显示才加载
                    include_once './tape_management.php';
                }
                ?>
            </div>
            <div class="tab-pane <?php echo $activeClassArr['tape_task'] ?>" id="jobdiv">
                <?php
                if (empty($displayArr['tape_task'])) {
                    include_once './tape_jobs.php';
                }
                ?>
            </div>
            <div class="tab-pane <?php echo $activeClassArr['tape_manage_data'] ?>" id="datadiv">
                <?php
                if (empty($displayArr['tape_manage_data'])) {
                    include_once './tape_data.php';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- BEGIN ADD MODAL -->
<div id="tape_group_modal" class="modal xmodal fade form-horizontal " tabindex="-1" data-backdrop="static">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal"></button>
        <h4 class="modal-title" id="addTitle"><?php echo $LANG['UI_TAPE_CREATE_TAPE_GROUP'] ?></h4>
    </div>
    <div class="modal-body">
        <form action="#" id="tapeForm" class="form-horizontal">
            <!-- 选择带库 -->
            <div class="form-group simpleDiv">
                <label class="col-md-3 control-label"><?php echo $LANG['UI_TAPE_SELECT_TAPE_LIB'] ?></label>
                <div class="col-md-8">
                    <div class="input-icon right">
                        <i class="fa"></i>
                        <!-- <input type="text" class="form-control" maxlength="128" name="ipaddress" id="group_name" placeholder=""> -->
                        <select name="" class="form-control select2me" id="select_tape_lib">
                        </select>
                    </div>
                </div>
            </div>
            <!-- 设置组名 -->
            <div class="form-group simpleDiv">
                <label class="col-md-3 control-label"><?php echo $LANG['UI_TAPE_GROUP_NAME'] ?></label>
                <div class="col-md-8">
                    <div class="input-icon right">
                        <i class="fa"></i>
                        <input type="text" class="form-control" maxlength="31" name="ipaddress" id="group_name"
                                placeholder="">
                    </div>
                </div>
            </div>
            <!-- 选择磁带 -->
            <div class="form-group" id="selectTapeDiv">
                <label class="control-label col-md-3"
                        style="line-height: 27px;"><?php echo $LANG['UI_TAPE_SELECT_TAPE_CARRIAGE'] ?></label>
                <div class="col-md-8">
                    <div class="table-container">
                        <table id="selectTape"></table>
                    </div>
                </div>
            </div>

            <!-- 磁带组用途 -->
            <div class="form-group" id="usemodeDiv" style="display: none;">
                <label class="control-label col-md-3"
                        style="margin-top: 5px;"><?php echo $LANG['UI_STORAGE_USED_FOR'] ?></label>
                <div class="col-md-6">
                    <div class="input-group marginh10" id="useMode">
                        <label class="backupDiv" style="padding-right: 20px;"><input type="checkbox"
                                                                                        id="backupCheck" data-checkbox="icheckbox_square-blue" data-mode="1"
                                                                                        class="icheck"><?php echo $LANG['UI_PLATFORM_BACKUP'] ?></label>
                        <label class="copyDiv" style="padding-right: 20px;"><input type="checkbox"
                                                                                    id="copyCheck" data-checkbox="icheckbox_square-blue" data-mode="2"
                                                                                    class="icheck"><?php echo $LANG['WEB_PLATFORM_DES_COPY_AND_ARCHIVE'] ?></label>
                    </div>
                </div>
            </div>

            <!-- 告警阈值 -->
            <div class="storagewarningdiv">
                <div class="form-group">
                    <label
                            class="control-label col-md-3 form-group-label"><?php echo $LANG['UI_STORAGE_ALERT'] ?></label>
                    <div class="col-md-2 form-group-content">
                        <input type="checkbox" id="noticeswitch" checked class="make-switch" data-size="small"
                                data-on-color="primary" data-off-color="info"
                                data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                    </div>
                </div>

                <div class="form-group warnningdiv">
                    <label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_ALERT_TYPE'] ?>
                    </label>
                    <div class="col-md-8">
                        <select class="form-control select2me" name="noticetype">
                            <option value="1"><?php echo $LANG['UI_STORAGE_ALERT_PERCENT'] ?></option>
                            <option value="2"><?php echo $LANG['UI_STORAGE_ALERT_SIZE'] ?></option>
                        </select>
                        <div><span class="help-block ">
                                <?php echo $LANG['UI_STORAGE_ALERT_TIPS'] ?>
                            </span></div>
                    </div>
                </div>

                <div class="form-group warnningdiv percentdiv" id='percentdiv'>
                    <label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_ALERT_THRESHOLD'] ?>
                    </label>
                    <div class="col-md-4" style="display:inline-flex;">
                        <div id="spinnerpercent">
                            <div class="input-group spinner-group">
                                <input type="text" id="warningpercent" style="text-align: left;"
                                        class="spinner-input form-control" maxlength="3"
                                        onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}"
                                        onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'0')}else{this.value=this.value.replace(/\D/g,'')}">
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
                        <div style="font-size: 14px;margin: 6px 12px 12px 12px;">
                            %
                        </div>
                    </div>

                </div>

                <div class="form-group warnningdiv sizediv display-none" id='sizediv'>
                    <label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_ALERT_THRESHOLD'] ?>
                    </label>
                    <div class="col-md-4" style="display:inline-flex;">
                        <div id="spinnersize">
                            <div class="input-group spinner-group">
                                <input type="text" id="warningsize" style="text-align: left;"
                                        class="spinner-input form-control" maxlength="8"
                                        onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}"
                                        onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'0')}else{this.value=this.value.replace(/\D/g,'')}">
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
                        <div style="font-size: 14px;margin: 12px;margin-top: 7px">
                            GB
                        </div>
                    </div>
                </div>
            </div>

            <!-- 磁带组策略 -->
            <div class="form-group simpleDiv">
                <label class="col-md-3 control-label"><?php echo $LANG['UI_TAPE_GROUP_STRATEGY'] ?></label>
                <div class="col-md-8 accordion">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a class="accordion-toggle accordion-toggle-styled popovers" data-content=""
                                    data-container="body" data-trigger="hover" data-placement="top"
                                    data-toggle="collapse" data-parent="" href="#highconfig">
                                    <?php echo $LANG['UI_TAPE_GROUP_STRATEGY'] ?></a>
                            </h4>
                        </div>
                        <div id="highconfig" class="panel-collapse collapse in">
                            <div class="panel-body">
                                <div class="col-md-12 pl0">
                                    <!-- strategy -->
                                    <div class="">
                                        <div class="form-group simpleDiv">
                                            <label class="col-md-4 control-label" style="word-break: keep-all;">
                                                <?php echo $LANG['UI_TAPE_SELECT_GENERATE_STRATEGY'] ?>
                                            </label>
                                            <div class="col-md-7">
                                                <select name="" class="form-control select2me"
                                                        id="generate_strategy">
                                                    <option value="1" class="form-control select2me">
                                                        <?php echo $LANG['UI_TAPE_SELECT_GENERATE_STRATEGY1'] ?>
                                                    </option>
                                                    <option value="2" class="form-control select2me">
                                                        <?php echo $LANG['UI_TAPE_SELECT_GENERATE_STRATEGY2'] ?>
                                                    </option>
                                                    <option value="3" class="form-control select2me">
                                                        <?php echo $LANG['UI_TAPE_SELECT_GENERATE_STRATEGY3'] ?>
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-md-1">
                                                <a class="popovers" data-container="body" data-trigger="hover"
                                                    data-placement="right" data-content="<?php echo $LANG['UI_TAPE_GROUP_GENERATE_STRATEGY_TIP1'] . "<br><br>"
                                                        . $LANG['UI_TAPE_GROUP_GENERATE_STRATEGY_TIP2'] . "<br><br>"
                                                        . $LANG['UI_TAPE_GROUP_GENERATE_STRATEGY_TIP3'] ?>" data-html="true" data-original-title="" title="">
                                                    <i class="viconfont vicon-tishi"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="form-group simpleDiv" id="generate_strategy_days_div"
                                                style="display:none">
                                            <label for="generate_strategy_days"
                                                    class="col-md-4 control-label"><?php echo $LANG['UI_TAPE_DAYS_INTERVAL'] ?></label>
                                            <div class="col-md-7">
                                                <div id="generate_div">
                                                    <div class="input-group spinner-group">
                                                        <input type="text" id="generate_strategy_days"
                                                                style="text-align: left;"
                                                                class="spinner-input form-control" maxlength="3"
                                                                onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}"
                                                                onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'0')}else{this.value=this.value.replace(/\D/g,'')}">
                                                        <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                            <button type="button"
                                                                    class="btn spinner-up default">
                                                                <i class="fa fa-angle-up"></i>
                                                            </button>
                                                            <button type="button"
                                                                    class="btn spinner-down default">
                                                                <i class="fa fa-angle-down"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="form-group simpleDiv configDiv">
                                        <label class="control-label col-md-4"
                                                style="word-break: keep-all;"><?php echo $LANG['UI_TAPE_RESERVE_STRATEGY'] ?>
                                        </label>
                                        <div class="col-md-7">
                                            <select name="" class="form-control select2me"
                                                    id="reserve_strategy">
                                                <option class="form-control select2me" value="1">
                                                    <?php echo $LANG['UI_TAPE_RESERVE_STRATEGY1'] ?>
                                                </option>
                                                <option class="form-control select2me" value="2">
                                                    <?php echo $LANG['UI_TAPE_RESERVE_STRATEGY2'] ?>
                                                </option>
                                                <option class="form-control select2me" value="3">
                                                    <?php echo $LANG['UI_TAPE_RESERVE_STRATEGY3'] ?>
                                                </option>
                                            </select>
                                        </div>
                                        <div class="col-md-1">
                                            <a class="popovers" data-container="body" data-trigger="hover"
                                                data-placement="right"
                                                data-content="<?php echo $LANG['UI_TAPE_GROUP_RESERVE_STRATEGY_TIP1'] . "<br><br>"
                                                    . $LANG['UI_TAPE_GROUP_RESERVE_STRATEGY_TIP2'] . "<br><br>"
                                                    . $LANG['UI_TAPE_GROUP_RESERVE_STRATEGY_TIP3'] . "<br><br>" ?>"
                                                data-html="true" data-original-title="" title="">
                                                <i class="viconfont vicon-tishi"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="form-group simpleDiv" id="reserve_strategy_days_div"
                                            style="display:none">
                                        <label for="reserve_strategy_days"
                                                class="col-md-4 control-label"><?php echo $LANG['UI_BACKUP_RESERVE_DAY'] ?></label>
                                        <div class="col-md-7">
                                            <div id="reserve_div">
                                                <div class="input-group spinner-group">
                                                    <input type="text" id="reserve_strategy_days"
                                                            style="text-align: left;"
                                                            class="spinner-input form-control" maxlength="3"
                                                            onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}"
                                                            onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'0')}else{this.value=this.value.replace(/\D/g,'')}">
                                                    <div
                                                            class="spinner-buttons input-group-btn spinner-group-btn">
                                                        <button type="button" class="btn spinner-up default">
                                                            <i class="fa fa-angle-up"></i>
                                                        </button>
                                                        <button type="button" class="btn spinner-down default">
                                                            <i class="fa fa-angle-down"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group simpleDiv">
                    <label class="col-md-3 control-label"><?php echo $LANG['UI_PUBLIC_DESCRIPTION'] ?></label>
                    <div class="col-md-8">
                        <div class="input-icon right">
                            <i class="fa"></i>
                            <textarea name="" class="form-control tape-textarea" id="group_des" cols="30"
                                        rows="1" maxlength="255"></textarea>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>
    <div class="modal-footer">
        <button type="button" data-dismiss="modal"
                class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
        <button type="button" class="btn btn-primary"
                id="addsubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>
<!-- END ADD MODAL -->

<!-- BEGIN EDIT MODAL -->
<div id="edit_tape_group_modal" class="modal xmodal fade form-horizontal " tabindex="-1"
        data-focus-on="input:first" data-backdrop="static">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal"></button>
        <h4 class="modal-title" id="addTitle"><?php echo $LANG['UI_TAPE_MODIFY_GROUP'] ?></h4>
    </div>
    <div class="modal-body">
        <form action="#" class="form-horizontal">
            <div class="form-group simpleDiv">
                <label class="col-md-3 control-label"><?php echo $LANG['UI_TAPE_GROUP_NAME'] ?></label>
                <div class="col-md-8">
                    <div class="input-icon right">
                        <i class="fa"></i>
                        <input type="text" class="form-control" maxlength="128" name="group_name"
                                id="edit_group_name" placeholder="">
                    </div>
                </div>
            </div>
            <!-- 操作已存在于磁带组的磁带 -->
            <div class="form-group" id="edit_select_tapediv">
                <label class="control-label col-md-3"
                        style="line-height: 27px;"><?php echo $LANG['UI_TAPE_SELECTED_TAPE_CARRIAGE'] ?></label>
                <div class="col-md-8">
                    <div class="table-container">
                        <table id="edit_select_tape"></table>
                    </div>
                </div>
            </div>

            <!-- 选择新添加磁带 -->
            <div class="form-group" id="edit_add_tapediv">
                <label class="control-label col-md-3"
                        style="line-height: 27px;"><?php echo $LANG['UI_TAPE_NEW_TAPE_CARRIAGE'] ?></label>
                <div class="col-md-8 accordion">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a class="accordion-toggle accordion-toggle-styled popovers" data-content=""
                                    data-container="body" data-trigger="hover" data-placement="top"
                                    data-toggle="collapse" data-parent="" href="#addtape">
                                    <?php echo $LANG['UI_TAPE_SELECT_NEW_TAPE_CARRIAGE'] ?>
                                </a>
                            </h4>
                        </div>
                        <div id="addtape" class="panel-collapse collapse in">
                            <div class="panel-body">
                                <div class="table-container">
                                    <table id="edit_add_tape"></table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 磁带组用途 -->
            <div class="form-group" id="editusemodeDiv" style="display: none;">
                <label class="control-label col-md-3"
                        style="margin-top: 5px;"><?php echo $LANG['UI_STORAGE_USED_FOR'] ?></label>
                <div class="col-md-6">
                    <div class="input-group marginh10" id="edituseMode">
                        <label class="backupDiv" style="padding-right: 20px;"><input type="checkbox"
                                                                                        id="editbackupCheck" data-checkbox="icheckbox_square-blue" data-mode="1"
                                                                                        class="icheck"><?php echo $LANG['UI_PLATFORM_BACKUP'] ?></label>
                        <label class="copyDiv" style="padding-right: 20px;"><input type="checkbox"
                                                                                    id="editcopyCheck" data-checkbox="icheckbox_square-blue" data-mode="2"
                                                                                    class="icheck"><?php echo $LANG['WEB_PLATFORM_DES_COPY_AND_ARCHIVE'] ?></label>
                    </div>
                </div>
            </div>

            <!-- 告警阈值 -->
            <div class="storagewarningdiv">
                <div class="form-group">
                    <label
                            class="control-label col-md-3 form-group-label"><?php echo $LANG['UI_STORAGE_ALERT'] ?></label>
                    <div class="col-md-2 form-group-content">
                        <input type="checkbox" id="editnoticeswitch" checked class="make-switch"
                                data-size="small" data-on-color="primary" data-off-color="info"
                                data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                    </div>
                </div>

                <div class="form-group warnningdiv">
                    <label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_ALERT_TYPE'] ?>
                    </label>
                    <div class="col-md-8">
                        <select class="form-control select2me" name="noticetype">
                            <option value="1"><?php echo $LANG['UI_STORAGE_ALERT_PERCENT'] ?></option>
                            <option value="2"><?php echo $LANG['UI_STORAGE_ALERT_SIZE'] ?></option>
                        </select>
                        <div><span class="help-block ">
                                <?php echo $LANG['UI_STORAGE_ALERT_TIPS'] ?>
                            </span></div>
                    </div>
                </div>

                <div class="form-group warnningdiv percentdiv" id='editpercentdiv'>
                    <label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_ALERT_THRESHOLD'] ?>
                    </label>
                    <div class="col-md-4" style="display:inline-flex;">
                        <div id="editspinnerpercent">
                            <div class="input-group spinner-group">
                                <input type="text" id="editwarningpercent" style="text-align: left;"
                                        class="spinner-input form-control" maxlength="3"
                                        onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}"
                                        onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'0')}else{this.value=this.value.replace(/\D/g,'')}">
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
                        <div style="font-size: 14px;margin: 6px 12px 12px 12px;">
                            %
                        </div>
                    </div>

                </div>

                <div class="form-group warnningdiv display-none sizediv" id='editsizediv'>
                    <label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_ALERT_THRESHOLD'] ?>
                    </label>
                    <div class="col-md-4" style="display:inline-flex;">
                        <div id="editspinnersize">
                            <div class="input-group spinner-group">
                                <input type="text" id="editwarningsize" style="text-align: left;"
                                        class="spinner-input form-control" maxlength="8"
                                        onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}"
                                        onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'0')}else{this.value=this.value.replace(/\D/g,'')}">
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
                        <div style="font-size: 14px;margin: 12px;margin-top: 7px">
                            GB
                        </div>
                    </div>
                </div>
            </div>

            <!-- 磁带组策略 -->
            <div class="form-group simpleDiv">
                <label class="col-md-3 control-label"><?php echo $LANG['UI_TAPE_GROUP_STRATEGY'] ?></label>
                <div class="col-md-8 accordion">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a class="accordion-toggle accordion-toggle-styled popovers" data-content=""
                                    data-container="body" data-trigger="hover" data-placement="top"
                                    data-toggle="collapse" data-parent="" href="#edithighconfig">
                                    <?php echo $LANG['UI_TAPE_GROUP_STRATEGY'] ?></a>
                            </h4>
                        </div>
                        <div id="edithighconfig" class="panel-collapse collapse in">
                            <div class="panel-body">
                                <div class="col-md-12 pl0">
                                    <div class="">
                                        <div class="form-group simpleDiv">
                                            <label class="col-md-4 control-label"
                                                    style="word-break: keep-all;"><?php echo $LANG['UI_TAPE_SELECT_GENERATE_STRATEGY'] ?>
                                            </label>
                                            <div class="col-md-7">
                                                <!-- <input type="text" class="form-control" maxlength="64" name="nasport" id="nasport"> -->
                                                <select name="" class="form-control select2me"
                                                        id="edit_generate_strategy">
                                                    <option value="1" class="form-control select2me">
                                                        <?php echo $LANG['UI_TAPE_SELECT_GENERATE_STRATEGY1'] ?>
                                                    </option>
                                                    <option value="2" class="form-control select2me">
                                                        <?php echo $LANG['UI_TAPE_SELECT_GENERATE_STRATEGY2'] ?>
                                                    </option>
                                                    <option value="3" class="form-control select2me">
                                                        <?php echo $LANG['UI_TAPE_SELECT_GENERATE_STRATEGY3'] ?>
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-md-1">
                                                <a class="popovers" data-container="body" data-trigger="hover"
                                                    data-placement="right" data-content="<?php echo $LANG['UI_TAPE_GROUP_GENERATE_STRATEGY_TIP1'] . "<br><br>"
                                                        . $LANG['UI_TAPE_GROUP_GENERATE_STRATEGY_TIP2'] . "<br><br>"
                                                        . $LANG['UI_TAPE_GROUP_GENERATE_STRATEGY_TIP3'] ?>" data-html="true" data-original-title="" title="">
                                                    <i class="viconfont vicon-tishi"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="form-group simpleDiv" id="edit_generate_days_div"
                                                style="display:none">
                                            <label for="edit_generate_days_div"
                                                    class="col-md-4 control-label"><?php echo $LANG['UI_TAPE_DAYS_INTERVAL'] ?></label>
                                            <div class="col-md-7">
                                                <div id="edit_generate_div">
                                                    <div class="input-group spinner-group">
                                                        <input type="text" id="edit_generate_days"
                                                                style="text-align: left;"
                                                                class="spinner-input form-control" maxlength="3"
                                                                onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}"
                                                                onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'0')}else{this.value=this.value.replace(/\D/g,'')}">
                                                        <div
                                                                class="spinner-buttons input-group-btn spinner-group-btn">
                                                            <button type="button"
                                                                    class="btn spinner-up default">
                                                                <i class="fa fa-angle-up"></i>
                                                            </button>
                                                            <button type="button"
                                                                    class="btn spinner-down default">
                                                                <i class="fa fa-angle-down"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="form-group simpleDiv configDiv">
                                        <label class="control-label col-md-4"
                                                style="word-break: keep-all;"><?php echo $LANG['UI_TAPE_RESERVE_STRATEGY'] ?>
                                        </label>
                                        <div class="col-md-7">
                                            <select name="" class="form-control select2me"
                                                    id="edit_reserve_strategy">
                                                <option class="form-control select2me" value="1">
                                                    <?php echo $LANG['UI_TAPE_RESERVE_STRATEGY1'] ?>
                                                </option>
                                                <option class="form-control select2me" value="2">
                                                    <?php echo $LANG['UI_TAPE_RESERVE_STRATEGY2'] ?>
                                                </option>
                                                <option class="form-control select2me" value="3">
                                                    <?php echo $LANG['UI_TAPE_RESERVE_STRATEGY3'] ?>
                                                </option>
                                            </select>
                                        </div>
                                        <div class="col-md-1">
                                            <a class="popovers" data-container="body" data-trigger="hover"
                                                data-placement="right" data-content="<?php echo $LANG['UI_TAPE_GROUP_RESERVE_STRATEGY_TIP1'] . "<br><br>"
                                                    . $LANG['UI_TAPE_GROUP_RESERVE_STRATEGY_TIP2'] . "<br><br>"
                                                    . $LANG['UI_TAPE_GROUP_RESERVE_STRATEGY_TIP3'] . "<br><br>"
                                                    . $LANG['UI_TAPE_GROUP_RESERVE_STRATEGY_TIP4'] . "<br><br>"
                                                    . $LANG['UI_TAPE_GROUP_RESERVE_STRATEGY_TIP5'] ?>" data-html="true" data-original-title="" title="">
                                                <i class="viconfont vicon-tishi"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="form-group simpleDiv" id="edit_reserve_days_div"
                                            style="display:none">
                                        <label for="reserve_strategy_days"
                                                class="col-md-4 control-label"><?php echo $LANG['UI_BACKUP_RESERVE_DAY'] ?></label>
                                        <div class="col-md-7">
                                            <div id="edit_reserve_div">
                                                <div class="input-group spinner-group">
                                                    <input type="text" id="edit_reserve_days"
                                                            style="text-align: left;"
                                                            class="spinner-input form-control" maxlength="3"
                                                            onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}"
                                                            onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'0')}else{this.value=this.value.replace(/\D/g,'')}">
                                                    <div
                                                            class="spinner-buttons input-group-btn spinner-group-btn">
                                                        <button type="button" class="btn spinner-up default">
                                                            <i class="fa fa-angle-up"></i>
                                                        </button>
                                                        <button type="button" class="btn spinner-down default">
                                                            <i class="fa fa-angle-down"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group simpleDiv">
                    <label class="col-md-3 control-label"><?php echo $LANG['UI_PUBLIC_DESCRIPTION'] ?></label>
                    <div class="col-md-8">
                        <div class="input-icon right">
                            <i class="fa"></i>
                            <textarea name="" class="form-control tape-textarea" id="edit_group_des" cols="30"
                                        maxlength="255" rows="1"></textarea>
                            <!-- <input type="text" class="form-control" maxlength="128" name="ipaddress" id="group_name" placeholder=""> -->
                        </div>
                    </div>
                </div>
                <!-- <div class="col-md-offset-1 col-md-10 accordion">

                </div> -->
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <button type="button" data-dismiss="modal"
                class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
        <button type="button" class="btn btn-primary"
                id="editsubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>
<!-- END EDIT MODAL -->

<!-- BEGIN EDIT TAPE MODAL -->
<div id="edit_tape_modal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first"
        data-backdrop="static">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal"></button>
        <h4 class="modal-title" id="addTitle"><?php echo $LANG['UI_TAPE_MODIFY_CARRIAGE'] ?></h4>
    </div>
    <div class="modal-body">
        <form action="#" class="form-horizontal">
            <div class="form-group simpleDiv">
                <label class="col-md-3 control-label"><?php echo $LANG['UI_TAPE_CARRIAGE_NAME'] ?></label>
                <div class="col-md-8">
                    <div class="input-icon right">
                        <i class="fa"></i>
                        <input type="text" class="form-control" maxlength="128" name="group_name"
                                id="edit_tape_name" placeholder="">
                    </div>
                </div>
            </div>
            <div class="form-group simpleDiv">
                <label class="col-md-3 control-label"><?php echo $LANG['UI_TAPE_DESCRIPTION'] ?></label>
                <div class="col-md-8">
                    <div class="input-icon right">
                        <i class="fa"></i>
                        <input type="text" class="form-control" maxlength="255" name="group_name"
                                id="edit_tape_des" placeholder="">
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <button type="button" data-dismiss="modal"
                class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
        <button type="button" class="btn btn-primary"
                id="tapesubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>
<!-- END EDIT TAPE MODAL -->

<!-- BEGIN EDIT BACKUPSET MODAL -->
<div id="edit_backup_set_modal" class="modal xmodal fade form-horizontal " tabindex="-1"
        data-focus-on="input:first" data-backdrop="static">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal"></button>
        <h4 class="modal-title" id="addTitle"><?php echo $LANG['UI_TAPE_MODIFY_BACKUP_SET'] ?></h4>
    </div>
    <div class="modal-body">
        <form action="#" class="form-horizontal">
            <div class="form-group simpleDiv">
                <label class="col-md-3 control-label"><?php echo $LANG['UI_TAPE_BACKUP_SET_NAME'] ?></label>
                <div class="col-md-8">
                    <div class="input-icon right">
                        <i class="fa"></i>
                        <input type="text" class="form-control" maxlength="128" id="edit_backup_set_name">
                    </div>
                </div>
            </div>
        </form>

    </div>
    <div class="modal-footer">
        <button type="button" data-dismiss="modal"
                class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
        <button type="button" class="btn btn-primary"
                id="backup_set_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>
<!-- END EDIT BACKUPSET MODAL -->

<!-- BEGIN EDIT BACKUPSETPOINT MODAL -->
<div id="time_point_modal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first"
        data-backdrop="static">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal"></button>
        <h4 class="modal-title" id="addTitle"><?php echo $LANG['UI_RECOVERY_BACKUP_POINT'] ?></h4>
    </div>
    <div class="modal-body">
        <div class="filelisttext pd0" id="pointlist" style="height: 82%;">
            <ul id="backupset_tree" class="ztree ztree-fa"></ul>

        </div>
        <div class="alert alert-block alert-info fade in ml15 mt15 mb0" id="timepointtips">
            <button type="button" class="close" data-dismiss="alert"></button>
            <ul class="alert-ul">
                <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>：</strong>
                <li>
                    <?php echo $LANG['UI_TAPE_BACKUP_TIME_POINT_TIP'] ?>
                </li>
            </ul>
        </div>

    </div>
    <div class="modal-footer">
        <button type="button" data-dismiss="modal"
                class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
    </div>
</div>
<!-- END EDIT BACKUPSETPOINT MODAL -->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/libs/md5.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/popModal/popModal.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script src="./scripts/platform/resource/tape_equipment.js"></script>
<!-- END PAGE LEVEL PLUGINS -->

    <!--- 日志---->
<?php
include_once 'tape_monitor.php';
?>