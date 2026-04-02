<?php include_once '../../tpl/permission.php'; ?>
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
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
<link rel="stylesheet" type="text/css" href="./css/platform/component/resource_pool.css" />
<link href="/assets/global/plugins/select2/select2-4.1.0/css/select2.min.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./scripts/components/timePointDetail/css/timePointDetail.css" />
<!-- END PAGE LEVEL STYLES -->
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
    <span class="curent"><?php echo $LANG['UI_RECOVERY_FOR_KUBERNETES'] ?></span>
</h3>
<!-- END PAGE HEADER-->

<!-- BEGIN PAGE CONTENT-->
<div class="row recover-page">
    <div class="col-md-12" style="height:100%;">
        <div class="portlet box blue-hoki" id="k8srecovercontent">
            <div class="portlet-title">
                <div class="caption">
                    <i class="levelchild viconfont vicon-huifu1"></i><?php echo $LANG['WEB_KUBE_RECOVERY_NEW_JOB']; ?>
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
                                        <?php echo $LANG['UI_BR_RECOVERY_DATA_SOURCE']; ?>
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
                                        <?php echo $LANG['UI_RECOVERY_TYPE']; ?>
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
                            <div class="tab-content bakuptab">
                                <!-- 选择备份点 -->
                                <div class="tab-pane active" id="tab1">
                                    <div class="alert alert-danger display-none selecttimepointtip">
                                    </div>
                                    <div class="row tab-pane__row">
                                        <div class="col-md-4 tab-pane__row__source">
                                            <div class="form-group src-wrap">
                                                <div class="src-wrap__title">
                                                    <span><?php echo $LANG['WEB_KUBE_RECOVERY_SELECT_BACKUP_POINT']; ?></span>
                                                </div>
                                                <div class="src-wrap__content">
                                                    <div class="src-wrap__content__search">
                                                        <div style="position: relative;">
                                                            <div class="search_glass">
                                                                <div type="button" class="b-btn search-btn" id="searchSubmit"><i class="icon-search"></i></div>
                                                            </div>
                                                            <input class="search_glass_input" id="searchVal"  autocomplete="off" type="text" placeholder="<?php echo $LANG['WEB_KUBE_SEARCH_BY_KEYWORD']; ?>">
                                                        </div>
                                                    </div>
                                                    <div class="src-wrap__content__ztree" style="height: calc(100% - 92px)">
                                                        <div class="agent_tree_div vcenter-tree">
                                                            <ul id="agent_tree" class="ztree"></ul>
                                                            <div class="no_search_result" style="display: none;">
                                                                <div class="no_search_result_icon"></div>
                                                                <div class="text" style="margin-top: 10px;"><?php echo $LANG['WEB_KUBE_RECOVERY_NO_MORE_DATA']; ?></div>
                                                            </div>

                                                        </div>
                                                        <div class="alert alert-block alert-info fade in display-hide" id="noagent">
                                                            <button type="button" class="close" data-dismiss="alert"></button>
                                                            <h4 class="alert-heading">
                                                                <strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong>
                                                            </h4>
                                                            <ol class="alert-ol">
                                                                <li>
                                                                <?php echo $LANG['WEB_KUBE_BACKUP_NO_CLUSTERS_FOUND']; ?>
                                                                </li>
                                                                <li>
                                                                    <a id="toaddAgent"><?php echo $LANG['WEB_KUBE_RECOVERY_ADD_AND_AUTHORIZE']; ?></a>
                                                                </li>
                                                                <li>
                                                                <?php echo $LANG['WEB_KUBE_RECOVERY_AUTHORIZE_KUBERNETES']; ?>
                                                                </li>
                                                            </ol>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-8 VMList-div" id="k8streeDiv">
                                            <div class="addTitle">
                                                <span><?php echo $LANG['UI_RECOVERY_ALREADY_SELECT_POINT'] ?></span>
                                            </div>
                                            <div class="addIt-list">
                                                <div class="display-none" id="data_table_namespace_div">
                                                    <!-- 表格 -->
                                                    <table id="data_table_namespace"></table>
                                                    <!-- 表格 -->
                                                </div>
                                                <div class="display-none" id = "data_table_app_div">
                                                    <!-- 表格 -->
                                                    <table id="data_table_app"></table>
                                                    <!-- 表格 -->
                                                </div>
                                                <div class="alert alert-block alert-info fade in notipsDiv" id="step1tips">
                                                    <button type="button" class="close" data-dismiss="alert"></button>
                                                    <h4 class="alert-heading">
                                                        <strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                                    <ol class="alert-ol">
                                                        <li><?php echo $LANG['WEB_KUBE_RECOVERY_SELECT_RESTORE_POINTS']; ?></li>
                                                        <li>
                                                        <?php echo $LANG['WEB_KUBE_RECOVERY_SHOW_BACKUP_RESOURCES']; ?>
                                                        </li>
                                                        <li><?php echo $LANG['WEB_KUBE_RECOVERY_SELECT_PVC_NEXT']; ?></li>
                                                    </ol>
                                                </div>
                                            </div>


                                        </div>
                                    </div>
                                </div>

                                <!-- 恢复目标 -->
                                <div class="tab-pane" id="tab2">
                                    <div class="alert alert-danger display-none setrecover2tip">
                                    </div>
                                    <div class="row tab-pane__row">
                                        <div class="col-md-12 col-steptwo">
                                            <div class="form-group">
                                                <label class="control-label col-md-3"><?php echo $LANG['WEB_KUBE_RECOVERY_RESTORE_TO_CLUSTER']; ?></label>
                                                <div class="col-md-5">
                                                    <select class="form-control select2me" id="target_cluster">
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group namespace_div">
                                                <label class="control-label col-md-3"><?php echo $LANG['WEB_KUBE_KEEP_SOURCE_NAMESPACE']; ?></label>
                                                <div class="col-md-4">
                                                    <input type="checkbox" id="keep_old_namespace"
                                                        class="make-switch" data-on-color="primary"
                                                        data-off-color="info" data-size="small"
                                                        data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                        data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                    <a class="popovers ml15" data-container="body"
                                                    data-trigger="hover" data-html="true"
                                                    data-placement="right"
                                                    data-content="<?php echo $LANG['WEB_KUBE_KEEP_SOURCE_NAMESPACE_TIP'] ?>">
                                                        <i class="viconfont vicon-tishi"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="form-group namespace_div">
                                                <label class="control-label col-md-3"><?php echo $LANG['WEB_KUBE_NAMESPACE_DETAILS'] ?></label>
                                                <div class="col-md-6">
                                                    <table id="namespace_config"></table>
                                                </div>
                                            </div>
                                            <div class="form-group pvc_div">
                                                <label class="control-label col-md-3"><?php echo $LANG['WEB_KUBE_KEEP_SOURCE_PVC_CONFIG'] ?></label>
                                                <div class="col-md-4">
                                                    <input type="checkbox" id="keep_old_pvc"
                                                        class="make-switch" data-on-color="primary"
                                                        data-off-color="info" data-size="small"
                                                        data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                        data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                    <a class="popovers ml15" data-container="body"
                                                    data-trigger="hover" data-html="true"
                                                    data-placement="right"
                                                    data-content="<?php echo $LANG['WEB_KUBE_KEEP_SOURCE_PVC_CONFIG_TIP'] ?>">
                                                        <i class="viconfont vicon-tishi"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="form-group pvc_div">
                                                <label class="control-label col-md-3"><?php echo $LANG['WEB_KUBE_RESTORE_PVC_DETAIL_CONFIG'] ?></label>
                                                <div class="col-md-6">
                                                    <table id="pvc_config"></table>
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
                                                    <a href="#tab_common" class="" data-content="<?php echo $LANG['WEB_KUBE_RESTORE_COMMON_STRATEGIES'] ?>"
                                                       data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                        <i class="viconfont vicon-celve1"></i> <?php echo $LANG['UI_BACKUP_COMMON_STRATEGY'] ?> </a>
                                                </li>
                                                <li class="">
                                                    <a href="#tab_transfer" class=""
                                                       data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_TIPS'] ?>"
                                                       data-container="body" data-trigger="hover" data-placement="top"
                                                       data-toggle="tab">
                                                        <i class="viconfont vicon-ge_transfer"></i> <?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?>
                                                    </a>
                                                </li>
                                                <li class="otherLi">
                                                    <a href="#tab_safety" class="" data-content="<?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?>" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                        <i class="viconfont vicon-gaojipeizhi "></i> <?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?> </a>
                                                </li>
                                                <li class="highLi">
                                                    <a href="#tab_other" class=""
                                                       data-content="<?php echo $LANG['UI_BACKUP_HIGH_CONFIG_DES'] ?>"
                                                       data-container="body" data-trigger="hover" data-placement="top"
                                                       data-toggle="tab">
                                                        <i class="viconfont icon-gaojipeizhi"></i> <?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?>
                                                    </a>
                                                </li>
                                                <li class="scriptLi">
                                                    <a href="#tab_script" class="" data-content="<?php echo $LANG['WEB_KUBE_RECOVERY_SCRIPT_CONFIG']; ?>" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                    <i class="viconfont vicon-gaojipeizhi"></i> <?php echo $LANG['WEB_KUBE_RECOVERY_SCRIPT_CONFIG']; ?></a>
                                                </li>
                                            </ul>
                                            <div class="tab-content">
                                                <!-- 通用策略 -->
                                                <div class="tab-pane active" id="tab_common">
                                                    <div class="panel-body">
                                                        <!-- 时间策略 -->
                                                        <div class="form-group">
                                                            <div class="col-md-offset-1  col-md-10 pt15 recoveryTimeDiv">
                                                                <div class="accordion strategyOne" >
                                                                    <div class="panel panel-default strategy-panel">
                                                                        <div class="panel-heading">
                                                                            <h4 class="panel-title">
                                                                                <a class="accordion-toggle accordion-toggle-styled popovers" 
                                                                                data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#recoveryTime" aria-expanded="true">
                                                                                <i class="viconfont vicon-shijian2 font-green-seagreen"></i> 
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
                                                                                        <div class="col-md-3">
                                                                                            <select class="form-control select2me input-sm" id="recovertype">
                                                                                                <option value="1"><?php echo $LANG['UI_RECOVERY_START_TYPE_NOW'] ?></option>
                                                                                                <option value="4"><?php echo $LANG['UI_RECOVERY_START_TYPE_TIMING'] ?></option>
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
                                                        <div class="form-group">
                                                            <div id="backupStrategyDiv" class="col-md-10 col-md-offset-1">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="tab-pane " id="tab_transfer">
                                                    <div class="panel-body">
                                                        <div class="tabbable-custom pdlr15  col-md-10" >

                                                            <!-- 加密传输 -->
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3 encrypttransferlabel form-group-label"><?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER'] ?></label>
                                                                <div class="col-md-4 form-group-content">
                                                                    <input type="checkbox" id="encrypttransfer" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                    <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['WEB_KUBE_BACKUP_SSL_ENCRYPTION']; ?>">
                                                                        <i class="viconfont vicon-tishi"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                            <!-- 传输加密算法 -->
                                                            <div class="form-group encrypt_method_div" style="display: none;">
                                                                <label class="control-label col-md-3"> <?php echo $LANG['WEB_KUBE_RECOVERY_ENCRYPTION_ALGORITHM']; ?></label>
                                                                <div class="col-md-4">
                                                                    <select class="form-control select2me" id="encrypt_method">
                                                                        <option value="1">RSA</option>
                                                                        <?php
                                                                            if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
                                                                                echo '<option value="2">SM2</option>';
                                                                            }
                                                                        ?>
                                                                    </select>
                                                                </div>
                                                                
                                                            </div>

                                                            <!-- 传输网络 -->
                                                            <div class="form-group transfernetworkDiv">
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
                                                            <div class="form-group transfer_threads_div">
                                                                    <label class="control-label col-md-3"><?php echo $LANG['WEB_KUBE_RECOVERY_TRANSFER_THREADS']; ?></label>
                                                                    <div class="col-md-3">
                                                                        <div id="Socket-thread">
                                                                            <div class="input-group spinner-group">
                                                                                <input type="text" id="transfer_threads" style="text-align: left;" class="input-sm spinner-input form-control" maxlength="3" onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}" onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'0')}else{this.value=this.value.replace(/\D/g,'')}">
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
                                                                    <div class="col-md-2 mt10">
                                                                        <a class="popovers" data-container="body" data-trigger="hover" data-html="true" 
                                                                        data-placement="right" data-content="<?php echo $LANG['WEB_KUBE_RECOVERY_TRANSFER_THREADS_TIPS']; ?>">
                                                                        <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane" id="tab_safety">
                                                    <div class="panel-body">
                                                        <div class="tabbable-custom pdlr15  col-md-10">
                                                            <!--健康检测-->
                                                            <div class="form-group">
                                                                <div id="completeConfig"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane " id="tab_other">
                                                    <div class="panel-body">
                                                        <div class="row row-stepthree">
                                                            <div class="col-md-2 col-sm-3 col-xs-3 nav-tab-radios">
                                                                <ul class="nav nav-tabs tabs-left min-height400">
                                                                    <li class="active">
                                                                        <a href="#tab_cache_conf" class="" data-content="" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                                        <?php echo $LANG['WEB_KUBE_RECOVERY_SNAPSHOT']; ?> </a>
                                                                    </li>
                                                                    <li class="">
                                                                        <a href="#resources_monitoring" class="" data-content="" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                                        <?php echo $LANG['WEB_KUBE_RECOVERY_RESOURCE_MONITORING']; ?> </a>
                                                                    </li>
                                                                    <li class="">
                                                                        <a href="#retry_config" class="" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                                        <?php echo $LANG['UI_SETTINGS_UPDATE_RETRY'] ?> </a>
                                                                    </li>
                                                                    <li class="">
                                                                        <a href="#overload_protection" class="" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                                        <?php echo $LANG['UI_STRATEGY_OVER_LOAD_LABEL'] ?> </a>
                                                                    </li>
                                                                    
                                                                </ul>
                                                            </div>
                                                            <div class="col-md-10 col-sm-9 col-xs-9">
                                                                <div class="tab-content">
                                                                    <!-- 快照配置 -->
                                                                    <div class="tab-pane active" id="tab_cache_conf">
                                                                        <div class="col-md-12 ">
                                                                            
                                                                            <div class = "col-md-12 advanced-detail-conf-district">

                                                                                <div class="form-group mt45">
                                                                                    <label class="control-label col-md-3"><?php echo $LANG['WEB_KUBE_RECOVERY_USE_LOCAL_SNAPSHOT']; ?></label>
                                                                                    <div class="col-md-2">
                                                                                        <input  checked type="checkbox" id="firstSnapshoot" data-size="small" class="make-switch" data-on-color="primary" data-off-color="info"
                                                                                            data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                            data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                    </div>
                                                                                    <div class="col-md-2">
                                                                                        <a class="popovers" data-container="body" data-trigger="hover" data-html="true"
                                                                                        data-placement="right" data-content="<?php echo $LANG['WEB_KUBE_RECOVERY_USE_LOCAL_SNAPSHOT_TIPS']; ?>">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                            
                                                                                
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <!-- 资源监测 -->
                                                                    <div class="tab-pane" id="resources_monitoring">
                                                                        <div class="col-md-12 ">
                                                                            <div class = "col-md-12 advanced-detail-conf-district">
                                                                            <div class="form-group mt45">
                                                                                <label class="control-label col-md-3"><?php echo $LANG['WEB_KUBE_RECOVERY_SKIP_OR_OVERRIDE_RESOURCE']; ?></label>
                                                                                <div class="col-md-6">
                                                                                    <div class="row">
                                                                                        <label class="control-label"><input type="radio" value="1" name="overlay_resource" class="icheck" /><?php echo $LANG['WEB_KUBE_RECOVERY_SKIP_EXISTING_RESOURCES']; ?></label>
                                                                                    </div>
                                                                                    <div class="row">
                                                                                        <label class="control-label"><input type="radio" value="2" name="overlay_resource" class="icheck" /><?php echo $LANG['WEB_KUBE_RECOVERY_SKIP_EXISTING_AND_UPDATED_RESOURCES']; ?></label>
                                                                                    </div>
                                                                                    <div class="row">
                                                                                        <label class="control-label"><input type="radio" checked value="3" name="overlay_resource" class="icheck" /><?php echo $LANG['WEB_KUBE_RECOVERY_OVERRIDE_EXISTING_RESOURCES']; ?></label>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <label class="control-label col-md-3"><?php echo $LANG['WEB_KUBE_RECOVERY_RELEASE_WORKLOAD_NODE_LIMIT']; ?></label>
                                                                                <div class="col-md-6">
                                                                                    <div class="row">
                                                                                        <label class="control-label"><input type="checkbox" checked value="unset_node_affinity" name="load_node" class="icheck" /><?php echo $LANG['WEB_KUBE_RECOVERY_REMOVE_NODE_AFFINITY']; ?></label>
                                                                                    </div>
                                                                                    <div class="row">
                                                                                        <label class="control-label"><input type="checkbox" checked  value="unset_node_selector" name="load_node" class="icheck" /><?php echo $LANG['WEB_KUBE_RECOVERY_REMOVE_NODE_SELECTOR']; ?></label>
                                                                                    </div>
                                                                                    <div class="row">
                                                                                        <label class="control-label"><input type="checkbox" checked  value="unset_node_name" name="load_node" class="icheck" /><?php echo $LANG['WEB_KUBE_RECOVERY_REMOVE_NODE_NAME']; ?></label>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            </div>
                                                                        </div>

                                                                    </div>
                                                                    <!-- 重试 -->
                                                                    <div class="tab-pane" id="retry_config">
                                                                    </div>
                                                                    <div class="tab-pane" id="overload_protection">
                                                                        <div class="col-md-12 ">
                                                                            
                                                                            <div class = "col-md-12 advanced-detail-conf-district">
                                                                                <!-- 合并模式 -->
                                                                                <div class="form-group mt45">
                                                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE'] ?></label>
                                                                                    <div class="col-md-6">
                                                                                        <div class="col-md-3 form-group-content">
                                                                                            <input type="checkbox" checked id="ignore_resource_limiting_flag" class="make-switch" data-on-color="primary" data-size="small" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                        </div>
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
                                                <!-- 脚本配置 -->
                                                <div class="tab-pane " id="tab_script">
                                                    <div class="panel-body">
                                                        <!-- 脚本配置 -->
                                                        <div class="col-md-12 ">
                                                            <div class = "col-md-12 advanced-detail-conf-district">
                                                                <div class="form-group mt45">
                                                                    <label class="control-label col-md-3 incmodellabel form-group-label">
                                                                    <?php echo $LANG['WEB_KUBE_RECOVERY_HOOK_SCRIPT']; ?>
                                                                    </label>
                                                                    <div class="col-md-4 form-group-content">
                                                                        <select class="form-control" id="hookstype">
                                                                            <option value="0"><?php echo $LANG['UI_PUBLIC_OFF_TWO']; ?></option>
                                                                            <option value="SH"><?php echo $LANG['WEB_KUBE_BACKUP_SH_SCRIPT']; ?></option>
                                                                            <!-- <option value="CUSTOMSCRIPT">CustomScript脚本
                                                                            </option> -->
                                                                        </select>
                                                                    </div>
                                                                    <!-- <div class="col-md-2 vmbackup-mt10_en mt5_cn">
                                                                        <a class="popovers incmodeltips"
                                                                        data-container="body" data-trigger="hover"
                                                                        data-html="true"
                                                                        data-placement="right"
                                                                        data-content="??????">
                                                                            <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                    </div> -->
                                                                </div>
                                                                <div class="form-group">
                                                                    <div class="shDiv display-none">
                                                                        <div class="form-group">
                                                                            <label class="control-label col-md-3 form-group-label">
                                                                            <?php echo $LANG['WEB_KUBE_RECOVERY_EXECUTE_CONTAINER_ENV']; ?>
                                                                            </label>
                                                                            <div class="col-md-4 form-group-content">
                                                                                <select class="form-control" id="inPod_mode">
                                                                                    <option value="ALL"><?php echo $LANG['WEB_KUBE_RECOVERY_EXECUTE_ALL_PODS']; ?></option>
                                                                                    <option value="SPECIFIED"><?php echo $LANG['WEB_KUBE_RECOVERY_EXECUTE_SPECIFIED_PODS']; ?></option>
                                                                                    <option value="MATCHED"><?php echo $LANG['WEB_KUBE_RECOVERY_EXECUTE_MATCHED_PODS']; ?></option>
                                                                                    <option value="CUSTOM_IMAGE"><?php echo $LANG['WEB_KUBE_RECOVERY_EXECUTE_CUSTOM_IMAGE']; ?></option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="specified_pod display-none">
                                                                            <div class="form-group">
                                                                                <label class="control-label col-md-3">
                                                                                <span class="required">* </span>
                                                                                <?php echo $LANG['WEB_KUBE_BACKUP_NAMESPACE']; ?>
                                                                                </label>
                                                                                <div class="col-md-4">
                                                                                    <input class="form-control input-sm"
                                                                                        id="specified_namespaces">
                                                                                </div>
                                                                                <div class="col-md-1 vmbackup-mt10_en mt5_cn">
                                                                                    <a class="popovers" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php echo $LANG['WEB_KUBE_REQUIRED_NO_WILDCARD']; ?>" data-original-title="" title="">
                                                                                        <i class="viconfont vicon-tishi"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <label class="control-label col-md-3">
                                                                                <span class="required">* </span>
                                                                                <?php echo $LANG['WEB_KUBE_BACKUP_POD_NAME']; ?>
                                                                                </label>
                                                                                <div class="col-md-4">
                                                                                    <input class="form-control input-sm"
                                                                                        id="specified_pod">
                                                                                </div>
                                                                                <div class="col-md-1 vmbackup-mt10_en mt5_cn">
                                                                                    <a class="popovers" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php echo $LANG['WEB_KUBE_REQUIRED_NO_WILDCARD']; ?>" data-original-title="" title="">
                                                                                        <i class="viconfont vicon-tishi"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <label class="control-label col-md-3">
                                                                                <?php echo $LANG['WEB_KUBE_BACKUP_CONTAINER']; ?>
                                                                                </label>
                                                                                <div class="col-md-4">
                                                                                    <input class="form-control input-sm"
                                                                                        id="specified_container">
                                                                                </div>
                                                                                <div class="col-md-1 vmbackup-mt10_en mt5_cn">
                                                                                    <a class="popovers" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php echo $LANG['WEB_KUBE_OPTIONAL_USE_FIRST_CONTAINER']; ?>" data-original-title="" title="">
                                                                                        <i class="viconfont vicon-tishi"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="matched_pod display-none">
                                                                            <div class="form-group">
                                                                                <label class="control-label col-md-3">
                                                                                <span class="required">* </span>
                                                                                <?php echo $LANG['WEB_KUBE_BACKUP_NAMESPACE']; ?>
                                                                                </label>
                                                                                <div class="col-md-4">
                                                                                    <input class="form-control input-sm"
                                                                                        id="matched_namespaces">
                                                                                </div>
                                                                                <div class="col-md-1 vmbackup-mt10_en mt5_cn">
                                                                                    <a class="popovers" data-container="body" data-trigger="hover" data-placement="right" data-placement="right" data-content="<?php echo htmlspecialchars($LANG['WEB_KUBE_REQUIRED_MUST_WILDCARD'], ENT_QUOTES, 'UTF-8'); ?>" data-original-title="" title="">
                                                                                        <i class="viconfont vicon-tishi"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <label class="control-label col-md-3">
                                                                                <?php echo $LANG['WEB_KUBE_BACKUP_POD_NAME']; ?>
                                                                                </label>
                                                                                <div class="col-md-4">
                                                                                    <input class="form-control input-sm"
                                                                                        id="matched_pod">
                                                                                </div>
                                                                                <div class="col-md-1 vmbackup-mt10_en mt5_cn">
                                                                                    <a class="popovers" data-container="body" data-trigger="hover" data-placement="right" data-placement="right" data-content="<?php echo htmlspecialchars($LANG['WEB_KUBE_REQUIRED_MUST_WILDCARD'], ENT_QUOTES, 'UTF-8'); ?>" data-original-title="" title="">
                                                                                        <i class="viconfont vicon-tishi"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <label class="control-label col-md-3">
                                                                                <?php echo $LANG['WEB_KUBE_BACKUP_LABEL_NAME']; ?>
                                                                                </label>
                                                                                <div class="col-md-4">
                                                                                    <input class="form-control input-sm"
                                                                                        id="matched_label">
                                                                                </div>
                                                                                <div class="col-md-1 vmbackup-mt10_en mt5_cn">
                                                                                    <a class="popovers" data-container="body" data-trigger="hover" data-placement="right" data-placement="right" data-content="<?php echo htmlspecialchars($LANG['WEB_KUBE_REQUIRED_MUST_WILDCARD'], ENT_QUOTES, 'UTF-8'); ?>" data-original-title="" title="">
                                                                                        <i class="viconfont vicon-tishi"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="image_pod display-none">
                                                                            <div class="form-group">
                                                                                <label class="control-label col-md-3">
                                                                                <span class="required">* </span>
                                                                                <?php echo $LANG['WEB_KUBE_BACKUP_IMAGE_NAME']; ?>
                                                                                </label>
                                                                                <div class="col-md-4">
                                                                                    <input class="form-control input-sm"
                                                                                        id="image_images">
                                                                                </div>
                                                                                <div class="col-md-1 vmbackup-mt10_en mt5_cn">
                                                                                    <a class="popovers" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php echo $LANG['WEB_KUBE_REQUIRED']; ?>" data-original-title="" title="">
                                                                                        <i class="viconfont vicon-tishi"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <label class="control-label col-md-3">
                                                                                <span class="required">* </span>
                                                                                <?php echo $LANG['WEB_KUBE_BACKUP_RUNNING_NAMESPACE']; ?>
                                                                                </label>
                                                                                <div class="col-md-4">
                                                                                    <input class="form-control input-sm"
                                                                                        id="image_namespaces">
                                                                                </div>
                                                                                <div class="col-md-1 vmbackup-mt10_en mt5_cn">
                                                                                    <a class="popovers" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php echo $LANG['WEB_KUBE_REQUIRED']; ?>" data-original-title="" title="">
                                                                                        <i class="viconfont vicon-tishi"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="container_pod display-none">
                                                                        </div>
                                                                    </div>
                                                                    <div class="sh_code">
                                                                        <div class="form-group custom_div display-none">
                                                                            <div class="col-md-3">
                                                                            </div>
                                                                            <div class="col-md-9" style="left: -96px;">
                                                                                <div class="custom_script_ace"></div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group sh_div display-none">
                                                                            <!-- <div class="col-md-12"> -->
                                                                                <div class="row row-stepthree">
                                                                                    <div class="col-md-2 col-sm-3 col-xs-3 nav-tab-radios">
                                                                                        <ul class="nav nav-tabs tabs-left min-height400">
                                                                                            <li class="commonLi active cm-advanced-public-conf-title">
                                                                                                <a href="#tab_script_before" class="" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                                                                <?php echo $LANG['WEB_KUBE_RECOVERY_BEFORE_RESTORE']; ?>
                                                                                                </a>
                                                                                            </li>
                                                                                            <li class="">
                                                                                                <a href="#tab_script_success" class="" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                                                                <?php echo $LANG['WEB_KUBE_RECOVERY_AFTER_SUCCESS']; ?>
                                                                                                </a>
                                                                                            </li>
                                                                                            <li class="">
                                                                                                <a href="#tab_script_failure" class="" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                                                                <?php echo $LANG['WEB_KUBE_RECOVERY_AFTER_FAILURE']; ?>
                                                                                                </a>
                                                                                            </li>
                                                                                        </ul>
                                                                                    </div>
                                                                                    <div class="col-md-10 col-sm-9 col-xs-9" style="left: -13px;">
                                                                                        <div class="tab-content">
                                                                                            <!-- 公共配置 -->
                                                                                            <div class="tab-pane active" id="tab_script_before">
                                                                                                <div class="col-md-12 ">
                                                                                                    <div class="before_script_ace"></div>
                                                                                                </div>
                                                                                            </div>
                                                                                            <!-- 快照配置 -->
                                                                                            <div class="tab-pane" id="tab_script_success">
                                                                                                <div class="col-md-12 ">
                                                                                                    <div class="after_script_success_ace"></div>
                                                                                                </div>
                                                                                            </div>
                                                                                            <!-- 快照配置 -->
                                                                                            <div class="tab-pane" id="tab_script_failure">
                                                                                                <div class="col-md-12 ">
                                                                                                    <div class="after_script_failure_ace"></div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            <!-- </div> -->
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
                                            <div class="alert alert-danger display-none jobnametipjobnametip"></div>
                                            <div class="form-group mb0 mb10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_JOB_RNAME'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <div class="input-icon right">
                                                        <input type="text" maxlength="64" class="form-control" id="jobname"  onkeyup="customInputValidate('string', this.value, $(this))" id="jobname"/>
                                                        <span class="help-block "><?php echo $LANG['UI_JOB_RNAME_TIPS'] ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <h4 class="form-section"></h4>
                                            <div class="form-group mb0 mb10">
                                                <label class="control-label col-md-3"><?php echo $LANG['WEB_KUBE_RECOVERY_SOURCE_CLUSTER']; ?>
                                                    </label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static" id="sourceBackupClusterDesShow">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mb10">
                                                <label class="control-label col-md-3"><?php echo $LANG['WEB_KUBE_RECOVERY_BACKUP_TIMEPOINT']; ?></label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static" id="backupTimepointDesShow">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mb10">
                                                <label class="control-label col-md-3"><?php echo $LANG['WEB_KUBE_RECOVERY_RESOURCE_LIST']; ?></label>
                                                <div class="col-md-9">
                                           <textarea readonly class="form-control-static" id ="resourceListDesShow" style="min-height: 100px;
                                                resize: none;
                                                overflow-y: scroll;
                                                border: solid 1px #E6E6E6;
                                                outline: none;
                                                margin-top: 7px;
                                                width: 100%;">
                                            </textarea>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mb10">
                                                <label class="control-label col-md-3"><?php echo $LANG['WEB_KUBE_RECOVERY_RESTORE_CLUSTER']; ?></label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static" id="targetClusterDesShow">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mb10 redirectNamespaceDesShow_div">
                                                <label class="control-label col-md-3"><?php echo $LANG['WEB_KUBE_NAMESPACE_DETAILS']; ?>:</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static" id="redirectNamespaceDesShow">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mb10 coverPvcDesShow_div">
                                                <label class="control-label col-md-3"><?php echo $LANG['WEB_KUBE_PVC_DETAILS']; ?>:
                                                    </label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static" id="coverPvcDesShow">
                                                    </p>
                                                </div>
                                            </div>
                                            <!-- <div class="form-group mb0 mb10">
                                                <label class="control-label col-md-3"><?php echo $LANG['WEB_KUBE_RECOVERY_PVC_DETAILS_SHOW']; ?></label>
                                                <div class="col-md-9">
                                           <textarea readonly class="form-control-static" id ="pvcDetailsDesShow" style="min-height: 100px;
                                                resize: none;
                                                overflow-y: scroll;
                                                border: solid 1px #E6E6E6;
                                                outline: none;
                                                margin-top: 7px;
                                                width: 100%;">
                                            </textarea>
                                                </div>
                                            </div> -->
                                            <div class="form-group mb0 mb10">
                                                <label class="control-label col-md-3"><?php echo $LANG['WEB_KUBE_TIME_STRATEGY']; ?>:</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static" id="timeDesShow">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mb10">
                                                <label class="control-label col-md-3"><?php echo $LANG['WEB_KUBE_RECOVERY_SPEED_STRATEGY']; ?></label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static" id="speedDesShow">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mb10">
                                                <label class="control-label col-md-3"><?php echo $LANG['WEB_KUBE_RECOVERY_TRANSFER_STRATEGY_SHOW']; ?></label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static" id="transferDesShow">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mb10">
                                                <label class="control-label col-md-3"><?php echo $LANG['WEB_KUBE_SECURITY_POLICY']; ?>:</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static" id="safeDesShow">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mb10">
                                                <label class="control-label col-md-3"><?php echo $LANG['WEB_KUBE_ADVANCED_SETTINGS']; ?>:</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static" id="highStrategyDesShow">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mt10 retryShow"></div>
                                            <div class="form-group mb0 mb10">
                                                <label class="control-label col-md-3"><?php echo $LANG['WEB_KUBE_RECOVERY_HOOK_SCRIPT_SHOW']; ?></label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static" id="hookDesShow">
                                                    </p>
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
                </form>
            </div>

        </div>
        
    </div>


    <!-- modal start -->
    <div id="pvcsmodal" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-backdrop="static">
        <div class="modal-header " style="display: flex;padding: 10px;">
            <i class="viconfont vicon-danchuangtianjia1" style="padding: 12px 10px;font-size: 20px;"></i>
            <h4 style="font-weight: 400;font-size: 16px;color: #333333;"><?php echo $LANG['WEB_KUBE_RECOVERY_SELECTED_PVCS']; ?></h4>
        </div>
        <div class="modal-body">
            <div class="portlet-body" style="padding: 20px;height: 00px">
                <div class="table-container">
                    <!-- 表格 -->
                    <table id="pvcs_table"></table>
                    <!-- 表格 -->
                </div>
            </div>
        </div>
        <!-- modal-footer -->
        <div class="modal-footer">
            <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
            <button type="button" class="btn btn-primary"
                    id="edit_script_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
        </div>
    </div>
    <!-- modal end -->


    <!-- modal start -->
    <div id="addNamespaceModal" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-backdrop="static">
        <div class="modal-header " style="display: flex;padding: 10px;">
            <input id="edit_script_uuid" style="display: none;">
            <i class="viconfont vicon-mingmingkongjian" style="padding: 12px 10px;font-size: 20px;"></i>
            <h4 style="font-weight: 400;font-size: 16px;color: #333333;"><?php echo $LANG['WEB_KUBE_RECOVERY_NAMESPACE_REDIRECT_MODAL']; ?></h4>
        </div>
        <div class="modal-body">
            <div class="portlet-body" style="padding: 20px;height: 00px">
                <form id="formaddNamespaceModal">
                    <div class="table-container">
                        <div class="form-group">
                            <label class="control-label col-md-3"><?php echo $LANG['WEB_KUBE_RECOVERY_OLD_NAMESPACE']; ?></label>
                            <div class="col-md-8">
                                <select class="form-control " id="namespaceOld">
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3"><?php echo $LANG['WEB_KUBE_RECOVERY_NEW_NAMESPACE']; ?></label>
                            <div class="input-icon right right_8  col-md-8" style="display: inline-block;">
                                <i class="fa"></i>
                                <input name="namespaceNew" class="form-control" placeholder="<?php echo $LANG['WEB_KUBE_RECOVERY_NEW_NAMESPACE_REQUIRED']; ?>" id="namespaceNew">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- modal-footer -->
        <div class="modal-footer">
            <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
            <button type="button" class="btn btn-primary"
                    id="addNamespaceSubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
        </div>
    </div>
    <!-- modal end -->


    <!-- drawer开始 -->
    <div class="drawer slide" id="resourceDetailsDrawer" data-placement="right" tabindex="-1" role="dialog"
         aria-labelledby="drawer-1-title" aria-hidden="true" style="width: 600px;">
        <div class="drawer-content drawer-content-scrollable" role="document">
            <div class="drawer-header help_header drawer-mine-header">
                <h4 class="drawer-title help_title drawer-mine-title" id="drawer-1-title">
                    <i class="viconfont vicon-bangzhuzhongxin help_icon_helpcenter drawer-icon-helpcenter"></i><?php echo $LANG['WEB_KUBE_RECOVERY_RESOURCE_DETAILS']; ?>
                    <a data-dismiss="drawer" aria-label="Close" class="drawer-close help_icon_close drawer-mine-icon-close"><i
                                class="viconfont vicon-guanbi"></i></a>
                </h4>

            </div>
            <div class="drawer-body help_body">
                <textarea class="code_msg" id="resourceDetails" disabled
                          style="height: 66px;"><?php echo 'cat $HOME/.kube/config' ?>
            </textarea>
            </div>
            <div class="drawer-footer">
                <!--            <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-primary" id="addsubmit">确 定</button>-->
                <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_CLOSE']; ?></button>
            </div>
        </div>
    </div>
    <!-- drawer结束 -->


</div>
<!-- END PAGE CONTENT-->


<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./scripts/components/timePointDetail/js/timePointDetail.js" type="text/javascript"></script>
<script src="./scripts/components/timePointDetail/js/virusHistoryTable.js" type="text/javascript"></script>
<script type="text/javascript" src="./assets/global/plugins/clipboard/clipboard.min.js"></script>
<script src="./scripts/backupData/clipboard.js" type="text/javascript"></script>
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
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
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
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.speedStrategy.js"></script>
<!-- <script type="text/javascript" src="./scripts/plugins/jquery/jquery.strategy.js"></script> -->
<script type="text/javascript" src="./scripts/plugins/jquery/backup-strategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/calendar/jquery.searchableSelect.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.taskCrowd.js"></script>
<script type="text/javascript" src="./scripts/platform/component/retry_strategy.js"></script>
<script type="text/javascript" src="./scripts/platform/component/transfer_network.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_complete.js"></script>
<script type="text/javascript" src="./assets/global/plugins/ace/src-min-noconflict/ace.js"></script>
<script type="text/javascript" src="./scripts/platform/component/vinScript.js"></script>
<script type="text/javascript" src="/assets/global/plugins/select2/select2-4.1.0/js/select2.min.js"></script>

<!-- END PAGE LEVEL PLUGINS -->
<script src="./scripts/kubernetes/kubernetes_recovery.js" type="text/javascript"></script>
