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
            <span><?php echo $LANG['WEB_PLATFORM_DES_RECOVERY'] ?></span>
        </a>
    </li>
    <span>
        >
    </span>
    <span class="curent">
        <?php if ($_GET['recovery_type'] == 'vm') {
            // 虚拟机的
            if ($_GET['subtype'] == 1) {
                // 虚拟化
                echo $LANG['UI_INSTANT_RECOVERY_FOR_VM'];
            } elseif ($_GET['subtype'] == 2) {
                // 私有云
                echo $LANG['UI_INSTANT_RECOVERY_FOR_PRIVATE_CLOUD'];
            } else {
                // 公有云
                echo $LANG['UI_INSTANT_RECOVERY_FOR_PUBLIC_CLOUD'];
            }
        } else {
            echo $LANG['UI_INSTANT_RECOVERY_FOR_MACHINE'];
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
                            echo $LANG['UI_PLATFORM_INSTANT_RECOVERY_VM_JOB'];
                        } elseif ($_GET['subtype'] == 2) {
                            // 私有云
                            echo $LANG['UI_PLATFORM_INSTANT_RECOVERY_PRCLOUD_JOB'];
                        } else {
                            // 公有云
                            echo $LANG['UI_PLATFORM_INSTANT_RECOVERY_AWS_JOB'];
                        }
                    } else {
                        echo $LANG['UI_PLATFORM_INSTANT_RECOVERY_MACHINE_JOB'];
                    } ?>
                </div>
            </div>
            <div class="portlet-body form">
                <input type="hidden" id="recovery_type" value="<?php echo $_GET['recovery_type']; ?>" class="display-none"/>
                <input type="hidden" id="task_uuid" value="<?php echo $_GET['task_uuid']; ?>" class="display-none"/>
                <input type="hidden" id="subtype" value="<?php echo $_GET['subtype']; ?>" class="display-none"/>
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
                                    <?php include_once '../component/timepoint_tree.php'; ?>
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
                                                <div class="radio-group col-md-6 col-md-9_en" id="radio_group_1">
                                                    <label class="radio-group__item me-20" value="vmRecovery" style="width: 112px;">
                                                        <i class="viconfont vicon-overview-vm"></i><?php echo $LANG['UI_PLATFORM_VM_VIRTUAL']; ?></label>
                                                    <label class="radio-group__item me-20" value="vmRecovery2" style="width: 112px;">
                                                        <i class="viconfont vicon-overview-private-cloud"></i><?php echo $LANG['UI_PLATFORM_PRIVATE_CLOUD']; ?></label>
                                                    <label class="radio-group__item me-20" value="vmRecovery3" style="width: 112px;">
                                                        <i class="viconfont vicon-overview-plubic-cloud"></i><?php echo $LANG['UI_PLATFORM_PUBLIC_CLOUD']; ?></label>
                                                    <label class="radio-group__item me-20" value="completeMachineRecovery" style="width: 112px;">
                                                        <i class="viconfont vicon-overview-complete-machine"></i><?php echo $LANG['UI_PLATFORM_COMPLETE_MACHINE']; ?></label>
                                                    <label class="radio-group__item" value="vmRecoverys" style="width: 152px;">
                                                        <i class="viconfont vicon-ge_disaster_recovery"></i><?php echo $LANG['UI_VM_MACHINE_MANAGER']; ?></label>
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
                                                <li class="safeLi">
                                                    <a href="#tab_safe" data-toggle="tab">
                                                        <i class="viconfont vicon-anquancelve"></i> <?php echo $LANG['UI_BACKUP_SAFE_STRATEGY']; ?></a>
                                                </li>
                                               <!-- <li class="scriptsLi">
                                                    <a href="#tab_scripts" class="popovers" data-content="脚本配置" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                        <i class="viconfont vicon-jiaobenguanli"></i> 脚本配置</a>
                                                </li>-->
                                                <li class="highLi">
                                                    <a href="#tab_high" data-toggle="tab">
                                                        <i class="viconfont vicon-gaojipeizhi"></i> <?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?></a>
                                                </li>
                                            </ul>
                                            <div class="tab-content">
                                                <!-- 通用策略 -->
                                                <div class="tab-pane active" id="tab_common">
                                                    <div class="panel-body">

                                                        <div class="col-md-12 paddding-l-r-0 display-none ipDiv">
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3" style="padding-right: 6px;">
                                                                    <span class="required">* </span>
                                                                    <?php echo $LANG['UI_INSTANT_VM_OPENSTACK_CONTROLLER_IP'] ?>
                                                                </label>
                                                                <div class="col-md-6 auto-change-scale" style="padding-left:22px;padding-right:22px;">
                                                                    <div class="input-group width-100">
                                                                        <input type="text" maxlength="128" class="form-control width-100-15" name="openstackIP"/>
                                                                        <span class="input-group-btn">
                                                                            <button class="btn btn-sm green-haze" id="testIP" type="button"> <?php echo $LANG['UI_INSTANT_TEST_CONNECTION'] ?></button>
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="help-block"><?php echo $LANG['UI_INSTANT_VM_OPENSTACK_CONTROLLER_IP_TIPS'] ?>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- 加载存储ip配置 -->
                                                        <?php include_once '../component/storage_mount.php'; ?>

                                                    </div>
                                                </div>
                                                <!-- 安全策略 -->
                                                <div class="tab-pane " id="tab_safe">
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
                                                <!-- 脚本配置 -->
                                               <!-- <div class="tab-pane " id="tab_scripts">
                                                    <div class="panel-body" style="padding:16px">
                                                        <div class="row" style="margin: 0;">
                                                            <div class="col-md-2 col-sm-2 col-xs-2 nav-tab-radios">
                                                                <ul class="nav nav-tabs tabs-left">
                                                                    <li class="dwm active" data-type="2"><a href="#script_before" data-toggle="tab" aria-expanded="true">
                                                                            <div class="iradio_square-blue strategy-radio-custom" style="position: relative;"><input type="radio" data-radio="iradio_square-blue" class="icheck" name="bakradio0"></div> 恢复前
                                                                        </a></li>
                                                                    <li class="dwm" data-type="3"><a href="#script_after" data-toggle="tab" aria-expanded="true">
                                                                            <div class="iradio_square-blue strategy-radio-custom" style="position: relative;"><input type="radio" data-radio="iradio_square-blue" class="icheck" name="bakradio0"></div> 恢复后
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
                                                <!-- 高级配置 -->
                                                <div class="tab-pane " id="tab_high">
                                                    <div class="advance-config-wrap">
                                                        <div class="advance-config-wrap__row row" >
                                                            <div class="col-md-2 col-sm-2 col-xs-2 nav-tab-radios">
                                                                <ul class="nav nav-tabs tabs-left">
                                                                    <li class="active overload_protect">
                                                                        <a href="#tab_overload_protect" data-toggle="tab" aria-expanded="false"><?php echo $LANG['UI_STRATEGY_OVER_LOAD_LABEL'] ?></a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            <div class="col-md-10 col-sm-10 col-xs-10 advance-config-wrap__row__content">
                                                                <div class="tab-content">
                                                                    <!-- 过载保护 -->
                                                                    <div class="tab-pane active" id="tab_overload_protect">
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
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_FILE_PATH'] ?>:</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static recovershow">
                                                    </p>
                                                </div>
                                            </div>
                                            <h4 class="form-section"></h4>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_MOUNT_CONFIGURATION']; ?>:</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static amountinfoshow">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mt10 display-none" id="transportmodeshowdiv">
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
<script type="text/javascript" src="./scripts/platform/component/form_validator.js"></script>
<script type="text/javascript" src="./scripts/platform/component/network_config.js"></script>
<script type="text/javascript" src="./scripts/platform/component/driver_check.js"></script>
<script type="text/javascript" src="./assets/global/plugins/ace/src-min-noconflict/ace.js"></script>
<script type="text/javascript" src="./scripts/platform/component/vinScript.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_complete.js"></script>
<script type="text/javascript" src="./scripts/plugins/path-tree-selector.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_virus_detection.js"></script>

<!-- END PAGE LEVEL PLUGINS -->
<?php
if ($_GET['recovery_type'] == 'vm') {
    // 虚拟化
    echo '<script src="./scripts/platform/component/vm_timepoint_tree.js" type="text/javascript"></script>';
} else {
    // 操作系统
    echo '<script src="./scripts/platform/component/os_timepoint_tree.js" type="text/javascript"></script>';
}
?>
<!-- END PAGE LEVEL PLUGINS -->
<script src="./scripts/platform/recovery/instantaneous.js" type="text/javascript"></script>
