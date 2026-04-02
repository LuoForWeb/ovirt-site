<?php
include_once '../../tpl/permission.php';
include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php';
$authfun = $_SESSION['authfun'];
?>


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
<link rel="stylesheet" type="text/css" href="./css/platform/component/resource_pool.css" />
<?php
session_start();
session_commit();
?>
<!-- BEGIN PAGE HEADER-->
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row" style="height:100%;">
    <div class="col-md-12" style="height:100%;">
        <input id="job_uuid" value="<?php echo $_GET['uuid']; ?>" class="display-none">
        <span class="display-none" id="servertime"></span>
        <div class="portlet box blue-hoki backup-page" id="fileCopyContent">
            <div class="portlet-title">
                <div class="caption">
                    <i class="levelchild viconfont vicon-fuzhiliebiao c333"></i>
                    <?php
                    if (empty($_GET['uuid'])) {
                        echo $LANG['UI_FILE_COPY_CREATE_TASK'];
                    } else {
                        echo $LANG['UI_FILE_COPY_EDIT_TASK'];
                    }
                    ?>
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
                                                <?php echo $LANG['UI_FILE_COPY_RESOURCE'] ?>
                                                <i class="fa fa-check"></i>
                                            </span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#tab2" data-toggle="tab" class="step">
                                            <span class="number">2 </span>
                                            <span class="desc">
                                                <?php echo $LANG['UI_COPY_BACK_TARGET_NODE'] ?>
                                                <i class="fa fa-check"></i>
                                            </span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#tab3" data-toggle="tab" class="step">
                                            <span class="number">3 </span>
                                            <span class="desc">
                                                <?php echo $LANG['UI_FILE_COPY_STRATEGY'] ?>
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
                                <div class="tab-content tab-content-file-copy bakuptab">
                                    <!-- 复制源 -->
                                    <div class="tab-pane active" id="tab1">
                                        <div class="row tab-pane__row tab-pane-step1">
                                            <div class="source-target-wrap">
                                                <div class="tab-pane__row__source pr0">
                                                    <!-- 请选择复制源 -->
                                                    <div class="form-group src-wrap copy-list">
                                                        <div class="src-wrap__title">
                                                            <span>
                                                                <i class="viconfont vicon-beifenzhuji viconfont-color mr5"></i><?php echo $LANG['UI_FILE_COPY_RESOURCE_SELECT'] ?>
                                                            </span>
                                                        </div>
                                                        <div class="src-wrap__content">
                                                            <div class="src-wrap__content__ztree src-tree-div">
                                                                <div class="file-copy-tree">
                                                                    <select id="srcAgent" name="" class="bootstrap-mutiple-select selectpicker show-tick display-none" data-dropup-auto="false" data-size="5"></select>
                                                                    <div class="src-path-tree">
                                                                        <ul id="fileCopySrcTree" class="ztree"></ul>
                                                                    </div>
                                                                    <div class="alert alert-block alert-info fade in display-none mt0" id="noSrcAgent">
                                                                        <button type="button" class="close" data-dismiss="alert"></button>
                                                                        <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                                                        <ol class = "alert-ol">
                                                                            <li>
                                                                                <?php echo $LANG['UI_FILE_COPY_RESOURCE_TIPS1'] ?>
                                                                                <a id="toAddAgent" class="alert-link"><?php echo $LANG['UI_JOB_CLIENT'] ?></a>、
                                                                                <a id="toAddNas" class="alert-link"><?php echo $LANG['UI_NAS_DEVICE_NAME'] ?></a>
                                                                                <!-- 、<a id="toAddHadoop" class="alert-link"><?php echo $LANG['WEB_HADOOP_CLUSTER'] ?></a>、
                                                                                <a id="toAddObs" class="alert-link"><?php echo $LANG['UI_PLATFORM_OBS_STORAGE'] ?></a> -->
                                                                            </li>
                                                                            <li>
                                                                                <?php echo $LANG['UI_FILE_COPY_RESOURCE_TIPS3'] ?>
                                                                            </li>
                                                                        </ol>
                                                                    </div>
                                                                    <div class="alert alert-block alert-info fade in display-none mt15" id="toSelectSrcTips">
                                                                        <button type="button" class="close" data-dismiss="alert"></button>
                                                                        <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                                                        <ol class = "alert-ol">
                                                                            <li>
                                                                                <?php echo $LANG['UI_FILE_COPY_TO_SELECT_SRC_TIPS1'] ?>
                                                                            </li>
                                                                            <li>
                                                                                <?php echo $LANG['UI_FILE_COPY_TO_SELECT_SRC_TIPS2'] ?>
                                                                            </li>
                                                                            <li>
                                                                                <?php echo $LANG['UI_FILE_COPY_TO_SELECT_SRC_TIPS3'] ?>
                                                                            </li>
                                                                        </ol>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="arrow-svg"></div>
                                                <div class="tab-pane__row__source pr0">
                                                    <!-- 请选择复制目标端 -->
                                                    <div class="form-group src-wrap copy-list right">
                                                        <div class="src-wrap__title">
                                                            <span>
                                                                <i class="viconfont vicon-mubiao viconfont-color mr5"></i><?php echo $LANG['UI_FILE_COPY_DESTINATION_SELECT'] ?>
                                                            </span>
                                                        </div>
                                                        <div class="src-wrap__content">
                                                            <div class="src-wrap__content__ztree des-tree-div">
                                                                <div class="file-copy-tree">
                                                                    <select id="desAgent" name="" class="bootstrap-mutiple-select selectpicker show-tick display-none" data-dropup-auto="false" data-size="5"></select>
                                                                    <div class="des-path-tree">
                                                                        <ul id="fileCopyDesTree" class="ztree display-none"></ul>
                                                                    </div>
                                                                    <div class="alert alert-block alert-info fade in display-none mt0" id="noDesAgent">
                                                                        <button type="button" class="close" data-dismiss="alert"></button>
                                                                        <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                                                        <ol class = "alert-ol">
                                                                            <li>
                                                                                <?php echo $LANG['UI_FILE_COPY_DESTINATION_TIPS1'] ?>
                                                                                <a id="toAddAgent" class="alert-link"><?php echo $LANG['UI_JOB_CLIENT'] ?></a>、
                                                                                <a id="toAddNas" class="alert-link"><?php echo $LANG['UI_NAS_DEVICE_NAME'] ?></a>
                                                                                <!--  、<a id="toAddHadoop" class="alert-link"><?php echo $LANG['WEB_HADOOP_CLUSTER'] ?></a>、
                                                                                <a id="toAddObs" class="alert-link"><?php echo $LANG['UI_PLATFORM_OBS_STORAGE'] ?></a> -->
                                                                            </li>
                                                                            <li>
                                                                                <?php echo $LANG['UI_FILE_COPY_DESTINATION_TIPS3'] ?>
                                                                            </li>
                                                                            <li>
                                                                                <?php echo $LANG['UI_FILE_COPY_DESTINATION_TIPS4'] ?>
                                                                            </li>
                                                                        </ol>
                                                                    </div>
                                                                    <div class="alert alert-block alert-info fade in display-none mt15" id="toSelectDesTips">
                                                                        <button type="button" class="close" data-dismiss="alert"></button>
                                                                        <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                                                        <ol class = "alert-ol">
                                                                            <li>
                                                                                <?php echo $LANG['UI_FILE_COPY_TO_SELECT_DES_TIPS1'] ?>
                                                                            </li>
                                                                            <li>
                                                                                <?php echo $LANG['UI_FILE_COPY_TO_SELECT_DES_TIPS2'] ?>
                                                                            </li>
                                                                            <li>
                                                                                <?php echo $LANG['UI_FILE_COPY_TO_SELECT_DES_TIPS3'] ?>  
                                                                            </li>
                                                                            <li>
                                                                                <strong><?php echo $LANG['UI_FILE_COPY_TO_SELECT_DES_TIPS4'] ?></strong>
                                                                            </li>
                                                                        </ol>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
<!--                                            添加至列表按钮-->
                                            <div class="row">
                                                <div class="col-md-11"></div>
                                                <div class="col-md-1 add-list-div"><button type="button" class="btn green-haze" id="addToList"><?php echo $LANG['UI_FILE_COPY_ADD_TO_LIST'] ?></button></div>
                                            </div>
<!--                                            灰色线条-->
                                            <div class="grey-line"></div>
                                            <div class="portlet-body mlr10">
                                                <div class="table-container file-copy-table">
                                                    <div class="vin_toolbar" id="vin_file_copy_toolbar">
                                                        <div class="leftTool">
                                                            <div class="customBtn1">
                                                                <div style="cursor:not-allowed;">
                                                                    <button class="btn viconfont vicon-a-Deleteshanchu1 b-btn brr2 mr12 exch-forbid-event" id="deleteData"></button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <table class="table" id="fileCopyTable"></table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- 备份目的地 -->
                                    <div class="tab-pane" id="tab2">
                                        <div class="backup-destination-wrap row">
                                            <div class="col-md-9 hp-100" id="backupTarget"></div>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="tab3">
                                        <div class="row row-stepthree">
                                            <div class="tabbable-custom col-md-offset-1 col-md-10">
                                                <ul class="nav nav-tabs">
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
                                                    <li class="highLi">
                                                        <a href="#tab_other" class="popovers" 
                                                           data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                            <i class="viconfont vicon-gaojipeizhi"></i> <?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?> </a>
                                                    </li>
                                                </ul>
                                                <div class="tab-content">
                                                    <!-- 通用策略 -->
                                                    <div class="tab-pane active" id="tab_common">
                                                        <div class="panel-body">
                                                            <!-- 时间策略 -->
                                                            <div class="form-group">
                                                                <div class="col-md-offset-1 col-md-10 ">
                                                                    <div class="accordion strategyOne">
                                                                        <div class="panel panel-default strategy-panel">
                                                                            <div class="panel-heading">
                                                                                <h4 class="panel-title">
                                                                                    <a class="accordion-toggle accordion-toggle-styled popovers" data-container="body" data-trigger="hover"
                                                                                       data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#backupTime"
                                                                                       aria-expanded="true">
                                                                                        <i class="iconfont icon-timestrategy font-green-seagreen"></i>
                                                                                        <span class="font-green-seagreen"><?php echo $LANG['UI_STRATEGY_TIME'] ?></span>
                                                                                        <span class="strategyDes backupTimeDes"></span>
                                                                                    </a>
                                                                                </h4>
                                                                            </div>
                                                                            <div id="backupTime" class="panel-collapse collapse in">
                                                                                <div class="panel-body">
                                                                                    <div class="form-group mb0">
                                                                                        <div class="col-md-10 ">
                                                                                            <div class="form-group">
                                                                                                <label class="control-label col-md-3 form-group-label"><?php echo $LANG['UI_FILE_COPY_INTERVAL'] ?>
                                                                                                </label>
                                                                                                <div class="col-md-3 form-group-content pr0 mr12">
                                                                                                    <div class="input-group">
                                                                                                        <input type="text" value="23:00:00" class="form-control timepicker timepicker-24 copy-interval" id="copyInterval">
                                                                                                        <span class="input-group-btn">
                                                                                                            <button class="btn default btn-time" type="button"><i class="viconfont vicon-shengchengshijian"></i></button>
                                                                                                        </span>
                                                                                                    </div>
                                                                                                </div>
                                                                                                <div class="col-md-1 mt8 pl0">
                                                                                                    <a class="popovers" data-container="body" data-trigger="hover"
                                                                                                       data-placement="right" data-content="<?php echo $LANG['UI_FILE_COPY_INTERVAL_TIPS'] ?>">
                                                                                                        <i class="viconfont vicon-tishi fa-lg"></i>
                                                                                                    </a>
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="form-group mb0 first-copy-div">
                                                                                                <label class="control-label col-md-3 form-group-label"><?php echo $LANG['UI_FILE_COPY_FIRST_START_TIME'] ?>
                                                                                                </label>
                                                                                                <div class="col-md-6 form-group-content pr0 mr12 form-datetime-div">
                                                                                                    <div class="input-group date form_datetime">
                                                                                                        <input type="text" size="16" name="timeinput" class="form-control" id="firstCopyTime" readonly>
                                                                                                        <span class="input-group-btn">
                                                                                                            <button class="btn default date-set" type="button"><i class="viconfont vicon-rili2"></i></button>
                                                                                                        </span>
                                                                                                        <a class="popovers" data-container="body" data-trigger="hover"
                                                                                                           data-placement="right" data-content="<?php echo $LANG['UI_FILE_COPY_FIRST_START_TIME_TIPS'] ?>">
                                                                                                            <i class="viconfont vicon-tishi fa-lg"></i>
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
                                                            </div>
                                                            <!-- 限速策略 -->
                                                            <div id="backupStrategyDiv" class="col-md-10 col-md-offset-1">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- 传输策略 -->
                                                    <div class="tab-pane" id="tab_transfer">
                                                        <div class="panel-body">
                                                            <div class="tabbable-custom col-md-12" >
                                                                <!-- 压缩传输 -->
                                                                <div class="form-group">
                                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_DB_CDP_BACKUP_COMPRESSED_TRANSMISSION'] ?></label>
                                                                    <div class="col-md-4 form-group-content">
                                                                        <input type="checkbox" id="compress_transport_flag" data-size="small" class="make-switch" data-on-color="primary" data-off-color="info"
                                                                               data-on-text=""
                                                                               data-off-text="">
                                                                        <a class="popovers ml15" data-container="body" data-trigger="hover"
                                                                           data-placement="right" data-content="<?php echo $LANG['UI_FILE_COPY_COMPRESSED_TRANSMISSION_TIPS'] ?>">
                                                                            <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                                <!-- 压缩等级选择 -->
                                                                <div class="form-group compressGradeDiv display-none">
                                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE'] ?></label>
                                                                    <div class="col-md-4">
                                                                        <select class="form-control select2me" id="compressGrade">
                                                                            <option value="1"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_FAST'] ?></option>
                                                                            <option value="2"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_NORMAL'] ?></option>
                                                                            <option value="3"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_BETTER'] ?></option>
                                                                            <option value="4"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_BEST'] ?></option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <!-- 加密传输 -->
                                                                <div class="form-group transport-encrypt-div">
                                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER'] ?></label>
                                                                    <div class="col-md-4 form-group-content">
                                                                        <input type="checkbox" id="transport_encrypt_flag" data-size="small" class="make-switch" data-on-color="primary" data-off-color="info"
                                                                               data-on-text=""
                                                                               data-off-text="">
                                                                        <a class="popovers ml15" data-container="body" data-trigger="hover"
                                                                           data-placement="right" data-content="<?php echo $LANG['WEB_OS_TRANSFER_ENCRY_TIPS'] ?>">
                                                                            <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                                <!-- 传输加密算法 -->
                                                                <div class="form-group transfer-encrypt-method-form display-none">
                                                                    <label class="control-label transfer-encrypt-method-label col-md-3"><?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?></label>
                                                                    <div class="col-md-4">
                                                                        <select class="form-control select2me" id="transferEncryptMethod">
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
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- 高级配置 -->
                                                    <div class="tab-pane " id="tab_other">
                                                        <div class="advance-config-wrap">
                                                            <div class="advance-config-wrap__row row m0">
                                                                <div class="col-md-2 col-sm-2 col-xs-2 nav-tab-radios">
                                                                    <ul class="nav nav-tabs tabs-left">
                                                                        <li class="copy-stratagy-li active" data-type="1">
                                                                            <a href="#copy_stratagy" data-toggle="tab" aria-expanded="true">
                                                                                <?php echo $LANG['UI_DB_CDP_SYNC_STRATEGY'] ?>
                                                                            </a>
                                                                        </li>
                                                                        <!-- <li data-type="2">
                                                                            <a href="#recycle_reserve_config" data-toggle="tab">
                                                                                回收保留配置
                                                                            </a>
                                                                        </li> -->
                                                                        <li data-type="3" class="thread-config-li">
                                                                            <a href="#thread_config" data-toggle="tab">
                                                                                <?php echo $LANG['UI_FILE_COPY_THREAD_CONFIG'] ?>
                                                                            </a>
                                                                        </li>
                                                                        <li data-type="4">
                                                                            <a href="#abnormal_config" data-toggle="tab">
                                                                                <?php echo $LANG['UI_FILE_COPY_ABNORMAL_CONFIG'] ?>
                                                                            </a>
                                                                        </li>
                                                                        <li data-type="5">
                                                                            <a href="#wildcard_config" data-toggle="tab">
                                                                                <?php echo $LANG['UI_BACKUP_FILE_WILDCARD_FILTER'] ?>
                                                                            </a>
                                                                        </li>
                                                                        <li data-type="6">
                                                                            <a href="#time_config" data-toggle="tab">
                                                                                <?php echo $LANG['UI_PLATFORM_SET_TIME_FILTER'] ?>
                                                                            </a>
                                                                        </li>
                                                                        <!-- <li data-type="7">
                                                                            <a href="#script_config" data-toggle="tab">
                                                                                脚本配置
                                                                            </a>
                                                                        </li> -->
                                                                        <li class="">
                                                                            <a href="#retry_strategy_pane" data-toggle="tab" aria-expanded="true">
                                                                                <?php echo $LANG['UI_STRATEGY_RETRY'] ?>
                                                                            </a>
                                                                        </li>
                                                                        <li class="">
                                                                            <a href="#over_load_strategy_pane" data-toggle="tab" aria-expanded="true">
                                                                                <?php echo $LANG['UI_STRATEGY_OVER_LOAD_LABEL'] ?>
                                                                            </a>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                                <div class="col-md-10 col-sm-10 col-xs-10 pe-0 advance-config-wrap__row__content" id="all_advance_config">
                                                                    <div class="tab-content level1">
                                                                        <div class="tab-pane fade in active copy_stratagy_pane" id="copy_stratagy">
                                                                            <div class="abnormal-handle-form">
                                                                                 <!-- 快照 -->
                                                                                 <div class="form-group snapshot-div">
                                                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_FILE_SHOOTSNAP'] ?></label>
                                                                                    <div class="col-md-4 form-group-content">
                                                                                        <input type="checkbox" checked id="snapshot" data-size="small" class="make-switch" data-on-color="primary" data-off-color="info"
                                                                                               data-on-text=""
                                                                                               data-off-text="">
                                                                                        <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true"
                                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_FILE_SHOOTSNAP_TIPS'] ?>">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                                <!-- 文件权限复制 -->
                                                                                <div class="form-group copy-file-permission-div">
                                                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_FILE_COPY_PERMISSION'] ?></label>
                                                                                    <div class="col-md-4 form-group-content">
                                                                                        <input type="checkbox" id="copy_file_permission" data-size="small" class="make-switch" data-on-color="primary" data-off-color="info"
                                                                                               data-on-text=""
                                                                                               data-off-text="">
                                                                                        <a class="popovers ml15" data-container="body" data-trigger="hover"
                                                                                           data-placement="right" data-content="<?php echo $LANG['UI_FILE_COPY_PERMISSION_TIPS'] ?>">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                                <!-- 字符集复制 -->
                                                                                <!-- <div class="form-group">
                                                                                    <label class="control-label col-md-3">字符集复制</label>
                                                                                    <div class="col-md-4">
                                                                                        <input type="checkbox" id="charset_copy" data-size="small" class="make-switch" data-on-color="primary" data-off-color="info"
                                                                                               data-on-text=""
                                                                                               data-off-text="">
                                                                                        <a class="popovers ml15" data-container="body" data-trigger="hover"
                                                                                           data-placement="right" data-content="字符集复制提示">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div> -->
                                                                                <!-- 文件权限复制 -->
                                                                                <div class="form-group sync-self-flag-div">
                                                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_FILE_COPY_SELECTED_DIR'] ?></label>
                                                                                    <div class="col-md-4 form-group-content">
                                                                                        <input type="checkbox" id="sync_self_flag" data-size="small" class="make-switch" data-on-color="primary" data-off-color="info"
                                                                                               data-on-text=""
                                                                                               data-off-text="">
                                                                                        <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true"
                                                                                           data-placement="right" data-content="<?php echo $LANG['UI_FILE_COPY_SELECTED_DIR_TIPS'] ?>">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <!-- 回收保留配置 -->
                                                                        <!-- <div class="tab-pane fade recycle_reserve_config_pane" id="recycle_reserve_config">
                                                                            <div class="abnormal-handle-form">
                                                                                <div class="form-group">
                                                                                    <label class="control-label col-md-3">复制删除</label>
                                                                                    <div class="col-md-4">
                                                                                        <input type="checkbox" id="copy_del" data-size="small" class="make-switch" data-on-color="primary" data-off-color="info"
                                                                                               data-on-text=""
                                                                                               data-off-text="">
                                                                                        <a class="popovers ml8" data-container="body" data-trigger="hover"
                                                                                           data-placement="right" data-content="复制删除提示">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group copy-del-div display-none">
                                                                                    <label class="control-label col-md-3 del_reserve_label">删除保留</label>
                                                                                    <div class="col-md-4 pr0">
                                                                                        <select class="form-control select2me" id="del_reserve">
                                                                                            <option value="1">个数</option>
                                                                                            <option value="2">天数</option>
                                                                                        </select>
                                                                                    </div>
                                                                                    <div class="col-md-1 pd0 mt8">
                                                                                        <a class="popovers ml8" data-container="body" data-trigger="hover"
                                                                                           data-placement="right" data-content="删除保留提示">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group reserveNum copy-del-div display-none">
                                                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_RESERVE_NUM'] ?>
                                                                                    </label>
                                                                                    <div class="col-md-4">
                                                                                        <div id="delReserveNum">
                                                                                            <div class="input-group spinner-group">
                                                                                                <input type="text" id="spinnerNumInput" class="spinner-input form-control" maxlength="3" onkeyup="value=parseInt(value.replace(/[^\d]/g,''))">
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
                                                                                </div>
                                                                                <div class="form-group reserveDay display-none copy-del-div">
                                                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_RESERVE_DAY'] ?>
                                                                                    </label>
                                                                                    <div class="col-md-4">
                                                                                        <div id="delReserveDay">
                                                                                            <div class="input-group spinner-group">
                                                                                                <input type="text" id="spinnerDayInput" class="spinner-input form-control" maxlength="3" onkeyup="value=parseInt(value.replace(/[^\d]/g,''))">
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
                                                                                </div>
                                                                                <div class="form-group copy-del-div display-none">
                                                                                    <label class="control-label col-md-3">
                                                                                        回收站位置
                                                                                    </label>
                                                                                    <div class="col-md-4 pr0">
                                                                                        <input type="text" id="recycle_bin_path" placeholder="c:/file/" class="form-control">
                                                                                    </div>
                                                                                    <div class="col-md-2">
                                                                                        <button type="button" class="btn exch-green" id="toBrowse">浏览</button>
                                                                                        <a class="popovers ml8" data-container="body" data-trigger="hover"
                                                                                           data-placement="right" data-content="复制删除提示">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group reserve-size-div copy-del-div display-none">
                                                                                    <label class="control-label col-md-3">
                                                                                        保留空间大小
                                                                                    </label>
                                                                                    <div class="col-md-6">
                                                                                        <div id="reserveSize" style="display:inline-flex;">
                                                                                            <div class="input-group spinner-group quotaInputDiv">
                                                                                                <input type="text" id="reserve_size_input" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control" maxlength="3">
                                                                                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                                    <button type="button" class="btn spinner-up default input-sm">
                                                                                                        <i class="fa fa-angle-up"></i>
                                                                                                    </button>
                                                                                                    <button type="button" class="btn spinner-down default input-sm">
                                                                                                        <i class="fa fa-angle-down"></i>
                                                                                                    </button>
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="">
                                                                                                <select id="quotaUnit" class="form-control" style="margin-left: 10px;">
                                                                                                    <option value="MB">MB</option>
                                                                                                    <option value="GB">GB</option>
                                                                                                    <option value="TB">TB</option>
                                                                                                    <option value="PB">PB</option>
                                                                                                </select>
                                                                                            </div>
                                                                                        </div>
                                                                                        <a class="popovers ml15" data-container="body" data-trigger="hover" data-placement="right" data-content="保留空间大小提示" data-original-title="" title="">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div> -->
                                                                        <div class="tab-pane fade thread_config_pane" id="thread_config">
                                                                            <div class="abnormal-handle-form">
                                                                                <!-- 扫描线程数 -->
                                                                                <div class="form-group">
                                                                                    <label class="control-label col-md-3 scanthreadlabel"><?php echo $LANG['UI_FILE_BAK_SCAN_THREAD'] ?>
                                                                                    </label>
                                                                                    <div class="col-md-2">
                                                                                        <div class="scanThreadDiv">
                                                                                            <div class="input-group spinner-group">
                                                                                                <input type="text" id="scanThreadNum" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm">
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
                                                                                        <a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_FILE_SCAN_THREADBUM_TIPS'] ?>" data-original-title="" title="">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                                <!-- 扫描文件数 -->
                                                                                <div class="form-group scanFileDiv display-none">
                                                                                    <label class="control-label col-md-3 scanfilelabel"><?php echo $LANG['UI_FILE_BAK_SCAN_FILE'] ?>
                                                                                    </label>
                                                                                    <div class="col-md-2">
                                                                                        <div>
                                                                                            <div class="input-group">
                                                                                                <div class="spinner-buttons input-group-btn">
                                                                                                    <select class="form-control select2me input-sm" id="scanFileNum">
                                                                                                        <option value="0"><?php echo $LANG['UI_FILE_BAK_SCAN_SPEED_FIVE'] ?></option>
                                                                                                        <option value="1000"><?php echo $LANG['UI_FILE_BAK_SCAN_SPEED_FOUR'] ?></option>
                                                                                                        <option value="800"><?php echo $LANG['UI_FILE_BAK_SCAN_SPEED_THREE'] ?></option>
                                                                                                        <option value="600"><?php echo $LANG['UI_FILE_BAK_SCAN_SPEED_TWO'] ?></option>
                                                                                                        <option value="400"><?php echo $LANG['UI_FILE_BAK_SCAN_SPEED_ONE'] ?></option>
                                                                                                    </select>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-md-2 mt5">
                                                                                        <a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_FILE_BAK_SCAN_FILE_TIPS_COPY'] ?>" data-original-title="" title="">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                            <?php
                                                                            if ($authfun['multithread']) {
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
                                                                            </div>
                                                                        </div>
                                                                        <div class="tab-pane fade abnormal_config_pane" id="abnormal_config">
                                                                            <div class="abnormal-handle-form">
                                                                                <!-- 跳过文件告警智能判断 -->
                                                                                <div class="form-group">
                                                                                    <label class="control-label col-md-3 passfilealarmlabel"><?php echo $LANG['UI_FILE_SKIP_OBJECT_ALARM_JUDGE'] ?></label>
                                                                                    <div class="col-md-3 form-group-content">
                                                                                        <input type="checkbox" id="passfilealarmcheck" class="make-switch" data-on-color="primary" data-size="small" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                        <a class="popovers ml8" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_FILE_SKIP_OBJECT_ALARM_JUDGE_TIPS'] ?>">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                                <!-- 跳过文件告警个数 -->
                                                                                <div class="form-group passfilealarmDiv display-none">
                                                                                    <label class="control-label col-md-3 passfilenumlabel"><?php echo $LANG['UI_FILE_SKIP_OBJECT_ALARM_NUM'] ?>
                                                                                    </label>
                                                                                    <div class="col-md-3 pr0">
                                                                                        <div id="passFileSpinnerNum">
                                                                                            <div class="input-group spinner-group">
                                                                                                <input type="text" maxlength="9" id="passFileNum" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control">
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
                                                                                </div>
                                                                                <!-- 跳过文件告警比例 -->
                                                                                <div class="form-group passfilealarmDiv display-none">
                                                                                    <label class="control-label col-md-3 passfilepercentlabel"><?php echo $LANG['UI_FILE_SKIP_OBJECT_ALARM_RATIO'] ?>
                                                                                    </label>
                                                                                    <div class="col-md-3 pr0">
                                                                                        <div id="passFileSpinnerpercent">
                                                                                            <div class="input-group spinner-group">
                                                                                                <input type="text" id="warningpercent" style="text-align: left;" class="input-sm spinner-input form-control" maxlength="3" onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}" onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'0')}else{this.value=this.value.replace(/\D/g,'')}">
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
                                                                                    <div class="col-md-1 pd0 ml8" style="line-height: 36px;">%</div>
                                                                                </div>
                                                                                <!-- 同名文件处理 -->
                                                                                <div class="form-group">
                                                                                    <label class="control-label col-md-3 samefilelabel"><?php echo $LANG['UI_RECOVERY_FILE_PROCESS_SAME_FILE'] ?>
                                                                                    </label>
                                                                                    <div class="col-md-4 pr0">
                                                                                        <select class="form-control" id="sameFile">
                                                                                            <option value="1">
                                                                                                <?php echo $LANG['UI_RECOVERY_FILE_PROCESS_SAME_FILE_COVER'] ?>
                                                                                            </option>
                                                                                            <option value="3">
                                                                                                <?php echo $LANG['WEB_PLATFORM_DES_SKIP'] ?>
                                                                                            </option>
                                                                                            <option value="4">
                                                                                                <?php echo $LANG['UI_RECOVERY_FILE_PROCESS_SAME_FILE_RENAME'] ?>
                                                                                            </option>
                                                                                        </select>
                                                                                    </div>
                                                                                    <div class="col-md-1 mt8 pd0">
                                                                                        <a class="popovers ml8" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_RECOVERY_FILE_PROCESS_SAME_FILE_TIPS1'] ?>" data-original-title="" title="">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                                <!-- 校验方式 -->
                                                                                <div class="form-group recover-way-form">
                                                                                    <label class="control-label col-md-3 check-mode-label"><?php echo $LANG['UI_FILE_COPY_PARITY'] ?>
                                                                                    </label>
                                                                                    <div class="col-md-4 pr0">
                                                                                        <select class="form-control" id="check_mode">
                                                                                            <option value="1">
                                                                                                <?php echo $LANG['UI_FILE_COPY_METADATA'] ?>
                                                                                            </option>
                                                                                            <!-- <option value="2">
                                                                                                <?php echo $LANG['WEB_KUBE_CLUSTER_FILE_CONTENT'] ?>
                                                                                            </option> -->
                                                                                        </select>
                                                                                    </div>
                                                                                    <div class="col-md-1 mt8 pd0">
                                                                                        <a class="popovers ml8" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_FILE_COPY_METADATA_TIPS'] ?>" data-original-title="" title="">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                                 <!-- 校验算法 -->
                                                                                 <div class="form-group recover-way-form display-none verification-algorithm-form">
                                                                                    <label class="control-label col-md-3 file-verification-algorithm-label"><?php echo $LANG['UI_FILE_COPY_VERIFICATION_ALGORITHM'] ?>
                                                                                    </label>
                                                                                    <div class="col-md-4 pr0">
                                                                                        <select class="form-control" id="file_verification_algorithm">
                                                                                            <option value="1">
                                                                                                MD5
                                                                                            </option>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                                <!-- 覆盖规则 -->
                                                                                <div class="form-group recover-way-form">
                                                                                    <label class="control-label col-md-3 sync-rule-label"><?php echo $LANG['UI_FILE_COPY_RULE'] ?>
                                                                                    </label>
                                                                                    <div class="col-md-4 pr0">
                                                                                        <select class="form-control" id="sync_rule">
                                                                                            <option value="1">
                                                                                                <?php echo $LANG['UI_FILE_COPY_RULE1'] ?>
                                                                                            </option>
                                                                                            <option value="2">
                                                                                                <?php echo $LANG['UI_FILE_COPY_RULE2'] ?>
                                                                                            </option>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="tab-pane fade wildcard_config_pane" id="wildcard_config">
                                                                            <div class="abnormal-handle-form">
                                                                                <!-- 匹配条件 -->
                                                                                <div class="form-group">
                                                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_FILE_COPY_CONDITION'] ?></label>
                                                                                    <div class="col-md-8 match-mode-div ml15" id="wildcardMatchMode">
                                                                                        <label class="">
                                                                                            <input type="radio" checked name="icheckbox-wildcard" data-mode="0"><?php echo $LANG['WEB_PLATFORM_PUBLIC_NONE'] ?>
                                                                                        </label>
                                                                                        <label class="">
                                                                                            <input type="radio" id="wildcardExcludeCheck" name="icheckbox-wildcard" data-mode="1"><?php echo $LANG['WEB_M365_EXCLUDE'] ?>
                                                                                        </label>
                                                                                        <label class="">
                                                                                            <input type="radio" id="wildcardSelectedCheck" name="icheckbox-wildcard" data-mode="2"><?php echo $LANG['UI_FILE_COPY_SELECTED'] ?>
                                                                                        </label>
                                                                                        <a class="popovers ml8 mt8" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_FILE_COPY_CONDITION_TIPS'] ?>" data-original-title="" title="">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                                <!-- 通配符生效对象 -->
                                                                                <div class="wildcard-form display-none">
                                                                                    <div class="form-group">
                                                                                        <label class="col-md-3 control-label"><?php echo $LANG['UI_FILE_COPY_WILDCARD_OBJ'] ?></label>
                                                                                        <div class="col-md-3 pr0">
                                                                                            <select class="form-control" id="effectiveObject">
                                                                                                <option value="1"><?php echo $LANG['UI_PLATFORM_FILES'] ?></option>
                                                                                                <option value="2"><?php echo $LANG['WEB_PLATFORM_DES_FOLDER'] ?></option>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>
                                                                                    <!-- 通配符 -->
                                                                                    <div class="form-group">
                                                                                        <label class="control-label col-md-3">
                                                                                            <?php echo $LANG['UI_FILE_WILDCARD'] ?>
                                                                                        </label>
                                                                                        <div class="col-md-5 pr0">
                                                                                            <input type="text" id="wildcardInput" class="form-control">
                                                                                        </div>
                                                                                        <div class="col-md-3">
                                                                                            <button type="button" class="btn exch-green" id="addWildcardToList">
                                                                                                <i class="viconfont vicon-biaogetianjia mr5"></i><?php echo $LANG['UI_BACKUP_FILE_ADD'] ?>
                                                                                            </button>
                                                                                            <a class="popovers ml8" data-container="body" data-trigger="hover" data-html="true"
                                                                                               data-placement="right" data-content="<?php echo $LANG['UI_FILE_WILDCARD_RULES_ADD_TIPS'] ?>">
                                                                                                <i class="viconfont vicon-tishi fa-lg"></i>
                                                                                            </a>
                                                                                        </div>
                                                                                    </div>
                                                                                    <!-- 配置信息 -->
                                                                                    <div class="form-group wildcard-info-div display-none">
                                                                                        <label class="control-label col-md-3">
                                                                                            <?php echo $LANG['UI_CM_CDP_CONFIG_TIPS'] ?>
                                                                                        </label>
                                                                                        <div class="col-md-6 pr0" id="wildcardList"></div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="tab-pane fade time_config_pane" id="time_config">
                                                                            <div class="abnormal-handle-form">
                                                                                 <!-- 匹配条件 -->
                                                                                 <div class="form-group">
                                                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_FILE_COPY_CONDITION'] ?></label>
                                                                                    <div class="col-md-8 match-mode-div ml15" id="timeMatchMode">
                                                                                        <label class="">
                                                                                            <input type="radio" checked name="icheckbox-time" data-mode="0"><?php echo $LANG['WEB_PLATFORM_PUBLIC_NONE'] ?>
                                                                                        </label>
                                                                                        <label class="">
                                                                                            <input type="radio" id="timeExcludeCheck" name="icheckbox-time"  data-mode="1"><?php echo $LANG['WEB_M365_EXCLUDE'] ?>
                                                                                        </label>
                                                                                        <label class="">
                                                                                            <input type="radio" id="timeSelectedCheck" name="icheckbox-time"  data-mode="2"><?php echo $LANG['UI_FILE_COPY_SELECTED'] ?>
                                                                                        </label>
                                                                                        <a class="popovers ml8 mt8" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_FILE_COPY_CONDITION_TIME_TIPS'] ?>" data-original-title="" title="">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                                 <div class="time-rang-form display-none">
                                                                                    <!-- 时间范围 -->
                                                                                     <div class="form-group">
                                                                                        <label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_THIRD_PUSH_TIME_TYPE'] ?></label>
                                                                                        <div class="col-md-8 match-mode-div ml15" id="rangeMode">
                                                                                            <label class="">
                                                                                                <input type="radio" checked id="recentRange" name="icheckbox-range-mode"  data-mode="1"><?php echo $LANG['UI_FILE_COPY_RECENT_TIME_RANGE'] ?>
                                                                                            </label>
                                                                                            <label class="">
                                                                                                <input type="radio" id="fixedRange" name="icheckbox-range-mode"  data-mode="2"><?php echo $LANG['UI_FILE_COPY_FIX_TIME_RANGE'] ?>
                                                                                            </label>
                                                                                        </div>
                                                                                    </div>
                                                                                    <!-- 文件修改最近天数 -->
                                                                                    <div class="form-group recent-days-form">
                                                                                        <label class="col-md-3 control-label"><?php echo $LANG['UI_FILE_COPY_MODIFY_RECENT_DAY'] ?></label>
                                                                                        <div class="col-md-4 pr0">
                                                                                            <input type="text" id="copyDays" class="form-control" maxlength="5" oninput="this.value = this.value.replace(/[^\d]/g,'')">
                                                                                        </div>
                                                                                        <div class="col-md-1 pd0 ml8 mt8">
                                                                                            <a class="popovers" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php echo $LANG['UI_FILE_COPY_MODIFY_RECENT_DAY_TIPS'] ?>" data-original-title="" title="">
                                                                                                <i class="viconfont vicon-tishi"></i>
                                                                                            </a>
                                                                                        </div>
                                                                                    </div>
                                                                                    <!-- 文件修改时间范围 -->
                                                                                    <div class="form-group recent-range-form display-none">
                                                                                        <label class="control-label col-md-3">
                                                                                            <?php echo $LANG['UI_FILE_COPY_MODIFY_TIME_RANGE'] ?>
                                                                                        </label>
                                                                                        <div class="col-md-5 pr0">
                                                                                            <div class="input-group date file-edit-time-range-div">
                                                                                                <input type="text" size="16" class="form-control" autocomplete="off" id="timeInputRange">
                                                                                                <span class="input-group-btn">
                                                                                                    <button class="btn default date-set" type="button">
                                                                                                        <i class="viconfont vicon-rili2"></i>
                                                                                                    </button>
                                                                                                </span>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <!-- 配置信息 -->
                                                                                    <div class="form-group time-range-info-div display-none">
                                                                                        <label class="control-label col-md-3">
                                                                                            <?php echo $LANG['UI_BACKUP_SET_STRATEGY_MESSAGE'] ?>
                                                                                        </label>
                                                                                        <div class="col-md-6 pr0" id="timeRangeList"></div>
                                                                                    </div>
                                                                                 </div>
                                                                            </div>
                                                                        </div>
                                                                        <!-- 自定义脚本  脚本路径 -->
                                                                        <!-- <div class="tab-pane fade script_config_pane" id="script_config">
                                                                            <div class="abnormal-handle-form">
                                                                                <div class="form-group">
                                                                                    <label class="control-label col-md-3">自定义脚本</label>
                                                                                    <div class="col-md-3 form-group-content">
                                                                                        <input type="checkbox" id="diy_script" class="make-switch" data-on-color="primary" data-size="small" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                        <a class="popovers ml8" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="自定义脚本提示">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group">
                                                                                    <label class="control-label col-md-3">
                                                                                        脚本路径
                                                                                    </label>
                                                                                    <div class="col-md-4 pr0">
                                                                                        <input type="text" id="recycle_bin_path" class="form-control" placeholder="请输入脚本路径">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div> -->
                                                                        <div class="tab-pane fade retry_strategy_pane_pane" id="retry_strategy_pane">
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
                                                    <div class="col-md-9">
                                                        <div class="input-icon right">
                                                            <i class="fa"></i>
                                                            <input type="text" maxlength="64" class="form-control" id="file_copy_job_name"/>
                                                            <span class="help-block "><?php echo $LANG['UI_JOB_RNAME_TIPS'] ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <h4 class="form-section"></h4>
                                                <div class="form-group mb0 mt10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_FILE_COPY_SRC'] ?></label>
                                                    <div class="col-md-7">
                                                        <div class="form-control-static " id="srcAgentShow">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_FILE_COPY_DES'] ?></label>
                                                    <div class="col-md-7">
                                                        <div class="form-control-static " id="desAgentShow">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_FILE_COPY_LIST'] ?></label>
                                                    <div class="col-md-9">
                                                        <div class="form-control-static ">
                                                            <textarea class="textarea-file" id="copyList"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                                <h4 class="form-section"></h4>
                                                <div class="form-group mb0 mt10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TARGET_NODE'] ?></label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static  nodeInfoShow">
                                                        </p>
                                                    </div>
                                                </div>
                                                <h4 class="form-section"></h4>
                                                <div class="form-group mb0 mt10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_FILE_COPY_INTERVAL'] ?></label>
                                                    <div class="col-md-7">
                                                        <div class="form-control-static " id="copyIntervalShow">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_FILE_COPY_FIRST_START_TIME'] ?></label>
                                                    <div class="col-md-7">
                                                        <div class="form-control-static " id="copyStartTimeShow">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 speed-limit-div">
                                                    <label class="control-label col-md-3"><?php echo $LANG['WEB_COMMON_SPEED_STRATEGY'] ?></label>
                                                    <div class="col-md-7">
                                                        <div class="form-control-static " id="speedLimitShow">
                                                        </div>
                                                    </div>
                                                </div>
                                                <h4 class="form-section transfer-strategy-show"></h4>
                                                <div class="form-group mb0 mt10 transfer-strategy-show">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_DB_CDP_BACKUP_COMPRESSED_TRANSMISSION'] ?></label>
                                                    <div class="col-md-7">
                                                        <div class="form-control-static " id="compressTransportShow">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 transfer-strategy-show">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER'] ?></label>
                                                    <div class="col-md-7">
                                                        <div class="form-control-static " id="encryptTransportShow">
                                                        </div>
                                                    </div>
                                                </div>
                                                <h4 class="form-section"></h4>
                                                <div class="form-group mb0 mt10 copy-strategy-show">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_DB_CDP_SYNC_STRATEGY'] ?></label>
                                                    <div class="col-md-6">
                                                        <div class="form-control-static ">
                                                            <span class="snapshotShowDiv"><span class="snapshotShow"></span><br></span>
                                                            <span class="copyFilePermissionShow"></span>
                                                            <div class="copySelfFlagShow"></div>
                                                            <!-- <span class="backupmodeshow3">字符集复制:开启</span> -->
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- <div class="form-group mb0 mt10">
                                                    <label class="control-label col-md-3">回收保留配置</label>
                                                    <div class="col-md-6">
                                                        <p class="form-control-static ">
                                                            <span class="backupmodeshow3">复制删除:开启</span><br>
                                                            <span class="backupmodeshow3">删除保留:个数</span><br>
                                                            <span class="backupmodeshow3">保留个数:30</span><br>
                                                            <span class="backupmodeshow3">回收站位置:/aa/1111/</span><br>
                                                            <span class="backupmodeshow3">保留空间大小:30GB</span>
                                                        </p>
                                                    </div>
                                                </div> -->
                                                <div class="form-group mb0 mt10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_FILE_COPY_THREAD_CONFIG'] ?></label>
                                                    <div class="col-md-6">
                                                        <p class="form-control-static ">
                                                            <?php
                                                            if ($authfun['multithread']) {
                                                                echo '
                                                                        <span class="backupThreadNumShow"></span><br>
                                                                    ';
                                                            }
                                                            ?> 
                                                            <span class="scanThreadNumShow"></span><br>
                                                            <span class="scanFileNumShow"></span>
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_FILE_COPY_ABNORMAL_CONFIG'] ?></label>
                                                    <div class="col-md-6">
                                                        <div class="form-control-static ">
                                                            <div class="passfilealarmcheckShow"></div>
                                                            <div class="passFileNumShow"></div>
                                                            <div class="warningpercentShow"></div>
                                                            <div class="sameFileShow"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10 wildcardShowDiv">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_FILE_WILDCARD_FILTER'] ?></label>
                                                    <div class="col-md-6">
                                                        <p class="form-control-static ">
                                                            <span class="wildcardShow"></span>
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_SET_TIME_FILTER'] ?></label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static ">
                                                            <span class="timeRangeShow"></span>
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
         <!-- 回收站位置模态框开始 -->
         <div id="recycleBinModal" class="modal xmodal fade form-horizontal "
             tabindex="-1" data-focus-on="input:first" data-backdrop="static">
            <div class="modal-header ">
                <button type="button" class="close" data-dismiss="modal"
                        aria-hidden="true"></button>
                <h4 class="modal-title add-title">
                    <i class="viconfont vicon-lianjie mr8"></i><?php echo $LANG['UI_FILE_COPY_RECYCLE_BIN_SELECT_POSITION'] ?>
                    <span class="titleDes"> </span>
                </h4>
            </div>
            <div class="modal-body des-tree-div" style="padding: 20px;">
                <div class="form-group simpleDiv">
                    <input name="search" id="searchRecycleBinInput" type="text" class="form-control" placeholder="<?php echo $LANG['WEB_M365_RECOVERY_SEARCH_USER'] ?>">
                </div>
                <div class="form-group simpleDiv">
                    <ul class="ztree " id="recycleBinTree">
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button name="cancelBtn" type="button" data-dismiss="modal" class="btn btn-default">
                    <?php echo $LANG['UI_PUBLIC_OFF_TWO'] ?>
                </button>
                <button type="button" class="btn green-haze recycleBinSubmit"
                ><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
            </div>
        </div>
        <!-- 回收站位置模态框结束 -->
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
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.strategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.speedStrategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/calendar/jquery.searchableSelect.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.taskCrowd.js"></script>
<!-- <script type="text/javascript" src="./scripts/plugins/jquery/jquery.backupStrategy.js"></script> -->
<script type="text/javascript" src="./scripts/platform/global_strategy/speed_strategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/backup-strategy.js"></script>
<script type="text/javascript" src="./scripts/platform/component/backup_target.js"></script>
<script type="text/javascript" src="./scripts/platform/component/transfer_network.js"></script>
<script type="text/javascript" src="./scripts/platform/component/retry_strategy.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script src="./scripts/filecopy/file_copy.js" type="text/javascript"></script>
