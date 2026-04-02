<?php include_once '../../../tpl/permission.php';
include_once '../public/bs_table.php';
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
<link rel="stylesheet" type="text/css" href="./css/platform/component/safe_virus.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/component/style.css" />
<link href="/assets/global/plugins/select2/select2-4.1.0/css/select2.min.css"rel="stylesheet" type="text/css"/>
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="recovery" href="./content/recovery/recoverycenter.php">
            <span><?php echo $LANG['WEB_PLATFORM_DES_RECOVERY']; ?></span>
        </a>
    </li>
    <span>
        >
    </span>
    <span class="curent">
        <?php if ($_GET['recovery_type'] == 'vm') {
            if ($_GET['subtype'] == 1) {
                // 虚拟化
                echo $LANG['UI_CROSS_PLATFORM_RECOVERY_FOR_VM'];
            } elseif ($_GET['subtype'] == 2) {
                // 私有云
                echo $LANG['UI_CROSS_PLATFORM_RECOVERY_FOR_PRIVATE_CLOUD'];
            } else {
                // 公有云
                echo $LANG['UI_CROSS_PLATFORM_RECOVERY_FOR_PUBLIC_CLOUD'];
            }
        } else if ($_GET['recovery_type'] == 'cdp') {
            // 实时
            // copy and real need check by permission
            if (in_array('complete_cdp_backup',  $_SESSION['permission']) && !in_array('machine_copy',  $_SESSION['permission'])) {
                // 整机实时保护跨平台恢复
                $lang = $LANG['UI_PLATFORM_RECOVERY_VOL_MACHINE_JOB1'];
            } elseif (in_array('complete_cdp_backup',  $_SESSION['permission']) && in_array('machine_copy',  $_SESSION['permission'])) {
                // 整机实时保护&复制跨平台恢复任务
                $lang = $LANG['UI_PLATFORM_RECOVERY_VOL_MACHINE_JOB_'];
            } else {
                // 整机实时复制跨平台恢复
                $lang = $LANG['UI_PLATFORM_RECOVERY_VOL_MACHINE_JOB2'];
            }
            echo $lang;
        } else {
            echo $LANG['UI_CROSS_PLATFORM_RECOVERY_FOR_MACHINE'];
        } ?>
    </span>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row recover-page">
    <div class="col-md-12" style="height:100%;">
        <div class="portlet box blue-hoki" id="vmrecovercontent">
            <div class="portlet-title">
                <div class="caption">
                    <i class="levelchild viconfont vicon-huifu1"></i>
                    <?php if ($_GET['recovery_type'] == 'vm') {
                        // 虚拟机的
                        if ($_GET['subtype'] == 1) {
                            // 虚拟化
                            echo $LANG['UI_PLATFORM_RECOVERY_VM_JOB'];
                        } elseif ($_GET['subtype'] == 2) {
                            // 私有云
                            echo $LANG['UI_PLATFORM_RECOVERY_PRCLOUD_JOB'];
                        } else {
                            // 公有云
                            echo $LANG['UI_PLATFORM_RECOVERY_AWS_JOB'];
                        }
                    } else if ($_GET['recovery_type'] == 'cdp') {
                        // 实时
                        // copy and real need check by permission
                        if (in_array('complete_cdp_backup',  $_SESSION['permission']) && !in_array('machine_copy',  $_SESSION['permission'])) {
                            // 新建整机实时保护跨平台恢复
                            $lang = $LANG['UI_PLATFORM_RECOVERY_VOL_MACHINE_JOB1'];
                        } elseif (in_array('complete_cdp_backup',  $_SESSION['permission']) && in_array('machine_copy',  $_SESSION['permission'])) {
                            // 新建整机实时保护&复制跨平台恢复任务
                            $lang = $LANG['UI_PLATFORM_RECOVERY_VOL_MACHINE_JOB'];
                        } else {
                            // 新建整机实时复制跨平台恢复
                            $lang = $LANG['UI_PLATFORM_RECOVERY_VOL_MACHINE_JOB2'];
                        }
                        echo $lang;
                    } else {
                        echo $LANG['UI_PLATFORM_RECOVERY_MACHINE_JOB'];
                    } ?>
                </div>
            </div>
            <div class="portlet-body form">
                <input type="hidden" id="recovery_type" value="<?php echo $_GET['recovery_type']; ?>" class="display-none"/>
                <input type="hidden" id="subtype" value="<?php echo $_GET['subtype']; ?>" class="display-none"/>
                <input type="hidden" id="recovery_job_type" value="1" class="display-none"/>
                <input id="externalPointUuid" value="<?php echo $_GET['point_uuid']; ?>" class="display-none"></input>
                <input id="externalTaskUuid" value="<?php echo $_GET['task_uuid']; ?>" class="display-none"></input>
                <input id="externalItemUuid" value="<?php echo $_GET['item_uuid']; ?>" class="display-none"></input>
                <input id="externalSubType" value="<?php echo $_GET['sub_type']; ?>" class="display-none"></input>
                <form action="#" class="form-horizontal" id="submit_form" method="POST">
                    <div class="form-wizard">
                        <div class="form-body">
                            <ul class="nav nav-pills steps" >
                                <li>
                                    <a href="#tab1" data-toggle="tab" class="step">
                                        <span class="number">1 </span>
                                        <span class="desc">
                                            <?php echo $LANG['UI_VOL_CDP_RECOVERY_DATA_SOURCE'] ?>
                                            <i class="fa fa-check"></i>
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#tab2" data-toggle="tab" class="step active">
                                        <span class="number">2 </span>
                                        <span class="desc">
                                            <?php echo $LANG['UI_RECOVERY_GOAL'] ?>
                                            <i class="fa fa-check"></i>
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#tab3" data-toggle="tab" class="step">
                                        <span class="number">3 </span>
                                        <span class="desc">
                                            <?php echo $LANG['UI_RECOVERY_STRATEGY'] ?>
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
                                    <?php
                                    if ($_GET['recovery_type'] == 'cdp') {
                                        include_once '../component/cdp_timepoint_tree.php';
                                    } else {
                                        include_once '../component/timepoint_tree.php';
                                    }
                                    ?>
                                </div>
                                <!-- 恢复目标 -->
                                <div class="tab-pane" id="tab2">
                                    <div class="alert alert-danger display-none setrecover2tip">
                                    </div>
                                    <div class="row tab-pane__row">
                                        <div class="col-md-12 col-steptwo">
                                            <!-- 选择目标类型 -->
                                            <div class="form-group">
                                                <label class="control-label col-md-3 mt-0 ptlb20"><?php echo $LANG['UI_PLATFORM_RECOVERY_SELECT_TARGET_TYPE']; ?></label>
                                                <div class="radio-group col-md-6" id="radio_group_1">
                                                    <label class="radio-group__item me-20" value="vmRecovery" style="width: 112px;">
                                                        <i class="viconfont vicon-overview-vm"></i><?php echo $LANG['UI_PLATFORM_VM_VIRTUAL']; ?>
                                                    </label>
                                                    <label class="radio-group__item me-20" value="vmRecovery2" style="width: 112px;">
                                                        <i class="viconfont vicon-overview-private-cloud"></i><?php echo $LANG['UI_PLATFORM_PRIVATE_CLOUD']; ?>
                                                    </label>
                                                    <label class="radio-group__item me-20" value="vmRecovery3" style="width: 112px;">
                                                        <i class="viconfont vicon-overview-plubic-cloud"></i><?php echo $LANG['UI_PLATFORM_PUBLIC_CLOUD']; ?>
                                                    </label>
                                                    <label class="radio-group__item" value="completeMachineRecovery" style="width: 112px;">
                                                        <i class="viconfont vicon-overview-complete-machine"></i><?php echo $LANG['UI_PLATFORM_COMPLETE_MACHINE']; ?>
                                                    </label>
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
                                                        <i class="viconfont vicon-anquancelve"></i> <?php echo $LANG['UI_BACKUP_SAFE_STRATEGY']; ?> </a>
                                                </li>
                                                <li class="highLi">
                                                    <a href="#tab_high" data-toggle="tab">
                                                        <i class="viconfont vicon-gaojipeizhi"></i> <?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?></a>
                                                </li>
                                            </ul>
                                            <div class="tab-content">
                                                <!-- 通用策略 -->
                                                <div class="tab-pane active" id="tab_common">
                                                    <div class="panel-body">
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
                                                                                        <label class="control-label col-md-2"><?php echo $LANG['UI_RECOVERY_START_TYPE'] ?>
                                                                                        </label>
                                                                                        <div class="col-md-4">
                                                                                            <select class="form-control  input-sm" id="recovertype">
                                                                                                <option value="1"><?php echo $LANG['UI_RECOVERY_START_TYPE_NOW'] ?></option>
                                                                                                <option value="2"><?php echo $LANG['UI_RECOVERY_START_TYPE_TIMING'] ?></option>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="form-group display-hide setOnceTime">
                                                                                        <div class="onceTime-content">
                                                                                            <label class="control-label col-md-2">
                                                                                                <span class="required">* </span>
                                                                                                <?php echo $LANG['UI_BACKUP_SET_TIME'] ?>
                                                                                            </label>
                                                                                            <div class="onceTime-content-input">
                                                                                                <div class="input-group date form_datetime">
                                                                                                    <input type="text" size="16" readonly id="oncetime" class="form-control input-sm"
                                                                                                           style="width: 160px;">
                                                                                                    <span class="input-group-btn">
                                                                                                        <button class="btn default input-sm" id="resetdate" type="button"><i
                                                                                                                    class="fa fa-times"></i></button>
                                                                                                        <button class="btn default date-set input-sm" type="button"><i
                                                                                                                    class="viconfont vicon-ge_calendar"></i></button>
                                                                                                    </span>
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="onceTime-content-tips">
                                                                                                <a class="popovers " data-container="body" data-trigger="hover" data-placement="right"
                                                                                                   data-content="<?php echo $LANG['UI_RECOVERY_TYPE_TIMING_TIME'] ?>"
                                                                                                   data-original-title="" title="">
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
                                                            <!-- 传输模式 cdp专用-->
                                                            <div class="form-group cdp-special transportdiv">
                                                                <label class="control-label col-md-3 cdp_transferlabel"><?php echo $LANG['UI_BACKUP_TRANSPORT_MODE']; ?></label>
                                                                <div class="col-md-4">
                                                                    <select class="form-control" id="cdp_transport_mode">
                                                                        <option value="nbd"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD']; ?></option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-2 mt5">
                                                                    <a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_ICS_VVDK_SELECT_TIPS']; ?>" data-original-title="" title="">
                                                                        <i class="viconfont vicon-tishi"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                            <!-- 加密传输 -->
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3 encrypttransferlabel form-group-label"><?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER'] ?></label>
                                                                <div class="col-md-4 form-group-content">
                                                                    <input type="checkbox" id="agent_encrypttransfer" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                </div>
                                                            </div>
                                                            <!-- 传输加密算法 cdp隐藏 -->
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

                                                            <!-- 传输压缩 cdp专用 -->
                                                            <div class="form-group cdp-special trancompressdiv">
                                                                <label class="control-label col-md-3 cdp_transfercompress form-group-label"><?php echo $LANG['UI_COPY_COMPRESS_TRANSFER']; ?></label>
                                                                <div class="col-md-4 form-group-content">
                                                                    <input type="checkbox" id="cdp_tran_compress_switch" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>">
                                                                    <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_TRANSMISSION_COMPRESSION_TIPS']; ?>">
                                                                        <i class="viconfont vicon-tishi"></i>
                                                                    </a>
                                                                </div>
                                                            </div>

                                                            <!-- 压缩等级选择  cdp专用 -->
                                                            <div class="form-group transferCompressGradeDiv display-none">
                                                                <label class="control-label col-md-3 cdp_transfer-compress-grade-label"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE'] ?></label>
                                                                <div class="col-md-4">
                                                                    <select class="form-control input-sm" id="cdp_transferCompressGrade">
                                                                        <option value="1"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_FAST'] ?></option>
                                                                        <option value="2"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_NORMAL'] ?></option>
                                                                        <option value="3"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_BETTER'] ?></option>
                                                                        <option value="4"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_BEST'] ?></option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <!--传输线程-->
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3 threadnumlabel"><?php echo $LANG['UI_BACKUP_THREAD_NUM'] ?>
                                                                </label>
                                                                <div class="col-md-4">
                                                                    <div id="agent_recoveryThreadDiv">
                                                                        <div class="input-group spinner-group">
                                                                            <input type="text" id="agent_recoveryThreadNum" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" >
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
                                                                       data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_THREAD_NUM_TIPS'] ?>">
                                                                        <i class="viconfont vicon-tishi"></i>
                                                                    </a>
                                                                </div>
                                                            </div>

                                                            <!-- 传输数据包大小 cdp专用 -->
                                                            <div class="form-group cdp-special transferDataPackage">
                                                                <label class="control-label col-md-3 cdp_transferdatapackage"><?php echo $LANG['UI_VOL_CDP_TRANSMISSION_PACKET_SIZE']; ?>
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

                                                    <div class="vm-transport-strategy">
                                                        <!--虚拟化平台的代码-->
                                                        <?php include_once '../component/vm_transfer_strategy.php'; ?>
                                                    </div>

                                                    <div class="public_cloud-transport-strategy display-none">
                                                        <!--公有云平台的代码-->
                                                        <?php include_once '../component/aws_transfer_strategy.php'; ?>
                                                    </div>

                                                </div>

                                                <!--安全策略-->
                                                <div class="tab-pane" id="tab_safe">
                                                    <div class="panel-body">
                                                        <div class="tabbable-custom pdlr15  col-md-10" >
                                                            <div class="form-group">
                                                                <div id="virusConfig"></div>
                                                            </div>
                                                            <div class="form-group">
                                                                <div id="completeConfig"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- 高级配置 -->
                                                <div class="tab-pane " id="tab_high">
                                                    <div class="advance-config-wrap">
                                                        <div class="advance-config-wrap__row row" >
                                                            <div class="col-md-2 col-sm-2 col-xs-2 nav-tab-radios">
                                                                <ul class="nav nav-tabs tabs-left">
                                                                    <li class="active retry_strategy">
                                                                        <a href="#retry_config" data-toggle="tab" aria-expanded="false"><?php echo $LANG['UI_STRATEGY_RETRY'] ?></a>
                                                                    </li>
                                                                    <li class="overload_protect">
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
                                                                                <div class="col-md-1 form-group-content">
                                                                                    <input type="checkbox" id="ignore_resource_limit"  class="make-switch" data-on-color="primary" data-off-color="info" data-size="small"
                                                                                           data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                </div>
                                                                                <div class="col-md-1 vmbackup-mt10_en mt5_cn">
                                                                                    <a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right"
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
                                                        <input type="text" maxlength="64" name="job_name" class="form-control" id="job_name" onkeyup="customInputValidate('string', this.value, $(this))"/>
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
                                            <div class="form-group mb0 mt10 vm_final_div">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_FILE_PATH'] ?>:</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static recovershow">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mt10 vm_final_div renameshowdiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_VIRTUAL_MACHINE_RESET_HOSTNAME'] ?>:</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static renameshow">
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="form-group mb0 mt10 os_final_div">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_GOAL'] ?>:</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static recovershow2">
                                                    </p>
                                                </div>
                                            </div>

                                            <h4 class="form-section"></h4>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_START_TYPE'] ?>:</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static reservetypeshow">
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
    </div>
</div>
<!-- END PAGE CONTENT-->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="/assets/global/plugins/select2/select2-4.1.0/js/select2.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<?php
if ($_SESSION['language'] != "zh-cn" && $_SESSION['language'] != "zh-tw") {
    //如果不是英文,加载语言包
    echo '<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/i18n/defaults-en_US.min.js"></script>';
}
?>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
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
<script type="text/javascript" src="./scripts/platform/component/safe_complete.js"></script>
<script type="text/javascript" src="./scripts/plugins/path-tree-selector.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_virus_detection.js"></script>
<script type="text/javascript" src="./scripts/platform/component/vm_recovery_transfer.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<?php
if ($_GET['recovery_type'] == 'cdp') {
    // 实时
    echo '<script src="./scripts/platform/component/cdp_timepoint_tree.js" type="text/javascript"></script>';
    $jsCode = <<<JS
                <script>
               // 初始化第一步选择时间点
    $(document).ready(function() {
        RecoverTimepoint.init({jobType: 1});
    });
                </script>
JS;
} else {
    if ($_GET['recovery_type'] == 'vm') {
        // 虚拟化
        echo '<script src="./scripts/platform/component/vm_timepoint_tree.js" type="text/javascript"></script>';
    } else {
        // 操作系统
        echo '<script src="./scripts/platform/component/os_timepoint_tree.js" type="text/javascript"></script>';
    }
    // 验证成功 JS 结束标记前面不能用空格
    $jsCode = <<<JS
                <script>
               // 初始化第一步选择时间点
    $(document).ready(function() {
        var data = {
            moreChoose: true, // 是否多选
            instantaneous: false, // 是否包含瞬时恢复快照点
            jobType: 1
        };
        RecoverTimepoint.init(data);
    });
                </script>
JS;
}
echo $jsCode;
?>

<!-- END PAGE LEVEL PLUGINS -->
<script src="./scripts/platform/recovery/platform.js" type="text/javascript"></script>
