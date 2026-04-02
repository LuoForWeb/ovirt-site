<?php include_once '../../tpl/permission.php'; ?>

<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-select/bootstrap-select.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<link rel="stylesheet" type="text/css" href="./css/fs/filetype-small.css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./scripts/plugins/calendar/jquery.searchableSelect.css"/>
<link rel="stylesheet" type="text/css" href="./css/platform/component/safe_virus.css"/>
<?php
session_start();
session_commit();
?>
<!-- BEGIN PAGE HEADER-->
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row" style="height:100%;">
    <div class="col-md-12" style="height:100%;">
        <span class="display-none" id="servertime"></span>
        <input id="task_uuid" value="<?php echo $_GET['uuid']; ?>" class="display-none">
        <div class="portlet box blue-hoki backup-page" id="exchangebackupcontent">
            <div class="portlet-title">
                <div class="caption">
                    <i class="levelchild viconfont vicon-backup c333"></i><?php echo $LANG['WEB_M365_MODIFY_BACKUP_TASK'] ?>
                </div>
            </div>
            <div class="portlet-body form">
                <div action="#" class="form-horizontal" id="submit_form" method="POST">
                    <div class="form-wizard">
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
                                    <!-- 备份源 -->
                                    <div class="tab-pane active" id="tab1">
                                        <div class="alert alert-danger display-none selectvmtip">
                                        </div>
                                        <div class="row tab-pane__row">
                                            <div class="col-md-4 tab-pane__row__source">
                                                <!-- 请选择备份源 -->
                                                <div class="form-group src-wrap backup-list">
                                                    <div class="src-wrap__title">
                                                        <i class="levelchild viconfont vicon-windows-view viconfont-color"></i><?php echo $LANG['UI_MICROSOFT365_SELECT_RESOURCE'] ?>
                                                    </div>
                                                    <div class="src-wrap__content exchange_tree_div">
                                                        <div class="src-wrap__content__search">
                                                            <div class="vm_tree_div">
                                                                <div class="width100p">
                                                                    <input type="text" maxlength="128" placeholder="<?php echo $LANG['UI_COPY_SEARCH_AS_KEYWORD'] ?>" class="form-control" id="searchInput" style="margin-top: 0;" onkeyup="customInputValidate('string', this.value, $(this))">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="input-icon searchDiv mb10 display-none">
                                                            <div class="col-md-9 appSelectDiv pl12 pr0">
                                                                <select name="" id="appSelect" class="form-control">
                                                                    <option value="1">Exchange</option>
                                                                </select>
                                                            </div>
                                                            <!-- drawer开始 -->
                                                            <button type="button" id="excludeBtn" class="btn green-haze ml8" data-toggle="drawer" data-target="#drawer-1"><?php echo $LANG['WEB_M365_EXCLUDE'] ?></button>
                                                            <div class="drawer slide aws-drawer-width_en" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true" id="drawer-1">
                                                                <div class="drawer-content drawer-content-scrollable" role="document">
                                                                    <div class="drawer-header">
                                                                        <h4 class="drawer-title" id="drawer-1-title">
                                                                            <i class="viconfont vicon-paichu1 mr8"></i><?php echo $LANG['WEB_M365_EXCLUDE'] ?>
                                                                            <span data-dismiss="drawer" aria-label="Close" class="drawer-close"><i class="viconfont vicon-guanbi"></i></span>
                                                                        </h4>
                                                                    </div>
                                                                    <div class="drawer-body">
                                                                        <p style="color:#999999;"><?php echo $LANG['WEB_M365_EXCLUDE_BACKUP_DIR'] ?></p>
                                                                        <div class="col-md-4 pl0">
                                                                            <label><input type="checkbox" id="" data-checkbox="icheckbox_square-blue" data-mode="3" class="icheck" name="<?php echo $LANG['WEB_M365_SENT_EMAIL'] ?>"><?php echo $LANG['WEB_M365_SENT_EMAIL'] ?></label>
                                                                            <label><input type="checkbox" id="" data-checkbox="icheckbox_square-blue" data-mode="4" class="icheck" name="<?php echo $LANG['WEB_M365_DELETED_EMAIL'] ?>"><?php echo $LANG['WEB_M365_DELETED_EMAIL'] ?></label>
                                                                            <label><input type="checkbox" id="" data-checkbox="icheckbox_square-blue" data-mode="5" class="icheck" name="<?php echo $LANG['WEB_M365_SPAM'] ?>"><?php echo $LANG['WEB_M365_SPAM'] ?></label>
                                                                            <label><input type="checkbox" id="" data-checkbox="icheckbox_square-blue" data-mode="2" class="icheck" name="<?php echo $LANG['WEB_M365_DRAFT'] ?>"><?php echo $LANG['WEB_M365_DRAFT'] ?></label>
                                                                        </div>
                                                                        <div class="col-md-4 pl0">
                                                                            <label><input type="checkbox" id="" data-checkbox="icheckbox_square-blue" data-mode="200" class="icheck" name="<?php echo $LANG['WEB_M365_CONTACTS'] ?>"><?php echo $LANG['WEB_M365_CONTACTS'] ?></label>
                                                                            <label><input type="checkbox" id="" data-checkbox="icheckbox_square-blue" data-mode="100" class="icheck" name="<?php echo $LANG['WEB_M365_CALENDAR'] ?>"><?php echo $LANG['WEB_M365_CALENDAR'] ?></label>
                                                                            <label><input type="checkbox" id="" data-checkbox="icheckbox_square-blue" data-mode="1" class="icheck" name="<?php echo $LANG['WEB_M365_INBOX'] ?>"><?php echo $LANG['WEB_M365_INBOX'] ?></label>
                                                                        </div>
                                                                        <div class="col-md-4 pl0">
                                                                            <label><input type="checkbox" id="" data-checkbox="icheckbox_square-blue" data-mode="7" class="icheck" name="<?php echo $LANG['WEB_M365_DIALOGUE_HISTORY'] ?>"><?php echo $LANG['WEB_M365_DIALOGUE_HISTORY'] ?></label>
                                                                            <label><input type="checkbox" id="" data-checkbox="icheckbox_square-blue" data-mode="300" class="icheck" name="<?php echo $LANG['WEB_M365_TASK'] ?>"><?php echo $LANG['WEB_M365_TASK'] ?></label>
                                                                            <label><input type="checkbox" id="" data-checkbox="icheckbox_square-blue" data-mode="6" class="icheck" name="<?php echo $LANG['WEB_M365_FILE'] ?>"><?php echo $LANG['WEB_M365_FILE'] ?></label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="drawer-footer">
                                                                        <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-primary" id="addsubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
                                                                        <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- drawer结束 -->
                                                            <a class="popovers ml8" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['WEB_M365_SPECIFY_EXCLUDE_DIR'] ?>" data-original-title="" title="">
                                                                <i class="viconfont vicon-tishi fa-lg"></i>
                                                            </a>
                                                            <!--                                                            批量选择-->
                                                            <div class="mt10 batch-select-div">
                                                                <div class="col-md-9 appSelectDiv pl12 pr0">
                                                                    <input type="text" id="batchInput" class=" form-control input-sm" maxlength="5" onkeyup="value=value.replace(/[^\d]/g,'') " placeholder="<?php echo $LANG['WEB_M365_BATCH_TIPS1'] ?>">
                                                                </div>
                                                                <button type="button"  class="btn btn-primary ml8" id="batchSubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
                                                                <a class="popovers ml8" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['WEB_M365_BATCH_TIPS2'] ?>" data-original-title="" title="">
                                                                    <i class="viconfont vicon-tishi fa-lg"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                        <div class="src-wrap__content__ztree" style="overflow: auto">
                                                            <div class="exchange-tree">
                                                                <ul id="exchange_tree" class="ztree"></ul>
                                                            </div>
                                                            <div class="alert alert-block alert-info fade in" id="noagent">
                                                                <button type="button" class="close" data-dismiss="alert"></button>
                                                                <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                                                <ol class = "alert-ol">
                                                                    <li>
                                                                        <?php echo $LANG['UI_MICROSOFT365_ADD_AUTH'] ?>
                                                                    </li>
                                                                    <li>
                                                                        <a id="toAdd" class="alert-link"><?php echo $LANG['UI_MICROSOFT365_TO_ADD'] ?></a>
                                                                    </li>
                                                                    <li>
                                                                        <?php echo $LANG['WEB_M365_AUTO_REFRESH_ORGANIZTION'] ?>
                                                                    </li>
                                                                </ol>
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
                                            <div class="col-md-8">
                                                <div class="alert alert-block alert-info fade in" id="step1tips">
                                                    <button type="button" class="close" data-dismiss="alert"></button>
                                                    <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                                    <ol class = "alert-ol">
                                                        <li><?php echo $LANG['UI_MICROSOFT365_BAK_TIPSONE'] ?></li>
                                                        <li><?php echo $LANG['UI_MICROSOFT365_BAK_TIPSTWO'] ?></li>
                                                    </ol>
                                                </div>
                                            </div>
                                            <div class="col-md-8 VMList-div display-none" id="contentdiv">
                                                <!-- 已选择备份源 -->
                                                <div class="addTitle">
                                                    <span><i class="viconfont vicon-huifu"></i><?php echo $LANG['UI_MICROSOFT365_SRC_SELECTED'] ?></span>
                                                </div>

                                                <div class="addIt-list" id="allInfo">
                                                    <div  style="padding: 12px; background-color: #F5F5F5;">
                                                        <span class="selectedLabel"><?php echo $LANG['WEB_M365_SELECTED_TYPE'] ?>：</span>
                                                        <span class="selectedDes"></span>
                                                    </div>
                                                    <div class="mt10 excludeDiv display-none"  style="padding: 12px; background-color: #F5F5F5;">
                                                        <span class="selectedLabel"><?php echo $LANG['WEB_M365_EXCLUDE_DIR'] ?>：</span>
                                                        <span class="excludeDes"></span>
                                                    </div>
                                                    <div class="alert alert-block alert-info fade in display-none mt20" id="step1tips_2">
                                                        <button type="button" class="close" data-dismiss="alert"></button>
                                                        <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                                        <ol class = "alert-ol">
                                                            <li><?php echo $LANG['WEB_M365_ADD_LIST_TIP_ONE'] ?></li>
                                                            <li><?php echo $LANG['WEB_M365_ADD_LIST_TIP_TWO'] ?></li>
                                                            <li><?php echo $LANG['WEB_M365_ADD_LIST_TIP_THREE'] ?></li>
                                                        </ol>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="tab2">
                                        <div class="row" style="height: 100%; overflow-y: auto; padding-top: 20px">
                                            <div class="col-md-9" id="backupTarget"></div>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="tab3">
                                        <div class="row row-stepthree">
                                            <div class="tabbable-custom col-md-offset-1 col-md-10">
                                                <ul class="nav nav-tabs tab3-nav">
                                                    <li class="commonLi active">
                                                        <a href="#tab_common" class="popovers"
                                                           data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                            <i class="viconfont vicon-celve1"></i> <?php echo $LANG['UI_BACKUP_COMMON_STRATEGY'] ?> </a>
                                                    </li>
                                                    <li class="transferLi">
                                                        <a href="#tab_transfer" class="popovers"
                                                           data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                            <i class="viconfont vicon-a-chuanshu"></i> <?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?> </a>
                                                    </li>
                                                    <li class="safeLi">
                                                        <a href="#tab_safety" class="popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                        <i class="viconfont vicon-anquancelve"></i> <?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?> </a>
                                                    </li>
                                                    <li class="highLi">
                                                        <a href="#tab_other" class="popovers"
                                                           data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                            <i class="viconfont vicon-gaojipeizhi"></i> <?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?> </a>
                                                    </li>
                                                </ul>
                                                <div class="tab-content min-height300 tab3-content">
                                                    <!-- 通用策略 -->
                                                    <div class="tab-pane active" id="tab_common">
                                                        <div class="panel-body">
                                                            <div class="form-group">
                                                                <div class="control-label col-md-1"><?php echo $LANG['UI_BACKUP_SELECT_STRATEGY'] ?></div>
                                                                <div class="col-md-4">
                                                                    <select class="form-control select2me inline-block" id="strategySelect">
                                                                    </select>
                                                                    <a class="popovers ml15" data-container="body" data-trigger="hover"
                                                                       data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_SELECT_STRATEGY_TIPS'] ?>">
                                                                        <i class="viconfont vicon-tishi"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                            <!-- 时间策略 -->
                                                            <div class="form-group">
                                                                <div class="col-md-offset-1 col-md-10 ">
                                                                    <?php include('../platform/strategy/timeStrategy.php'); ?>
                                                                </div>
                                                            </div>
                                                            <!-- 限速策略 -->
                                                            <div class="form-group speedlimitDiv">
                                                                <div class="col-md-offset-1 col-md-10 accordion strategyOne" >
                                                                    <div class="panel panel-default strategy-panel">
                                                                        <div class="panel-heading">
                                                                            <h4 class="panel-title">
                                                                                <a class="accordion-toggle accordion-toggle-styled collapsed popovers"
                                                                                   data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#speed" aria-expanded="true">
                                                                                    <i class="viconfont vicon-xiansucelve1 font-green-seagreen"></i>
                                                                                    <span class="font-green-seagreen"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?></span>
                                                                                    <span class="strategyDes speedlimitDes"></span>
                                                                                </a>
                                                                            </h4>
                                                                        </div>
                                                                        <!--                                                                        <div id="speed" class="panel-collapse collapse ">-->
                                                                        <!--                                                                            <div class="panel-body">-->
                                                                        <!--                                                                                <div class="col-md-9">-->
                                                                        <!--                                                                                    <button type="button" id="addSpeedlimit" class="btn btn-sm green-haze">-->
                                                                        <!--                                                                                        <i class="viconfont vicon-ge_add_task"></i>--><?php //echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_ADD'] ?>
                                                                        <!--                                                                                    </button>-->
                                                                        <!--                                                                                    <ul id="speedList" style="padding-left: 0;">-->
                                                                        <!---->
                                                                        <!--                                                                                    </ul>-->
                                                                        <!--                                                                                </div>-->
                                                                        <!--                                                                            </div>-->
                                                                        <!--                                                                        </div>-->
                                                                        <?php include_once('../../content/platform/global_strategy/speedJob.php'); ?>
                                                                        <!--       全局限速策略组合的组件配置-->
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- 存储策略 -->
                                                            <div class="form-group">
                                                                <div class="col-md-offset-1 col-md-10 ">
                                                                    <?php include('../platform/strategy/storeStrategy.php') ?>
                                                                </div>
                                                            </div>
                                                            <!-- 保留策略 -->
                                                            <div class="form-group">
                                                                <div class="col-md-offset-1 col-md-10 ">
                                                                    <?php include('../platform/strategy/reserveStrategy.php') ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- 传输策略 -->
                                                    <div class="tab-pane" id="tab_transfer">
                                                        <div class="panel-body">
                                                            <div class="tabbable-custom col-md-10" >
                                                                <!-- 传输加密 -->
                                                                <div class="form-group transport-encrypt-div">
                                                                    <label class="control-label form-group-top4-label col-md-3"><?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER'] ?></label>
                                                                    <div class="col-md-4">
                                                                        <input type="checkbox" id="transport_encrypt_flag" data-size="small" class="make-switch" data-on-color="primary" data-off-color="info"
                                                                               data-on-text=""
                                                                               data-off-text="">
                                                                        <a class="popovers ml15" data-container="body" data-trigger="hover"
                                                                           data-placement="right" data-content="<?php echo $LANG['WEB_OS_TRANSFER_ENCRY_TIPS'] ?>">
                                                                            <i class="viconfont vicon-tishi fa-lg"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                                <!-- 传输加密算法 -->
                                                                <div class="form-group transfer-encrypt-method-form display-none">
                                                                    <label class="control-label transfer-encrypt-method-label col-md-3"><?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?></label>
                                                                    <div class="col-md-4">
                                                                        <select class="form-control select2me input-sm" id="transferEncryptMethod">
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
                                                                <!--选择客户端   -->
                                                                <div class="form-group display-none agentConfigDiv">
                                                                    <label class="control-label col-md-3 form-group-top4-label agentConfiglabel"><?php echo $LANG['WEB_M365_AGENT_CONFIG'] ?></label>
                                                                    <div class="col-md-4">
                                                                        <input type="checkbox" id="agentConfig" data-size="small" class="make-switch" data-on-color="primary" data-off-color="info" data-on-text="" data-off-text="">
                                                                        <a class="popovers ml15" data-container="body" data-trigger="hover"
                                                                           data-placement="right" data-content="<?php echo $LANG['WEB_M365_AGENT_CONFIG_TIPS'] ?>">
                                                                            <i class="viconfont vicon-tishi fa-lg"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group display-none agentListDiv">
                                                                    <label class="control-label col-md-3 agentListLabel"><?php echo $LANG['WEB_M365_SPECIFY_AGENT'] ?></label>
                                                                    <div class="col-md-4">
                                                                        <select class="form-control select2me" id="agentList">
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <!-- 传输网络 -->
                                                                <div class="form-group display-none transfernetworkDiv">
                                                                    <label class="control-label col-md-3 transfernetworklabel"><?php echo $LANG['UI_NODE_TRANSFER_NETWORK'] ?></label>
                                                                    <div class="col-md-4">
                                                                        <ul class="ztree" id="transferNetworkTree"></ul>
                                                                    </div>
                                                                    <div class="col-md-2 mt5">
                                                                        <a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_NODE_TRANSFER_NETWORK_TIPS'] ?>">
                                                                            <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                                <?php
                                                                $authfun = $_SESSION['authfun'];
                                                                if ($authfun['multithread']) {
                                                                    echo '
                                                                        <!-- 传输线程 -->
                                                                        <div class="form-group threadDiv">
                                                                            <label class="control-label col-md-3 threadnumlabel">' . $LANG['UI_BACKUP_THREAD_NUM'] . '
                                                                            </label>
                                                                            <div class="col-md-4">
                                                                                <div class="backupThreadDiv">
                                                                                    <div class="input-group spinner-group">
                                                                                        <input type="text" id="backupThreadNum" onkeyup="value=value.replace(/[^\d]/g,\'\')" class="spinner-input form-control input-sm" maxlength="2">
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
                                                                                <a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="' . $LANG['WEB_M365_THREAD_NUM_TIP'] . '" data-original-title="" title="">
                                                                                    <i class="viconfont vicon-tishi fa-lg"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                        ';
                                                                }
                                                                ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- 安全策略 -->
                                                    <div class="tab-pane " id="tab_safety">
                                                        <div class="panel-body">
                                                            <div class="tabbable-custom">
                                                                <!-- WORM -->
                                                                <div id="wormConfig"></div>
                                                                <!-- 病毒检测 -->
                                                                <div id="virusConfig"></div>
                                                                <!-- 完整性校验 -->
                                                                <div id="completeConfig"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- 高级配置 -->
                                                    <div class="tab-pane " id="tab_other">
                                                        <div class="advance-config-wrap">
                                                            <div class="advance-config-wrap__row row m0">
                                                                <div class="col-md-2 col-sm-2 col-xs-2 nav-tab-radios">
                                                                    <ul class="nav nav-tabs tabs-left">
                                                                        <li class="active">
                                                                            <a href="#retry_strategy_pane"
                                                                                data-toggle="tab" aria-expanded="true">
                                                                                <?php echo $LANG['UI_STRATEGY_RETRY'] ?>
                                                                            </a>
                                                                        </li>
                                                                        <li class="">
                                                                            <a href="#over_load_strategy_pane"
                                                                                data-toggle="tab" aria-expanded="true">
                                                                                <?php echo $LANG['UI_STRATEGY_OVER_LOAD_LABEL'] ?>
                                                                            </a>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                                <div class="col-md-10 col-sm-10 col-xs-10 pe-0 advance-config-wrap__row__content">
                                                                    <div class="tab-content level1">
                                                                        <div class="tab-pane fade in active" id="retry_strategy_pane">
                                                                            <div class="">
                                                                                <div class="tab-content">
                                                                                    <div id="retry_config" class="retry_config_pane">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="tab-pane fade" id="over_load_strategy_pane">
                                                                            <div class="abnormal-handle-form">
                                                                                <!-- 忽略节点资源限制 -->
                                                                                <div class="form-group advancedDiv ignoreResourceLimitDiv">
                                                                                    <label for="ignoreResourceLimit" class="control-label col-md-3 ignoreResourceLimitLabel form-group-label">
                                                                                        <?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE'] ?>
                                                                                    </label>
                                                                                    <div class="col-md-3 form-group-content">
                                                                                        <input type="checkbox" id="ignoreResourceLimit" class="make-switch" data-on-color="primary"
                                                                                               data-off-color="info" data-size="small"
                                                                                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                        <a class="popovers ml15" data-container="body" data-trigger="hover"
                                                                                           data-html="true" data-placement="right"
                                                                                           data-content="<?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE_TIPS'] ?>">
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
                                    <div class="tab-pane" id="tab4">
                                        <div class="tab-pane__body">
                                            <div class="tab-pane__body__form">
                                                <div class="alert alert-danger display-none jobnametip" style="display: none;"></div>
                                                <div class="form-group mb0 mt10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_JOB_RNAME'] ?>:</label>
                                                    <div class="col-md-4">
                                                        <div class="input-icon right">
                                                            <i class="fa"></i>
                                                            <input type="text" maxlength="64" class="form-control" id="jobname" onkeyup="customInputValidate('string', this.value, $(this))" id="jobname"/>
                                                            <span class="help-block "><?php echo $LANG['UI_JOB_RNAME_TIPS'] ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <h4 class="form-section"></h4>
                                                <div class="form-group mb0 mt10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_FILE_DATA_SRC'] ?></label>
                                                    <div class="col-md-7">
                                                        <div class="form-control-static " id="srcDatalist">

                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TARGET_STORAGE'] ?></label>
                                                    <div class="col-md-7">
                                                        <p class="form-control-static   storageInfoShow">
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TARGET_NODE'] ?></label>
                                                    <div class="col-md-7">
                                                        <p class="form-control-static   nodeInfoShow">
                                                        </p>
                                                    </div>
                                                </div>
                                                
                                                <div class="form-group mb0 mt10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TYPE'] ?></label>
                                                    <div class="col-md-4">
                                                        <p class="form-control-static   backuptypeshow">
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10 backupTypeInfoDiv">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_STRATEGY_OR_TIME'] ?></label>
                                                    <div class="col-md-6">
                                                        <p class="form-control-static   backuptypeinfoshow">
                                                        </p>
                                                    </div>
                                                </div>
                                                <h4 class="form-section"></h4>
                                                <div class="form-group mb0 mt10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_STORAGE_STRATEGY'] ?></label>
                                                    <div class="col-md-4">
                                                        <p class="form-control-static   storageinfoshow">
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10 speedlimitDiv">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?></label>
                                                    <div class="col-md-6">
                                                        <p class="form-control-static   speedlimitshow">
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10 reserveDiv">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_RESERVE_STRATEGY'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static  reservetypeshow">
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10 transferDiv">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?></label>
                                                    <div class="col-md-8">
                                                        <p class="form-control-static ">
                                                            <span class="transferinfoshow"></span>
                                                            <span class="agentconfigshow display-none"></span>
                                                            <span class="agentlistshow display-none"></span>
                                                            <span class="applianceshow"></span>
                                                            <?php
                                                            $authfun = $_SESSION['authfun'];
                                                            if ($authfun['multithread']) {
                                                                echo '<span class="backupmodeshow3"></span>';
                                                            }
                                                            ?>
                                                        </p>
                                                    </div>
                                                </div>
                                                <!-- 安全策略展示 -->
                                                <div class="form-group mb0 mt10 safeDiv">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static safeStrategyShow ">
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10 retryShow"></div>
                                                <div class="form-group mb0 mt10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <div class="form-control-static">
                                                            <div class="ignoreResourceLimitShow "></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-offset-6 col-md-6">
                                    <a href="javascript:;" class="btn default button-previous">
                                        <i class="viconfont vicon-shangyibu" style="margin-right: 2px;"></i> <?php echo $LANG['UI_PUBLIC_PREV_STEP'] ?>
                                    </a>
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
                </div>
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

                    <div class="form-group">
                        <label class="control-label col-md-2 form-group-label">  <?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_TYPE'] ?>
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
                        <label class="control-label col-md-2 form-group-label"> <?php echo $LANG['UI_BACKUP_SET_STRATEGY'] ?>
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
                                        <div class="input-group spinner-group" >
                                            <input type="text" id="speedSpinnerNumInput" style="text-align: left;" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control" onkeyup="value=value.replace(/[^\d]/g,'')">
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
                                    <select id="unit" style="width: 80px; margin-left: 10px;height: 33px;text-align:center; border: 1px solid #E6E6E6;">
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
                <button type="button"class="btn btn-primary" id="speed_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
            </div>
        </div>
        <!-- END RATE LIMIT MODAL -->
    </div>
</div>
<!-- END PAGE CONTENT-->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/public/strategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/additional-methods.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.strategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.speedStrategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/calendar/jquery.searchableSelect.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.taskCrowd.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.backupStrategy.js"></script>
<script type="text/javascript" src="./scripts/platform/component/backup_target.js"></script>
<script type="text/javascript" src="./scripts/platform/component/transfer_network.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_worm.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_virus_detection.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_complete.js"></script>
<script type="text/javascript" src="./scripts/platform/component/retry_strategy.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script src="./scripts/exchange/exchange_backupedit.js" type="text/javascript"></script>
