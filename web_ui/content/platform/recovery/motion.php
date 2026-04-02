<?php include_once '../../../tpl/permission.php';
include_once  '../public/bs_table.php';
?>
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
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/tree-grid/jquery.treegrid.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/component/driver_check.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/component/network_config.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/component/style.css" />
<link href="/assets/global/plugins/select2/select2-4.1.0/css/select2.min.css"rel="stylesheet" type="text/css"/>
<!-- END PAGE LEVEL STYLES -->

<!-- BEGIN PAGE HEADER -->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="task" href="./content/platform/jobs/jobs.php">
            <span><?php echo $LANG['UI_PLATFORM_CURRENT_JOB'] ?></span>
        </a>
    </li>
    <span>
        >
    </span>
    <span class="curent">

        <?php if ($_GET['module'] == 2) {
            // 虚拟机
            echo $LANG['UI_MOTION_VM'];
        } else {
            echo $LANG['UI_MOTION_MACHINE'];
        } ?>
    </span>
</h3>
<!-- END PAGE HEADER -->

<!-- BEGIN PAGE CONTENT-->
<div class="row" style="height:calc(100% - 28px);">
    <div class="col-md-12" style="height:100%;">
        <div class="portlet box blue-hoki" id="vmrecovercontent">
            <div class="portlet-title">
                <div class="caption">
                    <i class="levelchild viconfont vicon-huifu1"></i><?php echo $LANG['UI_BACKUP_NEW_MOTION_JOB']?>
                </div>
            </div>
            <div class="portlet-body form">
                <input id="taskuuid" value="<?php echo $_GET['uuid'];?>" class="display-none"/>
                <form action="#" class="form-horizontal" id="submit_form" method="POST">
                    <div class="form-wizard">
                        <div class="form-body">
                            <ul class="nav nav-pills steps" >
                                <li>
                                    <a href="#tab1" data-toggle="tab" class="step">
                                        <span class="number">1 </span>
                                        <span class="desc">
											<?php echo $LANG['UI_OS_MIGRATE_TARGET'];?>
											<i class="fa fa-check"></i>
										</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#tab2" data-toggle="tab" class="step active">
                                        <span class="number">2 </span>
                                        <span class="desc">
											<?php echo $LANG['UI_PLATFORM_RECOVERY_MOTION_STRATEGY'];?>
											<i class="fa fa-check"></i>
										</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#tab3" data-toggle="tab" class="step">
                                        <span class="number">3 </span>
                                        <span class="desc">
											<?php echo $LANG['UI_BACKUP_CONFIRM_CONFIG']?>
											<i class="fa fa-check"></i>
										</span>
                                    </a>
                                </li>
                            </ul>
                            <div class="tab-content bakuptab">
                                <!-- 恢复目标 -->
                                <div class="tab-pane" id="tab1">
                                    <div class="alert alert-danger display-none setrecover2tip">
                                    </div>
                                    <div class="row tab-pane__row">
                                        <div class="col-md-12 col-steptwo">
                                            <!-- 选择目标类型 -->
                                            <div class="form-group">
                                                <label class="control-label col-md-3 mt-0 ptlb20"><?php echo $LANG['UI_PLATFORM_RECOVERY_SELECT_TARGET_TYPE'];?></label>
                                                <div class="radio-group col-md-6" id="radio_group_1">
                                                    <label class="radio-group__item me-20" value="vmRecovery" style="width: 112px;">
                                                        <i class="viconfont vicon-overview-vm"></i><?php echo $LANG['UI_PLATFORM_VM_VIRTUAL'];?></label>
                                                    <label class="radio-group__item me-20" value="vmRecovery2" style="width: 112px;">
                                                        <i class="viconfont vicon-overview-private-cloud"></i><?php echo $LANG['UI_PLATFORM_PRIVATE_CLOUD'];?></label>
                                                    <label class="radio-group__item me-20" value="vmRecovery3" style="width: 112px;">
                                                        <i class="viconfont vicon-overview-plubic-cloud"></i><?php echo $LANG['UI_PLATFORM_PUBLIC_CLOUD'];?></label>
                                                    <label class="radio-group__item" value="completeMachineRecovery" style="width: 112px;">
                                                        <i class="viconfont vicon-overview-complete-machine"></i><?php echo $LANG['UI_PLATFORM_COMPLETE_MACHINE'];?></label>
                                                </div>
                                            </div>
                                            <div id="show_vmRecovery" class="display-none">
                                                <!--虚拟化平台的代码-->
                                                <?php include_once '../recovery/vm_recovery.php'; ?>
                                            </div>
                                            <div id="show_completeMachineRecovery" class="display-none">
                                                <!--整机部分的代码-->
                                                <?php include_once '../recovery/client_recovery.php'; ?>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label col-md-3">
                                                </label>
                                                <div class="col-md-7">
                                                    <div class="alert alert-block alert-info fade in">
                                                        <button type="button" class="close" data-dismiss="alert"></button>
                                                        <ul class="alert-ul">
                                                            <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                                                            <li>
                                                                <?php echo $LANG['UI_RECOVERY_CHOOSE_GOAL_TIPS'] ?>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 迁移策略 -->
                                <div class="tab-pane" id="tab2">
                                    <div class="row row-stepthree">
                                        <div class="tabbable-custom col-md-offset-1 col-md-10">
                                            <ul class="nav nav-tabs">
                                                <li class="commonLi active">
                                                    <a href="#tab_common" data-toggle="tab">
                                                        <i class="viconfont vicon-celve1"></i> <?php echo $LANG['UI_BACKUP_COMMON_STRATEGY']?> </a>
                                                </li>
                                                <li class="transferLi">
                                                    <a href="#tab_transfer" data-toggle="tab">
                                                        <i class="viconfont vicon-a-chuanshu"></i> <?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?> </a>
                                                </li>
                                                <li class="retryLi">
                                                    <a href="#tab_retry" data-toggle="tab">
                                                        <i class="viconfont vicon-gaojipeizhi"></i> <?php echo $LANG['UI_STRATEGY_RETRY'];?></a>
                                                </li>
                                                <li class="highLi">
                                                    <a href="#tab_high" data-toggle="tab">
                                                        <i class="viconfont vicon-gaojipeizhi"></i> <?php echo $LANG['UI_BACKUP_HIGH_CONFIG']?> </a>
                                                </li>
                                            </ul>
                                            <div class="tab-content">
                                                <!-- 通用策略 -->
                                                <div class="tab-pane active" id="tab_common">
                                                    <div class="panel-body">
                                                        <div class="form-group display-none" >
                                                            <div class="control-label col-md-2" ><?php echo $LANG['UI_BACKUP_SELECT_STRATEGY']?></div>
                                                            <div class="col-md-4">
                                                                <select class="form-control  inline-block" id="strategySelect">
                                                                </select>
                                                                <a class="popovers ml15" data-container="body" data-trigger="hover"
                                                                   data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_SELECT_STRATEGY_TIPS'] ?>">
                                                                    <i class="viconfont vicon-tishi"></i>
                                                                </a>
                                                            </div>
                                                            <div class="col-md-1 mt10">
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
                                                                                <span class="font-green-seagreen"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT']?></span>
                                                                                <span class="strategyDes speedlimitDes"></span>
                                                                            </a>
                                                                        </h4>
                                                                    </div>
                                                                    <?php include_once('../global_strategy/speedJob.php'); ?>
                                                                    <!--       全局限速策略组合的组件配置-->
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>

                                                <!--传输策略-->
                                                <div class="tab-pane " id="tab_transfer">

                                                    <div class="panel-body agent-transport-strategy display-none">
                                                        <div class="tabbable-custom col-md-10">
                                                            <!-- 加密传输 -->
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3 encrypttransferlabel form-group-label"><?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER'] ?></label>
                                                                <div class="col-md-4 form-group-content">
                                                                    <input type="checkbox" id="agent_encrypttransfer" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                </div>
                                                            </div>
                                                            <!-- 传输加密算法 -->
                                                            <div class="form-group transfer-agent-encrypt-method-form display-none">
                                                                <label class="control-label transfer-encrypt-method-label col-md-3"><?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?></label>
                                                                <div class="col-md-4">
                                                                    <select class="form-control  input-sm" id="agent_transferEncryptMethod">
                                                                        <?php
                                                                        
                                                                        foreach ($CONF['TRANSPORT_ENCRYPT_METHOD'] as $index => $encryptMethod) {
                                                                            if ($_SESSION['language'] != "zh-cn" && $_SESSION['language'] != "zh-tw" && $index == 2) {
                                                                            } else {
                                                                                echo '<option value="' . $index . '">' . $encryptMethod . '</option>';
                                                                            }
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="form-group">
                                                                <label class="control-label col-md-3 threadnumlabel"><?php echo $LANG['UI_BACKUP_THREAD_NUM']?>
                                                                </label>
                                                                <div class="col-md-4">
                                                                    <div id="agent_recoveryThreadDiv">
                                                                        <div class="input-group spinner-group">
                                                                            <input type="text" id="agent_recoveryThreadNum" onkeyup="value=value.replace(/[^\d]/g,'')" value="3" class="spinner-input form-control input-sm" >
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
                                                                </div>
                                                                <div class="col-md-2 vmbackup-mt10_en mt5_cn">
                                                                    <a class="popovers" data-container="body" data-trigger="hover" data-html="true"
                                                                       data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_THREAD_NUM_TIPS']?>">
                                                                        <i class="viconfont vicon-tishi"></i>
                                                                    </a>
                                                                </div>
                                                            </div>

                                                            <div class="form-group transfernetworkDiv display-none">
                                                                <label class="control-label col-md-3 transfernetworklabel"><?php echo $LANG['UI_COPY_TRANSFER_NET']?></label>
                                                                <div class="col-md-4">
                                                                    <ul class="ztree" id="agent_transferNetworkTree"></ul>
                                                                </div>
                                                                <div class="col-md-2 mt5">
                                                                    <a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_NODE_TRANSFER_NETWORK_TIPS']?>">
                                                                        <i class="viconfont vicon-tishi"></i>
                                                                    </a>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>

                                                    <div class="vm-transport-strategy">
                                                        <!--虚拟化平台的代码-->
                                                        <?php include_once '../component/vm_motion_transfer_strategy.php'; ?>
                                                    </div>
                                                </div>

                                                <!-- 脚本配置 -->
                                                <!--<div class="tab-pane " id="tab_scripts">
                                                    <div class="panel-body" style="padding:16px">
                                                        <div class="row" style="margin: 0;">
                                                            <div class="col-md-2 col-sm-2 col-xs-2 nav-tab-radios">
                                                                <ul class="nav nav-tabs tabs-left">
                                                                    <li class="dwm active" data-type="2"><a href="#script_before" data-toggle="tab" aria-expanded="true">
                                                                            <div class="iradio_square-blue strategy-radio-custom" style="position: relative;"><input type="radio" data-radio="iradio_square-blue" class="icheck" name="bakradio0"></div> 迁移前
                                                                        </a></li>
                                                                    <li class="dwm" data-type="3"><a href="#script_after" data-toggle="tab" aria-expanded="true">
                                                                            <div class="iradio_square-blue strategy-radio-custom" style="position: relative;"><input type="radio" data-radio="iradio_square-blue" class="icheck" name="bakradio0"></div> 迁移后
                                                                        </a></li>
                                                                </ul>
                                                            </div>
                                                            <div class="col-md-10 col-sm-10 col-xs-10">
                                                                <div class="tab-content">
                                                                    <div class="tab-pane fade in active" id="script_before">
                                                                        <div class="script_before"></div>
                                                                    </div>
                                                                    <div class="tab-pane fade in" id="script_after">
                                                                        <div class="script_after"></div>
                                                                    </div>

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>-->

                                                <!-- 重试策略 -->
                                                <div class="tab-pane" id="tab_retry">
                                                    <div class="advance-config-wrap">
                                                        <div class="advance-config-wrap__row row m0">
                                                            <div class="col-md-12 col-sm-12 col-xs-12 pe-0 advance-config-wrap__row__content">
                                                                <div class="tab-content">
                                                                    <div id="retry_config" class="tab-pane retry_config_pane active">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!--高级策略-->
                                                <div class="tab-pane" id="tab_high">
                                                    <div class="panel-body">
                                                        <div class="tabbable-custom pdlr15  col-md-10">
                                                            <div class="form-group autofinishdiv">
                                                                <label class="control-label col-md-3 stopmotionaftercompletelabel form-group-label"><?php echo $LANG['UI_PLATFORM_RECOVERY_MOTION_STOP_COMPLETE'];?></label>
                                                                <div class="col-md-4"  style="margin-top: 4px;">
                                                                    <input type="checkbox" id="stopmotionaftercomplete" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info"
                                                                           data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>"
                                                                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
                                                                    <span class="help-block"><?php echo $LANG['UI_PLATFORM_RECOVERY_MOTION_STOP_COMPLETE_TIPS']?></span>
                                                                </div>
                                                            </div>

                                                            <div class="form-group autofinishdiv">
                                                                <label class="control-label col-md-3 autocompletemotionlabel form-group-label"><?php echo $LANG['UI_PLATFORM_RECOVERY_MOTION_AUTO_COMPLETE'];?></label>
                                                                <div class="col-md-4">
                                                                    <div class="input-group " id="useMode" style="margin-top: 4px">
                                                                        <label class="backupDiv" style="padding-right: 20px;"><input type="checkbox" id="autoCheck"  data-checkbox="icheckbox_square-blue" data-mode="1" class="icheck"><?php echo $LANG['UI_PLATFORM_RECOVERY_MOTION_AUTO_COMPLETE_1']?></label>
                                                                        <label class="copyDiv" style="padding-right: 20px;"><input type="checkbox" id="handCheck" checked data-checkbox="icheckbox_square-blue" data-mode="2" class="icheck"><?php echo $LANG['UI_PLATFORM_RECOVERY_MOTION_AUTO_COMPLETE_2']?></label>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="form-group autofinishSettingdiv">
                                                                <label class="control-label col-md-3">
                                                                </label>
                                                                <div class="col-md-4 col-md-6_en" style="display:inline-flex;">
                                                                    <div class="input-group width-100" id="cache_data_sync_interval">
                                                                        <span class="input-group-btn">
                                                                            <button class="btn" type="button" style="background:none;padding-left:0;"><?php echo $LANG['UI_PLATFORM_RECOVERY_MOTION_DATA_INTERVAL']?></button>
                                                                        </span>

                                                                        <div class="input-group spinner-group">
                                                                            <input type="text" id="limitsize" style="text-align: left;" class="spinner-input form-control" maxlength="8" onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}" onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'0')}else{this.value=this.value.replace(/\D/g,'')}">
                                                                            <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                <button type="button" class="btn spinner-up default">
                                                                                    <i class="fa fa-angle-up"></i>
                                                                                </button>
                                                                                <button type="button" class="btn spinner-down default">
                                                                                    <i class="fa fa-angle-down"></i>
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                        <span class="input-group-btn">
                                                                        <button class="btn" type="button"><?php echo $LANG['WEB_UTILS_MINUTE']?></button>
                                                                    </span>

                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div style="margin-top:-10px;margin-bottom:20px;display:flex;">
                                                                <div class="col-md-3"></div>
                                                                <div class="col-md-4">
                                                                    <span class="help-block"><?php echo $LANG['UI_PLATFORM_RECOVERY_MOTION_STOP_COMPLETE_TIPS2']?></span>
                                                                </div>
                                                            </div>

                                                            <!-- 忽略节点资源限制 -->
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3 ignoreResourceLimitLabel form-group-label"><?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE']?></label>
                                                                <div class="col-md-1 form-group-content">
                                                                    <input type="checkbox" id="ignore_resource_limit"  class="make-switch" data-on-color="primary" data-off-color="info" data-size="small"
                                                                           data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>"
                                                                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
                                                                </div>
                                                                <div class="col-md-1 vmbackup-mt10_en mt5_cn">
                                                                    <a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right"
                                                                       data-content="<?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE_TIPS']?>" >
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

                                <!-- 确认配置 -->
                                <div class="tab-pane" id="tab3">
                                    <div class="tab-pane__body">
                                        <div class="tab-pane__body__form">
                                            <div class="alert alert-danger display-none jobnametip"></div>
                                            <div class="form-group mb0 mt10 display-none">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_JOB_RNAME'] ?>:</label>
                                                <div class="col-md-9">
                                                    <div class="input-icon right">
                                                        <input type="text" maxlength="64" name="job_name" class="form-control" id="job_name" />
                                                        <span class="help-block "><?php echo $LANG['UI_JOB_RNAME_TIPS'] ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <h4 class="form-section display-none"></h4>
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
                                            <div class="form-group mb0 mt10" id="transportmodeshowdiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?>:</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static transportinfoshow">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mt10" id="speedstrategyshowdiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?>:</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static speedlimitshow">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mt10 retryShow">
                                            </div>
                                            <div class="form-group mb0 mt10" id="highstrategyshowdiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_GLOBAL_STRATEGY_HIGH'] ?>:</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static highstrategyshow">
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
                                        <i class="viconfont vicon-shangyibu"></i> <?php echo $LANG['UI_PUBLIC_PREV_STEP']?> </a>
                                    <a href="javascript:;" class="btn green-turquoise button-next next-btn-margin-left">
                                        <?php echo $LANG['UI_PUBLIC_NEXT_STEP']?> <i class="viconfont vicon-xiayibu"></i>
                                    </a>
                                    <a href="javascript:;" class="btn green-haze button-submit">
                                        <?php echo $LANG['UI_PUBLIC_SUBMIT']?> <i class="viconfont vicon-xiayibu"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
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
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<?php

if($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw"){
    //如果不是英文,加载语言包
    echo '<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>';
}
if($_SESSION['language'] != "en-us"){
    //如果不是英文,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
        $_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script type="text/javascript" src="/assets/global/plugins/select2/select2-4.1.0/js/select2.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js" ></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.vmRecoveryConfig.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.vmConfigValidate.js"></script>
<script type="text/javascript" src="./scripts/plugins/calendar/jquery.searchableSelect.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.speedStrategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.strategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.taskCrowd.js"></script>
<script type="text/javascript" src="./scripts/public/initComponents.js"></script>
<script type="text/javascript" src="./assets/global/plugins/tree-grid/jquery.treegrid.js"></script>
<script type="text/javascript" src="./assets/global/plugins/tree-grid/bootstrap-table-treegrid.js"></script>
<script type="text/javascript" src="./scripts/platform/component/targetPlug.js"></script>
<script type="text/javascript" src="./scripts/platform/component/network_config.js"></script>
<script type="text/javascript" src="./scripts/platform/component/driver_check.js"></script>
<script type="text/javascript" src="./assets/global/plugins/ace/src-min-noconflict/ace.js"></script>
<script type="text/javascript" src="./scripts/platform/component/vinScript.js"></script>
<script type="text/javascript" src="./scripts/platform/component/retry_strategy.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script src="./scripts/platform/recovery/motion.js" type="text/javascript"></script>
