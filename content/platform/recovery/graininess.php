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
<link rel="stylesheet" type="text/css" href="./css/platform/component/safe_virus.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/component/style.css" />
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
        <?php if ($_GET['recovery_type'] == 'vm') { // 虚拟机模块
                // 虚拟机的
                if ($_GET['subtype'] == 1) { // 虚拟化
                    echo $LANG['UI_GRAIN_RECOVERY_FOR_VM'];
                } elseif ($_GET['subtype'] == 2) { // 私有云
                    echo $LANG['UI_GRAIN_RECOVERY_FOR_PRIVATE_CLOUD'];
                } else { // 公有云
                    echo $LANG['UI_GRAIN_RECOVERY_FOR_PUBLIC_CLOUD'];
                }
            } else if ($_GET['recovery_type'] == 'cdp') {
                // copy and real need check by permission
                if (in_array('complete_cdp_backup',  $_SESSION['permission']) && !in_array('machine_copy',  $_SESSION['permission'])) {
                    // 整机实时保护细粒度恢复
                    $lang = $LANG['UI_PLATFORM_GRAIN_RECOVERY_VOL_MACHINE_JOB1_'];
                } elseif (in_array('complete_cdp_backup',  $_SESSION['permission']) && in_array('machine_copy',  $_SESSION['permission'])) {
                    // 整机实时保护&复制细粒度恢复任务
                    $lang = $LANG['UI_PLATFORM_GRAIN_RECOVERY_VOL_MACHINE_JOB_'];
                } else {
                    // 整机实时复制细粒度恢复
                    $lang = $LANG['UI_PLATFORM_GRAIN_RECOVERY_VOL_MACHINE_JOB2_'];
                }
                echo $lang;
            } else {
                echo $LANG['UI_GRAIN_RECOVERY_FOR_MACHINE']; // 整机
            } ?>
    </span>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row recover-page">
    <div class="col-md-12" style="height:100%;">
        <div class="portlet box blue-hoki" id="vmrecovercontent">
                <input id="recovery_type" value="<?php echo $_GET['recovery_type']; ?>" class="display-none"/>
                <input type="hidden" id="subtype" value="<?php echo $_GET['subtype']; ?>" class="display-none"/>
                <input id="externalPointUuid" value="<?php echo $_GET['point_uuid']; ?>" class="display-none"></input>
                <input id="externalTaskUuid" value="<?php echo $_GET['task_uuid']; ?>" class="display-none"></input>
                <input id="externalItemUuid" value="<?php echo $_GET['item_uuid']; ?>" class="display-none"></input>
                <input id="externalSubType" value="<?php echo $_GET['sub_type']; ?>" class="display-none"></input>
            <div class="portlet-title">
                <div class="caption">
                    <i class="levelchild viconfont vicon-huifu1"></i>
                    <?php if ($_GET['recovery_type'] == 'vm') {
                        // 虚拟机的
                        if ($_GET['subtype'] == 1) {
                            // 虚拟化
                            echo $LANG['UI_PLATFORM_GRAIN_RECOVERY_VM_JOB'];
                        } elseif ($_GET['subtype'] == 2) {
                            // 私有云
                            echo $LANG['UI_PLATFORM_GRAIN_RECOVERY_PRCLOUD_JOB'];
                        } else {
                            // 公有云
                            echo $LANG['UI_PLATFORM_GRAIN_RECOVERY_AWS_JOB'];
                        }
                    } else if ($_GET['recovery_type'] == 'cdp') {
                        // 实时
                        // copy and real need check by permission
                        if (in_array('complete_cdp_backup',  $_SESSION['permission']) && !in_array('machine_copy',  $_SESSION['permission'])) {
                            // 新建整机实时保护细粒度恢复
                            $lang = $LANG['UI_PLATFORM_GRAIN_RECOVERY_VOL_MACHINE_JOB1'];
                        } elseif (in_array('complete_cdp_backup',  $_SESSION['permission']) && in_array('machine_copy',  $_SESSION['permission'])) {
                            // 新建整机实时保护&复制细粒度恢复任务
                            $lang = $LANG['UI_PLATFORM_GRAIN_RECOVERY_VOL_MACHINE_JOB'];
                        } else {
                            // 新建整机实时复制细粒度恢复
                            $lang = $LANG['UI_PLATFORM_GRAIN_RECOVERY_VOL_MACHINE_JOB2'];
                        }
                        echo $lang;
                    } else {
                        echo $LANG['UI_PLATFORM_GRAIN_RECOVERY_MACHINE_JOB'];
                    } ?>
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
                                            <?php echo $LANG['UI_VOL_CDP_RECOVERY_DATA_SOURCE'] ?>
                                            <i class="fa fa-check"></i>
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#tab2" data-toggle="tab" class="step active">
                                        <span class="number">2 </span>
                                        <span class="desc">
                                            <?php echo $LANG['UI_RECOVERY_STRATEGY'] ?>
                                            <i class="fa fa-check"></i>
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#tab3" data-toggle="tab" class="step">
                                        <span class="number">3 </span>
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

                                <!-- 策略 -->
                                <div class="tab-pane" id="tab2">
                                    <div class="row row-stepthree">
                                        <div class="tabbable-custom col-md-offset-1 col-md-10">
                                            <ul class="nav nav-tabs">
                                                <li class="commonLi active">
                                                    <a href="#tab_common" data-toggle="tab">
                                                        <i class="viconfont vicon-celve1"></i> <?php echo $LANG['UI_BACKUP_COMMON_STRATEGY'] ?> </a>
                                                </li>
                                                <li class="transferLi">
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
                                                            <label class="control-label col-md-2"><?php echo $LANG['UI_PLATFORM_RECOVERY_OS_TYPE']; ?>
                                                            </label>
                                                            <div class="col-md-4">
                                                                <select class="form-control  input-sm" id="recoverostype">
                                                                    <option value=""><?php echo $LANG['UI_PUBLIC_SELECT'] ?></option>
                                                                    <option value="Linux">Linux</option>
                                                                    <option value="Windows">Windows</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!--安全策略-->
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
                                <div class="tab-pane" id="tab3">
                                    <div class="tab-pane__body">
                                        <div class="tab-pane__body__form">
                                            <div class="alert alert-danger display-none jobnametip"></div>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_JOB_RNAME'] ?>:</label>
                                                <div class="col-md-9">
                                                    <div class="input-icon right">
                                                        <input type="text" maxlength="64" class="form-control" name="job_name" id="job_name" onkeyup="customInputValidate('string', this.value, $(this))"/>
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
                                            <div class="form-group div-vol-cdp-reovery-time mb0 display-none">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_RECOVERY_TIME_POINT'] ?>:</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static vol-cdp-reovery-time">
                                                    </p>
                                                </div>
                                            </div>
                                            <h4 class="form-section"></h4>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_COMMON_STRATEGY'] ?>:</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static reserveostypeshow">
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
<script type="text/javascript" src="./assets/global/plugins/bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<?php
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
<script type="text/javascript" src="./scripts/platform/component/safe_complete.js"></script>
<script type="text/javascript" src="./scripts/plugins/path-tree-selector.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_virus_detection.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<?php
if ($_GET['recovery_type'] == 'cdp') {
    // 实时
    echo '<script src="./scripts/platform/component/cdp_timepoint_tree.js" type="text/javascript"></script>';
    $jsCode = <<<JS
                <script>
               // 初始化第一步选择时间点
    $(document).ready(function() {
        RecoverTimepoint.init({jobType: 4});
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
            moreChoose: false, // 是否多选
            jobType: 4,
            instantaneous: false // 是否包含瞬时恢复快照点
        };
        RecoverTimepoint.init(data);
    });
                </script>
JS;
}
echo $jsCode;
?>

<script src="./scripts/platform/recovery/graininess.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/public/initComponents.js"></script>