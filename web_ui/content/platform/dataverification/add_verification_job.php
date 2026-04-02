<?php

include_once '../../../tpl/permission.php'; ?>
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link rel="stylesheet" type="text/css" href="./scripts/plugins/popModal/popModal.css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="./css/platform/component/driver_check.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/tree-grid/jquery.treegrid.css" />
<link rel="stylesheet" type="text/css" href="./scripts/components/filter/css/filter.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/component/safe_virus.css"/>
<style>
    /*数据验证-虚拟演练室详情里的表格默认高度调整*/
    .tableDiv .fixed-table-body{
        height: revert !important;
    }
    .basicDiv{
        background: #FAFAFA;
        padding: 20px 16px;
        border-radius: 4px;
    }
    .radio-group .radio-group__item strong{
        color: #333;
    }
    .radio-group .radio-group__item.active strong{
        color: #0FBF98;
    }
    .filter-dropdown-menu__content{
        height: 200px;
        overflow: auto;
    }
</style>
<?php session_start();
session_commit(); ?>
<!-- BEGIN PAGE HEADER-->

<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row" style="height:100%;">
    <div class="col-md-12" style="height:100%;">
        <input id="task_uuid" value="<?php echo $_GET['uuid']; ?>" class="display-none"></input>
        <input id="oem_version" value="<?php echo $CONF['SYSTEM_INFO']['vendor']; ?>" class="display-none"></input>
        <div class="portlet box blue-hoki" id="verificationContent">
            <div class="portlet-title">
                <div class="caption">
                    <i class="viconfont vicon-add_verification_job"></i> <?php if (!empty($_GET['uuid'])) {
                        echo $LANG['WEB_SR_OP_CODE_EDIT_SUREBACKUP'];
                    } else {
                        echo $LANG['UI_VERIFY_CREATE_JOB'];
                    } ?>
                </div>
            </div>
            <div class="portlet-body form">
                <form action="#" class="form-horizontal" id="submit_form" method="POST">
                    <div class="form-wizard">
                        <div class="form-body">
                            <ul class="nav nav-pills steps">
                                <li>
                                    <a href="#tab1" data-toggle="tab" class="step">
                                        <span class="number">1 </span>
                                        <span class="desc">
                                            <?php echo $LANG['UI_VERIFY_MODE'] ?>
                                            <i class="fa fa-check"></i>
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#tab2" data-toggle="tab" class="step">
                                        <span class="number">2 </span>
                                        <span class="desc">
                                            <?php echo $LANG['UI_VERIFY_SRC'] ?>
                                            <i class="fa fa-check"></i>
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#tab3" data-toggle="tab" class="step active">
                                        <span class="number">3 </span>
                                        <span class="desc">
                                            <?php echo $LANG['UI_VERIFY_STRATEGY'] ?>
                                            <i class="fa fa-check"></i>
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#tab4" data-toggle="tab" class="step">
                                        <span class="number">4 </span>
                                        <span class="desc">
                                            <?php echo $LANG['UI_BACKUP_CONFIRM_CONFIG'] ?>
                                            <i class="fa fa-check"></i>
                                        </span>
                                    </a>
                                </li>
                            </ul>
                            <div class="tab-content bakuptab">
                                <div class="tab-pane active overflowy-auto" id="tab1">
                                    <div class="row tab-pane__row">

                                        <div class="form-group <?php if ($CONF['SYSTEM_INFO']['vendor'] == $CONF['VENDOR_LIST']['gmp']) {
                                            echo "display-hide";
                                        } ?>">
                                            <label class="control-label col-md-3" style="padding-top:23px !important;"><?php echo $LANG['UI_VERIFY_MODE'] ?></label>
                                            <div class="col-md-9">
                                                <div class="radio-group" id="verifyMode">
                                                    <label class="radio-group__item me-20 active" value="2" style="width:300px;height:150px;display: inline-block;">
                                                        <strong><?php echo $LANG['UI_VERIFY_MODE_FULL_RECOVER'] ?></strong><br>
                                                        <p style="font-size:12px;margin-top:10px;color:#666;"><?php echo $LANG['UI_VERIFY_MODE_FULL_RECOVER_TIPS'] ?></p>
                                                    </label>
                                                    <label class="radio-group__item me-20 " value="1" style="width:300px;height:150px; <?php if($CONF['SYSTEM_INFO']['vendor'] == "sangfor"){ echo "display: none;";}else{echo "display: inline-block;";}?>">
                                                        <strong><?php echo $LANG['UI_VERIFY_MODE_BACKUP_DATA_SCAN'] ?></strong><br>
                                                        <p style="font-size:12px;margin-top:10px;color:#666;"><?php echo $LANG['UI_VERIFY_MODE_BACKUP_DATA_SCAN_TIPS'] ?></p>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group <?php if ($CONF['SYSTEM_INFO']['vendor'] == $CONF['VENDOR_LIST']['gmp'] || $CONF['SYSTEM_INFO']['vendor'] == "sangfor") {
                                            echo "display-hide";
                                        } ?> verifyTypeDiv">
                                            <label class="control-label col-md-3 pt0"><?php echo $LANG['UI_VERIFY_TYPE'] ?></label>
                                            <div class="col-md-4 col-md-5_en">
                                                <div class="radio-group" id="verifyType">
                                                    <label class="mb0 radio-label"><input type="radio"  data-checkbox="iradio_square-blue" data-mode="0" class="icheck" checked><?php echo $LANG['UI_VERIFY_AUTOMATIC'] ?></label>
                                                    <label class="pl40 mb0 radio-label"><input type="radio"  data-checkbox="iradio_square-blue" data-mode="1" class="icheck"><?php echo $LANG['UI_VERIFY_MANUAL'] ?></label>

                                                    <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VERIFY_TYPE_TIPS'] ?>">
                                                        <i class="viconfont vicon-tishi"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group templeteDiv <?php if ($CONF['SYSTEM_INFO']['vendor'] != $CONF['VENDOR_LIST']['gmp']) {
                                            echo "display-hide";
                                        } ?>">
                                            <label class="control-label col-md-3 "><?php echo $LANG['UI_VERIFY_REPORT_TEMPLATE'] ?></label>
                                            <div class="col-md-6">
                                                <select class="form-control select2me inline-block width364" name="template">
                                                </select>
                                                <a id="editTemplate" class="ml10 " href="javascript:;"><?php echo $LANG['UI_VERIFY_REPORT_TEMPLATE_EDIT'] ?></a>
                                            </div>
                                        </div>

                                        <div class="form-group approvalDiv <?php if ($CONF['SYSTEM_INFO']['vendor'] != $CONF['VENDOR_LIST']['gmp']) {
                                            echo "display-hide";
                                        } ?>">
                                            <label class="control-label col-md-3 "><?php echo $LANG['UI_VERIFY_APPROVAL'] ?></label>
                                            <div class="col-md-4">
                                                <select class="form-control select2me width364" name="approval" >
                                                </select>
                                            </div>
                                        </div>


                                        <div class="form-group labTypeDiv <?php if ($CONF['SYSTEM_INFO']['vendor'] == $CONF['VENDOR_LIST']['gmp']) {
                                            echo "display-hide";
                                        } ?>">
                                            <label class="control-label col-md-3  pt0"><?php echo $LANG['UI_VIRTURL_LAB_NAME'] ?></label>
                                            <div class="col-md-6">
                                                <div class="radio-group" id="labType">
                                                    <label class="pr40 mb0 radio-label"><input type="radio"   data-checkbox="iradio_square-blue" data-mode="1" class="icheck" checked><?php echo $LANG['UI_VERIFY_LAB_AUTO_CREATE'] ?></label>
                                                    <label class="mb0 radio-label"><input type="radio"  data-checkbox="iradio_square-blue" data-mode="2" class="icheck"><?php echo $LANG['UI_VERIFY_LAB_CREATED'] ?></label>

                                                    <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VERIFY_LAB_CREATE_TIPS'] ?>">
                                                        <i class="viconfont vicon-tishi"></i>
                                                    </a>
                                                </div>
                                                <span class="help-block autolabTips"><?php echo $LANG['UI_VERIFY_LAB_AUTO_CREATE_TIPS'] ?></span>
                                            </div>
                                        </div>

                                        <div class="form-group virtualLabDiv display-hide">
                                            <label class="control-label col-md-3 "><?php echo $LANG['UI_VERIFY_LAB_SELECT'] ?></label>
                                            <div class="col-md-6">
                                                <select class="form-control select2me inline-block width364" name="virtualLab" >
                                                </select>
                                                <a id="showLabDetail" class="ml10" href="javascript:;"><?php echo $LANG['UI_VERIFY_CHECK_DETAILS'] ?></a>
                                            </div>
                                        </div>
                                        <div class="col-md-offset-3 col-md-6 pt40">
                                            <div class="alert alert-block alert-info fade in" id="marktips">
                                                <button type="button" class="close" data-dismiss="alert"></button>
                                                <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                                <ol class ="alert-ol">
                                                    <li>
                                                        <?php echo $LANG['UI_VERIFY_CREATE_JOB_TIPS'] ?>
                                                    </li>
                                                    <li>
                                                        <?php echo $LANG['UI_VERIFY_SELECT_LAB_OBJECT_TIPS'] ?>
                                                    </li>
                                                    <li>
                                                        <?php echo $LANG['UI_VERIFY_CREATE_JOB_TIPS1'] ?>
                                                    </li>
                                                    <li>
                                                        <?php echo $LANG['UI_VERIFY_CREATE_JOB_TIPS2'] ?>
                                                    </li>
                                                </ol>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="tab-pane" id="tab2">
                                    <div class="row tab-pane__row">
                                        <div class="col-md-4 tab-pane__row__source">
                                            <!-- 请选择备份源 -->
                                            <div class="form-group src-wrap">
                                                <div class="src-wrap__title">
                                                    <i class="viconfont vicon-ge_vm"></i>
                                                    <span><?php echo $LANG['UI_VERIFY_SRC_SELECT'] ?></span>
                                                </div>
                                                <div class="src-wrap__content">
                                                    <div class="src-wrap__content__search strategy-group">
                                                        <div class="src-search_wrapper appgroupDiv">
                                                            <label class="control-label src-search_label"><?php echo $LANG['UI_VERIFY_APP_GROUP_SELECT'] ?></label>
                                                            <select class="bs-select form-control input-sm" data-show-subtext="true" name="appgroup">
                                                            </select>
                                                        </div>
                                                        <div class="src-search_wrapper ">
                                                            <label class="control-label src-search_label"><?php echo $LANG['UI_VERIFY_SELECT_STORAGE'] ?></label>
                                                            <select class="bs-select form-control input-sm" data-show-subtext="true" id="storagetypeselect">
                                                            </select>
                                                        </div>

                                                        <div class="src-search_wrapper nodeDiv">
                                                            <label class="control-label src-search_label"><?php echo $LANG['UI_NODE_POOL_NODE_LIST'] ?></label>
                                                            <select class="bs-select form-control input-sm" data-show-subtext="true" id="nodeSelect">
                                                            </select>
                                                        </div>

                                                        <div style="display:flex;">
                                                            <div class="input-icon" style="flex:3;">
                                                                <input class="form-control" type="text" placeholder="<?php echo $LANG['UI_VERIFY_SEARCH_BY_OBJECT_NAME'] ?>" id="search" onkeyup="customInputValidate('string', this.value, $(this))" >
                                                            </div>
                                                            <div style="flex:1;display: flex;">
                                                                <div id="verify_filter_wrapper" class="position-relative" style="margin-left: 8px;"></div>
                                                                <button type="button" class="btn b-btn btn-title btn b-btn btn-title-default btn-table orderDesc" title="<?php echo $LANG['UI_VERIFY_ORDER_DESC'] ?>" style="color:#0FBF98;width:34px;height:34px;margin-left: 8px;padding:9px;">
                                                                    <i class="viconfont vicon-desc"></i>
                                                                </button>
                                                                <button type="button" class="btn b-btn btn-title btn b-btn btn-title-default btn-table orderAsc display-hide" title="<?php echo $LANG['UI_VERIFY_ORDER_ASC'] ?>" style="color:#0FBF98;width:34px;height:34px;margin-left: 8px;padding:9px;">
                                                                    <i class="viconfont vicon-asc"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="src-wrap__content__ztree" style="height: calc(100% - 165px) !important;">
                                                        <div class="three_tree">
                                                            <ul id="object_tree" class="ztree bd1de5"></ul>
                                                        </div>
                                                        <div class="alert alert-block alert-info fade in display-hide"  id="nosearchtips">
                                                            <button type="button" class="close" data-dismiss="alert"></button>
                                                            <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                                            <ul class = "alert-ul pl0">
                                                                <li>
                                                                    <?php echo $LANG['UI_DATA_NOSEARCH_TIPS'] ?>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                        <div class="alert alert-block alert-info fade in display-hide"  id="noobjecttips">
                                                            <button type="button" class="close" data-dismiss="alert"></button>
                                                            <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                                            <ul class = "alert-ul pl0">
                                                                <li>
                                                                    <?php echo $LANG['UI_VERIFY_GET_OBJECT_NO_DATA_TIPS'] ?>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-8 VMList-div">
                                            <!-- 已选择验证源 -->
                                            <div class="addTitle">
                                                    <span>
                                                        <i class="icon-action-redo"></i>
                                                        <?php echo $LANG['UI_VERIFY_SRC_SELECTED'] ?>
                                                    </span>

                                            </div>

                                            <div class="addIt-list">
                                                <div>
                                                    <div class="strategy-group appgroupDiv">
                                                        <div class="strategy-group__title" style="margin-bottom:8px;">
                                                            <span class="decoration me-4"></span>
                                                            <span class="strategy-group__title__text" style="color: #999;"><?php echo $LANG['UI_PLATFORM_LAB_APP_GROUP'] ?></span>
                                                        </div>
                                                        <ul class="feeds accordion display-hide" id="appgroupList" >
                                                        </ul>
                                                        <div class="alert alert-block alert-info fade in "  id="noAppTips">
                                                            <button type="button" class="close" data-dismiss="alert"></button>
                                                            <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                                            <ul class = "alert-ul pl0">
                                                                <li>
                                                                    <?php echo $LANG['UI_VERIFY_NO_APP_GROUP_SELECT_TIPS'] ?>
                                                                </li>
                                                            </ul>
                                                        </div>

                                                    </div>
                                                    <div class="strategy-group">
                                                        <div class="strategy-group__title" style="margin-bottom:8px;">
                                                            <span class="decoration me-4"></span>
                                                            <span class="strategy-group__title__text" style="color: #999;"><?php echo $LANG['UI_VERIFY_OBJECT'] ?></span>
                                                        </div>
                                                        <ul class="feeds accordion" id="objectList">
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div id="driverCheck_vm" style="position: relative; bottom: -12px;"></div>

                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane" id="tab3">
                                    <div class="row row-stepthree">
                                        <div class="tabbable-custom col-md-offset-1 col-md-10">
                                            <ul class="nav nav-tabs">
                                                <li class="commonDiv active">
                                                    <a href="#tab_common" data-toggle="tab">
                                                        <i class="viconfont vicon-celve1"></i> <?php echo $LANG['UI_VERIFY_COMMON_SETTING'] ?> </a>
                                                </li>
                                                <li class="highDiv">
                                                    <a href="#tab_high" data-toggle="tab">
                                                        <i class="viconfont vicon-gaojipeizhi"></i> <?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?> </a>
                                                </li>
                                            </ul>
                                            <div class="tab-content">
                                                <div class="tab-pane active" id="tab_common">
                                                    <div class="panel-body">
                                                        <div class="form-group">
                                                            <label class="control-label col-md-3"><?php echo $LANG['UI_VM_MACHINE_BOOT_MODE'] ?>
                                                            </label>
                                                            <div class="col-md-4">
                                                                <select class="form-control select2me" id="startType">
                                                                    <option value="2"><?php echo $LANG['UI_VERIFY_BY_TIME_STRATEGY'] ?></option>
                                                                    <option value="1"><?php echo $LANG['UI_VERIFY_IMMEDIATELY'] ?></option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group" id="setstrategy">
                                                            <label class="control-label col-md-3">
                                                                <span class="required">* </span>
                                                                <?php echo $LANG['UI_BACKUP_SET_STRATEGY'] ?>
                                                            </label>
                                                            <div class="col-md-9">
                                                                <div class="portlet">
                                                                    <div class="portlet-body">
                                                                        <div class="panel-group accordion" id="verifyTimestrategy">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane" id="tab_high">
                                                    <div class="panel-body">
                                                        <div class="col-md-9 ">
                                                            <?php include_once $_SESSION['ROOTPATH'] . 'content/platform/component/storage_mount.php'; ?>
                                                            <div class="form-group ">
                                                                <label class="control-label col-md-3 serveriplabel"><?php echo $LANG['UI_VERIFY_BACKUP_SERVER_IP'] ?>
                                                                </label>
                                                                <div class="col-md-6">
                                                                    <select class="form-control inline-block width160" id="serveripaddr">
                                                                    </select>
                                                                    <a class="ml10 popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VERIFY_BACKUP_SERVER_IP_TIPS'] ?>">
                                                                        <i class="viconfont vicon-tishi"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                            <div class="form-group threadNumDiv">
                                                                <label class="control-label col-md-3 vmnumlabel"><?php echo $LANG['UI_VERIFY_MANAGE_HOST_NUM_MEANWHILE'] ?></label>
                                                                <div class="col-md-9 ">

                                                                    <div class="width160 inline-block" id="hostNumDiv">
                                                                        <div class="input-group spinner-group" >
                                                                            <input type="text" value="1" id="vmnum" maxlength="3" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" >
                                                                            <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                <button type="button" class="btn spinner-up default input-sm">
                                                                                    <i class="fa fa-angle-up"></i>
                                                                                </button>
                                                                                <button type="button" class="btn spinner-down default input-sm">
                                                                                    <i class="fa fa-angle-down"></i>
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <a class="ml10 popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VERIFY_MANAGE_HOST_NUM_MEANWHILE_TIPS'] ?>">
                                                                        <i class="viconfont vicon-tishi"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                            <div class="form-group threadShowDiv display-hide">
                                                                <label class="control-label col-md-3 threadlabel"><?php echo $LANG['UI_VERIFY_FILE_THREAD_NUM'] ?></label>
                                                                <div class="col-md-9 ">

                                                                    <div class="width160 inline-block" id="threadDiv">
                                                                        <div class="input-group spinner-group" >
                                                                            <input type="text" id="fileThreadNum" maxlength="3" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" >
                                                                            <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                <button type="button" class="btn spinner-up default input-sm">
                                                                                    <i class="fa fa-angle-up"></i>
                                                                                </button>
                                                                                <button type="button" class="btn spinner-down default input-sm">
                                                                                    <i class="fa fa-angle-down"></i>
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <a class="ml10 popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VERIFY_FILE_THREAD_NUM_TIPS'] ?>">
                                                                        <i class="viconfont vicon-tishi"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                            <!-- 过载保护 -->
                                                            <!-- 忽略节点资源限制 -->
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3 ignoreResourceLimitLabel form-group-label"><?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE'] ?></label>
                                                                <div class="col-md-4 form-group-content">
                                                                    <input type="checkbox" id="ignore_resource_limit"  class="make-switch" data-on-color="primary" data-off-color="info" data-size="small"
                                                                           data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                    <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right"
                                                                       data-content="<?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE_TIPS'] ?>" >
                                                                        <i class="viconfont vicon-tishi"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                            <!-- 任务最大并发数 + 任务禁止运行时间段-->
                                                            <div class="form-group node-limit-form">
                                                                <div class="table-container col-md-offset-3 pl15">
                                                                    <table class="table table-hover table-borderless" id="nodeLimitTable">
                                                                    </table>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane" id="tab4">
                                    <div class="tab-pane__body">
                                        <div class="tab-pane__body__form">
                                            <div class="alert alert-danger display-none jobnametip"></div>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_JOB_RNAME'] ?></label>
                                                <div class="col-md-9">
                                                    <div class="input-icon right">
                                                        <i class="fa"></i>
                                                        <input type="text" maxlength="64" class="form-control" id="jobname" />
                                                        <span class="help-block "><?php echo $LANG['UI_JOB_RNAME_TIPS'] ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <h4 class="form-section"></h4>
                                            <div class="form-group mb0 mt10 <?php if ($CONF['SYSTEM_INFO']['vendor'] == $CONF['VENDOR_LIST']['gmp']) {
                                                echo "display-hide";
                                            } ?>">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_VERIFY_MODE'] ?></label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static  verifymodeshow">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mt10 <?php if ($CONF['SYSTEM_INFO']['vendor'] == $CONF['VENDOR_LIST']['gmp']) {
                                                echo "display-hide";
                                            } ?> verifyTypeDiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_VERIFY_TYPE'] ?></label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static  verifytypeshow">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mt10 <?php if ($CONF['SYSTEM_INFO']['vendor'] != $CONF['VENDOR_LIST']['gmp']) {
                                                echo "display-hide";
                                            } ?>">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_VERIFY_REPORT_TEMPLATE'] ?></label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static  templeteshow">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mt10 <?php if ($CONF['SYSTEM_INFO']['vendor'] != $CONF['VENDOR_LIST']['gmp']) {
                                                echo "display-hide";
                                            } ?>">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_VERIFY_APPROVAL'] ?></label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static  approvalshow">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mt10 <?php if ($CONF['SYSTEM_INFO']['vendor'] == $CONF['VENDOR_LIST']['gmp']) {
                                                echo "display-hide";
                                            } ?> labshowDiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_VIRTURL_LAB_NAME'] ?></label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static  virtuallabshow">
                                                    </p>
                                                </div>
                                            </div>
                                            <h4 class="form-section"></h4>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_VERIFY_SRC'] ?></label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static  srcinfoshow">
                                                    </p>
                                                </div>
                                            </div>
                                            <h4 class="form-section"></h4>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_STRATEGY_OR_TIME'] ?></label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static  timestrategyshow">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mt10 highshowDiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?></label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static  highshow">
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions form-actions--create">
                            <div class="row">
                                <div class="col-md-offset-6 col-md-6">
                                    <a href="javascript:;" class="btn default button-previous">
                                        <i class="viconfont vicon-shangyibu" style="margin-right: 2px;"></i> <?php echo $LANG['UI_PUBLIC_PREV_STEP'] ?> </a>
                                    <a href="javascript:;" class="btn green-turquoise button-next next-btn-margin-left" style="margin-left: 10px">
                                        <?php echo $LANG['UI_PUBLIC_NEXT_STEP'] ?> <i class="viconfont vicon-xiayibu" style="margin-left: 2px;"></i>
                                    </a>
                                    <a href="javascript:;" class="btn green-haze button-submit" style="margin-left: 10px">
                                        <?php echo $LANG['UI_PUBLIC_SUBMIT'] ?> <i class="viconfont vicon-xiayibu" style="margin-left: 2px;"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- BEGIN ADD DRAWER -->
    <div class="drawer slide" data-placement="right" tabindex="-1" role="dialog"
         aria-labelledby="drawer-1-title" aria-hidden="true" id="select_point_drawer" style="width: 800px;">
        <div class="drawer-content drawer-content-scrollable" role="document">
            <div class="drawer-header">
                <input id="object_uuid" type="text" class="display-hide"></input>
                <input id="src_task_uuid" type="text" class="display-hide"></input>
                <h4 class="drawer-title">
                    <span id="object_name">
                    </span>
                    <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i
                                class="viconfont vicon-guanbi"></i></span>
                </h4>
            </div>
            <div class="drawer-body">
                <div class="form-body form-horizontal">
                    <div class="form-group mt30">
                        <label class="control-label col-md-2 "><?php echo $LANG['UI_VERIFY_APP_GROUP_SELECT_POINT'] ?></label>
                        <div class="col-md-8">
                            <select class="form-control select2me width364 inline-block" id="pointType" >
                                <option value="2"><?php echo $LANG['UI_VERIFY_APP_GROUP_NEWEST_POINT'] ?></option>
                                <option value="1"><?php echo $LANG['UI_VERIFY_APP_GROUP_ALL_POINT'] ?></option>
                                <option value="3"><?php echo $LANG['UI_VERIFY_APP_GROUP_ASSIGN_POINT'] ?></option>
                            </select>
                            <a class="ml10 popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php if ($CONF['SYSTEM_INFO']['vendor'] == $CONF['VENDOR_LIST']['gmp']) {
                                echo $LANG['UI_VERIFY_SELECT_TIMEPOINT_TYPE_TIPS_GMP'];
                            } else {
                                echo $LANG['UI_VERIFY_SELECT_TIMEPOINT_TYPE_TIPS'];
                            } ?>">
                                <i class="viconfont vicon-tishi"></i>
                            </a>
                        </div>
                    </div>

                    <div class="form-group pointNumDiv display-hide">
                        <label class="control-label col-md-2 pointNumLabel"><?php echo $LANG['UI_VERIFY_OBJECT_MAX_TIMEPOINT_NUM'] ?></label>
                        <div class="col-md-8 ">

                            <div class="width160 inline-block" id="pointNumDiv">
                                <div class="input-group spinner-group" >
                                    <input type="text" value="1" id="pointnum" maxlength="2" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" >
                                    <div class="spinner-buttons input-group-btn spinner-group-btn">
                                        <button type="button" class="btn spinner-up default input-sm">
                                            <i class="fa fa-angle-up"></i>
                                        </button>
                                        <button type="button" class="btn spinner-down default input-sm">
                                            <i class="fa fa-angle-down"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <a class="ml10 popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VERIFY_OBJECT_MAX_TIMEPOINT_NUM_TIPS'] ?>">
                                <i class="viconfont vicon-tishi"></i>
                            </a>
                        </div>
                    </div>

                    <div class="form-group cdptimeDiv display-hide">
                        <label class="control-label col-md-2 "><?php echo $LANG['UI_VERIFY_APP_GROUP_SELECT_POINT'] ?></label>
                        <div class="col-md-8">
                            <select class="form-control select2me width364 inline-block" id="cdptimerange" >
                            </select>
                            <a class="ml10 popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_CM_CDP_BACKUP_SET_TIPS'] ?>">
                                <i class="viconfont vicon-tishi"></i>
                            </a>
                        </div>
                    </div>

                    <div class="form-group cdptimeDiv display-hide">
                        <label class="control-label  col-md-2"><?php echo $LANG['UI_PLATFORM_CHOOSE_POINT'] ?></label>
                        <div class="col-md-4 form-group-timepoint ">
                            <div class="input-group date backupsettimepointview" >
                                <input type="text" style="display:none;">
                                <input type="text" size="16" id="" class="form-control input-sm selecttimepoint">
                                <span class="input-group-btn">
                                    <!-- <button class="btn default input-sm-vol-cdp" id="resetRecoveryTimepoint" type="button"><i class="fa fa-times"></i></button> -->
                                    <button class="btn default date-set input-sm-vol-cdp" type="button"><i class="viconfont vicon-ge_calendar"></i></button>
                                </span>
                            </div>
                        </div>
                        <div id="timePointValidity" class="control-label col-md-1" style="padding:10px 0;"><?php echo $LANG['UI_DB_CDP_BACKUP_VALID_TIME_POINT_TYPE']; ?></div>
                    </div>


                    <div class="form-group pointTableDiv display-hide">
                        <div class="table-container">
                            <div class="vin_toolbar" id="vin_point_toolbar">
                                <div class="leftTool">
                                </div>

                                <div class="rightTool">
                                    <div class="vin_btnToolbar">
                                    </div>
                                </div>
                            </div>
                            <table id="point_table">
                            </table>
                        </div>

                    </div>
                </div>


            </div>
            <!-- footer -->
            <div class="drawer-footer">
                <div class="drawer-footer-div" style="float: right;">
                    <a href="javascript:;" class="btn default mr10" data-dismiss="drawer" aria-label="Close" >
                        <?php echo $LANG['UI_PUBLIC_NO'] ?>
                    </a>
                    <a href="javascript:;" class="btn green-haze mr10" id="select_point_submit">
                        <?php echo $LANG['UI_PUBLIC_YES'] ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- END ADD DRAWER -->
    <!-- BEGIN SET IPADDR MODAL -->
    <div id="setAddrModal" class="modal xmodal fade form-horizontal high-search" tabindex="-1"
         data-focus-on="input:first" data-backdrop="static">
        <div class="modal-header ">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            <h4 class="modal-title"><i class="viconfont vicon-gaojipeizhi"></i> <?php echo $LANG['UI_VERIFY_SET_VISIT_IP'] ?>
            </h4>
        </div>
        <div class="modal-body">
            <div class="portlet-body">
                <div class="form-body form-horizontal">
                    <div class="form-group mt30">
                        <label class="control-label col-md-2 "><?php echo $LANG['UI_VERIFY_LIMIT_MODE'] ?></label>
                        <div class="col-md-8">
                            <select class="bootstrap-mutiple-select select2me selectpicker show-tick ignore" id="" data-live-search="true" >
                                <option value="1"><?php echo $LANG['UI_VERIFY_LIMIT_MODE_NOT_LIMIT'] ?></option>
                                <option value="2"><?php echo $LANG['UI_VERIFY_LIMIT_MODE_CUSTOM'] ?></option>
                            </select>
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
                    id="task_serach_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
        </div>
    </div>
    <!-- END SEARCH MODAL -->
    <!-- BEGIN SHOW LAB DETAIL DRAWER -->
    <div class="drawer slide aws-drawer-width_en" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true" id="show_labdetail_drawer" style="width:600px;">
        <div class="drawer-content drawer-content-scrollable" role="document">
            <div class="drawer-header">
                <div class="drawer-title" id="nas_more_detail_drawer_title"  style="display:flex;justify-content:space-between;align-items:center">
                    <div class="drawer-title-left">
                        <i class="viconfont vicon-tenant-detail"></i>
                        <span><?php echo $LANG['UI_VIRTUAL_LAB_DETAILS'] ?></span>
                    </div>
                    <div class="drawer-title-right drawer-close" data-dismiss="drawer" aria-label="Close">
                        <i class="viconfont vicon-guanbi"></i>
                    </div>
                </div>
            </div>
            <div class="drawer-body config-detail-wrap config-detail-drawer-backup">
                <!-- 基本信息 -->
                <div class="strategy-group mt20">

                    <!-- BEGIN BASIC INFO -->
                    <div class="strategy-group__title">
                        <span class="decoration me-4"></span><span
                                class="strategy-group__title__text"><?php echo $LANG['UI_JOB_BASE_INFO'] ?></span>
                    </div>
                    <div class="strategy-group__form basicDiv">
                        <div class="strategy-group__form__item align-items-baseline">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_VIRTUAL_LAB_NAME'] ?>
                            </div>
                            <div class="strategy-group__form__item__value col-md-8" id="labName">
                            </div>
                        </div>
                        <div class="strategy-group__form__item align-items-baseline">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_PUBLIC_STATUS'] ?>
                            </div>
                            <div class="strategy-group__form__item__value col-md-8" id="labStatus">
                            </div>
                        </div>
                        <div class="strategy-group__form__item align-items-baseline backup_style">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_VERIFY_PROXY'] ?>
                            </div>
                            <div class="strategy-group__form__item__value col-md-8" id="proxy">
                            </div>
                        </div>
                        <div class="strategy-group__form__item align-items-baseline backup_style">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_PUBLIC_IP_ADDRESS'] ?>
                            </div>
                            <div class="strategy-group__form__item__value col-md-8" id="labIpaddr">
                            </div>
                        </div>
                        <div class="strategy-group__form__item align-items-baseline backup_style">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_SETTINGS_NETMASK'] ?>
                            </div>
                            <div class="strategy-group__form__item__value col-md-8" id="labNetmask">
                            </div>
                        </div>
                        <div class="strategy-group__form__item align-items-baseline backup_style">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_SETTINGS_GATEWAY'] ?>
                            </div>
                            <div class="strategy-group__form__item__value col-md-8" id="labGateway">
                            </div>
                        </div>
                        <div class="strategy-group__form__item align-items-baseline backup_style">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_VIRTUAL_LAB_PRODUCT_NETWORK'] ?>
                            </div>
                            <div class="strategy-group__form__item__value col-md-8" id="labNetwork">
                            </div>
                        </div>
                        <div class="strategy-group__form__item align-items-baseline backup_style">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_VIRTUAL_LAB_TARGET_STORAGE'] ?>
                            </div>
                            <div class="strategy-group__form__item__value col-md-8" id="labStorage">
                            </div>
                        </div>
                        <div class="strategy-group__form__item align-items-baseline backup_style">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_VCENTER_VC'] ?>
                            </div>
                            <div class="strategy-group__form__item__value col-md-8" id="vcenterName">
                            </div>
                        </div>
                        <div class="strategy-group__form__item align-items-baseline backup_style">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_VCENTER_HOST'] ?>
                            </div>
                            <div class="strategy-group__form__item__value col-md-8" id="hostName">
                            </div>
                        </div>

                    </div>
                    <!-- END BASIC INFO -->
                </div>
                <!-- BEGIN NETWORK INFO-->
                <div class="strategy-group tableDiv">

                    <!-- BEGIN VERIFICATION GROUP -->
                    <div class="">
                        <div class="strategy-group__title">
                            <span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_VIRTUAL_LAB_NETWORK_INFO'] ?></span>
                        </div>
                        <div class="strategy-group__form">
                            <!-- 生产网络 -->
                            <div class="mb15">
                                <table id="product_network_table">
                                </table>
                            </div>
                            <!-- 隔离网络 -->
                            <div class="mb15">
                                <table id="isolated_network_table">
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- END NETWORK INFO-->
                </div>
            </div>
            <div class="drawer-footer">
                <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-primary" id="addsubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
            </div>
        </div>
    </div>
    <!-- END SHOW LAB DETAIL DRAWER -->
    <?php
    include_once $_SESSION['ROOTPATH'] . 'content/platform/industry/template_choose.php';
    ?>

</div>
<!-- END PAGE CONTENT-->


<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/additional-methods.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>

<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script src="./assets/global/plugins/fuelux/js/spinner.min.js" type="text/javascript"></script>
<script type="text/javascript" src="./assets/global/plugins/tree-grid/jquery.treegrid.js"></script>
<script type="text/javascript" src="./assets/global/plugins/tree-grid/bootstrap-table-treegrid.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/plugins/popModal/popModal.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.strategy.js"></script>
<script type="text/javascript" src="./scripts/platform/component/driver_check.js"></script>
<script type="text/javascript" src="./scripts/components/filter/js/filter.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_virus_detection.js"></script>
<script type="text/javascript" src="./scripts/plugins/path-tree-selector.js"></script>

<script src="./scripts/platform/dataverification/add_verification_job.js" type="text/javascript"></script>