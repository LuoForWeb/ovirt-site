<?php include_once '../../tpl/permission.php'; ?>
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css"
      href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
<link rel="stylesheet" type="text/css"
      href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/simple-line-icons/simple-line-icons.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./scripts/plugins/calendar/jquery.searchableSelect.css"/>
<link rel="stylesheet" type="text/css" href="./css/platform/component/safe_virus.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/component/style.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/component/resource_pool.css" />

<?php session_start();
session_commit(); ?>
<!-- BEGIN PAGE HEADER-->
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row" style="height:100%;">
    <div class="col-md-12" style="height:100%;">
        <div class="portlet box blue-hoki backup-page" id="k8sbackupcontent">
            <div class="portlet-title">
                <div class="caption">
                    <i class="levelchild viconfont vicon-xinjianbeifen1"></i><?php echo $_GET['uuid'] ? $LANG['WEB_KUBE_BACKUP_KUBERNETES_CLUSTER_EDIT_BACKUP'] : $LANG['WEB_KUBE_BACKUP_KUBERNETES_CLUSTER_BACKUP']; ?>
                    <input type="hidden" id="uuid" value="<?php echo $_GET['uuid'] ?>">
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
                                                <div class="form-group src-wrap lockBackupDiv">
                                                    <div class="src-wrap__title">
                                                        <i class="levelchild viconfont vicon-k8s"></i><?php echo $LANG['WEB_KUBE_BACKUP_SELECT_BACKUP_SOURCE']; ?>
                                                    </div>
                                                    <div class="src-wrap__content">
                                                        <div class="src-wrap__content__search">
                                                            <div class="form-group">
                                                                <select class="bs-select form-control"
                                                                        style="height: 34px"
                                                                        data-show-subtext="true"
                                                                        id="k8s_backup_type">
                                                                    <option value="1" selected><?php echo $LANG['WEB_KUBE_BACKUP_BACKUP_BY_NAMESPACE']; ?></option>
                                                                    <option value="2"><?php echo $LANG['WEB_KUBE_BACKUP_BACKUP_BY_APPLICATION']; ?></option>
                                                                </select>
                                                                <div class="vm_tree_div" style="display:none;">
                                                                    <div class="searchDiv width100p">
                                                                        <div class="input-icon">
                                                                            <input type="text" maxlength="128" placeholder="<?php echo $LANG['WEB_KUBE_SEARCH_BY_KEYWORD']; ?>" class="form-control" id="searchKube"  onkeyup="customInputValidate('string', this.value, $(this))">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="src-wrap__content__ztree" style="height: calc(100% - 121px);">
                                                            <div class="three_tree" id="agent_div">
                                                                <ul id="agent_tree" class="ztree"></ul>
                                                            </div>
                                                            <div class="alert alert-block alert-info fade in display-hide" id="noKube">
                                                                <button type="button" class="close"
                                                                        data-dismiss="alert"></button>
                                                                <h4 class="alert-heading">
                                                                    <strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong>
                                                                </h4>
                                                                <?php echo $LANG['WEB_KUBE_NO_MATCHING_ITEMS'] ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-8 VMList-div" id="k8streeDiv">
                                                <!-- 已选择备份源 -->
                                                <div class="addTitle">
                                                    <span>
                                                        <i class="icon-action-redo" style="margin-right: 8px"></i>
                                                        <span><?php echo $LANG['WEB_KUBE_BACKUP_SELECTED_BACKUP_DATA']; ?></span>
                                                    </span>
                                                    
                                                    <span>
                                                        <a id="pvcSelected"><?php echo $LANG['WEB_KUBE_BACKUP_SELECTED_PERSISTENT_VOLUMES']; ?>  <span id="pvc_num">0/0</span></a>
                                                    </span>
                                                </div>

                                                <div class="addIt-list">
                                                    <ul class="feeds" id="allK8sTree">
                                                    </ul>

                                                    <div class="alert alert-block alert-info fade in" id="step1tips">
                                                        <button type="button" class="close" data-dismiss="alert"></button>
                                                        <h4 class="alert-heading">
                                                            <strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                                        <ol class="alert-ol">
                                                            <li><?php echo $LANG['WEB_KUBE_BACKUP_SELECT_NAMESPACES_OR_APPS']; ?></li>
                                                            <li>
                                                            <?php echo $LANG['WEB_KUBE_BACKUP_INCLUDE_PVC']; ?>
                                                            </li>
                                                            <li><?php echo $LANG['WEB_KUBE_BACKUP_ADD_TO_SELECTED_DATA']; ?></li>
                                                            <li><?php echo $LANG['WEB_KUBE_BACKUP_CROSS_CLUSTER_BACKUP']; ?></li>
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
                                                <ul class="nav nav-tabs">
                                                    <li class="commonLi active">
                                                        <a href="#tab_common" class=""
                                                           data-content="<?php echo $LANG['WEB_KUBE_STRATEGY_GROUP_COMMON'] ?>"
                                                           data-container="body" data-trigger="hover"
                                                           data-placement="top" data-toggle="tab">
                                                            <i class="viconfont vicon-celve1"></i> <?php echo $LANG['UI_BACKUP_COMMON_STRATEGY'] ?>
                                                        </a>
                                                    </li>
                                                    <li class="transferLi">
                                                        <a href="#tab_transfer" class=""
                                                           data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_TIPS'] ?>"
                                                           data-container="body" data-trigger="hover"
                                                           data-placement="top" data-toggle="tab">
                                                            <i class="viconfont vicon-a-chuanshu"></i> <?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?>
                                                        </a>
                                                    </li>
                                                    <li class="tab_safety">
                                                        <a href="#tab_safety" class="" data-content="<?php echo $LANG['UI_MACHINE_OS_DATA_SAFE_POLICY'] ?>" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                        <i class="viconfont vicon-anquancelve"></i> <?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?> </a>
                                                    </li>   
                                                    <li class="highLi">
                                                        <a href="#tab_other" class=""
                                                           data-content="<?php echo $LANG['UI_BACKUP_HIGH_CONFIG_DES'] ?>"
                                                           data-container="body" data-trigger="hover"
                                                           data-placement="top" data-toggle="tab">
                                                            <i class="viconfont vicon-gaojipeizhi"></i> <?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?>
                                                        </a>
                                                    </li>
                                                    <li class="scriptLi">
                                                        <a href="#tab_script" class="" data-content="<?php echo $LANG['WEB_KUBE_BACKUP_SCRIPT_CONFIGURATION']; ?>" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                        <i class="viconfont vicon-gaojipeizhi"></i> <?php echo $LANG['WEB_KUBE_BACKUP_SCRIPT_CONFIGURATION']; ?></a>
                                                    </li>
                                                </ul>
                                                <div class="tab-content">
                                                    <!-- 通用策略 -->
                                                    <div class="tab-pane active" id="tab_common">
                                                        <div class="panel-body">
                                                            <div class="form-group">
                                                                <div class="strategy-group-select-wrapper"></div>
                                                                <div id="backupStrategyDiv" class="col-md-10 col-md-offset-1">
                                                                </div>
                                                            </div>
                                                          
                                                        </div>
                                                    </div>
                                                    <!-- 传输策略 -->
                                                    <div class="tab-pane" id="tab_transfer">
                                                        <div class="panel-body">
                                                            <div class="tabbable-custom col-md-10">

                                                            <div class="form-group">
                                                                <label class="control-label col-md-3 form-group-label"><?php echo $LANG['WEB_KUBE_BACKUP_ENCRYPT_TRANSMISSION']; ?></label>
                                                                <div class="col-md-3 form-group-content">
                                                                    <input type="checkbox" id="encrypt" class="make-switch" data-on-color="primary" data-size="small" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                    <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['WEB_KUBE_BACKUP_SSL_ENCRYPTION']; ?>">
                                                                        <i class="viconfont vicon-tishi"></i>
                                                                    </a>
                                                                </div>
                                                            </div>

                                                            <div class="form-group encrypt_method_div" style="display:none;">
                                                                <label class="control-label col-md-3"><?php echo $LANG['WEB_KUBE_BACKUP_ENCRYPTION_ALGORITHM']; ?></label>
                                                                <div class="col-md-4">
                                                                    <select class="form-control" id="encrypt_method">
                                                                        <option value="1">RSA</option>
                                                                        <?php
                                                                            if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
                                                                                echo '<option value="2">SM2</option>';
                                                                            }
                                                                        ?>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-2 mt10" style="display:none;">
                                                                    <a class="popovers" data-container="body" data-trigger="hover" data-html="true" 
                                                                    data-placement="right" data-content="none">
                                                                    <i class="viconfont vicon-tishi"></i>
                                                                    </a>
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
                                                                    <label class="control-label col-md-3"><?php echo $LANG['WEB_KUBE_BACKUP_TRANSFER_THREADS']; ?></label>
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
                                                    <!-- 安全策略 -->
                                                    <div class="tab-pane " id="tab_safety">
                                                        <div class="panel-body">
                                                            <div class="tabbable-custom">
                                                                <!-- WORM -->
                                                                <div id="wormConfig"></div>
                                                                <!-- 完整性校验 -->
                                                                <div id="completionConfig"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- 高级配置 -->
                                                    <div class="tab-pane " id="tab_other">
                                                        <div class="panel-body">
                                                            <div class="row row-stepthree">
                                                                <div class="col-md-2 col-sm-3 col-xs-3 nav-tab-radios">
                                                                    <ul class="nav nav-tabs tabs-left min-height400">
                                                                        <li class="commonLi active cm-advanced-public-conf-title">
                                                                            <a href="#tab_common_conf" class="" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                                            <?php echo $LANG['WEB_KUBE_STORAGE']; ?> </a>
                                                                        </li>
                                                                        <li class="">
                                                                            <a href="#tab_cache_conf" class="" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                                            <?php echo $LANG['WEB_KUBE_BACKUP_SNAPSHOT']; ?> </a>
                                                                        </li>
                                                                        <li class="">
                                                                            <a href="#retry_config" class="" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                                            <?php echo $LANG['WEB_KUBE_BACKUP_RETRY']; ?> </a>
                                                                        </li>
                                                                        <li class="">
                                                                            <a href="#overload_protection" class="" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                                            <?php echo $LANG['UI_STRATEGY_OVER_LOAD_LABEL'] ?> </a>
                                                                        </li>
                                                                      
                                                                    </ul>
                                                                </div>
                                                                <div class="col-md-10 col-sm-9 col-xs-9">
                                                                    <div class="tab-content">
                                                                        <!-- 公共配置 -->
                                                                        <div class="tab-pane active" id="tab_common_conf">
                                                                            <div class="col-md-12 ">
                                                                            
                                                                                <div class = "col-md-12 advanced-detail-conf-district">
                                                                                    <!-- 存储数据库大小 -->
                                                                                    <div class="form-group standbyconf mt45">
                                                                                        <label class="control-label col-md-3"><?php echo $LANG['WEB_KUBE_BLOCK_SIZE'] ?>
                                                                                        </label>
                                                                                        <div class="col-md-4">
                                                                                            <select class="form-control " id="storage_block_size">
                                                                                                <option value=64>64 KB</option>
                                                                                                <option value=128>128 KB</option>
                                                                                                <option value=256>256 KB</option>
                                                                                                <option value=512>512 KB</option>
                                                                                                <option value=1024 selected>1024 KB</option>
                                                                                                <option value=2048>2048 KB</option>
                                                                                            </select>
                                                                                        </div>
                                                                                        <div class="col-md-1 vmbackup-mt10_en mt5_cn">
                                                                                            <a class="popovers" data-container="body" data-trigger="hover"
                                                                                            data-placement="right" data-content='<?php echo $LANG['WEB_KUBE_STORAGE_BLOCK_SIZE_DES'] ?>'>
                                                                                                <i class="viconfont vicon-tishi"></i>
                                                                                            </a>
                                                                                        </div>
                                                                                    </div>
                                            
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <!-- 快照配置 -->
                                                                        <div class="tab-pane" id="tab_cache_conf">
                                                                            <div class="col-md-12 ">
                                                                                
                                                                                <div class = "col-md-12 advanced-detail-conf-district">
                                                                                    <div class="form-group mt45">
                                                                                        <label class="control-label col-md-3 snapshotlabel form-group-label"><?php echo $LANG['WEB_KUBE_BACKUP_CLUSTER_LOCAL_PVC_SNAPSHOT_COPIES']; ?></label>
                                                                                        <div class="col-md-4 form-group-content">
                                                                                            <div class="keeplocalsnapshotsDiv">
                                                                                                <div class="input-group spinner-group">
                                                                                                    <input type="text"
                                                                                                        id="keeplocalsnapshots"
                                                                                                        onkeyup="value=value.replace(/[^\d]/g,'')"
                                                                                                        class="spinner-input form-control input-sm">
                                                                                                    <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                                        <button type="button"
                                                                                                                class="btn spinner-up default input-sm">
                                                                                                            <i class="fa fa-angle-up"></i>
                                                                                                        </button>
                                                                                                        <button type="button"
                                                                                                                class="btn spinner-down default input-sm">
                                                                                                            <i class="fa fa-angle-down"></i>
                                                                                                        </button>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-md-1 vmbackup-mt10_en mt5_cn">
                                                                                            <a class="popovers highSpeed"
                                                                                            data-container="body" data-trigger="hover"
                                                                                            data-html="true"
                                                                                            data-placement="right"
                                                                                            data-content="<?php echo $LANG['WEB_KUBE_BACKUP_IMPROVE_RECOVERY_SPEED']; ?>">
                                                                                                <i class="viconfont vicon-tishi"></i>
                                                                                            </a>
                                                                                        </div>
                                                                                    </div>
                                                                                
                                                                                    <div class="form-group snapshotExceptiondiv">
                                                                                        <label class="control-label col-md-3 snapshotExceptionlabel form-group-label"><?php echo $LANG['WEB_KUBE_BACKUP_IGNORE_SNAPSHOT_EXCEPTIONS']; ?></label>
                                                                                        <div class="col-md-4 form-group-content">
                                                                                            <input type="checkbox" id="snapshotException"
                                                                                                checked class="make-switch"
                                                                                                data-size="small" data-on-color="primary"
                                                                                                data-off-color="info"
                                                                                                data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                                data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                        </div>
                                                                                        <div class="col-md-1 vmbackup-mt10_en mt5_cn">
                                                                                            <a class="popovers highSpeed"
                                                                                            data-container="body" data-trigger="hover"
                                                                                            data-html="true"
                                                                                            data-placement="right"
                                                                                            data-content="<?php echo $LANG['WEB_KUBE_BACKUP_DEFAULT_HANDLING']; ?>">
                                                                                                <i class="viconfont vicon-tishi"></i>
                                                                                            </a>
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
                                                                                                <input type="checkbox" id="ignore_resource_limiting_flag" class="make-switch" data-on-color="primary" data-size="small" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
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
                                                                        <?php echo $LANG['WEB_KUBE_BACKUP_HOOK_SCRIPT']; ?>
                                                                        </label>
                                                                        <div class="col-md-4 form-group-content">
                                                                            <select class="form-control" id="hookstype">
                                                                                <option value="0"><?php echo $LANG['UI_PUBLIC_CLOSE']; ?></option>
                                                                                <option value="SH"><?php echo $LANG['WEB_KUBE_BACKUP_SH_SCRIPT']; ?></option>
                                                                                <!-- <option value="CUSTOMSCRIPT">CustomScript脚本
                                                                                </option> -->
                                                                            </select>
                                                                        </div>
                                                                      
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <div class="shDiv display-none">
                                                                            <div class="form-group">
                                                                                <label class="control-label col-md-3 form-group-label">
                                                                                <?php echo $LANG['WEB_KUBE_BACKUP_EXECUTION_ENVIRONMENT']; ?>
                                                                                </label>
                                                                                <div class="col-md-4 form-group-content">
                                                                                    <select class="form-control" id="inPod_mode">
                                                                                        <option value="ALL"><?php echo $LANG['WEB_KUBE_BACKUP_ALL_PODS']; ?></option>
                                                                                        <option value="SPECIFIED"><?php echo $LANG['WEB_KUBE_BACKUP_SPECIFIED_PODS']; ?></option>
                                                                                        <option value="MATCHED"><?php echo $LANG['WEB_KUBE_BACKUP_MATCHED_PODS']; ?></option>
                                                                                        <option value="CUSTOM_IMAGE"><?php echo $LANG['WEB_KUBE_BACKUP_CUSTOM_IMAGE']; ?></option>
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
                                                                                        <a class="popovers" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php echo htmlspecialchars($LANG['WEB_KUBE_REQUIRED_MUST_WILDCARD'], ENT_QUOTES, 'UTF-8'); ?>" data-original-title="" title="">
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
                                                                                        <a class="popovers" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php echo htmlspecialchars($LANG['WEB_KUBE_REQUIRED_MUST_WILDCARD'], ENT_QUOTES, 'UTF-8'); ?>" data-original-title="" title="">
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
                                                                                        <a class="popovers" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php echo htmlspecialchars($LANG['WEB_KUBE_REQUIRED_MUST_WILDCARD'], ENT_QUOTES, 'UTF-8'); ?>" data-original-title="" title="">
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
                                                                                                        <?php echo $LANG['WEB_KUBE_BACKUP_BEFORE_BACKUP']; ?>
                                                                                                    </a>
                                                                                                </li>
                                                                                                <li class="">
                                                                                                    <a href="#tab_script_success" class="" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                                                                        <?php echo $LANG['WEB_KUBE_BACKUP_AFTER_SUCCESS']; ?>
                                                                                                    </a>
                                                                                                </li>
                                                                                                <li class="">
                                                                                                    <a href="#tab_script_failure" class="" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                                                                        <?php echo $LANG['WEB_KUBE_BACKUP_AFTER_FAILURE']; ?>
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
                                    <div class="tab-pane" id="tab4">
                                        <div class="tab-pane__body">
                                            <div class="tab-pane__body__form">
                                                <!-- <div class="alert alert-danger display-none jobnametip"></div> -->
                                                <div class="form-group mb10 mb10">
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
                                                    <label class="control-label col-md-3"><?php echo $LANG['WEB_KUBE_BACKUP_RESOURCE_LIST']; ?>
                                                        :</label>
                                                    <div class="col-md-9">
                                            <textarea readonly class="form-control-static" id="resourceDesShow" style="min-height: 100px;
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
                                                    <label class="control-label col-md-3"><?php echo $LANG['WEB_KUBE_BACKUP_PVC_PERSISTENT_VOLUME_LIST']; ?>
                                                        :</label>
                                                    <div class="col-md-9">
                                            <textarea readonly class="form-control-static" id="pvcsDesShow" style="min-height: 100px;
                                                resize: none;
                                                overflow-y: scroll;
                                                border: solid 1px #E6E6E6;
                                                outline: none;
                                                margin-top: 7px;
                                                width: 100%;">
                                            </textarea>
                                                    </div>
                                                </div>
                                                <h4 class="form-section"></h4>
                                                <div class="form-group mb0 mt10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TARGET_STORAGE'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static  storageInfoShow">
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TARGET_NODE'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static  nodeInfoShow">
                                                        </p>
                                                    </div>
                                                </div>


                                                <h4 class="form-section"></h4>
                                               
                                                <div class="form-group mb0 mb10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['WEB_KUBE_BACKUP_STRATEGY_TIME']; ?>
                                                        :</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static" id="backuptypeinfoDesShow">
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mb10 ">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_STORAGE_STRATEGY']; ?>
                                                        :</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static" id="storeInfoDesShow">
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mb10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_RESERVE_STRATEGY']; ?>
                                                        :</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static" id="reservetypeDesShow">
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mb10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY']; ?>
                                                        :</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static" id="transferDesShow">
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mb10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_GLOBAL_SPEED_STRATEGY']; ?>
                                                        :</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static" id="speedDesShow">
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?> :</label>
                                                    <div class="col-md-9">
                                                        <div class="form-control-static safetyshow">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mb10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_HIGH_CONFIG']; ?> :</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static" id="highDesShow">
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10 retryShow"></div>
                                                <div class="form-group mb0 mb10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['WEB_KUBE_BACKUP_SCRIPT_CONFIGURATION']; ?> :</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static" id="scriptDesShow">
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions forn-actions--create">
                            <div class="row">
                                <div class="col-md-offset-6 col-md-6">
                                    <a href="javascript:;" class="btn default button-previous">
                                        <i class="viconfont vicon-shangyibu"></i> <?php echo $LANG['UI_PUBLIC_PREV_STEP'] ?>
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
                </div>
            </div>
        </div>
        <!-- BEGIN RATE LIMIT MODAL -->
        <div id="speedlimitModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first"
             data-backdrop="static">
            <div class="modal-header ">
                <button type="button" class="close" data-dismiss="modal"></button>
                <h4 class="modal-title"><i
                            class="iconfont icon-xiansucelve"></i> <?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="portlet-body">

                    <div class="form-group ">
                        <label class="control-label col-md-2">  <?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_TYPE'] ?>
                        </label>
                        <div class="col-md-6">
                            <select class="form-control select2me inline-block" id="speedModeType" style="width:203px;">
                                <option value="1"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_STRATEGY'] ?></option>
                                <option value="2"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_FOREVER'] ?></option>
                            </select>
                            <a class="popovers ml15" data-container="body" data-trigger="hover"
                               data-placement="right"
                               data-content="<?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_TYPE_TIPS'] ?>">
                                <i class="fa fa-info-circle fa-lg"></i>
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
                        <div class="col-md-4">
                            <div id="rateDiv" style="display:inline-flex;">
                                <div class="input-icon">
                                    <div id="speedSpinnerNum">
                                        <div class="input-group">
                                            <input type="text" id="speedSpinnerNumInput" style="text-align: center;"
                                                   onkeyup="value=value.replace(/[^\d]/g,'')"
                                                   class="spinner-input form-control">
                                            <div class="spinner-buttons input-group-btn">
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
                                    <select id="unit"
                                            style="width: 60px; margin-left: 10px;height: 33px;text-align:center;">
                                        <option value="1">KB/s</option>
                                        <option selected value="2">MB/s</option>
                                        <option value="3">GB/s</option>
                                    </select>
                                </div>
                                <a style="display:block; margin-top:8px;" class="popovers ml15" data-container="body"
                                   data-trigger="hover"
                                   data-placement="right"
                                   data-content="<?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_VALUE_TIPS'] ?>">
                                    <i class="fa fa-info-circle fa-lg"></i>
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- modal-footer -->
            <div class="modal-footer">
                <button type="button" data-dismiss="modal"
                        class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
                <button type="button" class="btn btn-primary"
                        id="speed_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
            </div>
        </div>
        <!-- END RATE LIMIT MODAL -->
    </div>


    <!-- modal start -->
    <div id="pvcsmodal" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-backdrop="static">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"  id="close_pvc_table"></button>
            <h4 class="modal-title" style="display: flex;align-items: center"><i class="viconfont vicon-juan1"></i><span style="margin-left: 5px"><?php echo $LANG['WEB_KUBE_SELECTED_PVC'] ?></span></h4>
        </div>

        <div class="modal-body">
            <div class="portlet-body" style="padding: 20px;">
                <div class="table-container">
                    <!-- 表格 -->
                    <table id="pvcs_table"></table>
                    <!-- 表格 -->
                </div>
            </div>
        </div>
        <!-- modal-footer -->
        <div class="modal-footer">
            <button type="button" data-dismiss="modal" class="btn btn-default" id="cancel_PVC"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
            <button type="button" data-dismiss="modal" class="btn btn-primary"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
        </div>
    </div>
    <!-- modal end -->


    <!-- drawer开始 -->
    <div class="drawer slide" id="resourceDetailsDrawer" data-placement="right" tabindex="-1" role="dialog"
         aria-labelledby="drawer-1-title" aria-hidden="true" style="width: 600px;">
        <div class="drawer-content drawer-content-scrollable" role="document">
            <div class="drawer-header help_header drawer-mine-header">
                <h4 class="drawer-title help_title drawer-mine-title" id="drawer-1-title">
                    <i class="viconfont vicon-bangzhuzhongxin help_icon_helpcenter drawer-icon-helpcenter"></i><?php echo $LANG['WEB_KUBE_BACKUP_RESOURCE_DETAILS']; ?>
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
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/additional-methods.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>

<script type="text/javascript"
        src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript"
        src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<?php
if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
    //如果不是英文,加载语言包
    echo '<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>';
}
?>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/strategy_group_selector.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.speedStrategy.js"></script>
<script type="text/javascript" src="./scripts/platform/global_strategy/speed_strategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.strategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/backup-strategy.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_worm.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_complete.js"></script>
<script type="text/javascript" src="./scripts/plugins/calendar/jquery.searchableSelect.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.taskCrowd.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.GFSStrategy.js"></script>
<script type="text/javascript" src="./assets/global/plugins/ace/src-min-noconflict/ace.js"></script>
<script type="text/javascript" src="./scripts/platform/component/retry_strategy.js"></script>
<script type="text/javascript" src="./scripts/platform/component/vinScript.js"></script>
<script type="text/javascript" src="./scripts/platform/component/backup_target.js"></script>

<script type="text/javascript" src="./scripts/platform/component/transfer_network.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script src="./scripts/kubernetes/kubernetes_backup.js" type="text/javascript"></script>
