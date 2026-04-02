<?php include_once '../../tpl/permission.php'; ?>
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<link rel="stylesheet" type="text/css" href="./scripts/plugins/ztree/css/metroStyle/metroStyle.css" />
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="./scripts/plugins/calendar/jquery.searchableSelect.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/component/driver_check.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/tree-grid/jquery.treegrid.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/component/network_config.css" />
<link rel="stylesheet" type="text/css" href="./css/virus/virus-page.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/component/safe_virus.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/component/style.css" />
<link href="/assets/global/plugins/select2/select2-4.1.0/css/select2.min.css"rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./css/platform/component/resource_pool.css" />
<?php
session_start();
session_commit();
?>
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
    <span class="curent">
        <span class="current_des"></span>
    </span>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row recover-page">
    <div class="col-md-12" style="height:100%;">
        <input id="task_uuid" value="<?php echo $_GET['task_uuid']; ?>" class="display-none">
        <input id="backup_set_id" value="<?php echo $_GET['backup_set_id']; ?>" class="display-none">
        <div class="portlet box blue-hoki" id="completeMachineVolcdprecoverycontent">
            <div class="portlet-title">
                <div class="caption">
                    <i class="levelchild viconfont vicon-huifu1"></i>
                    <span class="caption_des"></span>
                </div>
            </div>
            <div class="portlet-body form">
                <form action="#" class="form-horizontal" id="submit_form" method="POST">
                    <div class="form-wizard">
                        <div class="form-body mlr10">
                            <!--           导航步骤                 -->
                            <ul class="nav nav-pills steps">
                                <li>
                                    <a href="#tab1" data-toggle="tab" class="step">
                                        <span class="number">1 </span>
                                        <span class="desc">
                                            <?php echo $LANG['UI_VOL_CDP_RECOVERY_DATA_SOURCE']; ?>
                                            <i class="fa fa-check"></i>
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#tab2" data-toggle="tab" class="step">
                                        <span class="number">2 </span>
                                        <span class="desc">
                                            <?php echo $LANG['UI_RECOVERY_GOAL']; ?>
                                            <i class="fa fa-check"></i>
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#tab3" data-toggle="tab" class="step active">
                                        <span class="number">3 </span>
                                        <span class="desc">
                                            <?php echo $LANG['UI_RECOVERY_STRATEGY']; ?>
                                            <i class="fa fa-check"></i>
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#tab4" data-toggle="tab" class="step">
                                        <span class="number">4 </span>
                                        <span class="desc">
                                            <?php echo $LANG['UI_BACKUP_CONFIRM_CONFIG']; ?>
                                            <i class="fa fa-check"></i>
                                        </span>
                                    </a>
                                </li>
                            </ul>
                            <!--           中间内容                 -->
                            <div class="tab-content bakuptab">
                                <!--           STEP1                 -->
                                <div class="tab-pane active" id="tab1">
                                    <div class="row tab-pane__row">
                                        <!-- 恢复客户端 -->
                                        <div class="col-md-4 tab-pane__row__source">
                                            <div class="form-group src-wrap">
                                                <div class="src-wrap__title">
                                                            <span>
                                                                <i class="levelchild viconfont vicon-ge_backup_host"></i> <?php echo $LANG['UI_VOL_CDP_RECOVERY_DATA_SOURCE']; ?>
                                                            </span>
                                                </div>
                                                <div class="src-wrap__content">
                                                    <div class="src-wrap__content__search">
                                                        <select class="bs-select width100p form-control" name="" id="storageselect">
                                                        </select>
                                                    </div>
                                                    <div class="src-wrap__content__search">
                                                        <div class="vm_tree_div">
                                                            <div class="width100p searchDiv">
                                                                <div class="input-icon">
                                                                    <input type="text" maxlength="128" placeholder="<?php echo $LANG['UI_SEARCH_CLIENT_KEY_WORD_TIPS']; ?>" class="form-control" id="searchAgent" onkeyup="customInputValidate('string', this.value, $(this))" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="src-wrap__content__ztree">
                                                        <div class="recoveryAgentTree vcenter-tree">
                                                            <div class="alert alert-block alert-info fade in display-hide"  id="nopointtips">
                                                                <button type="button" class="close" data-dismiss="alert"></button>
                                                                <h4 class="alert-heading">
                                                                    <strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong>
                                                                </h4>
                                                                <ol class="alert-ol">
                                                                    <li><?php echo $LANG['UI_CM_VOL_CDP_RECOVERY_NO_AVAILABLE_RECOVERY_DATA']; ?></li>
                                                                    <li>
                                                                        <?php echo $LANG['UI_DB_CDP_RECOVER_PROCEED_FIRST']; ?>
                                                                        <a id="tobackup" class="alert-link"><?php echo $LANG['UI_VOL_CDP_CREATE_BAK_JOB']; ?></a>
                                                                    </li>
                                                                </ol>
                                                            </div>
                                                            <ul id="recovery_agent_tree" class="ztree ztree-fa tree-scroll"></ul>
                                                        </div>
                                                        <div class="alert alert-block alert-info fade in display-hide" id="novolcdpagent">
                                                            <button type="button" class="close" data-dismiss="alert"></button>
                                                            <ul class="alert-ul">
                                                                <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                                                                <li>
                                                                    <?php echo $LANG['UI_BACKUP_NO_VALID_AGENT_TIPS'] ?>
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
                                        <!-- 备份时间集 -->
                                        <div id="volBackupSet" class="col-md-8 VMList-div display-none">
                                            <div class="addTitle">
                                                <span><?php echo $LANG['UI_PLATFORM_CHOOSE_POINT']; ?></span>
                                            </div>
                                            <div class="addIt-list">
                                                <!-- 备份时间集 -->
                                                <div id="show_cdp_timepoint">
                                                    <?php include_once('../../content/platform/component/vol_cdp_backup_set_info.php'); ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="noCheckTips">
                                            <div class="col-md-8">
                                                <div class="alert alert-block alert-info fade in" id="step1tips">
                                                    <button type="button" class="close" data-dismiss="alert"></button>
                                                    <ul class="alert-ul">
                                                        <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong>
                                                        <li>
                                                            <?php echo $LANG['UI_CM_VOL_CDP_RECOVERY_PLEASE_SELECT_A_RECOVERY_SOURCE']; ?>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--           STEP2                 -->
                                <div class="tab-pane vol-cdp-backup-tab" id="tab2">
                                    <div class="row">
                                        <div class="col-md-9">
                                            <div class="form-group mb-4">
                                                <label class="control-label col-md-3 mt-0 ptlb20"><?php echo $LANG['UI_PLATFORM_RECOVERY_SELECT_TARGET_TYPE']; ?></label>
                                                <div class="radio-group col-md-6" id="radio_group_1">
                                                    <label class="radio-group__item me-20 active radio-group__item_en" value="completeMachineRecovery" style="width: 124px;">
                                                        <i class="viconfont vicon-overview-complete-machine"></i>
                                                        <?php echo $LANG['UI_MACHINE_OS_MACHINE_RECOVERY']; ?>
                                                    </label>
                                                    <label class="radio-group__item radio-group__item_en" value="dataVolumeRecovery" style="width: 138px;">
                                                        <i class="viconfont vicon-overview-volume"></i>
                                                        <?php echo $LANG['UI_MACHINE_OS_DATA_VOLUME_RECOVERY']; ?>
                                                    </label>
<!--                                                    <div class="">-->
<!--                                                        <a class="popovers ml15" data-container="body" data-trigger="hover" data-placement="right" data-content="" data-original-title="" title="">-->
<!--                                                            <i class="viconfont vicon-tishi"></i>-->
<!--                                                        </a>-->
<!--                                                    </div>-->
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label col-md-3 pr20"><?php echo $LANG['UI_VOL_CDP_RECOVER_TARGET_MACHINE']; ?></label>
                                                <div class="col-md-9">
                                                    <select name="" id="recoverTargetHost" class="form-control">
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group driverCheckConf display-none">
                                                <div id="driverCheck"></div>
                                            </div>
                                            <div class="form-group hostConf display-none">
                                                <div class="col-md-offset-3 col-md-9">
                                                    <div class="accordion strategyThree">
                                                        <div class="panel panel-default strategy-panel">
                                                            <div class="panel-heading">
                                                                <h4 class="panel-title">
                                                                    <a class="accordion-toggle accordion-toggle-styled popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyThree" href="#targetHostConf" aria-expanded="true" data-original-title="" title="">
                                                                        <i class="viconfont vicon-jieguanfuwuqi font-green-seagreen"></i>
                                                                        <span class="font-green-seagreen">
                                                                            <?php echo $LANG['UI_CM_VOL_CDP_RECOVERY_TO_HOST']; ?>
                                                                        </span>
                                                                        <span class="font-green-seagreen targetHostName" title=""></span>
                                                                    </a>
                                                                </h4>
                                                            </div>
                                                            <div id="targetHostConf" class="panel-collapse collapse in">
                                                                <div class="panel-body">
                                                                    <div class="nav-tabs-wrapper">
                                                                        <ul class="nav nav-tabs nav-line-tabs" id="config_ul">
                                                                            <!-- 数据卷配置-->
                                                                            <li class="volinfo_li nav-item active">
                                                                                <a href="#volinfo" class="popovers nav-link" data-toggle="tab" aria-expanded="true">
                                                                                    <i class="viconfont vicon-cipanpeizhi"></i> <?php echo $LANG['WEB_OS_GOAL_VOLUME']; ?> </a>
                                                                            </li>
                                                                            <!--网络配置-->
                                                                            <li id="net_config_li" class="netConfig_li nav-item">
                                                                                <a href="#ipConfig" class="popovers nav-link" data-toggle="tab" aria-expanded="true">
                                                                                    <i class="viconfont vicon-ge_tag_point"></i> <?php echo $LANG['UI_PLATFORM_NETWORK_SETTING']; ?>
                                                                                </a>
                                                                            </li>
                                                                            <!-- 高级配置-->
                                                                            <li class="task_data_event_point nav-item">
                                                                                <a href="#scriptConfig" class="popovers nav-link" data-toggle="tab" aria-expanded="true">
                                                                                    <i class="viconfont vicon-gaojipeizhi"></i> <?php echo $LANG['UI_PUBLIC_SCRIPT_CONFIGURE']; ?> </a>
                                                                            </li>
                                                                        </ul>
                                                                        <div class="tab-content border-bottom-none pt-0 pb0 configContent">
                                                                            <div id="volinfo" class="tab-pane mt-24 mb-24 active"></div>
                                                                            <div id="ipConfig" class="tab-pane mt-24 mb-24"></div>
                                                                            <div id="scriptConfig" class="tab-pane mt-24 mb-24">
                                                                                <div class="col-md-3 col-sm-3 col-xs-3 nav-tab-radios">
                                                                                    <ul class="nav nav-tabs tabs-left">
                                                                                        <li class="active">
                                                                                            <a href="#recoveryScript" data-toggle="tab" aria-expanded="true"><?php echo $LANG['UI_CM_VOL_CDP_RECOVERY_RECOVERY_SCRIPT']; ?></a>
                                                                                        </li>
                                                                                        <li id="system_config_li" class="display-none-force">
                                                                                            <a href="#systemConfig" data-toggle="tab" aria-expanded="true"><?php echo $LANG['UI_PLATFORM_SYSTEM_SET']; ?></a>
                                                                                        </li>
                                                                                    </ul>
                                                                                </div>
                                                                                <div class="col-md-9 col-sm-9 col-xs-9">
                                                                                    <div class="tab-content">
                                                                                        <!--恢复后脚本-->
                                                                                        <div class="tab-pane fade in active recoveryScript" id="recoveryScript">
                                                                                        </div>
                                                                                        <!--系统配置-->
                                                                                        <div class="tab-pane fade in" id="systemConfig">
                                                                                            <div class="form-group">
                                                                                                <label class="control-label col-md-3 form-group-label">
                                                                                                    <span><?php echo $LANG['UI_VIRTUAL_MACHINE_RESET_HOSTNAME']; ?></span>
                                                                                                </label>
                                                                                                <div class="col-md-4">
                                                                                                    <input type="checkbox" id="rename" class="make-switch"
                                                                                                           data-on-color="primary" data-off-color="info" data-size="small"
                                                                                                           data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                                </div>
                                                                                            </div>

                                                                                            <div class="form-group reset_hostname_div display-none">
                                                                                                <label class="control-label col-md-3 form-group-label">
                                                                                                    <span class="required">* </span>
                                                                                                    <span><?php echo $LANG['UI_CM_VOL_CDP_RECOVERY_NEW_HOST_NAME']; ?></span>
                                                                                                </label>
                                                                                                <div class="col-md-4">
                                                                                                    <input type="text" name="rehostname" class="form-control input-sm" maxlength="128" style="height: 28px">
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
                                            </div>
                                            <div class="form-group">
                                                <div class="notargetHostTips col-md-offset-3 col-md-9">
                                                    <div class="alert alert-block alert-info fade in">
                                                        <button type="button" class="close" data-dismiss="alert"></button>
                                                        <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS']; ?></strong></h4>
                                                        <ol class="alert-ol">
                                                            <li>
                                                                <?php echo $LANG['UI_CM_VOL_CDP_RECOVERY_PLEASE_SELECT_RECOVERY_TARGET_MACHINE_FIRST']; ?>
                                                            </li>
                                                        </ol>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--           STEP3                 -->
                                <div class="tab-pane vol-cdp-backup-tab" id="tab3">
                                    <div class="row row-stepthree">
                                        <div class="nav-tabs-wrapper col-md-offset-1 col-md-10">
                                            <ul class="nav nav-tabs nav-line-tabs">
                                                <li class="active nav-item">
                                                    <a href="#tab_common" class="popovers nav-link" data-content="" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                        <i class="viconfont vicon-celve1"></i> <?php echo $LANG['WEB_COMMON_SPEED_STRATEGY']; ?> </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="#tab_transfer" class="popovers nav-link" data-content="" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                        <i class="viconfont vicon-a-chuanshu"></i> <?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY']; ?> </a>
                                                </li>
                                            </ul>
                                            <div class="tab-content hover-scroll-y">
                                                <div class="tab-pane active" id="tab_common">
                                                    <div class="panel-body">
                                                        <!--启动时间-->
                                                        <div class="form-group">
                                                            <div class="col-md-offset-1 col-md-10 recoveryTimeDiv">
                                                                <div class="accordion strategyTwo">
                                                                    <div class="panel panel-default strategy-panel">
                                                                        <div class="panel-heading">
                                                                            <h4 class="panel-title">
                                                                                <a class="accordion-toggle accordion-toggle-styled popovers"
                                                                                   data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyTwo" href="#recoveryTime" aria-expanded="true">
                                                                                    <i class="viconfont vicon-shijiankongjian font-green-seagreen"></i>
                                                                                    <span class="font-green-seagreen"><?php echo $LANG['UI_STRATEGY_TIME'] ?></span>
                                                                                    <span class="strategyDes recoveryTimeDes"></span>
                                                                                </a>
                                                                            </h4>
                                                                        </div>
                                                                        <div id="recoveryTime" class="panel-collapse collapse in">
                                                                            <div class="panel-body">
                                                                                <div class="col-md-12">
                                                                                    <div class="form-group">
                                                                                        <label class="control-label col-md-2"><?php echo $LANG['UI_RECOVERY_START_TYPE']; ?></label>
                                                                                        <div class="col-md-4">
                                                                                            <select class="form-control select2me" id="start_time_type">
                                                                                                <option value="1"><?php echo $LANG['UI_BACKUP_AS_MANUAL']; ?></option>
                                                                                                <option value="4"><?php echo $LANG['UI_RECOVERY_START_TYPE_TIMING']; ?></option>
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
                                                        <!--限速策略-->
                                                        <div class="form-group">
                                                            <div class="col-md-offset-1 col-md-10 accordion strategyOne">
                                                                <div class="panel panel-default strategy-panel">
                                                                    <div class="panel-heading">
                                                                        <h4 class="panel-title">
                                                                            <a class="accordion-toggle accordion-toggle-styled collapsed popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#speed" aria-expanded="true">
                                                                                <i class="viconfont vicon-xiansucelve1 font-green-seagreen"></i>
                                                                                <span class="font-green-seagreen"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT']; ?></span>
                                                                                <span class="strategyDes speedlimitDes"></span>
                                                                            </a>
                                                                        </h4>
                                                                    </div>
                                                                    <?php include_once('../../content/platform/global_strategy/speedJob.php'); ?>
                                                                    <!--       全局限速策略组合的组件配置-->
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane" id="tab_transfer">
                                                    <div class="panel-body">
                                                        <!--传输策略-->
                                                        <div class="tabbable-custom col-md-10">
                                                            <!-- 传输模式-->
                                                            <div class="form-group transportdiv">
                                                                <label class="control-label col-md-3 transferlabel"> <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE']; ?> </label>
                                                                <div class="col-md-4">
                                                                    <select class="form-control " id="transport_mode">
                                                                        <option value="nbd"> <?php echo $LANG['UI_BACKUP_TRANSPORT_NBD']; ?> </option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-2 mt5">
                                                                    <a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_ICS_VVDK_SELECT_TIPS']; ?>" data-original-title="" title="">
                                                                        <i class="viconfont vicon-tishi"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                            <!-- 传输网络-->
                                                            <div class="form-group transfernetworkview">
                                                                <label class="control-label col-md-3 encrypttransferlabels"><?php echo $LANG['UI_NODE_TRANSFER_NETWORK']; ?></label>
                                                                <div class="col-md-4">
                                                                    <ul class="ztree" id="transferNetworkTree"></ul>
                                                                </div>
                                                                <div class="col-md-2 mt5">
                                                                    <a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_NODE_TRANSFER_NETWORK_TIPS']; ?>">
                                                                        <i class="viconfont vicon-tishi"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                            <!-- 加密传输-->
                                                            <div class="form-group encrypttransferdiv">
                                                                <label class="control-label col-md-3 encrypttransferlabel form-group-label"><?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER']; ?></label>
                                                                <div class="col-md-4 form-group-content">
                                                                    <input type="checkbox" id="encrypttransfer" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>">
                                                                    <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_ENCRYPTED_TRANSMISSION_TIPS']; ?>">
                                                                        <i class="viconfont vicon-tishi"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                            <!-- 传输加密算法 -->
                                                            <div class="form-group transfer-encrypt-method-form display-none">
                                                                <label class="control-label transfer-encrypt-method-label col-md-3"><?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?></label>
                                                                <div class="col-md-4 form-group-content">
                                                                    <select class="form-control input-sm" id="transferEncryptMethod">
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

                                                            <!-- 传输压缩 -->
                                                            <div class="form-group trancompressdiv">
                                                                <label class="control-label col-md-3 transfercompress form-group-label"><?php echo $LANG['UI_PLATFORM_SRC_COMPRESS']; ?></label>
                                                                <div class="col-md-4 form-group-content">
                                                                    <input type="checkbox" id="tran_compress_switch" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>">
                                                                    <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_TRANSMISSION_COMPRESSION_TIPS']; ?>">
                                                                        <i class="viconfont vicon-tishi"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                            <!-- 压缩等级选择 -->
                                                            <div class="form-group transferCompressGradeDiv display-none">
                                                                <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE'] ?></label>
                                                                <div class="col-md-4">
                                                                    <select class="form-control input-sm" id="transferCompressGrade">
                                                                        <option value="1"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_FAST'] ?></option>
                                                                        <option value="2"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_NORMAL'] ?></option>
                                                                        <option value="3"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_BETTER'] ?></option>
                                                                        <option value="4"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_BEST'] ?></option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="form-group threadDiv">
                                                                <label class="control-label col-md-3 threadnumlabel"><?php echo $LANG['UI_BACKUP_THREAD_NUM']; ?>
                                                                </label>
                                                                <div class="col-md-4">
                                                                    <div id="recoveryThreadDiv">
                                                                        <div class="input-group spinner-group">
                                                                            <input type="text" id="recoveryThreadNum" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" >
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
                                                                <div class="col-md-2 mt5">
                                                                    <a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_THREAD_NUM_TIPS']; ?>" data-original-title="" title="">
                                                                        <i class="viconfont vicon-tishi"></i>
                                                                    </a>
                                                                </div>
                                                            </div>

                                                            <!-- 传输数据包大小 -->
                                                            <div class="form-group transferDataPackage">
                                                                <label class="control-label col-md-3 transferdatapackage"><?php echo $LANG['UI_VOL_CDP_TRANSMISSION_PACKET_SIZE']; ?>
                                                                </label>
                                                                <div class="col-md-4">
                                                                    <select class="form-control" id="transfer_datapackage_size">
                                                                        <option value=1>1 MB</option>
                                                                        <option value=2>2 MB</option>
                                                                        <option value=4 selected>4 MB</option>
                                                                        <option value=8>8 MB</option>
                                                                        <option value=16>16 MB</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-2 mt5">
                                                                    <a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_TRANS_DATA_PACKED_SIZE_TIPS']; ?>">
                                                                        <i class="viconfont vicon-tishi"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!--                                                <div class="tab-pane" id="tab_safe">-->
                                                <!--                                                    <div id="wormConfig"></div>-->
                                                <!--                                                </div>-->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--           STEP4                 -->
                                <div class="tab-pane vol-cdp-backup-tab" id="tab4">
                                    <div class="tab-pane__body">
                                        <div class="tab-pane__body__form">
                                            <div class="alert alert-danger display-none jobnametip" style="display: none;"></div>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_DATA_EXPORT_JOB_NAME']; ?>:</label>
                                                <div class="col-md-9">
                                                    <div class="input-icon right">
                                                        <i class="fa"></i>
                                                        <input type="text" maxlength="64" class="form-control cmcdptaskname" onkeyup="customInputValidate('string', this.value, $(this))">
                                                        <span class="help-block "><?php echo $LANG['UI_JOB_RNAME_TIPS']; ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <h4 class="form-section"></h4>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_RECOVERY_DATA_SOURCE_CLIENT']; ?>:</label>
                                                <div class="col-md-9">
                                                    <div class="form-control-static" id="recovery_data_source_agent"></div>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_DB_RECOVERY_RECOVERY_TIME']; ?>:</label>
                                                <div class="col-md-9">
                                                    <div class="form-control-static" id="recovery_time"></div>
                                                </div>
                                            </div>
                                            <h4 class="form-section"></h4>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_EMERGENCY_PLAN_RECOVER_TYPE']; ?>:</label>
                                                <div class="col-md-9">
                                                    <div class="form-control-static" id="target_type"></div>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_RECOVER_TARGET_MACHINE']; ?>:</label>
                                                <div class="col-md-6">
                                                    <div class="form-control-static" id="recovery_target_host"></div>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_CM_VOL_CDP_RECOVERY_TARGET_HOST_CONFIGURATION']; ?>:</label>
                                                <div class="col-md-9">
                                                    <div class="form-control-static" id="restoreTargetHostDes"></div>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_SCRIPT_CONFIGURE']; ?>:</label>
                                                <div class="col-md-9">
                                                    <div class="form-control-static" id="scriptDes"></div>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_SYSTEM_SET']; ?>:</label>
                                                <div class="col-md-9">
                                                    <div class="form-control-static" id="systemConfigDes"></div>
                                                </div>
                                            </div>
                                            <h4 class="form-section"></h4>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_START_TYPE']; ?>:</label>
                                                <div class="col-md-9">
                                                    <div class="form-control-static" id="start_time_des"></div>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['WEB_COMMON_SPEED_STRATEGY']; ?>:</label>
                                                <div class="col-md-9">
                                                    <div class="form-control-static" id="speed_limit_strategy"></div>
                                                </div>
                                            </div>
                                            <h4 class="form-section"></h4>

                                            <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY']; ?>:</label>
                                            <div class="col-md-9">
                                                <p class="form-control-static transportinfoshow"></p>
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
                                        <i class="viconfont vicon-shangyibu"></i> <?php echo $LANG['UI_PUBLIC_PREV_STEP']; ?>
                                    </a>
                                    <a href="javascript:;" class="btn green-turquoise button-next next-btn-margin-left">
                                        <?php echo $LANG['UI_PUBLIC_NEXT_STEP']; ?> <i class="viconfont vicon-xiayibu"></i>
                                    </a>
                                    <a href="javascript:;" class="btn  green-turquoise button-submit">
                                        <?php echo $LANG['UI_PUBLIC_SUBMIT']; ?> <i class="viconfont vicon-xiayibu"></i>
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
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/public/strategy.js"></script>

<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>

<script type="text/javascript" src="./scripts/plugins/jquery/jquery.speedStrategy.js"></script>

<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/additional-methods.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>

<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>


<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.strategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/libs/md5.js"></script>
<script type="text/javascript" src="./scripts/public/initComponents.js"></script>
<script type="text/javascript" src="./scripts/platform/component/driver_check.js"></script>
<script type="text/javascript" src="./assets/global/plugins/tree-grid/jquery.treegrid.js"></script>
<script type="text/javascript" src="./assets/global/plugins/tree-grid/bootstrap-table-treegrid.js"></script>
<script type="text/javascript" src="./scripts/platform/component/targetPlug.js"></script>
<script type="text/javascript" src="./scripts/platform/component/network_config.js"></script>
<script type="text/javascript" src="./assets/global/plugins/ace/src-min-noconflict/ace.js"></script>
<script type="text/javascript" src="/assets/global/plugins/select2/select2-4.1.0/js/select2.min.js"></script>
<script type="text/javascript" src="./scripts/platform/component/vinScript.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_virus_detection.js"></script>
<script type="text/javascript" src="./scripts/plugins/path-tree-selector.js"></script>
<script type="text/javascript" src="./scripts/platform/component/transfer_network.js"></script>
<script src="./scripts/complete_machine_volcdp/cm_volcdp_recovery.js"></script>
