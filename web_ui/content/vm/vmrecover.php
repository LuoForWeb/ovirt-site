<?php include_once '../../tpl/permission.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>

<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./scripts/plugins/calendar/jquery.searchableSelect.css"/>
<link rel="stylesheet" type="text/css" href="./css/platform/component/safe_virus.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css"/>
<link rel="stylesheet" type="text/css" href="./css/platform/component/network_config.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/component/driver_check.css" />
<link rel="stylesheet" type="text/css" href="./scripts/components/timePointDetail/css/timePointDetail.css"/>
<!-- END PAGE LEVEL STYLES -->
<style>
    #accordionvm .dropdown-menu.inner {
        max-height: 150px!important;
        overflow-x: hidden;
    }
</style>
<?php session_start();
session_commit(); ?>
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="recovery" href="./content/recovery/recoverycenter.php">
            <span><?php echo $LANG['WEB_PLATFORM_DES_RECOVERY'] ?></span>
        </a>
    </li>
    <span>
        >
    </span>
    <span class="curent"><?php echo $_GET['sub_module_type'] == 2 ? $LANG['UI_RECOVERY_FOR_PRIVATE_CLOUD'] : $LANG['UI_RECOVERY_FOR_VIRTURE'] ?></span>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row recover-page" id="vm-recover">
    <div class="col-md-12" style="height:100%;">
        <input type="hidden" id="subModuleType" value="<?php echo $_GET['sub_module_type'] ?? 1 ?>">
        <input id="externalPointUuid" value="<?php echo $_GET['point_uuid']; ?>" class="display-none"></input>
        <input id="externalTaskUuid" value="<?php echo $_GET['task_uuid']; ?>" class="display-none"></input>
        <input id="externalItemUuid" value="<?php echo $_GET['item_uuid']; ?>" class="display-none"></input>
        <input id="externalSubType" value="<?php echo $_GET['sub_type']; ?>" class="display-none"></input>
        <div class="portlet box blue-hoki" id="vmrecovercontent">
            <div class="portlet-title">
                <div class="caption">
                    <i class="levelchild viconfont vicon-huifu1"></i><?php echo $_GET['sub_module_type'] == 2 ? $LANG['UI_BACKUP_NEW_PRIVATE_CLOUD_RECOVERY_JOB'] : $LANG['UI_BACKUP_NEW_VM_RECOVERY_JOB'] ?>
                </div>
            </div>
            <div class="portlet-body form">
                <form action="#" class="form-horizontal" id="submit_form" method="POST">
                    <div class="form-wizard">
                        <div class="form-body">
                            <ul class="nav nav-pills steps" >
                                <li>
                                    <a href="#tab1" data-toggle="tab" class="step">
                                        <span class="number">1 </span>
                                        <span class="desc">
                                            <?php echo $LANG['UI_RECOVERY_DATA_SOURCE'] ?>
                                            <i class="fa fa-check"></i>
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#tab2" data-toggle="tab" class="step">
                                        <span class="number">2 </span>
                                        <span class="desc">
                                            <?php echo $LANG['UI_RECOVERY_GOAL'] ?>
                                            <i class="fa fa-check"></i>
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#tab3" data-toggle="tab" class="step active">
                                        <span class="number">3 </span>
                                        <span class="desc">
                                            <?php echo $LANG['UI_RECOVERY_VM_TYPE'] ?>
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
                                <!-- 选择备份点 -->
                                <div class="tab-pane active" id="tab1">
                                    <div class="alert alert-danger display-none selecttimepointtip">
                                    </div>
                                    <div class="row tab-pane__row">
                                        <div class="col-md-4 tab-pane__row__source">
                                            <div class="form-group src-wrap">
                                                <div class="src-wrap__title">
                                                    <span><?php echo $LANG['UI_PUBLIC_SELECT'] ?><?php echo $LANG['UI_RECOVERY_BACKUP_POINT_LOWERCASE'] ?></span>
                                                </div>
                                                <div class="src-wrap__content">
                                                    <div class="src-wrap__content__search">
                                                        <select class="bs-select width100p form-control" style="height: 34px" data-show-subtext="true" id="storagetypeselect">
                                                        </select>
                                                        <div class="vm_tree_div">
                                                            <select class="bs-select width45p display-hide" data-show-subtext="true" id="c">
                                                                <option data-icon="timevmtype icon-default" value="1"> <?php echo $LANG['UI_RECOVERY_BACKUP_POINT_VM_GROUP'] ?></option>
                                                                <option data-icon="timepointtype icon-default" value="2"> <?php echo $LANG['UI_RECOVERY_BACKUP_POINT_TIME_GROUP'] ?></option>
                                                            </select>
                                                            <div class="input-icon width100p">
                                                                <input type="text" maxlength="128" placeholder="<?php echo $_GET['sub_module_type'] == 2 ? $LANG['UI_BACKUP_SEARCH_AS_INSTANCE_OR_TASK'] : $LANG['UI_BACKUP_SEARCH_AS_VM_OR_TASK'] ?>" class="form-control" id="searchvm" autocomplete="off" onkeyup="customInputValidate('string', this.value, $(this))" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="src-wrap__content__ztree" style="height: calc(100% - 92px)">
                                                        <div class="two_tree vcenter-tree">
                                                            <ul id="storagetypetree" class="ztree bd1de5 tree_div ztree-fa"></ul>
                                                            <ul id="vmtypetree" class="ztree bd1de5  tree_div display-hide"></ul>
                                                        </div>
                                                        <div class="alert alert-block alert-info fade in display-hide" id="nopointtips">
                                                            <button type="button" class="close" data-dismiss="alert"></button>
                                                            <ul class="alert-ul">
                                                                <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                                                                <li>
                                                                    <strong><?php echo $LANG['UI_RECOVERY_NO_VM_TITLE'] ?></strong>
                                                                    <a id="tobackup"><small><?php echo $_GET['sub_module_type'] == 2 ? $LANG['UI_RECOVERY_PRIVATE_CLOUD_NO_VM_TIPS'] : $LANG['UI_RECOVERY_NO_VM_TIPS'] ?></small></a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                        <div class="alert alert-block alert-info fade in display-hide" id="nosearchtips">
                                                            <button type="button" class="close" data-dismiss="alert"></button>
                                                            <ul class="alert-ul">
                                                                <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                                                                <li>
                                                                    <?php echo $LANG['UI_DATA_NOSEARCH_TIPS'] ?>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-8 VMList-div">
                                            <div class="addTitle">
                                                <span><?php echo $LANG['UI_RECOVERY_ALREADY_SELECT_POINT'] ?></span>
                                            </div>
                                            <div class="addIt-list">
                                                <ul class="feeds addVMList" id="VMGroupList">
                                                </ul>
                                                <ul class="feeds addVMList" id="cbrTimeGroupList">
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 恢复目标 -->
                                <div class="tab-pane" id="tab2">
                                    <div class="row tab-pane__row">
                                        <div class="col-md-12 col-steptwo">
                                            <div class="alert alert-danger display-none setrecover2tip">
                                            </div>
                                            <!-- 恢复方式：新建虚拟机/指定虚拟机 -->
                                            <div class="form-group display-none" id="recovery_way">
                                                <label class="control-label col-md-3 mt-0 ptlb20">
                                                    <span class="required">* </span>
                                                    <?php echo $LANG['UI_RECOVERY_SELECT_WAY']?>
                                                </label>
                                                <div class="radio-group col-md-6" id="recovery_way_radio_group">
                                                    <label class="radio-group__item me-20" style="width: 152px!important;" value="1">
                                                        <?php echo $_GET['sub_module_type'] == 2 ? $LANG['UI_RECOVERY_WAY_NEW_INSTANCE'] : $LANG['UI_RECOVERY_WAY_NEW_VM']?>
                                                    </label>
                                                    <label class="radio-group__item me-20" style="width: 152px!important;" value="2">
                                                        <?php echo $_GET['sub_module_type'] == 2 ? $LANG['UI_RECOVERY_WAY_SPECIFIC_INSTANCE'] : $LANG['UI_RECOVERY_WAY_SPECIFIC_VM']?>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group host_tree_div display-none" id="selectHost">
                                                <label class="control-label col-md-3">
                                                    <span class="required">* </span>
                                                    <?php echo $_GET['sub_module_type'] == 2 ? $LANG['UI_RECOVERY_SELECT_PROJECT'] : $LANG['UI_RECOVERY_SELECT_HOST'] ?>
                                                </label>
                                                <div class="col-md-6 tree_div2" >
                                                    <ul id="host_tree" class="ztree bd1de5"></ul>
                                                    <div><span class="help-block ">
                                                        <?php echo $_GET['sub_module_type'] == 2 ? $LANG['UI_RECOVERY_INSTANCE_SELECT_PROJECT'] : $LANG['UI_RECOVERY_VM_SELECT_HOST'] ?>
                                                </span></div>
                                                </div>
                                            </div>
                                            <div class="form-group display-hide" id="nohosttips">
                                                <label class="control-label col-md-3">
                                                    <span class="required">* </span>
                                                    <?php echo $LANG['UI_RECOVERY_SELECT_HOST'] ?>
                                                </label>
                                                <div class="col-md-6 alert alert-block alert-info fade in" >
                                                    <button type="button" class="close" data-dismiss="alert"></button>
                                                    <ul class="alert-ul">
                                                        <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                                                        <li>
                                                            <strong><?php echo $LANG['UI_RECOVERY_NO_HOST'] ?></strong>
                                                            <?php
                                                            //if(empty($_SESSION['tenantuuid'])){
                                                            $lang = $_GET['sub_module_type'] == 2 ? $LANG['UI_RECOVERY_ADD_CLOUD_PLATFORM_TIPS'] : $LANG['UI_RECOVERY_ADD_VCENTER_TIPS'];
                                                            echo '<p><a id="toaddvcenter"><small>' . $lang . '</small></a></p>';
                                                            //}
                                                            ?>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="form-group usergroup_tree_div display-hide" id="selectUsergroup">
                                                <label class="control-label col-md-3">
                                                    <span class="required">* </span>
                                                    <?php echo $LANG['UI_RECOVERY_SELECT_USER_GROUP'] ?>
                                                </label>
                                                <div class="col-md-6 tree_div2">
                                                    <ul id="usergroup_tree" class="ztree bd1de5"></ul>
                                                    <div><span class="help-block ">
                                                        <?php echo $LANG['UI_RECOVERY_SELECT_USER_GROUP_TIPS'] ?>
                                                </span></div>
                                                </div>
                                            </div>

                                            <div class="form-group display-none groupdiv">
                                                <label class="control-label col-md-3">
                                                    <span class="required">* </span>
                                                    <?php echo $LANG['UI_RECOVERY_INPUT_USER_NMAE'] ?>
                                                </label>
                                                <div class="col-md-3">
                                                    <div class="input-icon right ">
                                                        <i class="fa"></i>
                                                        <select class="form-control" id="groupusername"></select>
                                                        <div><span class="help-block "><?php echo $LANG['UI_RECOVERY_SELECT_GROUP_USER_NAME'] ?></span></div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="input-icon right ">
                                                        <i class="fa"></i>
                                                        <input type="password" autocomplete="off" maxlength="128" class="form-control" id="grouppassword" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')"/>
                                                        <div><span class="help-block "><?php echo $LANG['UI_RECOVERY_INPUT_PASSWORD'] ?></span></div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="btn-group" style="margin-top: 6px;">
                                                        <button type="button" id="verifyuser" class="btn btn-sm green-haze">
                                                            <?php echo $LANG['UI_RECOVERY_VERIFY_USER'] ?></button>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- 华为云Stack可用域 -->
                                            <div class="form-group display-none domainDiv">
                                                <label class="control-label col-md-3 form-group-label">
                                                    <span class="required">* </span>
                                                    <?php echo $LANG['UI_RECOVERY_AVAILABLE_DOMAIN'] ?>
                                                </label>
                                                <div class="col-md-3">
                                                    <select class="form-control" id="available_domain"></select>
                                                </div>
                                            </div>

                                            <div class="form-group dndiv" id="unifysettingdiv">
                                                <label class="control-label col-md-3 form-group-label">
                                                    <span class="required">* </span>
                                                    <?php echo $_GET['sub_module_type'] == 2 ? $LANG['UI_RECOVERY_VM_MORE_INSTANCE_SETTING'] : $LANG['UI_RECOVERY_VM_MORE_VM_SETTING'] ?>
                                                </label>
                                                <div class="col-md-3 form-group-content">
                                                    <input type="checkbox" id="vmssetting"   class="make-switch" data-size="small" data-on-color="primary" data-off-color="info"
                                                           data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">

                                                    <a class="popovers ml15" data-container="body" data-trigger="hover"
                                                       data-placement="right" data-content="<?php echo $_GET['sub_module_type'] == 2 ? $LANG['UI_RECOVERY_VM_MORE_INSTANCE_SETTING_TIPS'] : $LANG['UI_RECOVERY_VM_MORE_VM_SETTING_TIPS'] ?>">
                                                        <i class="viconfont vicon-tishi"></i>
                                                    </a>
                                                </div>
                                                <div class="col-md-12 mt10 display-hide vmssettingdiv">
                                                    <div class="form-group">
                                                        <label class="control-label col-md-3">
                                                            <span class="required">* </span>
                                                            <?php echo $LANG['UI_RECOVERY_SELECT_STORAGE'] ?>
                                                        </label>
                                                        <div class="col-md-6">
                                                            <select class="form-control select2me selectpicker show-tick ignore" name="hoststorage" id="vmsstorage" data-live-search="true">
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="control-label col-md-3">
                                                            <span class="required" >* </span>
                                                            <?php echo $LANG['UI_RECOVERY_VM_SELECT_NETWORK'] ?>
                                                        </label>
                                                        <div class="col-md-6">
                                                            <select class="form-control select2me selectpicker show-tick ignore" name="hostnetwork" id="vmsnetwork" data-live-search="true">
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group display-none" id="usedomainDiv">
                                                        <label class="control-label col-md-3">
                                                            <span class="required" >* </span>
                                                            <?php echo $LANG['UI_PUBLIC_USE_DOMAIN'] ?>
                                                        </label>
                                                        <div class="col-md-6">
                                                            <select class="form-control" name="usedomain" id="vmUsedomain">
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="control-label col-md-3 form-group-label">
                                                            <span class="required">* </span>
                                                            <?php echo $LANG['UI_INSTANT_VM_POWER'] ?>
                                                        </label>
                                                        <div class="col-md-6 form-group-content">
                                                            <input type="checkbox" id="powercheck" data-size="small" class="make-switch" data-on-color="primary" data-off-color="info"
                                                                   data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                   data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                            <div><span class="margintop-15"><?php echo $LANG['UI_RECOVERY_POWER_VM_TIPS'] ?></span></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 mt10 display-hide applianceDiv">
                                                    <div class="form-group">
                                                        <label class="control-label col-md-3">
                                                            <span class="required">* </span>
                                                            <?php echo $LANG['UI_RECOVERY_V2V_AGENT'] ?>
                                                        </label>
                                                        <div class="col-md-6">
                                                            <select class="form-control select2me" id="v2vAppliance">
                                                            </select>
                                                            <div><span class="help-block "><?php echo $LANG['UI_RECOVERY_SELECT_TRANSFER_AGENT'] ?></span></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- 计算节点 -->
                                            <div class="form-group dndiv" id="nodeconfigs">
                                                <label class="control-label col-md-3 diynodelabel"><?php echo $LANG['UI_BACKUP_AWS_COMPUTE_NODE'] ?></label>
                                                <div class="col-md-9">
                                                    <select class="form-control select2me" id="nodeconfig">
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group dndiv" id="vmconfigs">
                                                <label class="control-label col-md-3">
                                                    <span class="required">* </span>
                                                    <?php echo $_GET['sub_module_type'] == 2 ? $LANG['UI_RECOVERY_INSTANCE_SETTING'] : $LANG['UI_RECOVERY_VM_SETTING'] ?>
                                                </label>
                                                <div class="col-md-9" style="padding-right: 20px;">
                                                    <div class="portlet">
                                                        <div class="portlet-body">
                                                            <div class="panel-group accordion" id="accordionvm">
                                                                <?php include_once('../../content/platform/component/vm_recovery.php'); ?>
                                                            </div>
                                                        </div>
                                                        <div class="margintop-15">
                                                            <span class="help-block "><?php echo $_GET['sub_module_type'] == 2 ? $LANG['UI_RECOVERY_INSTANCE_SETTING_TIPS'] : $LANG['UI_RECOVERY_VM_SETTING_TIPS'] ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php include_once('../../content/platform/component/vm_recovery_target.php'); ?>
                                            <!-- 驱动检测 -->
                                            <div class="form-group">
                                                <div id="driverCheck_vm_button" class="display-none">
                                                    <div class="form-group ">
                                                        <label class="col-md-3 text-align-reverse pt7 driver-config__label">
                                                            <?php echo $LANG['WEB_TOOL_DR_OP_CHECK_DIFF_PLAT_AND_DRIVER_EXIST']; ?>
                                                        </label>
                                                        <div class="col-md-6 driver-config__content">
                                                            <button class="btn btn-light-primary" type="button" id="driverCheck_vm_button_checkDriver"><?php echo $LANG['WEB_PLATFORM_DES_CHECK']; ?></button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="driverCheck"></div>
                                                <div id="driverCheckFailContinue" class="display-none">
                                                    <div class="form-group ">
                                                        <label class="col-md-3">
                                                        </label>
                                                        <div class="col-md-6 driver-config__content">
                                                            <label style="float:left;line-height:32px;margin-right:12px;"><?php echo $LANG['UI_PLATFORM_DRIVER_TIPS'] ?>：</label>
                                                            <div class="input-group " id="continue_driver_fail" style="margin-top: 4px;float: left;">
                                                                <label style="padding-right: 20px;">
                                                                    <input type="checkbox" id="continueCheck" data-checkbox="icheckbox_square-blue" data-mode="2" class="icheck">
                                                                    <?php echo $LANG['WEB_PLATFORM_PUBLIC_YES'] ?>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 恢复方式 -->
                                <div class="tab-pane" id="tab3">
                                    <div class="row row-stepthree">
                                        <div class="tabbable-custom col-md-offset-1 col-md-10">
                                            <ul class="nav nav-tabs">
                                                <li class="commonLi active">
                                                    <a href="#tab_common" data-toggle="tab">
                                                        <i class="viconfont vicon-celve1"></i> <?php echo $LANG['UI_BACKUP_COMMON_STRATEGY'] ?> </a>
                                                </li>
                                                <li class="transferLi">
                                                    <a href="#tab_transfer" data-toggle="tab">
                                                        <i class="viconfont vicon-a-chuanshu"></i> <?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?> </a>
                                                </li>
                                                <li class="safeLi">
                                                    <a href="#tab_safe" data-toggle="tab">
                                                        <i class="viconfont vicon-anquancelve"></i> <?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?> </a>
                                                </li>
                                                <li class="highLi">
                                                    <a href="#tab_other" data-toggle="tab">
                                                        <i class="viconfont vicon-gaojipeizhi"></i> <?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?> </a>
                                                </li>
                                            </ul>
                                            <div class="tab-content">
                                                <!-- 通用策略 -->
                                                <div class="tab-pane active" id="tab_common">
                                                    <div class="panel-body">
                                                        <div class="form-group display-none" >
                                                            <div class="control-label col-md-2" ><?php echo $LANG['UI_BACKUP_SELECT_STRATEGY'] ?></div>
                                                            <div class="col-md-4">
                                                                <select class="form-control select2me inline-block" id="strategySelect">
                                                                </select>
                                                                <a class="popovers ml15" data-container="body" data-trigger="hover"
                                                                   data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_SELECT_STRATEGY_TIPS'] ?>">
                                                                    <i class="viconfont vicon-tishi"></i>
                                                                </a>
                                                            </div>
                                                            <div class="col-md-1 mt10">
                                                            </div>
                                                        </div>

                                                        <div class="form-group">
                                                            <div class="col-md-offset-2  col-md-9 pt15 recoveryTimeDiv">
                                                                <div class="accordion strategyTwo" >
                                                                    <div class="panel panel-default strategy-panel">
                                                                        <div class="panel-heading">
                                                                            <h4 class="panel-title">
                                                                                <a class="accordion-toggle accordion-toggle-styled collapsed popovers"
                                                                                   data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyTwo" href="#recoveryTime" aria-expanded="true">
                                                                                    <i class="iconfont icon-time font-green-seagreen"></i>
                                                                                    <span class="font-green-seagreen"><?php echo $LANG['UI_STRATEGY_TIME'] ?></span>
                                                                                    <span class="strategyDes recoveryTimeDes"></span>
                                                                                </a>
                                                                            </h4>
                                                                        </div>
                                                                        <div id="recoveryTime" class="panel-collapse collapse in">
                                                                            <div class="panel-body">
                                                                                <div class="col-md-12">
                                                                                    <div class="form-group">
                                                                                        <label class="control-label col-md-2"><?php echo $LANG['UI_RECOVERY_TYPE'] ?>
                                                                                        </label>
                                                                                        <div class="col-md-4">
                                                                                            <select class="form-control select2me" id="recovertype">
                                                                                                <option value="1"><?php echo $LANG['UI_RECOVERY_TYPE_NOW'] ?></option>
                                                                                                <option value="4"><?php echo $LANG['UI_RECOVERY_TYPE_TIMING'] ?></option>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="form-group display-hide setOnceTime">
                                                                                        <label class="control-label col-md-2">
                                                                                            <span class="required">* </span>
                                                                                            <?php echo $LANG['UI_BACKUP_SET_TIME'] ?>
                                                                                        </label>
                                                                                        <div class="col-md-10">
                                                                                            <div class="input-group date form_datetime">
                                                                                                <input type="text" size="16" readonly id="oncetime" class="form-control input-sm">
                                                                                                <span class="input-group-btn">
                                                                                                <button class="btn default" id="resetdate" type="button"><i class="fa fa-times"></i></button>
                                                                                                <button class="btn default date-set" type="button"><i class="viconfont vicon-ge_calendar"></i></button>
                                                                                                </span>
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
                                                        <div class="form-group speedlimitDiv">
                                                            <div class="col-md-offset-2 col-md-9 accordion strategyTwo" >
                                                                <div class="panel panel-default strategy-panel">
                                                                    <div class="panel-heading">
                                                                        <h4 class="panel-title">
                                                                            <a class="accordion-toggle accordion-toggle-styled collapsed popovers"
                                                                               data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyTwo" href="#speed" aria-expanded="true">
                                                                                <i class="iconfont icon-xiansucelve font-green-seagreen"></i>
                                                                                <span class="font-green-seagreen"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?></span>
                                                                                <span class="strategyDes speedlimitDes"></span>
                                                                            </a>
                                                                        </h4>
                                                                    </div>
                                                                    <!--<div id="speed" class="panel-collapse collapse ">
                                                                        <div class="panel-body">
                                                                            <div class="col-md-12">
                                                                                <button type="button" id="addSpeedlimit" class="btn btn-sm green-haze">
                                                                                    <i class="viconfont vicon-ge_add_task"></i><?php /*echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_ADD']*/ ?>
                                                                                </button>
                                                                                <ul id="speedList" style="padding-left: 0;">
                                                        
                                                                                </ul>
                                                                            </div>
                                                                        </div>
                                                                    </div>-->
                                                                    <?php include_once('../../content/platform/global_strategy/speedJob.php'); ?>
                                                                    <!--       全局限速策略组合的组件配置-->
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group highDiv display-none">
                                                            <div class="col-md-offset-2  col-md-9 accordion strategyTwo" >
                                                                <div class="panel panel-default strategy-panel">
                                                                    <div class="panel-heading">
                                                                        <h4 class="panel-title">
                                                                            <a class="accordion-toggle accordion-toggle-styled collapsed popovers"
                                                                               data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyTwo" href="#recoveryHigh" aria-expanded="true">
                                                                                <i class="iconfont icon-gaojicelve font-green-seagreen"></i>
                                                                                <span class="font-green-seagreen"><?php echo $LANG['UI_GLOBAL_STRATEGY_HIGH'] ?></span>
                                                                                <span class="strategyDes recoveryHighDes"></span>
                                                                            </a>
                                                                        </h4>
                                                                    </div>
                                                                    <div id="recoveryHigh" class="panel-collapse collapse ">
                                                                        <div class="panel-body">
                                                                            <div class="col-md-12">
                                                                                <!-- 存储高负载时停止任务 -->
                                                                                <div class="form-group display-none storageHighLoadDiv">
                                                                                    <label class="control-label col-md-4 storageHighLoadLabel"><?php echo $LANG['UI_RECOVERY_STORAGE_HIGH_LOAD_STOP_TASK'] ?>
                                                                                    </label>
                                                                                    <div class="col-md-4 form-group-content">
                                                                                        <input type="checkbox" id="storageHighLoad"   class="make-switch" data-on-color="primary" data-off-color="info" data-size="small"
                                                                                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- 新增校验策略模块 -->
                                                        <div class="form-group display-none">
                                                            <div class="col-md-offset-2 col-md-9 accordion strategyOne" >
                                                                <div class="panel panel-default strategy-panel">
                                                                    <div class="panel-heading">
                                                                        <h4 class="panel-title">
                                                                            <a class="accordion-toggle accordion-toggle-styled collapsed popovers"
                                                                               data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#backupVerify" aria-expanded="true">
                                                                                <i class="viconfont vicon-a-Check-onexiaoyan font-green-seagreen"></i>
                                                                                <span class="font-green-seagreen"><?php echo $LANG['UI_CBR_SYNC_VERIFY_STRATEGY'] ?></span>
                                                                                <span class="strategyDes verifyDes"></span>
                                                                            </a>
                                                                        </h4>
                                                                    </div>
                                                                    <div id="backupVerify" class="panel-collapse collapse ">
                                                                        <div class="panel-body">
                                                                            <div class="col-md-12">
                                                                                <div class="form-group">
                                                                                    <label class="control-label col-md-4 verifyLable form-group-label"><?php echo $LANG['UI_CBR_SYNC_INTEGRALITY_CHECK'] ?></label>
                                                                                    <div class="col-md-4 form-group-content">
                                                                                        <input type="checkbox" id="verifyflag"   class="make-switch" data-on-color="primary" data-off-color="info" data-size="small"
                                                                                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                        <div class="ml15" id="otherverifytips" style="display: inline-block">
                                                                                            <a class="popovers" data-container="body" data-trigger="hover" data-html="true"
                                                                                               data-placement="right" data-content="<?php echo $LANG['UI_CBR_SYNC_INTEGRALITY_CHECK_TIPS1'] ?>">
                                                                                                <i class="viconfont vicon-tishi"></i>
                                                                                            </a>
                                                                                        </div>
                                                                                        <div class="ml15 display-none" id="cbrverifytips" style="display: inline-block">
                                                                                            <a class="popovers" data-container="body" data-trigger="hover" data-html="true"
                                                                                               data-placement="right" data-content="<?php echo $LANG['UI_CBR_SYNC_INTEGRALITY_CHECK_TIPS2'] ?>">
                                                                                                <i class="viconfont vicon-tishi"></i>
                                                                                            </a>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group verifyalarmDiv display-none">
                                                                                    <label class="control-label col-md-4 verifyalarmLable form-group-label">
                                                                                        <?php echo $LANG['UI_CBR_SYNC_VERIFY_ALARM'] ?>
                                                                                    </label>
                                                                                    <div class="col-md-4 form-group-content">
                                                                                        <input type="checkbox" id="verifyalarmflag"   class="make-switch" data-on-color="primary" data-off-color="info" data-size="small"
                                                                                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                        <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true"
                                                                                           data-placement="right" data-content="<?php echo $LANG['UI_CBR_SYNC_VERIFY_ALARM_TIPS'] ?>">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
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

                                                <div class="tab-pane " id="tab_transfer">
                                                    <?php include_once('../../content/platform/component/vm_transfer_strategy.php'); ?>
                                                </div>

                                                <!-- 安全策略 -->
                                                <div class="tab-pane" id="tab_safe">
                                                    <div class="panel-body">
                                                        <!-- 病毒检测 插件 -->
                                                        <div id="virusConfig"></div>
                                                        <!-- 备份数据完整性校验 插件 -->
                                                        <div class="mt20" id="completeConfig"></div>
                                                    </div>
                                                </div>

                                                <!-- 高级配置 -->
                                                <div class="tab-pane " id="tab_other">
                                                    <div class="advance-config-wrap">
                                                        <div class="advance-config-wrap__row row m0" >
                                                            <div class="col-md-2 col-sm-2 col-xs-2 nav-tab-radios" style="align-items: flex-start;">
                                                                <ul class="nav nav-tabs tabs-left">
                                                                    <li class="active">
                                                                        <a href="#retry_config" data-toggle="tab" aria-expanded="false"><?php echo $LANG['UI_STRATEGY_RETRY'] ?></a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="#tab_overload_protect" data-toggle="tab" aria-expanded="false"><?php echo $LANG['UI_STRATEGY_OVER_LOAD_LABEL'] ?></a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            <div class="col-md-10 col-sm-10 col-xs-10 advance-config-wrap__row__content">
                                                                <div class="tab-content">
                                                                    <!-- 重试 -->
                                                                    <div id="retry_config" class="tab-pane retry_config_pane active">
                                                                    </div>
                                                                    <!-- 过载保护 -->
                                                                    <div class="tab-pane" id="tab_overload_protect">
                                                                        <div class="col-md-12 advanced-detail-conf-district pt40">
                                                                            <!-- 忽略节点资源限制 -->
                                                                            <div class="form-group">
                                                                                <label class="control-label col-md-3 ignoreResourceLimitLabel form-group-label"><?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE'] ?></label>
                                                                                <div class="col-md-2 form-group-content">
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
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <!-- 确认配置 -->
                                <div class="tab-pane" id="tab4">
                                    <div class="tab-pane__body">
                                        <div class="tab-pane__body__form">
                                            <div class="alert alert-danger display-none jobnametip"></div>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_JOB_RNAME'] ?>:</label>
                                                <div class="col-md-9">
                                                    <div class="input-icon right">
                                                        <input type="text" maxlength="64" class="form-control" id="jobname" onkeyup="customInputValidate('string', this.value, $(this))"/>
                                                        <span class="help-block "><?php echo $LANG['UI_JOB_RNAME_TIPS'] ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <h4 class="form-section"></h4>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_POINT_INFO'] ?>:</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static vmtypeshow">
                                                    </p>
                                                </div>
                                            </div>
                                            <h4 class="form-section"></h4>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_FILE_PATH'] ?>:</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static recovershow">
                                                    </p>
                                                </div>
                                            </div>
                                            <h4 class="form-section"></h4>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_TYPE'] ?>:</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static reservetypeshow">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mt10" id="transportmodeshowdiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?>:</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static transportinfoshow">
                                                    </p>
                                                    <br>
                                                    <p class="form-control-static applianceshow">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group safeDiv" id="safeShowDiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?>:</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static safeStrategyShow">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mt10" id="highstrategyshowdiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_GLOBAL_STRATEGY_HIGH'] ?>:</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static highstrategyshow">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mt10 retryShow">
                                            </div>
                                            <div class="form-group mb0 mt10" id="speedstrategyshowdiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?>:</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static speedlimitshow">
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <h4 class="form-section"></h4>
                                    <div class="form-group">
                                        <label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_FILE_PATH'] ?>:</label>
                                        <div class="col-md-9">
                                            <p class="form-control-static recovershow">
                                            </p>
                                        </div>
                                    </div>

                                    <h4 class="form-section"></h4>
                                    <div class="form-group">
                                        <label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_TYPE'] ?>:</label>
                                        <div class="col-md-4">
                                            <p class="form-control-static reservetypeshow">
                                            </p>
                                        </div>
                                    </div>
                                    <div class="form-group " id="transportmodeshowdiv">
                                        <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?>:</label>
                                        <div class="col-md-4">
                                            <p class="form-control-static transportinfoshow">
                                            </p>
                                            <p class="form-control-static applianceshow">
                                            </p>
                                        </div>
                                    </div>
                                    <div class="form-group safeDiv" id="safeShowDiv">
                                        <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?>:</label>
                                        <div class="col-md-4">
                                            <p class="form-control-static safeStrategyShow">
                                            </p>
                                        </div>
                                    </div>
                                    <div class="form-group " id="highstrategyshowdiv">
                                        <label class="control-label col-md-3"><?php echo $LANG['UI_GLOBAL_STRATEGY_HIGH'] ?>:</label>
                                        <div class="col-md-4">
                                            <p class="form-control-static highstrategyshow">
                                            </p>
                                        </div>
                                    </div>
                                    <div class="form-group" id="verifymodeshowdiv">
                                        <label class="control-label col-md-3"><?php echo $LANG['UI_CBR_SYNC_VERIFY_STRATEGY'] ?></label>
                                        <div class="col-md-6">
                                            <div class="form-control-static colorblack verifymodeshow">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group " id="speedstrategyshowdiv">
                                        <label class="control-label col-md-3"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?>:</label>
                                        <div class="col-md-4">
                                            <p class="form-control-static speedlimitshow">
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions form-actions--create">
                            <div class="row">
                                <div class="col-md-offset-6 col-md-6">
                                    <a href="javascript:;" class="btn default button-previous">
                                        <i class="viconfont vicon-shangyibu"></i> <?php echo $LANG['UI_PUBLIC_PREV_STEP'] ?> </a>
                                    <a href="javascript:;" class="btn green-turquoise button-next next-btn-margin-left">
                                        <?php echo $LANG['UI_PUBLIC_NEXT_STEP'] ?> <i class="viconfont vicon-xiayibu"></i>
                                    </a>
                                    <a href="javascript:;" class="btn green-haze button-submit">
                                        <?php echo $LANG['UI_PUBLIC_SUBMIT'] ?> <i class="viconfont vicon-xiayibu"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- BEGIN RATE LIMIT MODAL -->
        <div id="speedlimitModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
            <div class="modal-header ">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title"><i class="iconfont icon-xiansucelve"></i> <?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="portlet-body">

                    <div class="form-group ">
                        <label class="control-label col-md-2">  <?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_TYPE'] ?>
                        </label>
                        <div class="col-md-6">
                            <select class="form-control select2me inline-block" id="speedModeType"  style="width:203px;">
                                <option value="1"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_STRATEGY'] ?></option>
                                <option value="2"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_FOREVER'] ?></option>
                            </select>
                            <a class="popovers ml15" data-container="body" data-trigger="hover"
                               data-placement="right" data-content="<?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_TYPE_TIPS'] ?>">
                                <i class="viconfont vicon-tishi"></i>
                            </a>
                        </div>
                    </div>

                    <div class="form-group setSpeedStrategy">
                        <label class="control-label col-md-2"> <?php echo $LANG['UI_BACKUP_SET_STRATEGY'] ?>
                        </label>
                        <div class="col-md-10">
                            <div class="portlet">
                                <div class="portlet-body">
                                    <div class="panel-group accordion" id="speedstrategy">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-2"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_VALUE'] ?>
                        </label>
                        <div class="col-md-6">
                            <div id="rateDiv" style="display:inline-flex;">
                                <div class="input-icon" >
                                    <div id="speedSpinnerNum" >
                                        <div class="input-group spinner-group">
                                            <input type="text" id="speedSpinnerNumInput" style="text-align: left;" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control">
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
                                </div>
                                <div class="">
                                    <select id="unit" style="width: 80px; margin-left: 10px;height: 33px;text-align:center;border: 1px solid #E6E6E6;">
                                        <option value="1">KB/s</option>
                                        <option selected value="2">MB/s</option>
                                        <option value="3">GB/s</option>
                                    </select>
                                </div>
                                <a style="display:block; margin-top:8px;" class="popovers ml15" data-container="body" data-trigger="hover"
                                   data-placement="right" data-content="<?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_VALUE_TIPS'] ?>">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- modal-footer -->
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
                <button type="button" class="btn btn-primary" id="speed_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
            </div>
        </div>
        <!-- END RATE LIMIT MODAL -->
    </div>
</div>
<!-- END PAGE CONTENT-->


<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<!--<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>-->
<?php
if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
    //如果不是英文,加载语言包
    echo '<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>';
}
if ($_SESSION['language'] != "en-us") {
    //如果不是英文,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
        $_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js" ></script>
<!--<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>-->
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.speedStrategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.strategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/calendar/jquery.searchableSelect.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.taskCrowd.js"></script>
<script type="text/javascript" src="./scripts/public/initComponents.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_virus_detection.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_complete.js"></script>
<script type="text/javascript" src="./scripts/platform/component/vm_recovery_transfer.js"></script>
<script type="text/javascript" src="./scripts/platform/component/retry_strategy.js"></script>
<script type="text/javascript" src="./scripts/platform/component/driver_check.js"></script>
<script type="text/javascript" src="./scripts/platform/component/form_validator.js"></script>
<script type="text/javascript" src="./scripts/plugins/path-tree-selector.js"></script>
<script type="text/javascript" src="./scripts/components/timePointDetail/js/timePointDetail.js"></script>
<script type="text/javascript" src="./scripts/components/timePointDetail/js/virusHistoryTable.js"></script>
<script type="text/javascript" src="./assets/global/plugins/clipboard/clipboard.min.js"></script>
<script src="./scripts/backupData/clipboard.js" type="text/javascript"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script src="./scripts/vm/vmrecover.js" type="text/javascript"></script>
