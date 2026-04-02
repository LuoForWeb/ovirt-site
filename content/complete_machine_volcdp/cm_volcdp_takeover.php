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
        <a class="ajaxify" name="cdp_takeover" href="./content/volcdp/takeover.php">
            <span><?php echo $LANG['UI_PLATFORM_TAKEOVER'] ?></span>
        </a>
    </li>
    <span>
        >
    </span>
    <span class="curent">
        <?php echo $LANG['UI_COMPLETE_MACHINE']?>
    </span>
</h3>
<!-- END PAGE HEADER-->

<!-- BEGIN PAGE CONTENT-->
<div class="row recover-page">
    <div class="col-md-12" style="height:100%;">
        <input id="task_uuid" value="<?php echo $_GET['task_uuid']; ?>" class="display-none">
        <input id="backup_set_id" value="<?php echo $_GET['backup_set_id']; ?>" class="display-none">
        <div class="portlet box blue-hoki" id="completeMachineVolcdptakeovercontent">
            <div class="portlet-title">
                <div class="caption">
                    <i class="levelchild viconfont vicon-vol_cdp_takeover"></i>
                    <?php echo $LANG['UI_CM_CDP_CONTINUOUS_DATA_PROTECT_REPLICATION_DATA_TAKEOVER']?>
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
                                                <?php echo $LANG['UI_CM_VOL_CDP_TAKEOVER_DATA_SOURCE']; ?>
                                            <i class="fa fa-check"></i>
                                        </span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#tab2" data-toggle="tab" class="step">
                                            <span class="number">2 </span>
                                            <span class="desc">
                                                <?php echo $LANG['UI_CM_VOL_CDP_TAKEOVER_TARGET']; ?>
                                            <i class="fa fa-check"></i>
                                        </span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#tab3" data-toggle="tab" class="step active">
                                            <span class="number">3 </span>
                                            <span class="desc">
                                                <?php echo $LANG['UI_CM_VOL_CDP_TAKEOVER_STRATEGY']; ?>
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
                                                        <i class="levelchild viconfont vicon-ge_backup_host"></i>
                                                        <?php echo $LANG['UI_CM_VOL_CDP_TAKEOVER_DATA_SOURCE']; ?>
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
                                                            <div class="takeoverAgentTree vcenter-tree">
                                                                <div class="alert alert-block alert-info fade in display-hide"  id="nopointtips">
                                                                    <button type="button" class="close" data-dismiss="alert"></button>
                                                                    <ul class="alert-ul">
                                                                        <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                                                                        <li>
                                                                            <?php echo $LANG['UI_CM_VOL_CDP_TAKEOVER_NO_AVAILABLE_TAKEOVER_DATA']; ?>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                                <ul id="takeover_agent_tree" class="ztree ztree-fa tree-scroll"></ul>
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
                                                        <h4 class="alert-heading">
                                                            <strong>
                                                                <?php echo $LANG['UI_PUBLIC_TIPS'] ?>
                                                            </strong>
                                                        </h4>
                                                        <ol class="alert-ol">
                                                            <li>
                                                                <?php echo $LANG['UI_CM_VOL_CDP_TAKEOVER_PLEASE_SELECT_TAKEOVER_TIPS1']; ?>
                                                            </li>
                                                            <li>
                                                                <?php echo $LANG['UI_CM_VOL_CDP_TAKEOVER_PLEASE_SELECT_TAKEOVER_TIPS2']; ?>
                                                            </li>
                                                        </ol>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!--           STEP2                 -->
                                    <div class="tab-pane vol-cdp-backup-tab" id="tab2">
                                        <div class="row">
                                            <div class="col-md-9">
                                                <div class="form-group <?php if($CONF['SYSTEM_INFO']['vendor'] == "sangfor"){echo 'display-none-force';}?>" id="takeoverMethod">
                                                    <label class="control-label col-md-3 mt-0 pr20"><?php echo $LANG['UI_CM_CDP_TAKEOVER_MODE']; ?></label>
                                                    <div class="col-md-9 pl0">
                                                        <div>
                                                            <label class="pr40 mb0 radio-label control-label mt-0"><input type="radio" id="box1" name="radiobox" data-checkbox="icheckbox_square-blue" data-mode="1" class="icheck"><?php echo $LANG['UI_VOL_CDP_TAKE_TEMP_AGENT_DESC']; ?></label>
                                                            <label class="mb0 radio-label control-label mt-0"><input type="radio" id="box2" name="radiobox" data-checkbox="icheckbox_square-blue" data-mode="2" class="icheck"><?php echo $LANG['UI_COMPLETE_MACHINE']; ?></label>
                                                        </div>
                                                        <span class = "help-block cmtakeovermodehelp"><?php echo $LANG['UI_CM_CDP_CM_TAKEOVER_TIPS']; ?></span>
                                                        <span class = "help-block mounttakeovermodehelp display-none"><?php echo $LANG['UI_CM_CDP_MOUNT_TAKEOVER_TIPS']; ?></span>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="" class="control-label col-md-3 pr20"><?php echo $LANG['UI_CM_CDP_TAKEOVER_STANDBY']; ?></label>
                                                    <div class="col-md-9 pl0">
                                                        <button type="button" class="btn exch-green marginb8" data-toggle="drawer" data-target="#standby_map_conf_drawer" id="cmstandbymapconf"><?php echo $LANG['UI_VOL_CDP_STANDBY_CONF_MAP'];?></button>
                                                        <div class="takeoverBuiltInVmConf_div">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="standbyMapConfDes" class="form-group display-none">
                                                    <label class="col-md-3 driver-config__label"></label>
                                                    <div class="col-md-8 driver-check-content__wrapper driver-config__content">
                                                        <ul id="" class="driver-check-list" style="">
                                                            <li class="driver-check-item">
                                                                <div class="driver-check-item__name">
                                                                    <span class="text"></span>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <!-- 增加漂移IP提示信息 -->
                                                <div class="form-group  display-none takeoveripdriftview  mt-15">
                                                    <label class="control-label col-md-3 applianceselectlabel"></label>
                                                    <div class="col-md-9">
                                                        <ul id="" style="padding-left: 0;" class=" width100p help-block font-danger">
                                                            <?php echo $LANG['UI_VOL_CDP_TAKEOVER_IP_DRIFT_TIPS']; ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="form-group display-none">
                                                    <label class="control-label col-md-3 pr20"><?php echo $LANG['UI_VOL_CDP_TAKEOVER_STANDBY_MACHINE']; ?></label>
                                                    <div class="col-md-9 pl0">
                                                        <select name="" id="takeoverHost" class="form-control">
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group display-none takeoverNetConf">
                                                    <label for="" class="control-label col-md-3 pr20"><?php echo $LANG['UI_VOL_CDP_BACKUP_TAKEOVER_NET_CONF']; ?></label>
                                                    <div class="col-md-9 pl0">
                                                        <button type="button" class="btn exch-green takeovernetworkconf" id=""><?php echo $LANG['UI_VOL_CDP_TASK_TAKEOVER_NET_CONF']; ?></button>
                                                        <ul id="" style="padding-left: 0;" class="hm_takeover_ip_map_list width100p"></ul>
                                                    </div>
                                                </div>
                                                <div class="form-group notakeoverTargetTips">
                                                    <div class="col-md-offset-3 col-md-9 pl0">
                                                        <div class="alert alert-block alert-info fade in">
                                                            <button type="button" class="close" data-dismiss="alert"></button>
                                                            <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS']; ?></strong></h4>
                                                            <ol class="alert-ol">
                                                                <li>
                                                                    <?php echo $LANG['UI_CM_CDP_TAKEOVER_STANDBY_TIPS']; ?>
                                                                </li>
                                                                <li class="<?php if($CONF['SYSTEM_INFO']['vendor'] != "sangfor"){echo 'display-none-force';}?>">
                                                                    <?php echo $LANG['UI_CM_CDP_CM_TAKEOVER_TIPS']?>
                                                                </li>
                                                            </ol>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="mountTakeover display-none">
                                                    <div class="form-group">
                                                        <label class="control-label col-md-3 pr20"><?php echo $LANG['UI_VOL_CDP_TAKEOVER_CONFIGURE']; ?></label>
                                                        <div class="col-md-9 pl0">
                                                            <div class="accordion">
                                                                <div class="panel panel-default strategy-panel">
                                                                    <div class="panel-heading">
                                                                        <!--标题-->
                                                                        <div class="panel-title">
                                                                            <a class="accordion-toggle accordion-toggle-styled popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent="" href="#takeoverConf" aria-expanded="true" data-original-title="" title="">
                                                                                <i class="viconfont vicon-jieguanfuwuqi font-green-seagreen"></i>
                                                                                <span class="font-green-seagreen"><?php echo $LANG['UI_VOL_CDP_TAKEOVER_SERVER']; ?></span>
                                                                                <span class="font-green-seagreen takeoverHostName" title=""></span>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                    <div id="takeoverConf" class="panel-collapse collapse in">
                                                                        <div class="panel-body">
                                                                            <div class="nav-tabs-wrapper">
                                                                                <ul class="nav nav-tabs nav-line-tabs" id="">
                                                                                    <!-- 接管卷配置-->
                                                                                    <li class="active volinfoLi nav-item">
                                                                                        <a href="#volinfo" class="popovers nav-link" data-toggle="tab" aria-expanded="true">
                                                                                            <i class="viconfont vicon-cipanpeizhi"></i> <?php echo $LANG['WEB_OS_GOAL_VOLUME']; ?> </a>
                                                                                    </li>

                                                                                    <!-- 应用配置-->
                                                                                    <li class="appConfigLi nav-item">
                                                                                        <a href="#appConfig" class="popovers nav-link" data-toggle="tab" aria-expanded="true">
                                                                                            <i class="viconfont vicon-yingyong"></i> <?php echo $LANG['UI_CM_CDP_APP_SETTING']; ?> </a>
                                                                                    </li>

                                                                                    <!-- 脚本-->
                                                                                    <li class="scriptConfigLi nav-item">
                                                                                        <a href="#scriptConfig" class="popovers nav-link" data-toggle="tab" aria-expanded="true">
                                                                                            <i class="viconfont vicon-ziyuanxiangqing"></i> <?php echo $LANG['UI_CM_CDP_SCRIPT_CONFIGURE']; ?> </a>
                                                                                    </li>

                                                                                </ul>
                                                                                <div class="tab-content border-bottom-none pt-0 pb0">
                                                                                    <div id="volinfo" class="tab-pane mt-24 mb-24 active"></div>
                                                                                    <div class="tab-pane" id="appConfig">
                                                                                        <div class="alert alert-block alert-info fade in" id="noTakeoverApp">
                                                                                            <button type="button" class="close" data-dismiss="alert"></button>
                                                                                            <ul class="alert-ul">
                                                                                                <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                                                                                                <li>
                                                                                                    <?php echo $LANG['UI_VOL_CDP_TAKEOVER_TASK_APPLICATION_ALERT']; ?>
                                                                                                </li>
                                                                                            </ul>
                                                                                        </div>
                                                                                        <div class="panel-body">
                                                                                            <div class="mt12" style="height: 36px;margin-left: 8px;">
                                                                                                <label class="control-label mr10"><?php echo $LANG['UI_VOL_CDP_APPLICATION_TAKEOVER'];?></label>
                                                                                                <input type="checkbox" id="" class="make-switch apptakeoverswitch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>">
                                                                                                <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_CM_CDP_ENABLE_APP_TAKEOVER_TIPS']; ?>">
                                                                                                    <i class="viconfont vicon-tishi"></i>
                                                                                                </a>
                                                                                            </div>
                                                                                            <div class="form-group takeover_app_tree_div display-hide">
                                                                                                <ul id="takeover_app_tree" class="ztree"></ul>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div id="scriptConfig" class="tab-pane mt-24 mb-24">
                                                                                        <div class="col-md-3 col-sm-3 col-xs-3 nav-tab-radios">
                                                                                            <ul class="nav nav-tabs tabs-left">
                                                                                                <li class="active">
                                                                                                    <a href="#takeoverAfterScript" data-toggle="tab" aria-expanded="true"><?php echo $LANG['UI_CM_CDP_AFTER_TAKEOVER']; ?></a>
                                                                                                </li>
                                                                                            </ul>
                                                                                        </div>
                                                                                        <div class="col-md-9 col-sm-9 col-xs-9">
                                                                                            <div class="tab-content">
                                                                                                <!--接管后脚本-->
                                                                                                <div class="tab-pane fade in active takeoverAfterScript" id="takeoverAfterScript"></div>
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
                                                <div id="completeMachineTakeover" class="display-none">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!--           STEP3                 -->
                                    <div class="tab-pane vol-cdp-takeover-tab" id="tab3">
                                        <div class="row row-stepthree">
                                            <div class="nav-tabs-wrapper pdlr15 col-md-offset-1 col-md-10" style="height: 100%;">
                                                <ul class="nav nav-tabs nav-line-tabs">
                                                    <li class="active tab_transfer_li nav-item">
                                                        <a href="#tab_transfer" class="popovers nav-link" data-content="" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                            <i class="viconfont vicon-a-chuanshu"></i> <?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY']; ?> </a>
                                                    </li>
                                                    <li class="safe_strategy_li nav-item <?php if($CONF['SYSTEM_INFO']['vendor'] == "sangfor"){echo 'display-none-force';}?>">
                                                        <a href="#safe_strategy" class="popovers nav-link" data-content="" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                            <i class="viconfont vicon-anquancelve"></i> <?php echo $LANG['UI_BACKUP_SAFE_CONFIG_DES']; ?> </a>
                                                    </li>
                                                </ul>
                                                <div class="tab-content min-height300 hover-scroll-y" style="height: 90%;overflow-x:hidden;overflow-y: auto;">
                                                    <div class="tab-pane" id="safe_strategy">
                                                        <div id="wormConfig"></div>
                                                    </div>
                                                    <div class="tab-pane active" id="tab_transfer">
                                                        <div class="panel-body">
                                                            <!--传输策略-->
                                                            <div class="tabbable-custom col-md-10">
                                                                <!-- 传输模式-->
                                                                <div class="form-group transportdiv">
                                                                    <label class="control-label col-md-3 transferlabel"> <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE']; ?></label>
                                                                    <div class="col-md-4">
                                                                        <select class="form-control " id="transport_mode">
                                                                            <option value="nbd"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD']; ?></option>
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
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!--           STEP3                 -->
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
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_CM_VOL_CDP_TAKEOVER_DATA_SOURCE_CLIENT']; ?>:</label>
                                                    <div class="col-md-9">
                                                        <div class="form-control-static" id="takeoverDataSourceAgentDes"></div>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_CM_VOL_CDP_TAKEOVER_TIME']; ?>:</label>
                                                    <div class="col-md-9">
                                                        <div class="form-control-static" id="takeoverTimeDes"></div>
                                                    </div>
                                                </div>
                                                <h4 class="form-section"></h4>
                                                <div class="form-group mb0 mt10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_CM_CDP_TAKEOVER_MODE']; ?>:</label>
                                                    <div class="col-md-9">
                                                        <div class="form-control-static" id="targetTypeDes"></div>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_CM_VOL_CDP_TAKEOVER_TARGET_HOST']; ?>:</label>
                                                    <div class="col-md-9">
                                                        <div class="form-control-static" id="takeoverTargetHostDes"></div>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10 display-none takeoverNetwork_div">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_BACKUP_TAKEOVER_NET_CONF']; ?>:</label>
                                                    <div class="col-md-9">
                                                        <div class="form-control-static" id="takeoverNetworkDes"></div>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10 display-none standbymapConfig_div">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_TAKE_TEMP_AGENT_DESC']; ?>:</label>
                                                    <div class="col-md-9 pl0">
                                                        <div class="form-control-static" id="standbymapDes"></div>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10 targetpointConfig_div">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_TARGET_POINT_CONFIGURE']; ?>:</label>
                                                    <div class="col-md-9">
                                                        <div class="form-control-static" id="targetVolDes"></div>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_APPLICATION_CONFIGURE']; ?>:</label>
                                                    <div class="col-md-9">
                                                        <div class="form-control-static" id="appDes"></div>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_SCRIPT_CONFIGURE']; ?>:</label>
                                                    <div class="col-md-9">
                                                        <div class="form-control-static" id="scriptDes"></div>
                                                    </div>
                                                </div>
                                                <h4 class="form-section"></h4>
                                                <div class="form-group mb0 mt10 transfer_mode_des_div">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TRANSPORT_MODE']; ?></label>
                                                    <div class="col-md-9">
                                                        <div class="form-control-static modeDes"></div>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10 transfer_network_des_div">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_NODE_TRANSFER_NETWORK']; ?></label>
                                                    <div class="col-md-9">
                                                        <div class="form-control-static networkDes"></div>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10 safe_stategy_des_div">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_SAFE_STRATEGY']; ?>:</label>
                                                    <div class="col-md-9">
                                                        <div class="form-control-static safeStrategyDes"></div>
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
                                        <i class="viconfont vicon-shangyibu"></i> <?php echo $LANG['UI_PUBLIC_PREV_STEP']; ?> </a>
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
        <!-- 内置虚拟机 -->
<!--        <div id="vm_machine_modal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">-->
            <?php include('./cm_volcdp_vm_temp_config.php'); ?>
<!--        </div>-->
        <!-- 接管网络配置--modal START -->
        <div id="hmTakeoverNetworkConfModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
            <div class="modal-header ">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title"><i class="iconfont icon-xiansucelve"></i> <?php echo $LANG['UI_VOL_CDP_HOST_IP_SERIVCE_CONF']; ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="portlet-body">
                    <label class="control-label col-md-2"> <?php echo $LANG['UI_VOL_CDP_TASK_IP_SERVICE_CONF']; ?></label>
                    <div class="col-md-10">
                        <div class="portlet">
                            <div class="portlet-body panel panel-default strategy-panel">
                                <div class="panel-group accordion mt10 min-height150" style="overflow-y:auto" id="hm_takeover_ip_server_conf">

                                </div>
                                <div class="alert alert-block alert-info fade in mt-20" style="margin-bottom:0px;" id="">
                                    <button type="button" class="close" data-dismiss="alert"></button>
                                    <ul class="alert-ul">
                                        <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                                        <li><?php echo $LANG['UI_VOL_CDP_NET_CARD_CONF_TIP']; ?> </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <label class="control-label col-md-2"> <?php echo $LANG['UI_VOL_CDP_STANDBY_GATEWAY_CONF']; ?></label>
                    <div class="col-md-10">
                        <div class="portlet">
                            <div class="portlet-body panel panel-default strategy-panel">
                                <div class="panel-group accordion mt10 min-height150" style="overflow-y:auto" id="hm_standby_gateway_conf">

                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- modal-footer -->
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']; ?></button>
                <button type="button" class="btn btn-primary" id="submit_hm_takeover_network_conf"><?php echo $LANG['UI_PUBLIC_YES']; ?></button>
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
<script type="text/javascript" src="./scripts/platform/component/virus_detection.js"></script>
<script type="text/javascript" src="./scripts/plugins/path-tree-selector.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_virus_detection.js"></script>

<script src="./scripts/volcdp/takeover_ip_server_map.js"></script>
<script type="text/javascript" src="./scripts/platform/component/transfer_network.js"></script>
<script src="./scripts/complete_machine_volcdp/cm_volcdp_takeover.js"></script>
<script src="./scripts/complete_machine_volcdp/cm_volcdp_vm_temp_config.js"></script>