<?php
include_once '../../tpl/permission.php';
global $LANG;
// 恢复方式【1指定时间点 2定时恢复最新备份点】
$timepointRecoveryType = $_GET['timepoint_recovery_type'] ?? 1;
$editFlag = $_GET['edit_flag'] ?? 0;
?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css"
      href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>

<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css"
      href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
<link rel="stylesheet" type="text/css"
      href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/daterangepicker/daterangepicker.css"/>
<link rel="stylesheet" type="text/css" href="./scripts/plugins/calendar/jquery.searchableSelect.css"/>
<!--<link rel="stylesheet" type="text/css" href="./scripts/plugins/highlight/styles/dark.min.css" />-->
<!--<link rel="stylesheet" type="text/css" href="./scripts/plugins/highlight/styles/code-highlight.css" />-->
<link rel="stylesheet" type="text/css" href="./css/dbprotect/dbrecover.css"/>
<link rel="stylesheet" type="text/css" href="./css/platform/component/style.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/component/resource_pool.css"/>
<link rel="stylesheet" type="text/css" href="./css/platform/component/safe_virus.css"/>
<link rel="stylesheet" type="text/css" href="./scripts/components/timePointDetail/css/timePointDetail.css" />
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
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
    <span class="curent" style="<?php if ($timepointRecoveryType != 1) echo 'display: none' ?>"><?php echo $LANG['UI_PLATFORM_DB_RECOVERY'] ?></span>
    <span class="curent" style="<?php if ($timepointRecoveryType != 2) echo 'display: none' ?>"><?php echo $LANG['UI_PLATFORM_DB_DRILL'] ?></span>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row recover-page">
    <div class="col-md-12" style="height:100%;">
        <!-- 添加这个之后，F5刷新会在本地 -->
        <input id="pageUri" value="<?php echo $_SERVER['REQUEST_URI']; ?>" class="display-none"></input>
        <div class="portlet box blue-hoki" id="dbrecovercontent">
            <!-- 恢复方式【1指定时间点 2定时恢复最新备份点】 -->
            <input id="externalTimepointRecoveryType" value="<?php echo $timepointRecoveryType; ?>"
                   class="display-none"></input>
            <input id="externalEditFlag" value="<?php echo $editFlag; ?>" class="display-none"></input>
            <input id="externalPointUuid" value="<?php echo $_GET['point_uuid']; ?>" class="display-none"></input>
            <input id="externalTaskUuid" value="<?php echo $_GET['task_uuid']; ?>" class="display-none"></input>
            <input id="externalItemUuid" value="<?php echo $_GET['item_uuid']; ?>" class="display-none"></input>
            <input id="externalSubType" value="<?php echo $_GET['sub_type']; ?>" class="display-none"></input>
            <input id="externalInstanceName" value="<?php echo $_GET['instance_name']; ?>" class="display-none"></input>
            <input id="externalDbName" value="<?php echo $_GET['db_name']; ?>" class="display-none"></input>
            <input id="externalAgentUuid" value="<?php echo $_GET['agent_uuid']; ?>" class="display-none"></input>
            <input id="externalClusterUuid" value="<?php echo $_GET['cluster_uuid']; ?>" class="display-none"></input>
            <div class="portlet-title">
                <!-- 创建 -->
                <div class="caption" id="addRecoveryTaskTitle" style="<?php if ($editFlag) {
                    echo 'display: none';
                } else if ($timepointRecoveryType == 2) {
                    echo 'display: none';
                } ?>">
                    <i class="levelchild viconfont vicon-huifu1"></i><?php echo $LANG['UI_DB_ADD_NEW_RECOVERY_JOB'] ?>
                </div>
                <!-- 创建演练任务 -->
                <div class="caption" id="addDrillTaskTitle" style="<?php if ($editFlag) {
                    echo 'display: none';
                } else if ($timepointRecoveryType == 1) {
                    echo 'display: none';
                } ?>">
                    <i class="levelchild viconfont vicon-huifu1"></i><?php echo $LANG['UI_DB_ADD_NEW_DRILL_JOB'] ?>
                </div>
                <!-- 修改 -->
                <div class="caption" id="modifyRecoveryTaskTitle" style="<?php if (!$editFlag) echo 'display: none'; ?>">
                    <i class="levelchild viconfont vicon-huifu1"></i><?php echo $LANG['UI_DB_EDIT_NEW_DRILL_JOB'] ?>
                </div>
            </div>
            <div class="portlet-body form">
                <div action="#" class="form-horizontal" id="submit_form" method="POST">
                    <div class="form-wizard">
                        <div class="form-body">
                            <ul class="nav nav-pills steps">
                                <li>
                                    <a href="#tab1" data-toggle="tab" class="step">
                                        <span class="number">1 </span>
                                        <span class="desc">
											<?php echo $LANG['UI_RECOVERY_DATA_SOURCE'] ?>
											<i class="fa fa-check"></i>
										</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#tab2" data-toggle="tab" class="step">
                                        <span class="number">2 </span>
                                        <span class="desc">
											<?php echo $LANG['UI_RECOVERY_GOAL'] ?>
											<i class="fa fa-check"></i>
										</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#tab3" data-toggle="tab" class="step active">
                                        <span class="number">3 </span>
                                        <span class="desc">
											<?php echo $LANG['UI_RECOVERY_HIGH_STRATEGY'] ?>
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
                                <div class="tab-pane active" id="tab1">
                                    <div class="alert alert-danger display-none selecttimepointtip">
                                    </div>
                                    <div class="row tab-pane__row">
                                        <div class="col-md-4 tab-pane__row__source">
                                            <div class="form-group src-wrap">
                                                <div class="src-wrap__title">
													<span>
														<?php echo $LANG['UI_RECOVERY_DATA_SOURCE'] ?>
													</span>
                                                </div>
                                                <div class="src-wrap__content">
                                                    <div class="src-wrap__content__search">
                                                        <select class="bs-select width100p form-control display-none"
                                                                data-show-subtext="true" id="recoverySourceTypeSelect">
                                                            <option value="1"><?php echo $LANG['UI_RECOVERY_SPECIFIC_TIMEPOINT'] ?></option>
                                                            <option value="2"><?php echo $LANG['UI_RECOVERY_LATEST_TIMEPOINT'] ?></option>
                                                        </select>
                                                        <select class="bs-select width100p form-control display-none"
                                                                data-show-subtext="true" id="dbTypeSelect">
                                                        </select>
                                                        <select class="bs-select width100p form-control"
                                                                data-show-subtext="true" id="pointDbTypeSelect">
                                                        </select>
                                                        <!-- 备份点存储 -->
                                                        <select class="bs-select width100p form-control mt-10" data-show-subtext="true" id="timepointStorageSelect"></select>
                                                        <div class="vm_tree_div">
                                                            <div class="input-icon width100p">
                                                                <input type="text" maxlength="128"
                                                                       placeholder="<?php echo $LANG['UI_DB_SEARCH_AS_NAME'] ?>"
                                                                       class="form-control" id="searchvm"
                                                                       onkeyup="customInputValidate('string', this.value, $(this))"/>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="src-wrap__content__itree"
                                                         style="height: calc(100% - 136px)">
                                                        <div class="alert alert-block alert-info fade in display-hide"
                                                             id="nopointtips">
                                                            <button type="button" class="close"
                                                                    data-dismiss="alert"></button>
                                                            <ul class="alert-ul">
                                                                <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>
                                                                    :</strong>
                                                                <li>
                                                                    <?php echo $LANG['UI_RECOVERY_NO_DATA_SOURCE_TITLE'] ?>
                                                                    <a id="tobackup" class="alert-link">
                                                                        <?php echo $LANG['UI_DB_DO_BACKUP_FIRST'] ?>
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                        <div class="alert alert-block alert-info fade in display-hide"
                                                             id="noSourceAgentTips">
                                                            <button type="button" class="close"
                                                                    data-dismiss="alert"></button>
                                                            <h4 class="alert-heading">
                                                                <strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong>
                                                            </h4>
                                                            <ol class="alert-ol">
                                                                <li>
                                                                    <?php echo $LANG['UI_DB_RECOVERY_NO_AGENT_TIPS1'] ?>
                                                                </li>
                                                                <li>
                                                                    <a id="toAddAgent1"><?php echo $LANG['UI_BACKUP_NO_VALID_DB_HOST_TIPS1'] ?></a>
                                                                </li>
                                                                <li>
                                                                    <?php echo $LANG['UI_DB_RECOVERY_NO_AGENT_TIPS2'] ?>
                                                                </li>
                                                            </ol>
                                                        </div>
                                                        <div class="alert alert-block alert-info fade in display-hide"
                                                             id="nosearchpointtips">
                                                            <button type="button" class="close"
                                                                    data-dismiss="alert"></button>
                                                            <ul class="alert-ul">
                                                                <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>
                                                                    :</strong>
                                                                <li>
                                                                    <?php echo $LANG['UI_RECOVERY_NO_DATA_SOURCE_TITLE'] ?>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                        <div class="alert alert-block alert-info fade in display-hide"
                                                             id="noSearchAgentTips">
                                                            <button type="button" class="close"
                                                                    data-dismiss="alert"></button>
                                                            <ul class="alert-ul">
                                                                <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>
                                                                    :</strong>
                                                                <li>
                                                                    <?php echo $LANG['UI_DB_RECOVERY_NEWEST_TIMEPOINT_SEARCH_EMPTY'] ?>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                        <div class="two_tree vcenter-tree">
                                                            <ul id="pointtypetree"
                                                                class="ztree ztree-fa bd1de5 tree_div"></ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-8 VMList-div">
                                            <div class="addTitle">
                                                <span><?php echo $LANG['UI_RECOVERY_ALREADY_SELECT_DATA_SOURCE'] ?></span>
                                            </div>
                                            <div class="addIt-list">
                                                <ul class="feeds addVMList" id="VMGroupList"></ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="tab2">
                                    <div class="row row-dbrecover">
                                        <div class="alert alert-danger display-none setrecover2tip"></div>
                                        <div class="col-md-10">
                                            <!-- 新建实例 -->
                                            <div class="form-group createNewInstanceDiv">
                                                <label class="control-label col-md-3 createNewInstanceLabel">
                                                    <?php echo $LANG['UI_DB_NEW_INSTANCE'] ?>
                                                </label>
                                                <div class="col-md-6 flex-items-center">
                                                    <input type="checkbox" id="createNewInstance" class="make-switch"
                                                           data-on-color="primary"
                                                           data-off-color="info" data-size="small">
                                                    <a class="popovers ml12" data-container="body" data-trigger="hover"
                                                       data-html="true"
                                                       data-placement="right"
                                                       data-content="<?php echo $LANG['UI_DB_NEW_INSTANCE_TIPS'] ?>">
                                                        <i class="viconfont vicon-tishi"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <!-- 恢复目标 -->
                                            <div class="form-group host_tree_div" id="selectHost">
                                                <label class="control-label col-md-3">
                                                    <span class="required">* </span>
                                                    <?php echo $LANG['UI_DB_SELECT_AGENT'] ?>
                                                </label>
                                                <div class="col-md-6 tree_div2">
                                                    <ul id="host_tree" class="ztree ztree-fa bd1de5"></ul>
                                                    <div>
                                                        <span class="help-block "></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group display-hide" id="nohosttips">
                                                <label class="control-label col-md-3">
                                                    <span class="required">* </span>
                                                    <?php echo $LANG['UI_DB_SELECT_AGENT'] ?>
                                                </label>
                                                <div class="col-md-6 alert alert-block alert-info fade in ml15">
                                                    <button type="button" class="close" data-dismiss="alert"></button>
                                                    <ul class="alert-ul">
                                                        <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>
                                                            :</strong>
                                                        <li>
                                                            <?php echo $LANG['UI_DB_NO_AVAILABLE_AGENT_TIPS_TITLE'] ?><a
                                                                    id="toAddAgent2"><?php echo $LANG['UI_DB_NO_AVAILABLE_AGENT_TIPS'] ?></a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <!-- 新建目标主机 -->
                                            <div class="form-group createNewInstanceHostDiv">
                                                <label class="control-label col-md-3">
                                                    <span class="required">* </span>
                                                    <span class="createNewInstanceHostLabel"><?php echo $LANG['UI_DB_NEW_INSTANCE_HOST'] ?></span>
                                                </label>
                                                <div class="col-md-6">
                                                    <ul id="createNewInstanceHostTree"
                                                        class="ztree ztree-fa bd1de5"></ul>
                                                    <div>
                                                        <span class="help-block "><?php echo $LANG['UI_DB_NEW_INSTANCE_HOST_TIPS'] ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group display-none" id="sqlservertips">
                                                <label class="control-label col-md-3">
                                                </label>
                                                <div class="col-md-8 alert alert-block alert-info fade in ml15">
                                                    <button type="button" class="close" data-dismiss="alert"></button>
                                                    <ul class="alert-ul">
                                                        <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>
                                                            :</strong>
                                                        <li>
                                                            <?php echo $LANG['UI_DB_CONFIG_BEFORE_MYSQL_BACKUP_TIPS'] ?>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="form-group display-none" id="oracletips">
                                                <label class="control-label col-md-3">
                                                </label>
                                                <div class="col-md-8 alert alert-block alert-info fade in ml15">
                                                    <button type="button" class="close" data-dismiss="alert"></button>
                                                    <h4 class="alert-heading">
                                                        <strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                                    <ol class="alert-ol">
                                                        <li>
                                                            <small><?php echo $LANG['UI_DB_SOME_CONFIG_BEFORE_ORACLE_BACKUP'] ?>
                                                                ：</small></li>
                                                        <li>
                                                            <small><?php echo $LANG['UI_DB_SOME_CONFIG_BEFORE_ORACLE_BACKUP_TIPSONE'] ?></small>
                                                        </li>
                                                        <li>
                                                            <small><?php echo $LANG['UI_DB_SOME_CONFIG_BEFORE_ORACLE_BACKUP_TIPSTWO'] ?></small>
                                                        </li>
                                                        <li>
                                                            <small><?php echo $LANG['UI_DB_SOME_CONFIG_BEFORE_ORACLE_BACKUP_TIPSTHREE'] ?></small>
                                                        </li>
                                                        <li>
                                                            <small><?php echo $LANG['UI_DB_SOME_CONFIG_BEFORE_ORACLE_BACKUP_TIPSFOUR'] ?></small>
                                                        </li>
                                                        <li>
                                                            <small><?php echo $LANG['UI_DB_SOME_CONFIG_BEFORE_ORACLE_BACKUP_TIPSFIVE'] ?></small>
                                                        </li>
                                                    </ol>
                                                </div>
                                            </div>
                                            <div class="form-group display-none" id="mysqltips">
                                                <label class="control-label col-md-3">
                                                </label>
                                                <div class="col-md-8 alert alert-block alert-info fade in ml15">
                                                    <button type="button" class="close" data-dismiss="alert"></button>
                                                    <h4 class="alert-heading">
                                                        <strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                                    <ol class="alert-ol">
                                                        <li><?php echo $LANG['UI_DB_OPERATION_BEFORE_MYSQL_BACKUP'] ?></li>
                                                        <li><?php echo $LANG['UI_DB_OPERATION_BEFORE_MYSQL_BACKUP_TIPSONE'] ?></li>
                                                        <li><?php echo $LANG['UI_DB_OPERATION_BEFORE_MYSQL_BACKUP_TIPSTWO'] ?></li>
                                                        <li><?php echo $LANG['UI_DB_OPERATION_BEFORE_MYSQL_BACKUP_TIPSTHREE'] ?></li>
                                                        <li><?php echo $LANG['UI_DB_OPERATION_BEFORE_MYSQL_BACKUP_TIPSFIVE'] ?></li>
                                                    </ol>
                                                </div>
                                            </div>
                                            <div class="form-group display-none" id="mariatips">
                                                <label class="control-label col-md-3">
                                                </label>
                                                <div class="col-md-8 alert alert-block alert-info fade in ml15">
                                                    <button type="button" class="close" data-dismiss="alert"></button>
                                                    <h4 class="alert-heading">
                                                        <strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                                    <ol class="alert-ol">
                                                        <li><?php echo $LANG['UI_DB_OPERATION_BEFORE_MARIA_BACKUP'] ?></li>
                                                        <li><?php echo $LANG['UI_DB_OPERATION_BEFORE_MARIA_BACKUP_TIPSONE'] ?></li>
                                                        <li><?php echo $LANG['UI_DB_OPERATION_BEFORE_MARIA_BACKUP_TIPSTWO'] ?></li>
                                                        <li><?php echo $LANG['UI_DB_OPERATION_BEFORE_MARIA_BACKUP_TIPSTHREE'] ?></li>
                                                        <li><?php echo $LANG['UI_DB_OPERATION_BEFORE_MARIA_BACKUP_TIPSFIVE'] ?></li>
                                                    </ol>
                                                </div>
                                            </div>
                                            <div class="form-group display-none" id="dmtips">
                                                <label class="control-label col-md-3">
                                                </label>
                                                <div class="col-md-8 alert alert-block alert-info fade in ml15">
                                                    <button type="button" class="close" data-dismiss="alert"></button>
                                                    <h4 class="alert-heading">
                                                        <strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                                    <ol class="alert-ol">
                                                        <li><?php echo $LANG['UI_DB_CONFIG_BEFORE_DM_RECOVER'] ?></li>
                                                        <li><?php echo $LANG['UI_DB_CONFIG_BEFORE_DM_RECOVER_TIPSONE'] ?></li>
                                                        <li><?php echo $LANG['UI_DB_CONFIG_BEFORE_DM_RECOVER_TIPSTWO'] ?></li>
                                                        <li><?php echo $LANG['UI_DB_CONFIG_BEFORE_DM_RECOVER_TIPSTHREE'] ?></li>
                                                        <li><?php echo $LANG['UI_DB_CONFIG_BEFORE_DM_RECOVER_TIPSFOUR'] ?></li>
                                                    </ol>
                                                </div>
                                            </div>

                                            <!-- 恢复最新点提示 -->
                                            <div class="form-group display-none" id="recoveryNewestTips">
                                                <label class="control-label col-md-3">
                                                </label>
                                                <div class="col-md-8 alert alert-block alert-info fade in ml15">
                                                    <button type="button" class="close" data-dismiss="alert"></button>
                                                    <ul class="alert-ul">
                                                        <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>
                                                            :</strong>
                                                        <li>
                                                            <?php echo $LANG['UI_DB_CONFIG_BEFORE_RECOVER_NEWEST'] ?>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div><!-- //END: recoveryNewestTips-->
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane" id="tab3">
                                    <div class="row row-dbrecover">
                                        <div class="nav-tabs-wrapper col-md-offset-1 col-md-10">
                                            <ul class="nav nav-tabs nav-line-tabs">
                                                <li class="recoveryConfigLi nav-item active">
                                                    <a href="#tab_recovery_config" class="nav-link" data-toggle="tab">
                                                        <i class="viconfont vicon-shili1"></i> <?php echo $LANG['UI_DB_RECOVERY_RECOVERY_CONFIG'] ?>
                                                    </a>
                                                </li>
                                                <li class="commonLi nav-item">
                                                    <a href="#tab_common" class="nav-link" data-toggle="tab">
                                                        <i class="viconfont vicon-celve1"></i> <?php echo $LANG['UI_DB_RECOVERY_COMMON_STRATEGY'] ?>
                                                    </a>
                                                </li>
                                                <li class="transferLi nav-item">
                                                    <a href="#tab_transfer" class="nav-link" data-toggle="tab">
                                                        <i class="viconfont vicon-a-chuanshu"></i> <?php echo $LANG['UI_DB_RECOVERY_TRANSFER_STRATEGY'] ?>
                                                    </a>
                                                </li>
                                                <li class="scriptLi nav-item">
                                                    <a href="#tab_script" class="nav-link" data-toggle="tab">
                                                        <i class="viconfont vicon-ziyuanxiangqing"></i> <?php echo $LANG['UI_PUBLIC_SCRIPT_CONFIGURE'] ?>
                                                    </a>
                                                </li>
                                                <li class="safeStrategyLi nav-item">
                                                    <a href="#tab_safe_strategy" class="nav-link" data-toggle="tab">
                                                        <i class="viconfont vicon-anquancelve"></i> <?php echo $LANG['UI_DB_RECOVERY_SAFE_STRATEGY'] ?>
                                                    </a>
                                                </li>
                                                <li class="highLi nav-item">
                                                    <a href="#tab_other" class="nav-link" data-toggle="tab">
                                                        <i class="viconfont vicon-gaojipeizhi"></i> <?php echo $LANG['UI_DB_RECOVERY_HIGH_STRATEGY'] ?>
                                                    </a>
                                                </li>
                                            </ul>
                                            <div class="tab-content hover-scroll-y">
                                                <!-- 恢复配置 -->
                                                <div class="tab-pane active" id="tab_recovery_config">
                                                    <div class="panel-body">
                                                        <!-- 恢复方式 -->
                                                        <div class="form-group instanceDiv pathDiv display-hide">
                                                            <label class="control-label col-md-2">
                                                                <span class="required">* </span>
                                                                <?php echo $LANG['UI_RECOVERY_TYPE'] ?>
                                                            </label>
                                                            <div class="col-md-6">
                                                                <select class="form-control select2me step3pathtype"
                                                                        name="pathType" id="pathType">
                                                                    <!-- 这里采用隐藏选项的方式 -->
                                                                    <option value="1"
                                                                            id="coverRecovery"><?php echo $LANG['UI_DB_ORIGINAL_COVERAGE_RECOVERY'] ?></option>
                                                                    <option value="2"
                                                                            id="createRecovery"><?php echo $LANG['UI_DB_ADD_NEW_DATABASE_RECOVERY'] ?></option>
                                                                    <option value="9"
                                                                            id="incompleteRecovery"><?php echo $LANG['UI_DB_INCOMPLETE_RECOVERY'] ?></option>
                                                                    <option value="8"
                                                                            id="fullRecovery"><?php echo $LANG['UI_DB_FULL_RECOVERY'] ?></option>
                                                                    <option value="3"
                                                                            id="specifyRecovery"><?php echo $LANG['UI_DB_DM_FILE_DIR_RECOVERY'] ?></option>
                                                                    <option value="4"
                                                                            id="redirectRecovery"><?php echo $LANG['UI_DB_DM_REDIRECT_RECOVERY'] ?></option>
                                                                    <option value="5"
                                                                            id="exportRecovery"><?php echo $LANG['UI_DB_EXPORT_REDIRECT_RECOVERY'] ?></option>
                                                                    <!-- <option value="6">Pdb数据库恢复</option> -->
                                                                    <option value="7"
                                                                            id="restoreArchivelogRecovery"><?php echo $LANG['UI_DB_RESTORE_ARCHIVELOG'] ?></option>
                                                                </select>
                                                                <span class="help-block"><?php echo $LANG['UI_DB_CHOOSE_DIFFERENT_WAY'] ?></span>
                                                                <a class="popovers popover-abs-position recovery-type_full_in_exp display-none"
                                                                   data-container="body" data-trigger="hover"
                                                                   data-html="true" data-placement="right"
                                                                   data-content="<?php echo $LANG['WEB_DB_RECOVERY_TYPE_FULL_TIPS'] . '<br>' . $LANG['WEB_DB_RECOVERY_TYPE_INCOMPLETE_TIPS'] . '<br>' . $LANG['WEB_DB_RECOVERY_TYPE_EXPORT_TIPS'] ?>">
                                                                    <i class="viconfont vicon-tishi"></i>
                                                                </a>
                                                                <a class="popovers popover-abs-position recovery-type_incomplete display-none"
                                                                   data-container="body" data-trigger="hover"
                                                                   data-html="true" data-placement="right"
                                                                   data-content="<?php echo $LANG['WEB_DB_RECOVERY_TYPE_INCOMPLETE_TIPS'] ?>">
                                                                    <i class="viconfont vicon-tishi"></i>
                                                                </a>
                                                            </div>
                                                        </div><!-- //END pathDiv -->

                                                        <!-- 定时恢复提示信息 -->
                                                        <div class="form-group timer-recovery-newest-tips">
                                                            <label class="control-label col-md-2"></label>
                                                            <div class="col-md-6">
                                                                <div class="alert alert-block alert-danger fade in">
                                                                    <div><i class="fa fa-warning"></i></div>
                                                                    <div id="timerRecoveryNewestTips"></div>
                                                                </div>
                                                            </div>
                                                        </div><!-- //END timer-recovery-newest-tips -->

                                                        <!-- Oracle-恢复分支 -->
                                                        <div class="form-group recoveryItem display-hide oracleRecoveryIncarnationDiv">
                                                            <label class="control-label col-md-2"
                                                                   for="oracleRecoveryIncarnation">
                                                                <?php echo $LANG['UI_DB_RECOVERY_ORACLE_RECOVERY_INCARNATION'] ?>
                                                            </label>
                                                            <div class="col-md-6">
                                                                <select class="form-control select2me"
                                                                        id="oracleRecoveryIncarnation"></select>
                                                            </div>
                                                        </div><!-- //END oracleRecoveryIncarnationDiv -->

                                                        <!-- Oracle-时间点恢复方式 -->
                                                        <div class="form-group recoveryItem display-hide oracleRecoveryTimepointDiv">
                                                            <label class="control-label col-md-2"
                                                                   for="oracleRecoveryTimepoint">
                                                                <?php echo $LANG['UI_DB_RECOVERY_ORACLE_RECOVERY_TIMEPOINT'] ?>
                                                            </label>
                                                            <div class="col-md-6">
                                                                <select class="form-control select2me"
                                                                        id="oracleRecoveryTimepoint">
                                                                    <option value="1"><?php echo $LANG['UI_DB_RECOVERY_ORACLE_RECOVERY_TIMEPOINT_OPTIONS1'] ?></option>
                                                                    <option value="2"><?php echo $LANG['UI_DB_RECOVERY_ORACLE_RECOVERY_TIMEPOINT_OPTIONS2'] ?></option>
                                                                    <!-- <option value="3"><?php echo $LANG['UI_DB_RECOVERY_ORACLE_RECOVERY_TIMEPOINT_OPTIONS3'] ?></option> -->
                                                                </select>
                                                            </div>
                                                        </div><!-- //END oracleRecoveryTimepointDiv -->

                                                        <!-- Oracle-恢复来源 -->
                                                        <div class="form-group recoveryItem display-hide oracleRecoverySourceDiv">
                                                            <label class="control-label col-md-2"
                                                                   for="oracleRecoverySource">
                                                                <?php echo $LANG['UI_DB_RECOVERY_ORACLE_RECOVERY_SOURCE']; ?>
                                                            </label>
                                                            <div class="col-md-6">
                                                                <select class="form-control select2me"
                                                                        id="oracleRecoverySource">
                                                                    <option value="1"><?php echo $LANG['UI_DB_RECOVERY_ORACLE_RECOVERY_SOURCE_ALL_TASK']; ?></option>
                                                                    <option value="2"><?php echo $LANG['UI_DB_RECOVERY_ORACLE_RECOVERY_SOURCE_SPECIFY_TASK']; ?></option>
                                                                </select>
                                                            </div>
                                                        </div><!-- //END oracleRecoverySourceDiv -->

                                                        <!-- Oracle-指定任务 -->
                                                        <div class="form-group recoveryItem display-hide oracleRecoverySpecifyTaskDiv">
                                                            <label class="control-label col-md-2"
                                                                   for="oracleRecoverySpecifyTask">
                                                                <?php echo $LANG['UI_DB_RECOVERY_ORACLE_RECOVERY_SPECIFY_TASK']; ?>
                                                            </label>
                                                            <div class="col-md-9">
                                                                <ul class="ztree ztree-fa bd1de5"
                                                                    id="oracleRecoverySpecifyTaskTree"></ul>
                                                            </div>
                                                        </div><!-- //END oracleRecoverySpecifyTaskDiv -->

                                                        <!-- Oracle - 恢复时间显示 -->
                                                        <div class="form-group recoveryItem display-hide oracleRecoveryTimeShowBackDiv">
                                                            <label class="control-label col-md-2"
                                                                   for="oracleRecoveryTimeShowBack">
                                                                <?php echo $LANG['UI_DB_RECOVERY_ORACLE_RECOVERY_TIME_SELECT']; ?>
                                                            </label>
                                                            <div class="col-md-9"
                                                                 id="oracleRecoveryTimeShowBackWrapper">
                                                                <a class="colorgreen" id="oracleRecoveryTimeShowBack" title="<?php echo $LANG['UI_DB_RECOVERY_ORACLE_EDIT_RECOVERY_TIME_TITLE']; ?>">
                                                                    <span id="oracleRecoveryTimeShowBackText"></span>
                                                                    <i class="viconfont vicon-xiugai"
                                                                       id="oracleRecoveryTimeShowBackIcon"></i>
                                                                </a>
                                                            </div>
                                                        </div><!-- //END oracleRecoveryTimeShowBack -->

                                                        <!-- Oracle-恢复备份点 -->
                                                        <div class="form-group recoveryItem display-hide oracleRecoveryBackupTimepointDiv">
                                                            <label class="control-label col-md-2"
                                                                   for="oracleRecoveryBackupTimepoint">
                                                                <?php echo $LANG['UI_DB_RECOVERY_ORACLE_RECOVERY_BACKUP_TIMEPOINT']; ?>
                                                            </label>
                                                            <div class="col-md-9">
                                                                <ul class="ztree ztree-fa bd1de5"
                                                                    id="oracleRecoveryBackupTimepointTree"></ul>
                                                                <div id="oracleRecoveryBackupTimepointNoTimepointTips">
                                                                    <div class="alert alert-block alert-danger fade in mb-0 mt-0">
                                                                        <i class="fa fa-warning"></i>
                                                                        <span>
                                                                            <?php echo $LANG['UI_DB_RECOVERY_ORACLE_RECOVERY_BACKUP_TIMEPOINT_NO_TIMEPOINT']; ?>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <!-- //END oracleRecoveryBackupTimepointNoTimepointTips -->
                                                            </div>
                                                        </div><!-- //END oracleRecoveryBackupTimepointDiv -->

                                                        <!-- Oracle-新建实例配置 -->
                                                        <div class="form-group createNewInstanceConfigDiv">
                                                            <label class="control-label col-md-2"><?php echo $LANG['UI_DB_NEW_INSTANCE_CONFIG'] ?></label>
                                                            <div class="col-md-9">
                                                                <div class="new-instance-config-item">
                                                                    <div class="new-instance-config-item__btn">
                                                                        <button type="button"
                                                                                class="btn btn-light-primary"
                                                                                id="createNewInstanceBtn"><?php echo $LANG['UI_DB_NEW_INSTANCE_CONFIG_BTN'] ?></button>
                                                                    </div>
                                                                    <div class="new-instance-config-item__result-configured display-none">
                                                                        <i class="viconfont vicon-Frame-15"></i>
                                                                        <span><?php echo $LANG['UI_DB_NEW_INSTANCE_CONFIG_CONFIGURED'] ?></span>
                                                                    </div>
                                                                    <div class="new-instance-config-item__result-unconfigured display-none">
                                                                        <i class="viconfont vicon-Frame6"></i>
                                                                        <span><?php echo $LANG['UI_DB_NEW_INSTANCE_CONFIG_UNCONFIGURED'] ?></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div><!-- //END createNewInstanceConfigDiv -->

                                                        <!-- Oracle-数据恢复路径 -->
                                                        <div class="form-group recoveryItem display-hide oracleRecoveryDataPathDiv">
                                                            <label class="control-label col-md-2"
                                                                   for="oracleRecoveryDataPath">
                                                                <?php echo $LANG['UI_DB_RECOVERY_ORACLE_DATA_PATH'] ?>
                                                            </label>
                                                            <div class="col-md-9">
                                                                <div id="oracleRecoveryDataPath"></div>
                                                                <span class="help-block "
                                                                      id="oracleRecoveryPathHelp1"><?php echo $LANG['UI_DB_RECOVERY_ORACLE_DATA_PATH_HELP_TIPS'] ?></span>
                                                                <span class="help-block "
                                                                      id="oracleRecoveryPathHelp2"><?php echo $LANG['UI_DB_RECOVERY_ORACLE_DATA_PATH_HELP_TIPS2'] ?></span>
                                                            </div>
                                                        </div><!-- //END oracleRecoveryDataPathDiv -->

                                                        <!-- Oracle-恢复内容 -->
                                                        <div class="form-group recoveryItem oracleRecoveryContentDiv display-hide">
                                                            <label class="control-label col-md-2 oracleRecoveryContentLabel">
                                                                <?php echo $LANG['UI_DB_ORACLE_RECOVERY_CONTENT'] ?>
                                                            </label>
                                                            <div class="col-md-9 accordion strategyThree">
                                                                <div class="panel panel-default strategy-panel">
                                                                    <div class="panel-heading">
                                                                        <h4 class="panel-title">
                                                                            <a class="accordion-toggle accordion-toggle-styled popovers"
                                                                               data-container="body"
                                                                               data-trigger="hover" data-placement="top"
                                                                               data-toggle="collapse"
                                                                               data-parent=".strategyThree"
                                                                               href="#oracleRecoveryContentBody"
                                                                               aria-expanded="true">
                                                                                <i class="fa fa-file-text-o font-green-seagreen"></i>
                                                                                <span class="font-green-seagreen">
                                                                                    <?php echo $LANG['UI_DB_ORACLE_RECOVERY_CONTENT'] ?>
                                                                                </span>
                                                                                <span class="strategyDes configFileDes"></span>
                                                                            </a>
                                                                        </h4>
                                                                    </div>
                                                                    <div id="oracleRecoveryContentBody"
                                                                         class="panel-collapse collapse in ">
                                                                        <div class="panel-body">
                                                                            <ul class="ztree"
                                                                                id="oracleRecoveryContent"></ul>
                                                                        </div>
                                                                    </div><!--END oracleRecoveryContentBody-->
                                                                </div>
                                                            </div>
                                                        </div><!-- //END oracleRecoveryContentDiv -->

                                                        <!-- Oracle-导出备份点 -->
                                                        <div class="form-group recoveryItem oracleExportTreeDiv display-hide">
                                                            <label class="control-label col-md-2"
                                                                   for="oracleExportTree">
                                                                <?php echo $LANG['UI_DB_RECOVERY_ORACLE_EXPORT_TREE'] ?>
                                                            </label>
                                                            <div class="col-md-9">
                                                                <ul class="ztree ztree-fa" id="oracleExportTree"></ul>
                                                            </div>
                                                        </div><!-- //END oracleExportTreeDiv -->

                                                        <!-- Oracle-导出目录 -->
                                                        <div class="form-group recoveryItem display-hide exportDiv">
                                                            <label class="control-label col-md-2 exportlabel"><?php echo $LANG['UI_DB_BACKUP_EXPORT_DIR'] ?> </label>
                                                            <div class="col-md-9">
                                                                <div id="exportPath"></div>
                                                                <span class="help-block"><?php echo $LANG['UI_DB_BACKUP_EXPORT_DIR_TIPS'] ?></span>
                                                            </div>
                                                        </div><!-- //END exportDiv -->

                                                        <!-- Oracle-归档日志还原方式 -->
                                                        <div class="form-group recoveryItem display-hide restoreArchivelogTypeDiv">
                                                            <label class="control-label col-md-2 restoreArchivelogTypeLabel"
                                                                   for="restoreArchivelogType">
                                                                <?php echo $LANG['UI_DB_RESTORE_ARCHIVELOG_TYPE'] ?>
                                                            </label>
                                                            <div class="col-md-6">
                                                                <select id="restoreArchivelogType"
                                                                        class="form-control select2me">
                                                                    <option value="1"><?php echo $LANG['UI_DB_RESTORE_ARCHIVELOG_TYPE_TIME'] ?></option>
                                                                    <!-- <option value="2">-->
                                                                    <?php //echo $LANG['UI_DB_RESTORE_ARCHIVELOG_TYPE_SCN'] ?><!--</option>-->
                                                                </select>
                                                            </div>
                                                        </div><!-- //END restoreArchivelogTypeDiv -->

                                                        <!-- Oracle-按时间还原 -->
                                                        <div class="form-group recoveryItem display-hide oracleRestoreTimeSelectDiv">
                                                            <label class="control-label col-md-2"
                                                                   for="oracleRestoreTimeSelectRange">
                                                                <?php echo $LANG['UI_DB_RESTORE_ARCHIVELOG_RANGE_TIME'] ?>
                                                            </label>
                                                            <div class="col-md-9">
                                                                <div class="oracleRestoreTimeSelectWrapper">
                                                                    <div class="select-item">
                                                                        <div class="minTime"></div>
                                                                        <div class="maxTime"></div>
                                                                    </div>
                                                                    <div class="select-item">
                                                                        <div id="oracleRestoreTimeSelectRange"></div>
                                                                    </div>
                                                                    <div class="select-item">
                                                                        <div id="oracleRestoreTimeSelectStartPicker">
                                                                            <input type="text" size="20"
                                                                                   id="oracleRestoreTimeSelectStartTime"
                                                                                   class="form-control">
                                                                            <label for="oracleRestoreTimeSelectStartTime">
                                                                                <?php echo $LANG['UI_DB_RESTORE_ARCHIVELOG_START_TIME'] ?>
                                                                                :
                                                                            </label>
                                                                        </div>
                                                                        <div id="oracleRestoreTimeSelectEndPicker">
                                                                            <input type="text" size="20"
                                                                                   id="oracleRestoreTimeSelectEndTime"
                                                                                   class="form-control">
                                                                            <label for="oracleRestoreTimeSelectEndTime">
                                                                                <?php echo $LANG['UI_DB_RESTORE_ARCHIVELOG_END_TIME'] ?>
                                                                                :
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div><!-- //END oracleRestoreTimeSelectDiv -->

                                                        <!-- Oracle-还原归档日志路径-->
                                                        <div class="form-group recoveryItem display-hide restoreArchivelogDiv">
                                                            <label class="control-label col-md-2 restoreArchivelogLabel">
                                                                <?php echo $LANG['UI_DB_RESTORE_ARCHIVELOG_FOLDER'] ?>
                                                            </label>
                                                            <div class="col-md-9">
                                                                <div id="restoreArchivelog"></div>
                                                                <span class="help-block ">
                                                                    <?php echo $LANG['UI_DB_RESTORE_ARCHIVELOG_FOLDER_TIPS1'] ?>
                                                                </span>
                                                            </div>
                                                        </div><!-- //END restoreArchivelogDiv -->

                                                        <!-- Oracle-按SCN方式还原 -->
                                                        <div class="form-group recoveryItem display-hide restoreArchivelogSCNDiv">
                                                            <label class="control-label col-md-2 restoreArchivelogSCNLabel"
                                                                   for="restoreArchivelogSCN1">
                                                                <?php echo $LANG['UI_DB_RESTORE_ARCHIVELOG_RANGE_SCN'] ?>
                                                            </label>
                                                            <div class="col-md-9">
                                                                <div>
                                                                    <input type="number" id="restoreArchivelogSCN1"
                                                                           class="form-control" autocomplete="off"/>
                                                                    ~
                                                                    <input type="number" id="restoreArchivelogSCN2"
                                                                           class="form-control" autocomplete="off"/>
                                                                </div>
                                                                <span class="help-block"
                                                                      id="restoreArchivelogSCNTips"></span>
                                                            </div>
                                                        </div><!-- //END restoreArchivelogSCNDiv -->

                                                        <!-- MySQL - 打开数据库 -->
                                                        <div class="form-group recoveryItem mysqlOpenDbDiv display-none">
                                                            <label class="control-label col-md-2 openDbLabel"
                                                                   for="mysqlOpenDbCheck">
                                                                <?php echo $LANG['UI_DB_RECOVERY_OPEN_DB'] ?>
                                                            </label>
                                                            <div class="col-md-6">
                                                                <input type="checkbox" id="mysqlOpenDbCheck"
                                                                       class="make-switch" data-on-color="primary"
                                                                       data-off-color="info" data-size="small">
                                                                <span class="help-block"><?php echo $LANG['UI_DB_RECOVERY_OPEN_DB_TIPS2'] ?></span>
                                                            </div>
                                                        </div><!--//END mysqlOpenDbDiv -->

                                                        <!-- MySQL - 数据库启动方式 -->
                                                        <div class="form-group recoveryItem display-hide mysqlStartTypeDiv">
                                                            <label class="control-label col-md-2" for="mysqlStartType">
                                                                <?php echo $LANG['UI_DB_RECOVERY_MYSQL_START_TYPE'] ?>
                                                            </label>
                                                            <div class="col-md-6">
                                                                <select class="form-control select2me"
                                                                        id="mysqlStartType">
                                                                    <option value="1"
                                                                            id="coverRecovery"><?php echo $LANG['UI_DB_RECOVERY_MYSQL_START_TYPE_OPTIONS1'] ?></option>
                                                                    <option value="2"
                                                                            id="createRecovery"><?php echo $LANG['UI_DB_RECOVERY_MYSQL_START_TYPE_OPTIONS2'] ?></option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <!-- MySQL - 服务启动 -->
                                                        <div class="form-group recoveryItem display-hide startcommanddiv"
                                                             id="startcommandbox">
                                                            <label class="control-label col-md-2 startcommandlabel"><?php echo $LANG['UI_DB_BACKUP_START_COMMAND'] ?></label>
                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control"
                                                                       id="startcommand" placeholder="" value="mysql">
                                                                <span class="help-block"><?php echo $LANG['UI_DB_BACKUP_START_COMMAND_TIPS'] ?></span>
                                                            </div>
                                                        </div>

                                                        <!-- MySQL - 命令行启动 -->
                                                        <div class="form-group recoveryItem display-hide mysqlCustomStartDiv">
                                                            <label class="control-label col-md-2"
                                                                   for="mysqlCustomStart">
                                                                <?php echo $LANG['UI_DB_RECOVERY_MYSQL_CUSTOM_START'] ?>
                                                            </label>
                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control"
                                                                       id="mysqlCustomStart">
                                                                <span class="help-block"><?php echo $LANG['UI_DB_RECOVERY_MYSQL_CUSTOM_START_TIPS'] ?></span>
                                                            </div>
                                                        </div>

                                                        <!-- MySQL - 停止命令 -->
                                                        <div class="form-group recoveryItem display-hide mysqlCustomStopDiv">
                                                            <label class="control-label col-md-2" for="mysqlCustomStop">
                                                                <?php echo $LANG['UI_DB_RECOVERY_MYSQL_CUSTOM_STOP'] ?>
                                                            </label>
                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control"
                                                                       id="mysqlCustomStop">
                                                                <span class="help-block"><?php echo $LANG['UI_DB_RECOVERY_MYSQL_CUSTOM_STOP_TIPS'] ?></span>
                                                            </div>
                                                        </div>

                                                        <!-- MySQL - 重定向目录 -->
                                                        <div class="form-group recoveryItem display-hide"
                                                             id="logfileDiv">
                                                            <label class="control-label col-md-2 logfilelabel"><?php echo $LANG['UI_DB_REDIRECT_DIR'] ?></label>
                                                            <div class="col-md-6">
                                                                <div id="logfilePath"></div>
                                                                <span class="help-block"><?php echo $LANG['UI_DB_NO_DATA_DIRECTORY_IS_FLEXIBLE'] ?></span>
                                                            </div>
                                                        </div>

                                                        <!-- DM - 指定文件夹 -->
                                                        <div class="form-group recoveryItem dmfileDiv display-hide">
                                                            <label class="control-label col-md-2"><?php echo $LANG['UI_DB_SPECIFIED_FILE_PATH'] ?></label>
                                                            <div class="col-md-6">
                                                                <div id="dmFilePath"></div>
                                                                <span class="help-block "
                                                                      id="postgreTips"><?php echo $LANG['UI_SPECIFIED_FILE_PATH_TIPS'] ?></span>
                                                            </div>
                                                        </div>

                                                        <!-- pg系 - 数据文件路径 -->
                                                        <div class="form-group recoveryItem pgNewInstanceDiv display-hide">
                                                            <label class="control-label col-md-2">
                                                                <span class="required">* </span>
                                                                <?php echo $LANG['UI_DB_DATABASE_FILE_PATH'] ?>
                                                            </label>
                                                            <div class="col-md-6">
                                                                <div id="pgDataFilePath"></div>
                                                            </div>
                                                        </div>

                                                        <!-- pg系 - 归档日志文件路径 -->
                                                        <div class="form-group recoveryItem pgNewInstanceDiv display-hide">
                                                            <label class="control-label col-md-2">
                                                                <span class="required">* </span>
                                                                <?php echo $LANG['UI_DB_ARCHIVELOG_FILE_PATH'] ?>
                                                            </label>
                                                            <div class="col-md-6">
                                                                <div id="pgArchivelogFilePath"></div>
                                                            </div>
                                                        </div>

                                                        <!-- pg系 - 实例端口 -->
                                                        <div class="form-group recoveryItem pgNewInstanceDiv display-hide">
                                                            <label class="control-label col-md-2" for="pgInstancePort">
                                                                <span class="required">* </span>
                                                                <?php echo $LANG['UI_DB_POSTGRES_INSTANCE_PORT'] ?>
                                                            </label>
                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control width130"
                                                                       onkeyup="value=value.replace(/[^\d]/g,'')"
                                                                       maxlength="5" id="pgInstancePort">
                                                                <span class="help-block " id="pgInstancePortTips">
                                                                    <?php echo $LANG['UI_DB_POSTGRES_INSTANCE_PORT_TIPS'] ?>
                                                                </span>
                                                            </div>
                                                        </div>

                                                        <!-- pg系 - 指定文件夹 -->
                                                        <div class="form-group recoveryItem pgSpecifyFolderDiv display-hide">
                                                            <label class="control-label col-md-2">
                                                                <span class="required">* </span>
                                                                <?php echo $LANG['UI_DB_SPECIFIED_FILE_PATH'] ?>
                                                            </label>
                                                            <div class="col-md-6">
                                                                <div id="pgSpecifyFolder"></div>
                                                                <span class="help-block " id="pgSpecifyFolderHelpBlock">
                                                                    <?php echo $LANG['UI_SPECIFIED_FILE_PATH_TIPS'] ?>
                                                                </span>
                                                            </div>
                                                        </div>

                                                        <!-- pg系 - 自定义归档目录 -->
                                                        <div class="form-group recoveryItem pgSpecifyFolderDiv display-hide">
                                                            <label class="control-label col-md-2">
                                                                <span class="required">* </span>
                                                                <?php echo $LANG['UI_DB_CUSTOM_ARCHIVE_DIR'] ?>
                                                            </label>
                                                            <div class="col-md-6">
                                                                <div id="pgCustomArchivelog"></div>
                                                                <span class="help-block " id="pgCustomArchivelogHelpBlock">
                                                                    <?php echo $LANG['UI_DB_CUSTOM_ARCHIVE_DIR_TIPS'] ?>
                                                                </span>
                                                            </div>
                                                        </div>

                                                        <!-- SAP HANA-时间点恢复方式 -->
                                                        <div class="form-group recoveryItem display-hide sapHanaRecoveryTimeDiv">
                                                            <label class="control-label col-md-2"
                                                                   for="sapHanaRecoveryTime">
                                                                <?php echo $LANG['UI_DB_RECOVERY_SAP_HANA_RECOVERY_TIMEPOINT'] ?>
                                                            </label>
                                                            <div class="col-md-6">
                                                                <select class="form-control select2me"
                                                                        id="sapHanaRecoveryTime">
                                                                    <option value="1"><?php echo $LANG['UI_DB_RECOVERY_RECOVERY_TIME_OPTIONS1'] ?></option>
                                                                    <option value="2"><?php echo $LANG['UI_DB_RECOVERY_RECOVERY_TIME_OPTIONS2'] ?></option>
                                                                    <option value="3"><?php echo $LANG['UI_DB_RECOVERY_RECOVERY_TIME_OPTIONS3'] ?></option>
                                                                </select>
                                                                <span class="help-block" id="sapHanaRecoveryTimeTips1">
                                                                    <?php echo $LANG['UI_DB_RECOVERY_RECOVERY_TIME_TIPS1'] ?>
                                                                </span>
                                                                <span class="help-block" id="sapHanaRecoveryTimeTips2">
                                                                    <?php echo $LANG['UI_DB_RECOVERY_RECOVERY_TIME_TIPS2'] ?>
                                                                </span>
                                                                <span class="help-block" id="sapHanaRecoveryTimeTips3">
                                                                    <?php echo $LANG['UI_DB_RECOVERY_RECOVERY_TIME_TIPS3'] ?>
                                                                </span>
                                                            </div>
                                                        </div>

                                                        <!-- SAP HANA-存储离线提示框 -->
                                                        <div class="form-group recoveryItem display-hide sapHanaTimepointStorageOfflineDiv">
                                                            <div class="col-md-offset-2 col-md-6">
                                                                <div class="alert alert-block alert-danger fade in">
                                                                    <i class="fa fa-warning"></i>
                                                                    <span>
                                                                        <?php echo $LANG['UI_DB_RECOVERY_SAP_HANA_TIMEPOINT_STORAGE_IS_OFFLINE']; ?>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div><!-- //END sapHanaTimepointStorageOfflineDiv -->

                                                        <!-- SAP HANA、SQL Server-批量数据库恢复配置 -->
                                                        <div class="form-group recoveryItem multiDbConfig display-hide">
                                                            <label class="control-label col-md-2"><?php echo $LANG['UI_DB_RECOVERY_DB_CONFIG'] ?></label>
                                                            <div class="col-md-8">
                                                                <div id="multiDbConfigWrapper"></div>
                                                            </div>
                                                        </div>

                                                        <!-- TiDB - 时间点恢复方式 -->
                                                        <div class="form-group recoveryItem display-hide tidbRecoveryTimepointDiv">
                                                            <label class="control-label col-md-2"
                                                                   for="tidbRecoveryTimepoint">
                                                                <?php echo $LANG['UI_DB_RECOVERY_TIDB_RECOVERY_TIMEPOINT'] ?>
                                                            </label>
                                                            <div class="col-md-6">
                                                                <select class="form-control select2me"
                                                                        id="tidbRecoveryTimepoint">
                                                                    <option value="1"><?php echo $LANG['UI_DB_RECOVERY_TIDB_RECOVERY_TIMEPOINT_OPTIONS1'] ?></option>
                                                                    <option value="2"><?php echo $LANG['UI_DB_RECOVERY_TIDB_RECOVERY_TIMEPOINT_OPTIONS2'] ?></option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <!-- TiDB-存储离线提示框 -->
                                                        <div class="form-group recoveryItem display-hide tidbTimepointStorageOfflineDiv">
                                                            <div class="col-md-offset-2 col-md-6">
                                                                <div class="alert alert-block alert-danger fade in">
                                                                    <i class="fa fa-warning"></i>
                                                                    <span>
                                                                        <?php echo $LANG['UI_DB_RECOVERY_TIDB_TIMEPOINT_STORAGE_IS_OFFLINE']; ?>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div><!-- //END tidbTimepointStorageOfflineDiv -->

                                                        <!-- TiDB - 备份集 -->
                                                        <div class="form-group recoveryItem display-hide tidbRecoveryBackupSetDiv">
                                                            <label class="control-label col-md-2"
                                                                   for="tidbRecoveryBackupSet">
                                                                <?php echo $LANG['UI_DB_RECOVERY_TIDB_RECOVERY_BACKUP_SET'] ?>
                                                            </label>
                                                            <div class="col-md-6">
                                                                <select class="form-control select2me"
                                                                        id="tidbRecoveryBackupSet">
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <!-- TiDB-恢复时间 -->
                                                        <div class="form-group recoveryItem display-hide tidbRecoveryTimeSelectDiv">
                                                            <label class="control-label col-md-2"
                                                                   for="tidbRecoveryTimeSelectRange">
                                                                <?php echo $LANG['UI_DB_RECOVERY_TIDB_RECOVERY_TIME_SELECT'] ?>
                                                            </label>
                                                            <div class="col-md-9">
                                                                <div class="tidbRecoveryTimeSelectWrapper">
                                                                    <div class="select-item">
                                                                        <div class="minTime"></div>
                                                                        <div class="maxTime"></div>
                                                                    </div>
                                                                    <div class="select-item">
                                                                        <div id="tidbRecoveryTimeSelectRange"></div>
                                                                    </div>
                                                                    <div class="select-item"
                                                                         id="tidbRecoveryTimeSelectPicker">
                                                                        <label class="control-label"
                                                                               for="tidbRecoveryTimeSelectTime"><?php echo $LANG['UI_DB_RECOVERY_TIDB_RECOVERY_TIME_SELECT'] ?>
                                                                            :</label>
                                                                        <input type="text" size="20"
                                                                               id="tidbRecoveryTimeSelectTime"
                                                                               class="form-control">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- MongoDB-路径 -->
                                                        <div class="form-group recoveryItem mongodbSelectDirDiv display-hide">
                                                            <label class="control-label col-md-2 mongodbSelectDirLabel">
                                                                <?php echo $LANG['UI_DB_RECOVERY_MONGODB_INSTANCE_PATH'] ?>
                                                            </label>
                                                            <div class="col-md-6">
                                                                <div id="mongodbDatafilePath"></div>
                                                            </div>
                                                            <div class="col-md-6 alert alert-block alert-warning fade in ml15 display-none"
                                                                 id="noFilePathTreeTips">
                                                                <button type="button" class="close"
                                                                        data-dismiss="alert"></button>
                                                                <p>
                                                                    <?php echo $LANG['UI_RECOVERY_FILE_GET_PATH_FAILURE'] ?>
                                                                </p>
                                                            </div>
                                                        </div><!-- //END mongodbSelectDirDiv -->

                                                        <!-- Oracle、PG、TiDB、MongoDB - 打开数据库 -->
                                                        <div class="form-group recoveryItem openDbDiv display-none">
                                                            <label class="control-label col-md-2 openDbLabel"
                                                                   for="openDbCheck">
                                                                <?php echo $LANG['UI_DB_RECOVERY_OPEN_DB'] ?>
                                                            </label>
                                                            <div class="col-md-6">
                                                                <input type="checkbox" id="openDbCheck"
                                                                       class="make-switch" data-on-color="primary"
                                                                       data-off-color="info" data-size="small"
                                                                       data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                <span class="help-block"
                                                                      id="oracleOpenDbTips"><?php echo $LANG['UI_DB_RECOVERY_OPEN_DB_TIPS1'] ?></span>
                                                                <span class="help-block"
                                                                      id="oracleOpenDbFullTips"><?php echo $LANG['UI_DB_RECOVERY_OPEN_DB_TIPS2'] ?></span>
                                                                <span class="help-block"
                                                                      id="pgOpenDbTips"><?php echo $LANG['UI_DB_RECOVERY_OPEN_DB_TIPS2'] ?></span>
                                                                <span class="help-block"
                                                                      id="mongodbOpenDbTips"><?php echo $LANG['UI_DB_RECOVERY_OPEN_DB_TIPS3'] ?></span>
                                                            </div>
                                                        </div><!--//END openDbDiv -->

                                                        <!-- 日志回滚开关 -->
                                                        <div class="form-group recoveryItem logdateDiv display-hide">
                                                            <label class="control-label col-md-2 logtimelabel"><?php echo $LANG['UI_DB_ROLL_TIME'] ?></label>
                                                            <div class="col-md-6">
                                                                <input type="checkbox" id="logtimecheck"
                                                                       class="make-switch" data-on-color="primary"
                                                                       data-off-color="info" data-size="small"
                                                                       data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                <span class="help-block"><?php echo $LANG['UI_DB_INPUT_CORRECT_ROLL_TIME_AFTER_OPEN'] ?></span>
                                                            </div>
                                                        </div><!-- //END logdateDiv -->
                                                        <!-- 日志回滚 -->
                                                        <div class="form-group recoveryItem logdateInputDiv display-hide"
                                                             id="logTimeDiv">
                                                            <label class="control-label col-md-2"><?php echo $LANG['UI_DB_SELECT_ROLL_TIME'] ?> </label>
                                                            <div class="col-md-6">
                                                                <div class="input-group date form_datetime"
                                                                     id="rollbackFormDatetime">
                                                                    <input type="text" size="20" id="logtime"
                                                                           class="form-control">
                                                                    <span class="input-group-btn">
										                				<button class="btn default" id="resetdate"
                                                                                type="button"><i
                                                                                    class="fa fa-times"></i></button>
										                				<button class="btn default date-set"
                                                                                type="button"><i
                                                                                    class="viconfont vicon-ge_calendar"></i></button>
										                			</span>
                                                                </div>
                                                                <span class="help-block " id="rolltimeTips"></span>
                                                            </div>
                                                        </div><!-- //END logdateInputDiv -->

                                                        <!-- 数据加密密码 -->
                                                        <div class="form-group recoveryItem display-hide encryptDiv">
                                                            <label class="control-label col-md-2 passwordlabel"><?php echo $LANG['UI_VM_DATABASE_ENCR_PWD'] ?></label>
                                                            <div class="col-md-6">
                                                                <input type="password" autocomplete="off"
                                                                       class="form-control" id="encryptPassword"
                                                                       maxlength="256"
                                                                       oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')">
                                                                <button type="button"
                                                                        class="btn btn-link show-encrypt-password-btn"
                                                                        style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); text-decoration: none;">
                                                                    <i class="viconfont vicon-a-lujing8232"></i>
                                                                </button>
                                                            </div>
                                                        </div><!-- //END encryptDiv -->
                                                    </div>
                                                </div><!-- //END: tab_recovery_config-->

                                                <!-- 通用策略 -->
                                                <div class="tab-pane" id="tab_common">
                                                    <div class="panel-body">
                                                        <div id="backupStrategyDiv"
                                                             class="col-md-10 col-md-offset-1"></div>
                                                    </div>
                                                </div><!-- //END: tab_common-->

                                                <!-- 传输策略 -->
                                                <div class="tab-pane" id="tab_transfer">
                                                    <div class="panel-body">
                                                        <!-- 传输加密 -->
                                                        <div class="form-group transferEncryptedDiv">
                                                            <label class="control-label col-md-2 transferEncryptedLabel">
                                                                <?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER'] ?>
                                                            </label>
                                                            <div class="col-md-6">
                                                                <input type="checkbox" id="transferEncryptedCheck"
                                                                       class="make-switch"
                                                                       data-on-color="primary" data-off-color="info"
                                                                       data-size="small"
                                                                       data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                <span class="help-block"><?php echo $LANG['UI_DB_RECOVERY_TRANSFER_ENCRYPTED_TIP'] ?></span>
                                                            </div>
                                                        </div><!-- //END transferEncryptedDiv -->

                                                        <!-- 传输加密算法 -->
                                                        <div class="form-group transfer-encrypt-method-form display-none">
                                                            <label class="control-label transfer-encrypt-method-label col-md-2"><?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?></label>
                                                            <div class="col-md-4">
                                                                <select class="form-control select2me"
                                                                        id="transferEncryptMethod">
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
                                                        </div><!-- //END transfer-encrypt-method-form -->

                                                        <!-- 传输网络 -->
                                                        <div class="form-group display-none transfernetworkDiv">
                                                            <label class="control-label col-md-2 transfernetworklabel">
                                                                <?php echo $LANG['UI_NODE_TRANSFER_NETWORK'] ?>
                                                            </label>
                                                            <div class="col-md-6">
                                                                <ul class="ztree" id="transferNetworkTree"></ul>
                                                                <span class="help-block"><?php echo $LANG['UI_NODE_TRANSFER_NETWORK_TIPS'] ?></span>
                                                            </div>
                                                        </div><!-- //END transfernetworkDiv -->

                                                        <!-- Oracle-通道数 -->
                                                        <div class="form-group threadDiv display-none">
                                                            <label class="control-label col-md-2 threadnumlabel">
                                                                <?php echo $LANG['UI_DB_ORACLE_CHANNEL_NUM'] ?>
                                                            </label>
                                                            <div class="col-md-6">
                                                                <div id="recoveryThreadDiv">
                                                                    <div class="input-group spinner-group">
                                                                        <input id="recoveryThreadNum"
                                                                               onkeyup="value=value.replace(/[^\d]/g,'')"
                                                                               class="spinner-input form-control">
                                                                        <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                            <button type="button"
                                                                                    class="btn spinner-up default">
                                                                                <i class="fa fa-angle-up"></i>
                                                                            </button>
                                                                            <button type="button"
                                                                                    class="btn spinner-down default">
                                                                                <i class="fa fa-angle-down"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <span class="help-block"><?php echo $LANG['UI_DB_ORACLE_CHANNEL_NUM_TIPS2'] ?></span>
                                                            </div>
                                                        </div><!-- //END threadDiv -->

                                                        <!-- MongoDB-传输线程 1~16 -->
                                                        <div class="form-group mongodbThreadDiv display-none">
                                                            <label class="control-label col-md-2 mongodbthreadnumlabel">
                                                                <?php echo $LANG['UI_BACKUP_THREAD_NUM'] ?>
                                                            </label>
                                                            <div class="col-md-6">
                                                                <div id="mongodbRecoveryThreadDiv">
                                                                    <div class="input-group spinner-group">
                                                                        <input id="mongodbRecoveryThreadNum"
                                                                               onkeyup="value=value.replace(/[^\d]/g,'')"
                                                                               class="spinner-input form-control">
                                                                        <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                            <button type="button"
                                                                                    class="btn spinner-up default">
                                                                                <i class="fa fa-angle-up"></i>
                                                                            </button>
                                                                            <button type="button"
                                                                                    class="btn spinner-down default">
                                                                                <i class="fa fa-angle-down"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <span class="help-block"><?php echo $LANG['UI_DB_MONGODB_THREAD_NUM_TIPS'] ?></span>
                                                            </div>
                                                        </div><!-- //END mongodbThreadDiv -->
                                                    </div>
                                                </div><!-- //END: tab_transfer-->

                                                <!-- 脚本配置 -->
                                                <div class="tab-pane" id="tab_script">
                                                    <div class="panel-body">
                                                        <!-- 脚本配置开关 -->
                                                        <div class="form-group scriptConfigDiv">
                                                            <label class="control-label col-md-2 scriptConfigLabel">
                                                                <?php echo $LANG['UI_PUBLIC_SCRIPT_CONFIGURE'] ?>
                                                            </label>
                                                            <div class="col-md-6">
                                                                <input type="checkbox" id="scriptConfigSwitch"
                                                                       class="make-switch" data-on-color="primary"
                                                                       data-off-color="info" data-size="small">
                                                                <span class="help-block"
                                                                      id="scriptConfigTips"><?php echo $LANG['UI_DB_RECOVERY_SCRIPT_TIPS'] ?></span>
                                                                <span class="help-block"
                                                                      id="newestScriptConfigTips"><?php echo $LANG['UI_DB_RECOVERY_NEWEST_SCRIPT_TIPS'] ?></span>
                                                            </div>
                                                        </div><!--//END scriptConfigDiv-->

                                                        <!-- 脚本配置内容 -->
                                                        <div class="form-group scriptConfigScriptDiv">
                                                            <label class="control-label col-md-2"></label>
                                                            <div class="col-md-10">
                                                                <div class="accordion scriptConfigOne">
                                                                    <div class="panel panel-default">
                                                                        <div class="panel-heading">
                                                                            <h4 class="panel-title">
                                                                                <a class="accordion-toggle accordion-toggle-styled popovers"
                                                                                   data-container="body"
                                                                                   data-trigger="hover"
                                                                                   data-placement="top"
                                                                                   data-toggle="collapse"
                                                                                   data-parent=".scriptConfigOne"
                                                                                   href="#scriptConfigPane"
                                                                                   aria-expanded="true">
                                                                                    <i class="viconfont vicon-jiaobenguanli font-green-seagreen"></i>
                                                                                    <span class="font-green-seagreen"><?php echo $LANG['UI_PUBLIC_SCRIPT_CONFIGURE'] ?></span>
                                                                                </a>
                                                                            </h4>
                                                                        </div>
                                                                        <div id="scriptConfigPane"
                                                                             class="panel-collapse collapse in"
                                                                             aria-expanded="true">
                                                                            <div class="panel-body"
                                                                                 style="display: flex;">
                                                                                <div class="col-md-2 nav-tab-radios" style="padding: 0">
                                                                                    <ul class="nav nav-tabs tabs-left"
                                                                                        style="display: flex; flex-direction: column; justify-content: flex-start; height: 100%;">
                                                                                        <li class="active">
                                                                                            <a href="#tab_before_recovery"
                                                                                               data-toggle="tab"
                                                                                               aria-expanded="true"><?php echo $LANG['UI_PUBLIC_BEFORE_RECOVERY_SCRIPT'] ?></a>
                                                                                        </li>
                                                                                        <li>
                                                                                            <a href="#tab_after_recovery"
                                                                                               data-toggle="tab"
                                                                                               aria-expanded="false"><?php echo $LANG['UI_PUBLIC_AFTER_RECOVERY_SCRIPT'] ?></a>
                                                                                        </li>
                                                                                        <li class="sqlValidateLi">
                                                                                            <a href="#tab_validate"
                                                                                               data-toggle="tab"
                                                                                               aria-expanded="false"><?php echo $LANG['UI_DB_RECOVERY_VALIDATE_SCRIPT'] ?></a>
                                                                                        </li>
                                                                                    </ul>
                                                                                </div>
                                                                                <div class="col-md-10">
                                                                                    <div class="tab-content"
                                                                                         style="height: 100%">
                                                                                        <div class="tab-pane active in"
                                                                                             id="tab_before_recovery">
                                                                                            <div class="beforeRecoveryScriptConfig"></div>
                                                                                        </div>
                                                                                        <div class="tab-pane fade"
                                                                                             id="tab_after_recovery">
                                                                                            <div class="afterRecoveryScriptConfig"></div>
                                                                                        </div>
                                                                                        <div class="tab-pane fade"
                                                                                             id="tab_validate">
                                                                                            <div class="validateScriptConfig"></div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div><!--//END scriptConfigScriptDiv-->
                                                    </div>
                                                </div><!-- //END: tab_script-->

                                                <!-- 安全策略 -->
                                                <div class="tab-pane" id="tab_safe_strategy">
                                                    <div class="panel-body">
                                                        <!-- 安全策略 -->
                                                        <div class="form-group safeStrategyDiv">
                                                            <label class="control-label col-md-2 safeStrategyLabel">
                                                                <?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?>
                                                            </label>
                                                            <div class="col-md-9">
                                                                <div id="integrityCheck"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div><!-- //END: tab_safe_strategy-->

                                                <!-- 高级配置 -->
                                                <div class="tab-pane" id="tab_other">
                                                    <div class="advance-config-wrap">
                                                        <div class="advance-config-wrap__row row m0">
                                                            <!-- 切换页 -->
                                                            <div class="col-md-2 col-sm-3 col-xs-3 nav-tab-radios">
                                                                <ul class="nav nav-tabs tabs-left min-height400" id="tabAdvanceTabUl">
                                                                    <li id="tabAdvancedDatabaseConfigTab"
                                                                        class="active">
                                                                        <a href="#tab_advanced_database_config"
                                                                           data-toggle="tab"
                                                                           aria-expanded="true"><?php echo $LANG['UI_DB_RECOVERY_ADVANCED_TAB_DATABASE_CONFIG'] ?></a>
                                                                    </li>
                                                                    <li id="tabAdvancedRetryTab">
                                                                        <a href="#tab_advanced_retry" data-toggle="tab"
                                                                           aria-expanded="false"><?php echo $LANG['UI_STRATEGY_RETRY'] ?></a>
                                                                    </li>
                                                                    <li id="tabAdvancedOverloadTab">
                                                                        <a href="#tab_advanced_overload"
                                                                           data-toggle="tab"
                                                                           aria-expanded="false"><?php echo $LANG['UI_STRATEGY_OVER_LOAD_LABEL'] ?></a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            <!-- 切换内容 -->
                                                            <div class="col-md-10 col-sm-9 col-xs-9 pe-0 advance-config-wrap__row__content">
                                                                <div class="tab-content"
                                                                     style="height: 100%; overflow: hidden;"
                                                                     id="tabAdvancedContent">
                                                                    <!-- 数据库配置 -->
                                                                    <div class="tab-pane fade active in"
                                                                         id="tab_advanced_database_config">
                                                                        <div class="abnormal-handle-form">
                                                                            <div class="form-group parallelNumDiv display-none">
                                                                                <label class="control-label col-md-3 parallelNumLabel">
                                                                                    <?php echo $LANG['UI_DB_CLIENT_PARALLEL_NUM'] ?>
                                                                                </label>
                                                                                <div class="col-md-6">
                                                                                    <div id="parallelNumSpinner">
                                                                                        <div class="input-group spinner-group">
                                                                                            <input id="parallelNum"
                                                                                                   class="spinner-input form-control"
                                                                                                   onkeyup="value=value.replace(/[^\d]/g,'')">
                                                                                            <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                                <button type="button"
                                                                                                        class="btn spinner-up default">
                                                                                                    <i class="fa fa-angle-up"></i>
                                                                                                </button>
                                                                                                <button type="button"
                                                                                                        class="btn spinner-down default">
                                                                                                    <i class="fa fa-angle-down"></i>
                                                                                                </button>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <span class="help-block"><?php echo $LANG['UI_DB_CLIENT_PARALLEL_NUM_TIPS'] ?></span>
                                                                                </div>
                                                                            </div><!-- //END: parallelNumDiv -->
                                                                        </div>
                                                                        <!-- //END: advanced-detail-conf-district -->
                                                                    </div><!-- //END: tab_advanced_database_config -->

                                                                    <!-- 重试 -->
                                                                    <div class="tab-pane fade"
                                                                         style="margin-left: 20px;"
                                                                         id="tab_advanced_retry">
                                                                    </div><!-- //END: tab_advanced_retry -->

                                                                    <!-- 过载保护 -->
                                                                    <div class="tab-pane fade"
                                                                         id="tab_advanced_overload">
                                                                        <div class="abnormal-handle-form">
                                                                            <!-- 忽略节点资源限制 -->
                                                                            <div class="form-group advancedDiv ignoreResourceLimitDiv">
                                                                                <label for="ignoreResourceLimit"
                                                                                       class="control-label col-md-3 ignoreResourceLimitLabel form-group-label">
                                                                                    <?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE'] ?>
                                                                                </label>
                                                                                <div class="col-md-6">
                                                                                    <input type="checkbox"
                                                                                           id="ignoreResourceLimit"
                                                                                           class="make-switch"
                                                                                           data-on-color="primary"
                                                                                           data-off-color="info"
                                                                                           data-size="small"
                                                                                           data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                    <span class="help-block"><?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE_TIPS'] ?></span>
                                                                                </div>
                                                                            </div>
                                                                            <!-- 任务最大并发数 + 任务禁止运行时间段-->
                                                                            <div class="form-group node-limit-form">
                                                                                <div class="table-container col-md-offset-3 pl15">
                                                                                    <table class="table table-hover table-borderless"
                                                                                           id="nodeLimitTable">
                                                                                    </table>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <!-- //END: advanced-detail-conf-district -->
                                                                    </div><!-- //END: tab_advanced_overload -->
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div><!-- //END: tab_other-->
                                            </div>
                                        </div><!-- //END tabbable-custom -->

                                        <!-- 用recoveryItem标识所有的恢复选项 -->
                                    </div><!-- //END row-dbrecover -->
                                </div><!--END tab3-->

                                <div class="tab-pane" id="tab4">
                                    <div class="tab-pane__body">
                                        <div class="tab-pane__body__form">
                                            <div class="alert alert-danger display-none jobnametip"></div>
                                            <div class="form-group  mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_JOB_RNAME'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <div class="input-icon right">
                                                        <input type="text" maxlength="64" class="form-control"
                                                               id="jobname"
                                                               onkeyup="customInputValidate('string', this.value, $(this))"/>
                                                        <span class="help-block "><?php echo $LANG['UI_JOB_RNAME_TIPS'] ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <h4 class="form-section"></h4>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_DATA_SOURCE_INFO'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static  vmtypeshow">
                                                    </p>
                                                    <ul class="ztree" id="recoverySourceTree"
                                                        style="border: 1px solid #E6E6E6"></ul>
                                                </div>
                                            </div>

                                            <h4 class="form-section"></h4>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_FILE_PATH'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static  recovershow">
                                                    </p>
                                                    <ul class="ztree" id="recoveryTargetTree"
                                                        style="border: 1px solid #E6E6E6"></ul>
                                                </div>
                                            </div>

                                            <h4 class="form-section"></h4>
                                            <!-- 恢复配置 -->
                                            <div class="form-group mb0 mt10 recoveryConfigShowDiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_DB_RECOVERY_RECOVERY_CONFIG'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static recoveryConfigShow wp-100">
                                                    </p>
                                                </div>
                                            </div>
                                            <!-- Oracle - 新建实例恢复 -->
                                            <div class="form-group mb0 mt10 createNewInstanceConfigShowDiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_DB_NEW_INSTANCE_CONFIG'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static  createNewInstanceConfigShow wp-100">
                                                    </p>
                                                </div>
                                            </div>
                                            <!-- Oracle-恢复内容 -->
                                            <div class="form-group " id="oracleRecoveryContentShowDiv">
                                                <label class="control-label col-md-3">
                                                    <?php echo $LANG['UI_DB_ORACLE_RECOVERY_CONTENT'] ?>:
                                                </label>
                                                <div class="col-md-9">
                                                    <ul class="ztree" id="oracleRecoveryContentShow"></ul>
                                                </div>
                                            </div>
                                            <!-- 自定义配置文件 -->
                                            <div class="form-group " id="pfileShowDiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_DB_RECOVERY_PFILE'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static pfileShow">
                                                    </p>
                                                </div>
                                            </div><!--//END pfileShowDiv-->
                                            <div class="form-group " id="listenerShowDiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_DB_RECOVERY_LISTENER'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static listenerShow"></p>
                                                </div>
                                            </div>
                                            <div class="form-group " id="tnsnamesShowDiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_DB_RECOVERY_TNSNAMES'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static tnsnamesShow"></p>
                                                </div>
                                            </div>
                                            <div class="form-group " id="sqlnetShowDiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_DB_RECOVERY_SQLNET'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static sqlnetShow"></p>
                                                </div>
                                            </div><!--//END sqlnetShowDiv-->
                                            <!-- 时间策略 -->
                                            <div class="form-group mb0 mt10 timeStrategyShowDiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_STRATEGY_TIME'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static  timeStrategyShow">
                                                    </p>
                                                </div>
                                            </div>
                                            <!-- 限速策略 -->
                                            <div class="form-group mb0 mt10" id="speedstrategyshowdiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static  speedlimitshow">
                                                    </p>
                                                </div>
                                            </div>
                                            <!-- 传输策略 -->
                                            <div class="form-group mb0 mt10 display-none transferShowDiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static  transferShow">
                                                    </p>
                                                </div>
                                            </div>
                                            <!-- 脚本配置 -->
                                            <div class="form-group mb0 mt10" id="scriptConfigShowDiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_PUBLIC_SCRIPT_CONFIGURE'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static scriptConfigShow"></p>
                                                </div>
                                            </div>
                                            <!-- 安全策略 -->
                                            <div class="form-group mb0 mt10" id="safeStrategyShowDiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static safeStrategyShow"></p>
                                                </div>
                                            </div><!-- //END safeStrategyShowDiv -->
                                            <!-- 数据库配置 -->
                                            <div class="form-group mb0 mt10 advancedCategoryShow advancedDatabaseConfigShowDiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_DB_RECOVERY_ADVANCED_TAB_DATABASE_CONFIG'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static advancedDatabaseConfigShow">
                                                    </p>
                                                </div>
                                            </div><!--//END advancedDatabaseConfigShowDiv-->
                                            <!-- 重试策略 -->
                                            <div class="form-group mb0 mt10 advancedCategoryShow retryShow">
                                            </div><!--//END retryShow-->
                                            <!-- 过载保护 -->
                                            <div class="form-group mb0 mt10 advancedCategoryShow advancedOverloadShowDiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_STRATEGY_OVER_LOAD_LABEL'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static advancedOverloadShow">
                                                    </p>
                                                </div>
                                            </div><!--//END advancedOverloadShowDiv-->
                                        </div>
                                    </div>
                                </div><!--//END tab4-->
                            </div>
                        </div>
                        <div class="form-actions form-actions--create">
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
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title"><i
                            class="iconfont icon-xiansucelve"></i> <?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="portlet-body">

                    <div class="form-group ">
                        <label class="control-label col-md-2"
                               for="speedModeType"> <?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_TYPE'] ?>
                        </label>
                        <div class="col-md-6 flex-items-center">
                            <select class="form-control select2me inline-block" id="speedModeType">
                                <option value="1"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_STRATEGY'] ?></option>
                                <option value="2"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_FOREVER'] ?></option>
                            </select>
                            <a class="popovers ml12" data-container="body" data-trigger="hover" data-placement="right"
                               data-content="<?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_TYPE_TIPS'] ?>">
                                <i class="viconfont vicon-tishi"></i>
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
                        <div class="col-md-6">
                            <div id="rateDiv">
                                <div class="input-icon">
                                    <div id="speedSpinnerNum">
                                        <div class="input-group spinner-group">
                                            <input id="speedSpinnerNumInput"
                                                   onkeyup="value=value.replace(/[^\d]/g,'')"
                                                   class="spinner-input form-control">
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
                                    <select id="unit">
                                        <option value="1">KB/s</option>
                                        <option selected value="2">MB/s</option>
                                        <option value="3">GB/s</option>
                                    </select>
                                </div>
                                <a class="popovers ml12" data-container="body" data-trigger="hover"
                                   data-placement="right"
                                   data-content="<?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_VALUE_TIPS'] ?>">
                                    <i class="viconfont vicon-tishi"></i>
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

        <!-- BEGIN BATCH CONFIG MODAL --><!-- 批量配置弹窗 -->
        <div id="batchConfigModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first"
             data-backdrop="static">
            <div class="modal-header ">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title">
                    <i class="viconfont vicon-gaojipeizhi"></i>
                    <?php echo $LANG['UI_DB_BATCH_CONFIG'] ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="portlet-body">
                    <div class="row">
                        <!-- 数据加密密码 -->
                        <div class="form-group display-hide batchEncryptDiv">
                            <label class="control-label col-md-3">
                                <?php echo $LANG['UI_VM_DATABASE_ENCR_PWD'] ?>
                            </label>
                            <div class="col-md-6">
                                <input type="password" autocomplete="off" class="form-control" id="batchEncryptPassword"
                                       maxlength="256"
                                       oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')">
                            </div>
                        </div>

                        <!-- 回滚时间开关 -->
                        <div class="form-group batchLogTimeDiv display-hide">
                            <label class="control-label col-md-3 form-group-label">
                                <?php echo $LANG['UI_DB_ROLL_TIME'] ?>
                            </label>
                            <div class="col-md-6 form-group-content flex-items-center">
                                <input type="checkbox" id="batchLogTimeCheck" class="make-switch"
                                       data-on-color="primary" data-off-color="info" data-size="small"
                                       data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                <a class="popovers ml12 mb-5" data-container="body" data-trigger="hover" data-html="true"
                                   data-placement="right"
                                   data-content="<?php echo $LANG['UI_DB_INPUT_CORRECT_ROLL_TIME_AFTER_OPEN'] ?>">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                            </div>
                        </div>
                        <!-- 回滚时间 -->
                        <div class="form-group batchLogTimeInputDiv display-hide">
                            <label class="control-label col-md-3">
                                <?php echo $LANG['UI_DB_SELECT_ROLL_TIME'] ?>
                            </label>
                            <div class="col-md-6">
                                <div class="input-group date form_datetime batchFormDatetime">
                                    <input type="text" size="20" id="batchLogTimeInput" class="form-control"
                                           style="width: 300px">
                                    <span class="input-group-btn">
                                        <button class="btn default" id="batchResetTime" type="button"><i
                                                    class="fa fa-times"></i></button>
                                        <button class="btn default date-set" type="button"><i
                                                    class="viconfont vicon-ge_calendar"></i></button>
                                    </span>
                                </div>
                                <span class="help-block " id="batchLogTimeTips"></span>
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
                        id="batchConfigSubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
            </div>
        </div>
        <!-- END BATCH CONFIG MODAL -->

        <!-- BEGIN CUSTOM CONFIG FILE MODAL -->
        <div id="customConfigFileModal" class="modal xmodal fade form-horizontal" tabindex="-1"
             data-focus-on="input:first" data-backdrop="static" data-eventtype="pfile">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
                <div class="portlet-body">
                    <div class="form-group custom-config-modal__folder">
                        <label class="col-md-12 control-label" for="custom-config-modal__path"></label>
                        <div class="col-md-12">
                            <div id="custom-config-modal__path"></div>
                        </div>
                    </div>
                    <div class="form-group custom-config-modal__content">
                        <label class="col-md-12 control-label" for="custom-config-modal__content"></label>
                        <div class="col-md-12">
                            <textarea class="form-control" spellcheck="false"
                                      id="custom-config-modal__content" rows="22"></textarea>
                            <!--                            <div class="form-control " id="custom-config-modal__content"></div>-->
                        </div>
                    </div>
                    <div class="form-group custom-config-modal__password">
                        <label class="col-md-12 control-label" for="custom-config-modal__password">
                            <?php echo $LANG['UI_DB_RECOVERY_SYS_PASSWORD'] ?>
                        </label>
                        <div class="col-md-12">
                            <div class="col-md-6 pl0">
                                <i class="sys-password-icon fa fa-eye"></i>
                                <i class="sys-password-icon fa fa-eye-slash"></i>
                                <input type="password" class="form-control" id="sysPassword" autocomplete="off"
                                       maxlength="256"
                                       oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')"/>
                                <span class="help-block">
                                    <?php echo $LANG['UI_DB_RECOVERY_SYS_PASSWORD_TIPS2'] ?>
                                </span>
                                <a class="popovers popover-abs-position" data-container="body" data-trigger="hover" data-html="true"
                                   data-placement="right"
                                   data-content="<?php echo $LANG['UI_DB_RECOVERY_SYS_PASSWORD_TIPS'] ?>">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- modal-footer -->
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-default">
                    <?php echo $LANG['UI_PUBLIC_NO'] ?>
                </button>
                <button type="button" class="btn btn-primary" id="customConfigFileSubmit">
                    <?php echo $LANG['UI_PUBLIC_YES'] ?>
                </button>
            </div>
        </div>
        <!-- END CUSTOM CONFIG FILE MODAL -->
    </div>
</div>
<!-- END PAGE CONTENT-->

<!-- BEGIN CREATE NEW INSTANCE CONFIG MODAL -->
<div id="createNewInstanceConfigModal" class="modal xmodal fade form-horizontal" tabindex="-1"
     data-focus-on="input:first" data-backdrop="static" data-instance-name="" data-oracle-home="" data-oracle-base="">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"></button>
        <h4 class="modal-title">
            <i class="viconfont vicon-shili1"></i>
            <span class="text"><?php echo $LANG['UI_DB_NEW_INSTANCE_CONFIG'] ?></span>
        </h4>
    </div>
    <div class="modal-body">
        <div class="portlet-body">
            <form action="#" class="form-horizontal" method="POST">
                <div class="form-wizard">
                    <div class="form-body">
                        <ul class="nav nav-pills steps">
                            <li>
                                <a href="#createNewInstanceTab1" data-toggle="tab" class="step">
                                    <span class="number">1</span>
                                    <span class="desc">
                                        <?php echo $LANG['UI_DB_NEW_INSTANCE_CONFIG_TAB_INSTANCE_CONFIG'] ?>
                                        <i class="fa fa-check"></i>
                                    </span>
                                </a>
                            </li>
                            <li>
                                <a href="#createNewInstanceTab2" data-toggle="tab" class="step">
                                    <span class="number">2</span>
                                    <span class="desc">
                                        <?php echo $LANG['UI_DB_NEW_INSTANCE_CONFIG_TAB_SPFILE_CONFIG'] ?>
                                        <i class="fa fa-check"></i>
                                    </span>
                                </a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="createNewInstanceTab1">
                                <div class="row">
                                    <div class="col-md-12">
                                        <!-- 实例名 -->
                                        <div class="form-group">
                                            <label class="col-md-3 control-label newInstanceNameLabel"><?php echo $LANG['UI_DB_NEW_INSTANCE_CONFIG_NAME'] ?></label>
                                            <div class="col-md-8">
                                                <input type="text" class="form-control" id="newInstanceName"
                                                       maxlength="12"
                                                       oninput="value=value.replace(/[^0-9a-zA-Z]/g, '')"/>
                                                <div class="help-block" id="newInstanceNameHelpBlock"></div>
                                            </div>
                                        </div>
                                        <!-- sys用户密码 -->
                                        <div class="form-group">
                                            <label class="col-md-3 control-label newInstanceSysPasswordLabel"><?php echo $LANG['UI_DB_NEW_INSTANCE_CONFIG_PASSWORD'] ?></label>
                                            <div class="col-md-8">
                                                <input type="password" autocomplete="off" maxlength="128"
                                                       class="form-control" id="newInstanceSysPassword"
                                                       oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')"/>
                                                <button type="button" class="btn btn-link show-password-btn" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); text-decoration: none;">
                                                    <i class="viconfont vicon-a-lujing8232"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <!-- 再次输入密码 -->
                                        <div class="form-group">
                                            <label class="col-md-3 control-label newInstanceReSysPasswordLabel"><?php echo $LANG['UI_DB_NEW_INSTANCE_CONFIG_RE_PASSWORD'] ?></label>
                                            <div class="col-md-8">
                                                <input type="password" autocomplete="off" maxlength="128"
                                                       class="form-control" id="newInstanceReSysPassword"
                                                       oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')"/>
                                                <button type="button" class="btn btn-link show-password-btn" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); text-decoration: none;">
                                                    <i class="viconfont vicon-a-lujing8232"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <!-- 系统用户 -->
                                        <div class="form-group display-none">
                                            <label class="col-md-3 control-label newInstanceSystemUserLabel"><?php echo $LANG['UI_DB_NEW_INSTANCE_CONFIG_SYSTEM_USER'] ?></label>
                                            <div class="col-md-8">
                                                <input type="text" class="form-control" id="newInstanceSystemUser"
                                                       readonly
                                                       oninput="value=value.replace(/\s+/g,'')"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="createNewInstanceTab2">
                                <div class="row">
                                    <div class="col-md-12" id="spfileConfigWrapper"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- modal-footer -->
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
        <button type="button" class="btn default button-previous"><?php echo $LANG['UI_PUBLIC_PREV_STEP'] ?></button>
        <button type="button"
                class="btn green-turquoise button-next "><?php echo $LANG['UI_PUBLIC_NEXT_STEP'] ?></button>
        <button type="button" class="btn green-haze button-submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>
<!-- END CREATE NEW INSTANCE CONFIG MODAL -->

<!-- BEGIN ORACLE RECOVERY TIME MODAL -->
<div id="oracleRecoveryTimeModal" class="modal xmodal fade form-horizontal" tabindex="-1"
     data-focus-on="input:first" data-backdrop="static" data-instance-name="" data-oracle-home="" data-oracle-base="">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"></button>
        <h4 class="modal-title">
            <i class="viconfont vicon-xiugai"></i>
            <span class="text"><?php echo $LANG['UI_DB_RECOVERY_ORACLE_RECOVERY_TIME_SELECT'] ?></span>
        </h4>
    </div>

    <div class="modal-body">
        <div class="portlet-body">
            <!-- Oracle-存储离线提示框 -->
            <div class="form-group display-hide oracleTimepointStorageOfflineDiv">
                <div class="col-md-12">
                    <div class="alert alert-block alert-danger fade in mb-0 mt-0">
                        <i class="fa fa-warning"></i>
                        <span>
                            <?php echo $LANG['UI_DB_RECOVERY_ORACLE_TIMEPOINT_STORAGE_IS_OFFLINE']; ?>
                        </span>
                    </div>
                </div>
            </div><!-- //END oracleTimepointStorageOfflineDiv -->

            <!-- Oracle-不可恢复范围 -->
            <div class="form-group display-hide oracleTimepointRangeNonsupportDiv">
                <div class="col-md-12">
                    <div class="alert alert-block alert-danger fade in mb-0 mt-0">
                        <i class="fa fa-warning"></i>
                        <span>
                            <?php echo $LANG['UI_DB_RECOVERY_ORACLE_TIMEPOINT_RANGE_NONSUPPORT']; ?>
                        </span>
                    </div>
                </div>
            </div><!-- //END oracleTimepointRangeNonsupportDiv -->

            <!-- Oracle-恢复时间 -->
            <div class="form-group oracleRecoveryTimeSelectDiv">
                <div class="col-md-12">
                    <div class="oracleRecoveryTimeSelectWrapper">
                        <div class="select-item">
                            <div class="minTime"></div>
                            <div class="maxTime"></div>
                        </div>
                        <div class="select-item">
                            <div id="oracleRecoveryTimeSelectRange"></div>
                        </div>
                        <div class="select-item" id="oracleRecoveryTimeSelectPicker">
                            <label class="control-label"
                                   for="oracleRecoveryTimeSelectTime"><?php echo $LANG['UI_DB_RECOVERY_ORACLE_RECOVERY_TIME_SELECT'] ?>
                                :</label>
                            <input type="text" size="20" id="oracleRecoveryTimeSelectTime" class="form-control">
                            <!--                            <button type="button" class="btn btn-light-primary" id="confirmOracleRecoveryTimeSelectTime">-->
                            <?php //echo $LANG['UI_PUBLIC_CONFIRM']; ?><!--</button>-->
                        </div>
                    </div>
                </div>
            </div><!-- //END oracleRecoveryTimeSelectDiv -->
        </div>
    </div>

    <!-- modal-footer -->
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default">
            <?php echo $LANG['UI_PUBLIC_NO'] ?>
        </button>
        <button type="button" class="btn btn-primary">
            <?php echo $LANG['UI_PUBLIC_YES'] ?>
        </button>
    </div>
</div>
<!-- END ORACLE RECOVERY TIME MODAL -->


<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/platform/global_strategy/speed_strategy.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript"
        src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript"
        src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript"
        src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/ace/src-min-noconflict/ace.js"></script>
<script type="text/javascript" src="./scripts/public/initComponents.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.vmConfig.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.vmConfigValidate.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.speedStrategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.dbStrategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/calendar/jquery.searchableSelect.js"></script>
<script type="text/javascript" src="./scripts/platform/component/vinScript.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/backup-strategy.js"></script>
<!--<script type="text/javascript" src="./scripts/plugins/highlight/highlight.min.js"></script>-->
<!--<script type="text/javascript" src="./scripts/plugins/highlight/code-highlight.js"></script>-->
<!-- <script src="./scripts/plugins/bootstrap-datetimepicker.js" type="text/javascript"></script> -->
<?php
if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
    echo '<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>';
}
?>

<!-- END PAGE LEVEL PLUGINS -->
<script src="./assets/global/plugins/clipboard/clipboard.min.js" type="text/javascript"></script>
<script src="./scripts/backupData/clipboard.js" type="text/javascript"></script>
<script src="./scripts/components/timePointDetail/js/timePointDetail.js" type="text/javascript"></script>
<script src="./scripts/components/timePointDetail/js/virusHistoryTable.js" type="text/javascript"></script>
<script src="./scripts/plugins/path-tree-selector.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/platform/component/retry_strategy.js"></script>
<script type="text/javascript" src="./scripts/platform/component/transfer_network.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_complete.js"></script>
<script src="./scripts/dbprotect/dbrecover.js" type="text/javascript"></script>
