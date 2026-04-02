<?php include_once '../../tpl/permission.php'; ?>
<link rel="stylesheet" type="text/css"
    href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css" />
<link rel="stylesheet" type="text/css"
    href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
<link rel="stylesheet" type="text/css"
    href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-select/bootstrap-select.css" />
<link rel="stylesheet" type="text/css"
    href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<link rel="stylesheet" type="text/css" href="./css/fs/filetype-small.css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet"
    type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="./scripts/plugins/calendar/jquery.searchableSelect.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/component/resource_pool.css" />
<?php
session_start();
session_commit();
$authfun = $_SESSION['authfun'];
?>

<!-- BEGIN PAGE CONTENT-->
<div class="row" style="height: 100%;">
    <div class="col-md-12" style="height: 100%;">
    <input id="pageUri" value="<?php echo $_SERVER['REQUEST_URI']; ?>" class="display-none" />
        <div class="portlet box blue-hoki backup-page" id="obs_backup_content">
            <div class="portlet-title">
                <div class="caption">
                    <i class="levelchild viconfont  vicon-xinjianbeifen1"></i>
                    <span class="caption_text"></span>
                </div>
            </div>
            <div class="portlet-body form">
                <form action="#" class="form-horizontal" id="obs_backup_form" method="POST">
                    <div class="form-wizard">
                        <!-- BEGIN FORM BODY -->
                        <div class="form-body">
                            <div class="form-body-content">
                                <ul class="nav nav-pills steps">
                                    <li>
                                        <a href="#tab1" data-toggle="tab" class="step">
                                            <span class="number">1 </span>
                                            <span class="desc">
                                                <?php echo $LANG['UI_BACKUP_SOURCE'] ?>
                                                <i class="fa fa-check"></i>
                                            </span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#tab2" data-toggle="tab" class="step">
                                            <span class="number">2 </span>
                                            <span class="desc">
                                                <?php echo $LANG['UI_BACKUP_DATA_DES'] ?>
                                                <i class="fa fa-check"></i>
                                            </span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#tab3" data-toggle="tab" class="step active">
                                            <span class="number">3 </span>
                                            <span class="desc">
                                                <?php echo $LANG['UI_JOB_STRATEGY_INFO'] ?>
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
                                    <!-- BEGIN TAB ONE BACKUP SOURCE -->
                                    <div class="tab-pane active" id="tab1">
                                        <div class="row tab-pane__row">
                                            <div class="col-md-4 tab-pane__row__source">
                                                <div class="form-group src-wrap">
                                                    <div class="src-wrap__title">
                                                        <span>
                                                            <i
                                                                class="levelchild viconfont vicon-a-Instructionzhiling-01"></i>
                                                            <?php echo $LANG['UI_OBS_SELECT_BACKUP_SOURCE_OBS'] ?>
                                                        </span>
                                                    </div>
                                                    <div class="src-wrap__content">
                                                        <div class="src-wrap__content__search">
                                                            <div class="alert alert-block alert-info fade in display-none"
                                                                id="noagent">
                                                                <button type="button" class="close"
                                                                    data-dismiss="alert"></button>
                                                                <h4 class="alert-heading">
                                                                    <strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong>
                                                                </h4>
                                                                <ol class="alert-ol">
                                                                    <li>
                                                                        <?php echo $LANG['UI_BACKUP_OBS_PLEASE_ADD_NEW_AGENT_TIPS2'] ?>
                                                                    </li>
                                                                    <li>
                                                                        <a id="toAdd"
                                                                            class="alert-link"><?php echo $LANG['UI_BACKUP_OBS_PLEASE_ADD_NEW_AGENT_TIPS1'] ?></a>
                                                                    </li>
                                                                </ol>
                                                            </div>
                                                            <div class="vm_tree_div">
                                                                <div class="searchDiv width100p display-none">
                                                                    <div class="input-icon mt-0">
                                                                        <input type="text" maxlength="128"
                                                                        placeholder="<?php echo $LANG['UI_COPY_SEARCH_AS_KEYWORD'] ?>"
                                                                        class="form-control" id="searchAgent"
                                                                        style="margin: 0" onkeyup="customInputValidate('string', this.value, $(this))">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="src-wrap__content__ztree">
                                                            <div class="three_tree vcenter-tree">
                                                                <ul id="obs_tree" class="ztree"></ul>
                                                            </div>
                                                            <div class="alert alert-block alert-info fade in display-hide"
                                                                id="nosearchtips">
                                                                <button type="button" class="close"
                                                                    data-dismiss="alert"></button>
                                                                <ul class="alert-ul">
                                                                    <strong
                                                                        class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                                                                    <li>
                                                                        <?php echo $LANG['UI_DATA_NOSEARCH_TIPS'] ?>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="alert alert-block alert-info fade in" id="step1tips">
                                                    <button type="button" class="close" data-dismiss="alert"></button>
                                                    <h4 class="alert-heading">
                                                        <strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong>
                                                    </h4>
                                                    <ol class="alert-ol">
                                                        <li><?php echo $LANG['UI_BACKUP_OBS_BAK_TIPS1'] ?></li>
                                                        <li><?php echo $LANG['UI_BACKUP_OBS_BAK_TIPS2'] ?></li>
                                                        <li><?php echo $LANG['UI_BACKUP_OBS_BAK_TIPS3'] ?></li>
                                                    </ol>
                                                </div>
                                            </div>
                                            <div class="col-md-8 display-hide VMList-div" id="agentfilediv">
                                                <div class="addTitle">
                                                    <span><i class="viconfont vicon-ge_folder"></i>
                                                        <?php echo $LANG['UI_BACKUP_OBS_BAC_SRC'] ?></span>
                                                </div>
                                                <div class="file-source-list" id="allFileTree"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- END TAB ONE BACKUP SOURCE -->
                                    <!-- BEGIN TAB TWO BACKUP DESTINATION -->
                                    <div class="tab-pane" id="tab2">
                                        <div class="backup-destination-wrap row">
                                            <div class="col-md-9 hp-100" id="backupTarget"></div>
                                        </div>
                                    </div>
                                    <!-- END TAB TWO BACKUP DESTINATION -->
                                    <!-- BEGIN TAB THREE BACKUP STRATEGY -->
                                    <div class="tab-pane" id="tab3">
                                        <div class="row row-stepthree">
                                            <div class="nav-tabs-wrapper col-md-offset-1 col-md-10">
                                                <ul class="nav nav-tabs nav-line-tabs">
                                                    <li class="commonLi active nav-item">
                                                        <a href="#tab_common" class="nav-link" data-toggle="tab">
                                                            <i class="viconfont vicon-celve1"></i>
                                                            <?php echo $LANG['UI_BACKUP_COMMON_STRATEGY'] ?> </a>
                                                    </li>
                                                    <li class="transferLi nav-item">
                                                        <a href="#tab_transfer" class="nav-link" data-toggle="tab">
                                                            <i class="viconfont vicon-a-chuanshu"></i>
                                                            <?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?> </a>
                                                    </li>
                                                    <li class="safetyLi nav-item">
                                                        <a href="#tab_safety" class="nav-link" data-toggle="tab">
                                                            <i class="viconfont vicon-anquancelve"></i> <?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?> 
                                                        </a>
                                                    </li>
                                                    <li class="highLi nav-item">
                                                        <a href="#tab_other" class="nav-link" data-toggle="tab">
                                                            <i class="viconfont vicon-gaojipeizhi"></i>
                                                            <?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?> </a>
                                                    </li>
                                                </ul>
                                                <div class="tab-content hover-scroll-y">
                                                    <!-- BEGIN COMMON STRATEGY -->
                                                    <div class="tab-pane active" id="tab_common">
                                                        <div class="panel-body">
                                                            <div class="form-group">
                                                                <div class="control-label col-md-1">
                                                                    <?php echo $LANG['UI_BACKUP_SELECT_STRATEGY'] ?>
                                                                </div>
                                                                <div class="col-md-4" id="obs_backup_strategy_select">
                                                                    <select class="form-control select2me inline-block"
                                                                        id="strategySelect">
                                                                    </select>
                                                                    <a class="popovers ml15" data-container="body"
                                                                        data-trigger="hover" data-placement="right"
                                                                        data-content="<?php echo $LANG['UI_BACKUP_SELECT_STRATEGY_TIPS'] ?>">
                                                                        <i class="viconfont vicon-tishi"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                            <!-- BEGIN COMMON STRATEGY - TIME -->
                                                            <div class="form-group">
                                                                <div
                                                                    class="col-md-offset-1 col-md-10 accordion strategyOne">
                                                                    <?php include('../platform/strategy/timeStrategy.php'); ?>
                                                                </div>
                                                            </div>
                                                            <!-- END COMMON STRATEGY - TIME -->
                                                            <!-- BEGIN COMMON STRATEGY - SPEED --> 
                                                            <div class="form-group speedlimitDiv">
                                                                <div
                                                                    class="col-md-offset-1 col-md-10 accordion strategyOne">
                                                                    <div class="panel panel-default strategy-panel">
                                                                        <div class="panel-heading">
                                                                            <h4 class="panel-title">
                                                                                <a class="accordion-toggle accordion-toggle-styled collapsed popovers"
                                                                                    data-container="body"
                                                                                    data-trigger="hover"
                                                                                    data-placement="top"
                                                                                    data-toggle="collapse"
                                                                                    data-parent=".strategyOne"
                                                                                    href="#speed" aria-expanded="true">
                                                                                    <i
                                                                                        class="viconfont vicon-xiansucelve1 font-green-seagreen"></i>
                                                                                    <span
                                                                                        class="font-green-seagreen"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?></span>
                                                                                    <span
                                                                                        class="strategyDes speedlimitDes"></span>
                                                                                </a>
                                                                            </h4>
                                                                        </div>
                                                                        <?php include_once('../../content/platform/global_strategy/speedJob.php'); ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- END COMMON STRATEGY - SPEED -->

                                                            <!-- BEGIN COMMON STRATEGY - STORAGE -->
                                                            <div class="form-group form-group-panel-storage">
                                                                <div class="col-md-offset-1 col-md-10 ">
                                                                    <?php include('../platform/strategy/storeStrategy.php') ?>
                                                                </div>
                                                            </div>
                                                            <!-- END COMMON STRATEGY - STORAGE -->

                                                            <!-- BEGIN COMMON STRATEGY - RESERVE -->
                                                            <div class="form-group form-group-panel-reserve">
                                                                <div class="col-md-offset-1 col-md-10 ">
                                                                    <?php include('../platform/strategy/reserveStrategy.php') ?>
                                                                </div>
                                                            </div>
                                                            <!-- END COMMON STRATEGY - RESERVE -->

                                                            <!-- BEGIN TAPE GROUP STRATEGY -->
                                                            <div class="form-group form-group-panel-tape display-none">
                                                                <div class="col-md-offset-1 col-md-10 ">
                                                                    <div class="accordion strategyOne">
                                                                        <div class="panel panel-default strategy-panel">
                                                                            <div class="panel-heading">
                                                                                <h4 class="panel-title">
                                                                                    <a class="accordion-toggle accordion-toggle-styled popovers collapsed"
                                                                                        data-container="body"
                                                                                        data-trigger="hover"
                                                                                        data-placement="top"
                                                                                        data-toggle="collapse"
                                                                                        data-parent=".strategyOne"
                                                                                        href="#obs_tape"
                                                                                        aria-expanded="true"
                                                                                        data-original-title="" title="">
                                                                                        <i
                                                                                            class="iconfont icon-baoliu font-green-seagreen"></i>
                                                                                        <span
                                                                                            class="font-green-seagreen"><?php echo $LANG['UI_TAPE_GROUP_STRATEGY'] ?></span>
                                                                                    </a>
                                                                                </h4>
                                                                            </div>
                                                                            <div id="obs_tape"
                                                                                class="panel-collapse collapse"
                                                                                aria-expanded="true">
                                                                                <div class="panel-body">
                                                                                    <div class="col-md-12">
                                                                                        <div
                                                                                            class="form-group form-group-tape-strategy">
                                                                                            <label
                                                                                                class="control-label col-md-4">
                                                                                                <?php echo $LANG['UI_TAPE_SELECT_GENERATE_STRATEGY'] ?>:
                                                                                            </label>
                                                                                            <div class="col-md-4 mt5">
                                                                                                <span
                                                                                                    class="form-group-tape-strategy__value generate-strategy"></span>
                                                                                            </div>
                                                                                        </div>

                                                                                        <div
                                                                                            class="form-group form-group-tape-strategy">
                                                                                            <label
                                                                                                class="control-label col-md-4">
                                                                                                <?php echo $LANG['UI_TAPE_RESERVE_STRATEGY'] ?>:
                                                                                            </label>
                                                                                            <div class="col-md-4 mt5">
                                                                                                <span
                                                                                                    class="form-group-tape-strategy__value reserve-strategy"></span>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- END TAPE GROUP STRATEGY -->
                                                        </div>
                                                    </div>
                                                    <!-- END COMMON STRATEGY -->
                                                    <!-- BEGIN TRANSFER STRATEGY -->
                                                    <div class="tab-pane " id="tab_transfer">
                                                        <div class="panel-body">
                                                            <div class="tabbable-custom col-md-10">
                                                                <!-- 传输模式 -->
                                                                <div class="form-group transportdiv display-none">
                                                                    <label class="control-label col-md-3 transferlabel">
                                                                        <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE'] ?></label>
                                                                    <div class="col-md-4">
                                                                        <select class="form-control select2me"
                                                                            id="transport_mode">
                                                                            <option value="1">
                                                                                <?php echo $LANG['UI_BACKUP_TRANSPORT_NBD'] ?>
                                                                            </option>
                                                                            <!-- <option value="2"><?php echo $LANG['UI_BACKUP_TRANSPORT_SAN'] ?></option> -->
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-2 mt10">
                                                                        <a class="popovers" data-container="body"
                                                                            data-trigger="hover" data-html="true"
                                                                            data-placement="right"
                                                                            data-content="<?php echo $LANG['UI_DB_TRANSPORT_TIPS'] ?>">
                                                                            <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                                <!-- 传输网络 -->
                                                                <div
                                                                    class="form-group display-none transfernetworkDiv display-none">
                                                                    <label
                                                                        class="control-label col-md-3 transfernetworklabel"><?php echo $LANG['UI_NODE_TRANSFER_NETWORK'] ?></label>
                                                                    <div class="col-md-4">
                                                                        <select class="form-control select2me"
                                                                            id="transferNetwork">
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-2 mt5">
                                                                        <a class="popovers" data-container="body"
                                                                            data-trigger="hover" data-html="true"
                                                                            data-placement="right"
                                                                            data-content="<?php echo $LANG['UI_NODE_TRANSFER_NETWORK_TIPS'] ?>">
                                                                            <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                                <!-- 重连次数 -->
                                                                <div
                                                                    class="form-group reconnect-times-wrapper display-none">
                                                                    <label
                                                                        class="control-label col-md-3 reconnect-times-Label"><?php echo $LANG['UI_BACKUP_RECONNECT_TIMES'] ?></label>
                                                                    <div class="col-md-4">
                                                                        <input type="text" id="reconnect_time"
                                                                            onkeyup="value=value.replace(/[^\d]/g,'')"
                                                                            class="spinner-input form-control input-sm"
                                                                            value="60" min="1">
                                                                    </div>
                                                                    <div class="col-md-2 mt2">
                                                                        <a class="popovers" data-container="body"
                                                                            data-trigger="hover" data-html="true"
                                                                            data-placement="right"
                                                                            data-content="<?php echo $LANG['UI_BACKUP_RECONNECT_TIMES_TIPS'] ?>"
                                                                            data-original-title="" title="">
                                                                            <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                                <!-- 重连间隔时间 -->
                                                                <div
                                                                    class="form-group reconnect-Interval-wrapper display-none">
                                                                    <label
                                                                        class="control-label col-md-3 reconnect-Interval-Label"><?php echo $LANG['UI_BACKUP_RECONNECT_INTERVAL'] ?></label>
                                                                    <div class="col-md-4">
                                                                        <input type="text" id="reconnect_interval"
                                                                            onkeyup="value=value.replace(/[^\d]/g,'')"
                                                                            class="spinner-input form-control input-sm"
                                                                            value="30" min="5">
                                                                    </div>
                                                                    <div class="col-md-2 mt2">
                                                                        <a class="popovers" data-container="body"
                                                                            data-trigger="hover" data-html="true"
                                                                            data-placement="right"
                                                                            data-content="<?php echo $LANG['UI_BACKUP_RECONNECT_INTERVAL_TIPS'] ?>"
                                                                            data-original-title="" title="">
                                                                            <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                                <!-- 传输代理 -->
                                                                <div class="form-group">
                                                                    <label
                                                                        class="control-label col-md-3 form-group-label appliance_agency_flag_label"><?php echo $LANG['UI_PLATFORM_APPLIANCE'] ?></label>
                                                                    <div class="col-md-4 form-group-content">
                                                                        <input type="checkbox"
                                                                            id="appliance_agency_flag" data-size="small"
                                                                            class="make-switch" data-on-color="primary"
                                                                            data-off-color="info"
                                                                            data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                            data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group display-hide applianceselectdiv">
                                                                    <label
                                                                        class="control-label col-md-3 applianceselectlabel form-group-label"><?php echo $LANG['UI_OBS_MANAGER_SELECT_AGENT'] ?></label>
                                                                    <div class="col-md-4 content">
                                                                        <ul class="ztree" id="transferAgentTree"></ul>
                                                                    </div>
                                                                </div>
                                                                <!-- 加密传输 -->
                                                                <div
                                                                    class="form-group display-hide encrypt-transfer-form-group">
                                                                    <label
                                                                        class="control-label col-md-3 form-group-label"><?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER'] ?></label>
                                                                    <div class="col-md-4 form-group-content">
                                                                        <input type="checkbox"
                                                                            id="transport_encrypt_flag"
                                                                            data-size="small" class="make-switch"
                                                                            data-on-color="primary"
                                                                            data-off-color="info"
                                                                            data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                            data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                        <a class="popovers ml15" data-container="body"
                                                                            data-trigger="hover" data-placement="right"
                                                                            data-content="<?php echo $LANG['WEB_OS_TRANSFER_ENCRY_TIPS'] ?>">
                                                                            <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                                <!-- 传输加密算法 -->
                                                                <div
                                                                    class="form-group encrypt-transfer-method-form-group display-hide">
                                                                    <label
                                                                        class="control-label transfer-encrypt-method-label col-md-3"><?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?></label>
                                                                    <div class="col-md-4">
                                                                        <select class="form-control select2me input-sm"
                                                                            id="transferEncryptMethod">
                                                                            <option value="1">
                                                                                <?php echo $LANG['UI_BACKUP_TRANSFER_ENCRYPT_RSA'] ?>
                                                                            </option>
                                                                            <?php
                                                                            if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
                                                                                echo '<option value="2">' . $LANG['UI_BACKUP_TRANSFER_ENCRYPT_SM'] . '</option>';
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <?php
                                                                if ($_SESSION['authfun']['multithread']) {
                                                                    echo '
                                                                        <!-- 传输线程 -->
                                                                        <div class="form-group threadDiv">
                                                                            <label class="control-label col-md-3 threadnumlabel">' . $LANG['UI_BACKUP_THREAD_NUM'] . '
                                                                            </label>
                                                                            <div class="col-md-2">
                                                                                <div class="backupThreadDiv">
                                                                                    <div class="input-group spinner-group">
                                                                                        <input type="text" id="backupThreadNum" onkeyup="value=value.replace(/[^\d]/g,\'\')" class="spinner-input form-control input-sm">
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
                                                                            <div class="col-md-2 mt10">
                                                                                <a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="' . $LANG['UI_BACKUP_FILE_THREADBUM_TIPS'] . '" data-original-title="" title="">
                                                                                    <i class="viconfont vicon-tishi"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                        ';
                                                                }
                                                                ?>
                                                                <!-- 扫描线程数 -->
                                                                <div
                                                                    class="form-group scanthreadDiv">
                                                                    <label
                                                                        class="control-label col-md-3 scanthreadlabel">
                                                                        <?php echo $LANG['UI_FILE_BAK_SCAN_THREAD'] ?>
                                                                    </label>
                                                                    <div class="col-md-2">
                                                                        <div class="scanThreadDiv">
                                                                            <div
                                                                                class="input-group spinner-group">
                                                                                <input type="text"
                                                                                    id="scanThreadNum"
                                                                                    onkeyup="value=value.replace(/[^\d]/g,'')"
                                                                                    class="spinner-input form-control input-sm">
                                                                                <div
                                                                                    class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                    <button
                                                                                        type="button"
                                                                                        class="btn spinner-up default input-sm">
                                                                                        <i
                                                                                            class="fa fa-angle-up"></i>
                                                                                    </button>
                                                                                    <button
                                                                                        type="button"
                                                                                        class="btn spinner-down default input-sm">
                                                                                        <i
                                                                                            class="fa fa-angle-down"></i>
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-2 mt5">
                                                                        <a class="popovers"
                                                                            data-container="body"
                                                                            data-trigger="hover"
                                                                            data-html="true"
                                                                            data-placement="right"
                                                                            data-content="<?php echo $LANG['UI_BACKUP_FILE_SCAN_THREADBUM_TIPS'] ?>"
                                                                            data-original-title=""
                                                                            title="">
                                                                            <i
                                                                                class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                                <!-- 扫描文件数 -->
                                                                <div
                                                                    class="form-group scanFileDiv display-none">
                                                                    <label
                                                                        class="control-label col-md-3 scanfilelabel"><?php echo $LANG['UI_OBS_BAK_SCAN_FILE_SPEED'] ?>
                                                                    </label>
                                                                    <div class="col-md-2">
                                                                        <div class="scanFileDiv">
                                                                            <div
                                                                                class="input-group">
                                                                                <div
                                                                                    class="spinner-buttons input-group-btn">
                                                                                    <select
                                                                                        class="form-control select2me input-sm"
                                                                                        id="scanFileNum">
                                                                                        <option
                                                                                            value="0">
                                                                                            <?php echo $LANG['UI_FILE_BAK_SCAN_SPEED_FIVE'] ?>
                                                                                        </option>
                                                                                        <option
                                                                                            value="1000">
                                                                                            <?php echo $LANG['UI_FILE_BAK_SCAN_SPEED_FOUR'] ?>
                                                                                        </option>
                                                                                        <option
                                                                                            value="800">
                                                                                            <?php echo $LANG['UI_FILE_BAK_SCAN_SPEED_THREE'] ?>
                                                                                        </option>
                                                                                        <option
                                                                                            value="600">
                                                                                            <?php echo $LANG['UI_FILE_BAK_SCAN_SPEED_TWO'] ?>
                                                                                        </option>
                                                                                        <option
                                                                                            value="400">
                                                                                            <?php echo $LANG['UI_FILE_BAK_SCAN_SPEED_ONE'] ?>
                                                                                        </option>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-2 mt5">
                                                                        <a class="popovers"
                                                                            data-container="body"
                                                                            data-trigger="hover"
                                                                            data-html="true"
                                                                            data-placement="right"
                                                                            data-content="<?php echo $LANG['UI_FILE_BAK_SCAN_FILE_TIPS'] ?>"
                                                                            data-original-title=""
                                                                            title="">
                                                                            <i
                                                                                class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- END TRANSFER STRATEGY -->
                                                    <!-- BEGIN SAFETY STRATEGY -->
                                                    <div class="tab-pane" id="tab_safety">
                                                        <div class="panel-body">
                                                            <div class="worm-config-wrapper">
                                                                <div class="form-group worm-protect-form-group">
                                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_SAFE_STRATEGY_WORM_PROTECT'] ?></label>
                                                                    <div class="col-md-3 form-group-content">
                                                                        <input type="checkbox"
                                                                                id="worm_protect_check"
                                                                                class="make-switch"
                                                                                data-on-color="primary"
                                                                                data-size="small"
                                                                                data-off-color="info"
                                                                                data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">

                                                                        <a class="popovers ml15" data-container="body" data-trigger="hover" data-placement="right" 
                                                                            data-content="<?php echo $LANG['UI_WORM_CONFIG_TIP'] ?>" 
                                                                            data-original-title="" title="">
                                                                            <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group worm-disabled-form-group display-none">
                                                                    <label class="control-label col-md-3"></label>
                                                                    <div class="col-md-6">
                                                                        <div class="alert alert-block alert-info fade in m0">
                                                                            <button type="button" class="close" data-dismiss="alert"></button>
                                                                            <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong>
                                                                            <ul class="alert-ul">
                                                                                <li><?php echo $LANG['UI_WORM_CONFIG_ALERT_TIPS4'] ?></li>
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group form-group-unselected-storage display-none">
                                                                    <label class="control-label col-md-3"></label>
                                                                    <div class="col-md-6">
                                                                        <div class="alert alert-block alert-info fade in m0">
                                                                            <button type="button" class="close" data-dismiss="alert"></button>
                                                                            <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong>
                                                                            <ul class="alert-ul">
                                                                                <li><?php echo $LANG['UI_WORM_CONFIG_ALERT_TIPS1'] ?></li>
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group form-group-unworm-config display-none">
                                                                    <label class="control-label col-md-3"></label>
                                                                    <div class="col-md-6">
                                                                        <div class="alert alert-block alert-info fade in m0">
                                                                            <button type="button" class="close" data-dismiss="alert"></button>
                                                                            <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong>
                                                                            <ul class="alert-ul">
                                                                                <li>
                                                                                    <?php echo $LANG['UI_WORM_CONFIG_ALERT_TIPS2'] ?>
                                                                                    <a class="ajaxify" name="storage_manager" href="./content/platform/storage/storage.php"><?php echo $LANG['UI_DATACENTER_BACKUP_STORAGE'] ?></a>
                                                                                    <?php echo $LANG['UI_WORM_CONFIG_ALERT_TIPS3'] ?>
                                                                                </li>
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group worm-protect-term-form-group display-none">
                                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_SAFE_STRATEGY_WORM_PROTECT_PERIOD'] ?></label>
                                                                    <div class="col-md-2">
                                                                        <div id="worm_protect_term_spinner_group">
                                                                            <div class="input-group spinner-group">
                                                                                <input type="text"
                                                                                    id="worm_protect_term_spinner"
                                                                                    style="text-align: left;"
                                                                                    class="input-sm spinner-input form-control"
                                                                                    maxlength="3"
                                                                                    onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}"
                                                                                    onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'0')}else{this.value=this.value.replace(/\D/g,'')}">
                                                                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                    <button type="button" class="input-sm btn spinner-up default">
                                                                                        <i class="fa fa-angle-up"></i>
                                                                                    </button>
                                                                                    <button type="button" class="input-sm btn spinner-down default">
                                                                                        <i class="fa fa-angle-down"></i>
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-1 pd0" style="line-height: 28px;"><?php echo $LANG['WEB_UTILS_DAY'] ?></div>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="integrality-check-wrapper">
                                                                <div class="form-group integrality-switch-form-group">
                                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_SAFE_STRATEGY_INTEGRITY_CHECK'] ?></label>
                                                                    <div class="col-md-9 form-group-content">
                                                                        <div class="accordion">
                                                                            <div class="panel panel-default strategy-panel">
                                                                                <div class="panel-heading">
                                                                                    <h4 class="panel-title">
                                                                                        <a class="accordion-toggle accordion-toggle-styled popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#verify_strategy_panel" aria-expanded="true">
                                                                                            <i class="levelchild viconfont vicon-pt_setting_email_notification font-green-seagreen"></i>
                                                                                            <span class="font-green-seagreen"><?php echo $LANG['UI_VERIFY_STRATEGY'] ?></span>
                                                                                            <span class="strategyDes verify-strategy-desc"></span>
                                                                                        </a>
                                                                                    </h4>
                                                                                </div>
                                                                                <div id="verify_strategy_panel" class="panel-collapse collapse in">
                                                                                    <div class="panel-body">
                                                                                        <!-- 验证策略 - 备份点异常 -->
                                                                                        <div class="form-group d-flex align-items-baseline">
                                                                                            <label class="control-label col-md-3 form-group-label"><?php echo $LANG['UI_BACKUP_POINT_ABNORMAL'] ?></label>
                                                                                            <div class="col-md-9 form-group-content verify-strategy-radio-group" id="backup_point_abnormal">
                                                                                                <label>
                                                                                                    <input type="radio" checked class="icheck-backup-point-abnormal" name="icheckbox2" data-mode="1">
                                                                                                    <?php echo $LANG['UI_BACKUP_POINT_ABNORMAL_HANDLE_TYPE1'] ?>
                                                                                                </label>
                                                                                                <label>
                                                                                                    <input type="radio" class="icheck-backup-point-abnormal" name="icheckbox2" data-mode="0">
                                                                                                    <?php echo $LANG['UI_BACKUP_POINT_ABNORMAL_HANDLE_TYPE2'] ?>
                                                                                                </label>
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
                                                    <!-- END SAFETY STRATEGY -->
                                                    <!-- BEGIN HIGH CONFIG -->
                                                    <div class="tab-pane " id="tab_other">
                                                        <div class="advance-config-wrap">
                                                            <div class="advance-config-wrap__row row m0">
                                                                <div class="col-md-2 col-sm-2 col-xs-2 nav-tab-radios">
                                                                    <ul class="nav nav-tabs tabs-left">
                                                                        <li class="dwm active">
                                                                            <a href="#abnormal_handle_pane"
                                                                                data-toggle="tab" aria-expanded="true">
                                                                                <?php echo $LANG['UI_EXCEPTION_HANDLE'] ?>
                                                                            </a>
                                                                        </li>
                                                                        <li class="dwm">
                                                                            <a href="#retry_strategy_pane"
                                                                                data-toggle="tab" aria-expanded="true">
                                                                                <?php echo $LANG['UI_STRATEGY_RETRY'] ?>
                                                                            </a>
                                                                        </li>
                                                                        <li class="dwm">
                                                                            <a href="#overload_protection_pane"
                                                                                data-toggle="tab" aria-expanded="true">
                                                                                <?php echo $LANG['UI_STRATEGY_OVER_LOAD_LABEL'] ?>
                                                                            </a>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                                <div
                                                                    class="col-md-10 col-sm-10 col-xs-10 pe-0 advance-config-wrap__row__content">
                                                                    <div class="tab-content level1">
                                                                        <div class="tab-pane fade in active"
                                                                            id="abnormal_handle_pane">
                                                                            <div class="abnormal-handle-form">
                                                                                <!-- 文件权限备份 -->
                                                                                <div
                                                                                    class="form-group file-permission-div">
                                                                                    <label
                                                                                        class="control-label col-md-5 file-permission-label form-group-label"><?php echo $LANG['UI_OBS_PERMISSION_BACKUP'] ?></label>
                                                                                    <div
                                                                                        class="col-md-3 form-group-content">
                                                                                        <input type="checkbox"
                                                                                            id="file-permission"
                                                                                            class="make-switch"
                                                                                            data-on-color="primary"
                                                                                            data-size="small"
                                                                                            data-off-color="info"
                                                                                            data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                            data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                        <a class="popovers ml15"
                                                                                            data-container="body"
                                                                                            data-trigger="hover"
                                                                                            data-html="true"
                                                                                            data-placement="right"
                                                                                            data-content="<?php echo $LANG['UI_OBS_PERMISSION_BACKUP_TIP'] ?>">
                                                                                            <i
                                                                                                class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                                <!-- 跳过文件告警智能判断 -->
                                                                                <div
                                                                                    class="form-group passfilealarmdiv">
                                                                                    <label
                                                                                        class="control-label col-md-5 passfilealarmlabel form-group-label"><?php echo $LANG['UI_OBD_SKIP_OBS_ALARM_INTELLIGENT_JUDGMENT'] ?></label>
                                                                                    <div
                                                                                        class="col-md-3 form-group-content">
                                                                                        <input type="checkbox"
                                                                                            id="passfilealarmcheck"
                                                                                            class="make-switch"
                                                                                            data-on-color="primary"
                                                                                            data-size="small"
                                                                                            data-off-color="info"
                                                                                            data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                            data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                        <a class="popovers ml15"
                                                                                            data-container="body"
                                                                                            data-trigger="hover"
                                                                                            data-html="true"
                                                                                            data-placement="right"
                                                                                            data-content="<?php echo $LANG['UI_OBD_SKIP_OBS_ALARM_INTELLIGENT_JUDGMENT_TIP'] ?>">
                                                                                            <i
                                                                                                class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                                <!-- 跳过文件告警个数 -->
                                                                                <div
                                                                                    class="form-group passfilenumDiv display-none">
                                                                                    <label
                                                                                        class="control-label col-md-5 passfilenumlabel"><?php echo $LANG['UI_OBS_SKIP_THE_NUMBER_OF_OBJECT_ALARMS'] ?>
                                                                                    </label>
                                                                                    <div class="col-md-2">
                                                                                        <div class="">
                                                                                            <div
                                                                                                class="input-group spinner-group">
                                                                                                <input type="text"
                                                                                                    maxlength="9"
                                                                                                    id="passFileNum"
                                                                                                    onkeyup="value=value.replace(/[^\d]/g,'')"
                                                                                                    class="spinner-input form-control input-sm">
                                                                                                <div
                                                                                                    class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                                    <button
                                                                                                        type="button"
                                                                                                        class="btn spinner-up default input-sm">
                                                                                                        <i
                                                                                                            class="fa fa-angle-up"></i>
                                                                                                    </button>
                                                                                                    <button
                                                                                                        type="button"
                                                                                                        class="btn spinner-down default input-sm">
                                                                                                        <i
                                                                                                            class="fa fa-angle-down"></i>
                                                                                                    </button>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <!-- <div class="col-md-2 mt5">
                                                                                    <a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="跳过文件个数超过该个数，系统生成告警" data-original-title="" title="">
                                                                                        <i class="viconfont vicon-tishi"></i>
                                                                                    </a>
                                                                                </div> -->
                                                                                </div>
                                                                                <!-- 跳过文件告警比例 -->
                                                                                <div
                                                                                    class="form-group warnningdiv display-none">
                                                                                    <label
                                                                                        class="control-label col-md-5 passfilepercentlabel"><?php echo $LANG['UI_OBS_SKIP_THE_OBJECT_ALARM_RATIO'] ?>
                                                                                    </label>
                                                                                    <div class="col-md-2">
                                                                                        <div id="spinnerpercent">
                                                                                            <div
                                                                                                class="input-group spinner-group">
                                                                                                <input type="text"
                                                                                                    id="warningpercent"
                                                                                                    style="text-align: left;"
                                                                                                    class="input-sm spinner-input form-control"
                                                                                                    maxlength="3"
                                                                                                    onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}"
                                                                                                    onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'0')}else{this.value=this.value.replace(/\D/g,'')}">
                                                                                                <div
                                                                                                    class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                                    <button
                                                                                                        type="button"
                                                                                                        class="input-sm btn spinner-up default">
                                                                                                        <i
                                                                                                            class="fa fa-angle-up"></i>
                                                                                                    </button>
                                                                                                    <button
                                                                                                        type="button"
                                                                                                        class="input-sm btn spinner-down default">
                                                                                                        <i
                                                                                                            class="fa fa-angle-down"></i>
                                                                                                    </button>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-md-1 pd0"
                                                                                        style="line-height: 28px;">%
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="tab-pane fade in" id="retry_strategy_pane">
                                                                            <div class="retry-strategy-content">
                                                                                <!-- BEGIN NETWORK RECONECT FORM GROUP -->
                                                                                <div class="retry-strategy-content__group retry-group-wrap">
                                                                                    <div class="retry-group-wrap__header display-none">
                                                                                        <span class="decoration"></span>
                                                                                        <span class="retry-group-wrap__header__text"><?php echo $LANG['UI_NETWORK_RETRY'] ?></span>
                                                                                    </div>
                                                                                    <div class="retry-group-wrap__content">
                                                                                        <!-- 网络重连次数 -->
                                                                                        <div class="form-group">
                                                                                            <label class="control-label col-md-5 networkRetryTimeLabel">
                                                                                                <?php echo $LANG['UI_STRATEGY_NETWORK_RETRY_TIMES'] ?>
                                                                                            </label>
                                                                                            <div class="col-md-7">
                                                                                                <div id="network_retry_times_spinner" class="max-w-150px">
                                                                                                    <div class="input-group spinner-group">
                                                                                                        <input
                                                                                                            type="text"
                                                                                                            id="network_retry_times"
                                                                                                            onkeyup="value=value.replace(/[^\d]/g,'')"
                                                                                                            class="spinner-input form-control input-sm"
                                                                                                            maxlength="3">
                                                                                                        <div
                                                                                                            class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                                            <button
                                                                                                                type="button"
                                                                                                                class="btn spinner-up default input-sm">
                                                                                                                <i
                                                                                                                    class="fa fa-angle-up"></i>
                                                                                                            </button>
                                                                                                            <button
                                                                                                                type="button"
                                                                                                                class="btn spinner-down default input-sm">
                                                                                                                <i
                                                                                                                    class="fa fa-angle-down"></i>
                                                                                                            </button>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                                <div class="help-block "><?php echo $LANG['UI_NETWORK_RECONECT_TIMES_TIPS'] ?></div>
                                                                                            </div>
                                                                                        </div>
                                                                                        <!-- 网络重连间隔时间 -->
                                                                                        <div class="form-group ">
                                                                                            <label class="control-label col-md-5 retrytimelabel">
                                                                                                <?php echo $LANG['UI_STRATEGY_NETWORK_RETRY_INTERVAL'] ?>
                                                                                            </label>
                                                                                            <div class="col-md-7">
                                                                                                <div id="network_retry_interval_spinner" class="max-w-150px">
                                                                                                    <div class="input-group spinner-group">
                                                                                                        <input
                                                                                                            type="text"
                                                                                                            id="network_retry_interval"
                                                                                                            onkeyup="value=value.replace(/[^\d]/g,'')"
                                                                                                            class="spinner-input form-control input-sm"
                                                                                                            maxlength="3">
                                                                                                        <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                                            <button
                                                                                                                type="button"
                                                                                                                class="btn spinner-up default input-sm">
                                                                                                                <i class="fa fa-angle-up"></i>
                                                                                                            </button>
                                                                                                            <button
                                                                                                                type="button"
                                                                                                                class="btn spinner-down default input-sm">
                                                                                                                <i class="fa fa-angle-down"></i>
                                                                                                            </button>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                                <div class="help-block "><?php echo $LANG['UI_NETWORK_RECONECT_INTERVAL_TIPS'] ?></div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <!-- END NETWORK RECONECT FORM GROUP -->
                                                                                <!-- BEGIN OPERATE ABNORMAL RETRY FORM GROUP -->
                                                                                <div class="retry-strategy-content__group retry-group-wrap">
                                                                                    <div class="retry-group-wrap__header display-none">
                                                                                        <span class="decoration"></span>
                                                                                        <span class="retry-group-wrap__header__text"><?php echo $LANG['UI_OP_ABNORMAL_RETRY'] ?></span>
                                                                                    </div>
                                                                                    <div class="retry-group-wrap__content">
                                                                                        <!-- 自动重试开关 -->
                                                                                        <div class="form-group">
                                                                                            <label class="control-label form-group-label col-md-5"><?php echo $LANG['UI_STRATEGY_OP_AUTO_RETRY'] ?></label>
                                                                                            <div class="col-md-7">
                                                                                                <input type="checkbox"
                                                                                                    checked
                                                                                                    id="op_retry_flag"
                                                                                                    data-size="small"
                                                                                                    class="make-switch"
                                                                                                    data-on-color="primary"
                                                                                                    data-off-color="info"
                                                                                                    data-on-text=""
                                                                                                    data-off-text="">
                                                                                                <div class="help-block "><?php echo $LANG['UI_OP_ABNORMAL_RETRY_TIPS'] ?></div>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="operation-retry-wrap">
                                                                                            <!-- 操作重连次数 -->
                                                                                            <div class="form-group">
                                                                                                <label class="control-label col-md-5 retrytimelabel">
                                                                                                    <?php echo $LANG['UI_STRATEGY_OP_RETRY_TIMES'] ?>
                                                                                                </label>
                                                                                                <div class="col-md-7">
                                                                                                    <div id="op_retry_times_spinner" class="max-w-150px">
                                                                                                        <div class="input-group spinner-group">
                                                                                                            <input
                                                                                                                type="text"
                                                                                                                id="op_retry_times"
                                                                                                                onkeyup="value=value.replace(/[^\d]/g,'')"
                                                                                                                class="spinner-input form-control input-sm"
                                                                                                                maxlength="3">
                                                                                                            <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                                                <button
                                                                                                                    type="button"
                                                                                                                    class="btn spinner-up default input-sm">
                                                                                                                    <i class="fa fa-angle-up"></i>
                                                                                                                </button>
                                                                                                                <button
                                                                                                                    type="button"
                                                                                                                    class="btn spinner-down default input-sm">
                                                                                                                    <i class="fa fa-angle-down"></i>
                                                                                                                </button>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <div class="help-block "><?php echo $LANG['UI_OP_ABNORMAL_RETRY_TIMES_TIPS'] ?></div>
                                                                                                </div>
                                                                                            </div>
                                                                                            <!-- 操作重连间隔时间 -->
                                                                                            <div class="form-group">
                                                                                                <label class="control-label col-md-5 retrytimelabel">
                                                                                                    <?php echo $LANG['UI_STRATEGY_OP_RETRY_INTERVAL'] ?>
                                                                                                </label>
                                                                                                <div class="col-md-7">
                                                                                                    <div id="op_retry_interval_spinner" class="max-w-150px">
                                                                                                        <div class="input-group spinner-group">
                                                                                                            <input
                                                                                                                type="text"
                                                                                                                id="op_retry_interval"
                                                                                                                onkeyup="value=value.replace(/[^\d]/g,'')"
                                                                                                                class="spinner-input form-control input-sm"
                                                                                                                maxlength="3">
                                                                                                            <div
                                                                                                                class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                                                <button
                                                                                                                    type="button"
                                                                                                                    class="btn spinner-up default input-sm">
                                                                                                                    <i
                                                                                                                        class="fa fa-angle-up"></i>
                                                                                                                </button>
                                                                                                                <button
                                                                                                                    type="button"
                                                                                                                    class="btn spinner-down default input-sm">
                                                                                                                    <i
                                                                                                                        class="fa fa-angle-down"></i>
                                                                                                                </button>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <div class="help-block "><?php echo $LANG['UI_OP_ABNORMAL_RETRY_INTERVAL_TIPS'] ?></div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <!-- END OPERATE ABNORMAL RETRY FORM GROUP -->
                                                                                <!-- BEGIN TASK RETRY FORM GROUP -->
                                                                                <div class="retry-strategy-content__group retry-group-wrap">
                                                                                    <div class="retry-group-wrap__header display-none">
                                                                                        <span class="decoration"></span>
                                                                                        <span class="retry-group-wrap__header__text"><?php echo $LANG['UI_TASK_RETRY'] ?></span>
                                                                                    </div>
                                                                                    <div
                                                                                        class="retry-group-wrap__content">
                                                                                        <!-- 自动重试开关 -->
                                                                                        <div class="form-group">
                                                                                            <label class="control-label form-group-label col-md-5"><?php echo $LANG['UI_STRATEGY_TASK_AUTO_RETRY'] ?></label>
                                                                                            <div class="col-md-7">
                                                                                                <input type="checkbox"
                                                                                                    checked
                                                                                                    id="task_retry_flag"
                                                                                                    data-size="small"
                                                                                                    class="make-switch"
                                                                                                    data-on-color="primary"
                                                                                                    data-off-color="info"
                                                                                                    data-on-text=""
                                                                                                    data-off-text="">
                                                                                                <div class="help-block "><?php echo $LANG['UI_TASK_AUTO_RETRY_TIPS'] ?></div>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="task-retry-wrap">
                                                                                            <!-- 重连对象 -->
                                                                                            <div class="form-group">
                                                                                                <label
                                                                                                    class="control-label col-md-5 form-group-label"><?php echo $LANG['UI_STRATEGY_TASK_RETRY_OBJECT'] ?>
                                                                                                </label>
                                                                                                <div class="col-md-5">
                                                                                                    <select
                                                                                                        class="form-control select2me inline-block"
                                                                                                        id="task_retry_object">
                                                                                                        <option
                                                                                                            value="1">
                                                                                                            <?php echo $LANG['UI_RETRY_FAILED_OBJS_IN_TASK'] ?>
                                                                                                        </option>
                                                                                                        <option
                                                                                                            value="2">
                                                                                                            <?php echo $LANG['UI_RETRY_ALL_OBJS_IN_TASK'] ?>
                                                                                                        </option>
                                                                                                    </select>
                                                                                                </div>
                                                                                            </div>
                                                                                            <!-- 任务重连次数 -->
                                                                                            <div class="form-group">
                                                                                                <label
                                                                                                    class="control-label col-md-5 retrytimelabel"><?php echo $LANG['UI_STRATEGY_TASK_RETRY_TIMES'] ?>
                                                                                                </label>
                                                                                                <div class="col-md-7">
                                                                                                    <div id="task_retry_times_spinner" class="max-w-150px">
                                                                                                        <div
                                                                                                            class="input-group spinner-group">
                                                                                                            <input
                                                                                                                type="text"
                                                                                                                id="task_retry_times"
                                                                                                                onkeyup="value=value.replace(/[^\d]/g,'')"
                                                                                                                class="spinner-input form-control input-sm"
                                                                                                                maxlength="3">
                                                                                                            <div
                                                                                                                class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                                                <button
                                                                                                                    type="button"
                                                                                                                    class="btn spinner-up default input-sm">
                                                                                                                    <i
                                                                                                                        class="fa fa-angle-up"></i>
                                                                                                                </button>
                                                                                                                <button
                                                                                                                    type="button"
                                                                                                                    class="btn spinner-down default input-sm">
                                                                                                                    <i
                                                                                                                        class="fa fa-angle-down"></i>
                                                                                                                </button>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <div class="help-block "><?php echo $LANG['UI_TASK_AUTO_RETRY_TIMES_TIPS'] ?></div>
                                                                                                </div>
                                                                                            </div>
                                                                                            <!-- 任务重连间隔时间 -->
                                                                                            <div class="form-group">
                                                                                                <label class="control-label col-md-5 retrytimelabel">
                                                                                                    <?php echo $LANG['UI_STRATEGY_TASK_RETRY_INTERVAL'] ?>
                                                                                                </label>
                                                                                                <div class="col-md-7">
                                                                                                    <div id="task_retry_interval_spinner" class="max-w-150px">
                                                                                                        <div class="input-group spinner-group">
                                                                                                            <input
                                                                                                                type="text"
                                                                                                                id="task_retry_interval"
                                                                                                                onkeyup="value=value.replace(/[^\d]/g,'')"
                                                                                                                class="spinner-input form-control input-sm"
                                                                                                                maxlength="3">
                                                                                                            <div
                                                                                                                class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                                                <button
                                                                                                                    type="button"
                                                                                                                    class="btn spinner-up default input-sm">
                                                                                                                    <i
                                                                                                                        class="fa fa-angle-up"></i>
                                                                                                                </button>
                                                                                                                <button
                                                                                                                    type="button"
                                                                                                                    class="btn spinner-down default input-sm">
                                                                                                                    <i
                                                                                                                        class="fa fa-angle-down"></i>
                                                                                                                </button>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <div class="help-block "><?php echo $LANG['UI_TASK_AUTO_RETRY_INTERVAL_TIPS'] ?></div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <!-- END TASK RETRY FORM GROUP -->
                                                                            </div>
                                                                        </div>
                                                                        <div class="tab-pane fade in" id="overload_protection_pane">
                                                                            <div class="abnormal-handle-form">
                                                                                <!-- 忽略节点资源限制 -->
                                                                                <div class="form-group">
                                                                                    <label class="control-label col-md-3 overload-protection-label form-group-label"><?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE'] ?></label>
                                                                                    <div class="col-md-3 form-group-content">
                                                                                        <input type="checkbox"
                                                                                            id="ignore_resource_limiting_flag"
                                                                                            class="make-switch"
                                                                                            data-on-color="primary"
                                                                                            data-size="small"
                                                                                            data-off-color="info"
                                                                                            data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                            data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">

                                                                                        <a class="popovers ml15" data-container="body" data-trigger="hover" data-placement="right" 
                                                                                            data-content="<?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE_TIPS'] ?>" 
                                                                                            data-original-title="" title="">
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
                                                <!-- END HIGH CONFIG -->
                                            </div>
                                        </div>
                                    </div>
                                    <!-- END TAB THREE BACKUP STRATEGY -->
                                    <!-- BEGIN TAB FOUR CONFIRM CONFIG -->
                                    <div class="tab-pane" id="tab4">
                                        <div class="tab-pane__body">
                                            <div class="tab-pane__body__form">
                                                <div class="alert alert-danger display-none jobnametip"></div>
                                                <div class="form-group mb0 mt10">
                                                    <label
                                                        class="control-label col-md-3"><?php echo $LANG['UI_JOB_RNAME'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <div class="input-icon right">
                                                            <i class="fa"></i>
                                                            <input type="text" maxlength="64" class="form-control"
                                                                id="jobname" onkeyup="customInputValidate('string', this.value, $(this))" />
                                                            <span
                                                                class="help-block "><?php echo $LANG['UI_JOB_RNAME_TIPS'] ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <h4 class="form-section"></h4>
                                                <div class="form-group mb0 mt10">
                                                    <label
                                                        class="control-label col-md-3"><?php echo $LANG['UI_OBS_BACKUP_OBJECT_LIST'] ?></label>
                                                    <div class="col-md-9">
                                                        <div class="form-control-static" id="file_list">

                                                        </div>
                                                    </div>
                                                </div>
                                                <h4 class="form-section"></h4>
                                                <div class="form-group mb0 mt10">
                                                    <label
                                                        class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TARGET_NODE'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static confirm-backup-target-node">
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10">
                                                    <label
                                                        class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TARGET_STORAGE'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static confirm-backup-target-storage">
                                                        </p>
                                                    </div>
                                                </div>

                                                <h4 class="form-section"></h4>
                                                <div class="form-group mb0 mt10">
                                                    <label
                                                        class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TYPE'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static confirm-backup-type">
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10 form-group-confirm-time-strategy">
                                                    <label
                                                        class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_STRATEGY_OR_TIME'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static confirm-time-strategy">
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10">
                                                    <label
                                                        class="control-label col-md-3"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static confirm-speed-limit-strategy">
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10">
                                                    <label
                                                        class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_STORAGE_STRATEGY'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static confirm-storage-strategy">
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10 form-group-confirm-reserve-strategy">
                                                    <label
                                                        class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_RESERVE_STRATEGY'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static confirm-reserve-strategy">
                                                        </p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="form-group mb0 mt10 form-group-confirm-tape-strategy display-none">
                                                    <label
                                                        class="control-label col-md-3"><?php echo $LANG['UI_TAPE_GROUP_STRATEGY'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static confirm-tape-strategy">
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10">
                                                    <label
                                                        class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static">
                                                            <span class="confirm-transmit-strategy"></span>
                                                            <span class="confirm-transmit-network display-none"></span>
                                                            <span class="confirm-advanced-strategy-transmit-thread"></span>
                                                            <span class="confirm-advanced-strategy-scan-thread"></span>
                                                            <span class="confirm-advanced-strategy-scan-speed display-none"></span>
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group confirm-config-safety-form-group mb0 mt10">
                                                    <label
                                                        class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <div class="form-control-static">
                                                            <div class="confirm-config-wrom-config-wrapper">
                                                                <div class="confirm-worm-protect"></div>
                                                                <div class="confirm-worm-protect-term display-none"></div>
                                                            </div>
                                                            <div class="confirm-config-interity-wrapper">
                                                                <div class="confirm-integrity-check"></div>
                                                                <div class="confirm-integrity-check-backup-point-abnormal display-none"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10">
                                                    <label
                                                        class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <div class="form-control-static">
                                                            <div class="confirm-advanced-config-permission-backup">
                                                            </div>
                                                            <div class="confirm-advanced-config-ignore-warning"></div>
                                                            <div
                                                                class="confirm-advanced-config-ignore-warning-num display-none">
                                                            </div>
                                                            <div
                                                                class="confirm-advanced-config-ignore-warning-percent display-none">
                                                            </div>
                                                            <div class="confirm-advanced-config-network-retry-times">
                                                            </div>
                                                            <div class="confirm-advanced-config-network-retry-interval">
                                                            </div>
                                                            <div class="confirm-advanced-config-op-retry"></div>
                                                            <div
                                                                class="confirm-advanced-config-op-retry-times display-none">
                                                            </div>
                                                            <div
                                                                class="confirm-advanced-config-op-retry-interval display-none">
                                                            </div>
                                                            <div class="confirm-advanced-config-task-retry"></div>
                                                            <div
                                                                class="confirm-advanced-config-task-retry-object display-none">
                                                            </div>
                                                            <div
                                                                class="confirm-advanced-config-task-retry-times display-none">
                                                            </div>
                                                            <div
                                                                class="confirm-advanced-config-task-retry-interval display-none">
                                                            </div>
                                                            <div
                                                                class="confirm-advanced-config-overload-protection">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10">
                                                    <label
                                                        class="control-label control-label-wildcard col-md-3"></label>
                                                    <div class="col-md-9">
                                                        <div class="form-control-static form-control-wildcard-list">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- END TAB FOUR CONFIRM CONFIG -->
                                </div>
                            </div>
                        </div>
                        <!-- END FORM BODY -->
                        <div class="form-actions form-actions--create">
                            <div class="row">
                                <div class="col-md-offset-6 col-md-6">
                                    <a href="javascript:;" class="btn default button-previous">
                                        <i class="viconfont vicon-shangyibu"></i>
                                        <?php echo $LANG['UI_PUBLIC_PREV_STEP'] ?>
                                    </a>
                                    <a href="javascript:;" class="btn green-turquoise button-next next-btn-margin-left">
                                        <?php echo $LANG['UI_PUBLIC_NEXT_STEP'] ?> <i
                                            class="viconfont vicon-xiayibu"></i>
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
<script type="text/javascript" src="./scripts/public/strategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/additional-methods.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js">
</script>
<script type="text/javascript"
    src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js">
</script>
<?php
if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
    //如果不是英文,加载语言包
    echo '<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>';
}
?>
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/platform/component/backup_target.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.strategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.speedStrategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/calendar/jquery.searchableSelect.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.taskCrowd.js"></script>
<script type="text/javascript" src="./scripts/platform/component/transfer_agent.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.backupStrategy.js"></script>
<script src="./scripts/s3/obsbackup.js" type="text/javascript"></script>