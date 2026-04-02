        <?php include_once '../../tpl/permission.php'; ?>
        <?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
        <link rel="stylesheet" type="text/css" href="./scripts/plugins/ztree/css/metroStyle/metroStyle.css"/>
        <link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
        <link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
        <link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>
        <link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css"/>
        <link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css"/>
        <link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
        <link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
        <link rel="stylesheet" type="text/css" href="./scripts/plugins/calendar/jquery.searchableSelect.css"/>
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
            <span class="curent"><?php echo $LANG['UI_JOB_TYPE_CDP_DB_RECOVERY'] ?></span>
        </h3>
        <!-- END PAGE HEADER-->
        <!-- BEGIN PAGE CONTENT-->
        <div class="row recover-page">
            <div class="col-md-12" style="height:100%;">
                <div class="portlet box blue-hoki" id="dbcdprecovercontent">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="levelchild viconfont vicon-xinjianbeifen1"></i><?php echo $LANG['UI_DB_CDP_CREATE_RECOVER_JOB']; ?>
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
                                            <span class="number">
                                            1 </span>
                                                    <span class="desc">
                                             <?php echo $LANG['UI_BR_RECOVERY_DATA_SOURCE']; ?> <i class="fa fa-check"></i></span>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="#tab2" data-toggle="tab" class="step">
                                            <span class="number">
                                            2 </span>
                                                    <span class="desc">
                                             <?php echo $LANG['UI_VOL_CDP_RECOVER_TARGET_VOL']; ?> <i class="fa fa-check"></i></span>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="#tab3" data-toggle="tab" class="step active">
                                            <span class="number">
                                            3 </span>
                                                    <span class="desc">
                                             <?php echo $LANG['UI_RECOVERY_STRATEGY']; ?> <i class="fa fa-check"></i></span>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="#tab4" data-toggle="tab" class="step">
                                            <span class="number">
                                            4 </span>
                                                    <span class="desc">
                                             <?php echo $LANG['UI_BACKUP_CONFIRM_CONFIG'] ?><i class="fa fa-check"></i></span>
                                                </a>
                                            </li>
                                        </ul>
                                        <div class="tab-content bakuptab">
                                            <div class="tab-pane" id="tab1">
                                                <div class="row tab-pane__row ">
                                                    <div class="col-md-4 tab-pane__row__source">
                                                        <div class="form-group src-wrap">
                                                            <div class="src-wrap__title">
                                                                <i class="levelchild viconfont vicon-ge_backup_host"></i><?php echo $LANG['UI_DB_CDP_RECOVER_BACKUP_HOST_SELECT']; ?>
                                                            </div>
                                                            <div class="src-wrap__content">
                                                                <div class="src-wrap__content__search">
                                                                    <select id="dbtype" class="bs-select mb10 form-control" style="height: 34px" data-show-subtext="true">
                                                                    </select>
                                                                </div>
                                                                <div class="src-wrap__content__ztree">
                                                                    <div class="vcenter-tree">
                                                                        <ul id="agent_tree" class="ztree ztree-fa tree-scroll"></ul>
                                                                    </div>
                                                                    <div id="noagent" class="alert alert-block alert-info fade in display-hide">
                                                                        <button type="button" class="close" data-dismiss="alert"></button>
                                                                        <h4 class="alert-heading">
                                                                            <strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong>
                                                                        </h4>
                                                                        <ol class="alert-ol">
                                                                            <li>
                                                                                <?php echo $LANG['UI_DB_CDP_RECOVER_NO_HOST_TIPS']; ?>
                                                                            </li>
                                                                            <li>
                                                                                <?php echo $LANG['UI_DB_CDP_RECOVER_PROCEED_FIRST']; ?>
                                                                                <a id="tobackup" class="alert-link"><?php echo $LANG['UI_DB_CDP_CREATE_BACKUP_JOB']; ?></a>
                                                                            </li>
                                                                        </ol>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-8 notipsDiv">
                                                        <div class="alert alert-block alert-info fade in" id="step1tips">
                                                            <button type="button" class="close" data-dismiss="alert"></button>
                                                            <ul class="alert-ul">
                                                                <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong>
                                                                <li>
                                                                    <?php echo $LANG['UI_DB_CDP_RECOVER_ASSOCIATE_HOST_TIPS']; ?>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div class="timepointContent display-none">
                                                        <div id="dbtreediv" class="col-md-8">
                                                            <span class="addTitle">
                                                                <span><i class="levelchild viconfont vicon-beifen"></i><?php echo $LANG['UI_VOL_CDP_RECOVER_SELECTED_RECOVERY_TIME_POINT']; ?></span>
                                                            </span>
                                                            <div id="timePointDetail" class="addIt-list " style="height: 601px; padding: 12px 14px 0px 14px;overflow-y: scroll;overflow-x: hidden;">
                                                                <div class="form-group col-md-12 pt7" style="height: 34px;background:#E7F7F3;font-size: 14px;">
                                                                    <span id="standbyInfo" style="color: #999999"><?php echo $LANG['UI_VOL_CDP_RECOVER_RELATED_HOST_INFORMATION']; ?></span>
                                                                    <span id="hostInfo" style="color: #333333"></span>
                                                                </div>
                                                                <div class="form-group">
                                                                    <div class="col-md-12 accordion pd0">
                                                                        <div class="panel panel-default strategy-panel">

                                                                            <div class="panel-heading">
                                                                                <h4 class="panel-title">
                                                                                    <a class="accordion-toggle accordion-toggle-styled collapsed popovers"
                                                                                       data-container="body"
                                                                                       data-trigger="hover" data-placement="top"
                                                                                       data-toggle="collapse" href="#hostDetail"
                                                                                       aria-expanded="true">
                                                                                        <i class="viconfont vicon-shijian font-green-seagreen"></i>
                                                                                        <span class="font-green-seagreen"><?php echo $LANG['UI_JOB_TIME_RANGE']; ?></span>
                                                                                        <span id="timeRange" class="fs12" style="color:#999999;"></span>
                                                                                        (<span class="font-green-seagreen">scn:</span>
                                                                                        <span id="replay_scn"></span>
                                                                                        -
                                                                                        <span id="transaction_scn"></span>)
                                                                                    </a>
                                                                                </h4>
                                                                            </div>

                                                                            <div id="hostDetail" class="panel-collapse collapse in">
                                                                                <div class="panel-body min-height300">
                                                                                    <div class="col-md-12 tabbable-custom pl15 pr15">
                                                                                        <ul class="nav nav-tabs " id="timepointType">
                                                                                            <li class="commonLi active" id="recoveryAnytimeLi">
                                                                                                <a href="#anyPointTime" class="popovers" data-content="" data-container="" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                                                                    <i class="viconfont vicon-renyishijiandian"></i>
                                                                                                    <?php echo $LANG['UI_VOL_CDP_ANY_TIME_POINT']; ?>
                                                                                                </a>
                                                                                            </li>
                                                                                            <li class="highLi" id="agentTransactionInfoLi">
                                                                                                <a href="#transactionInfo" class="popovers" data-content="" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                                                                    <i class="viconfont vicon-shijian1"></i>
                                                                                                    <?php echo $LANG['UI_DB_CDP_DATA_DISASTER_RECOVERY_TRANSACTION_INFORMATION']; ?>
                                                                                                </a>
                                                                                            </li>
                                                                                        </ul>
                                                                                        <div class="tab-content min-height360" style="height: 90%;overflow-x:hidden;overflow-y: auto;">
                                                                                            <div class="tab-pane active " id="anyPointTime">
                                                                                                <div class="form-group" style="position: relative;z-index: 10;">
                                                                                                    <div class="col-md-2 pl30 pt10"><?php echo $LANG['UI_PLATFORM_THIRD_PUSH_TIME_TYPE']; ?></div>
                                                                                                    <div class="col-md-4">
                                                                                                        <select class="volcdp-time-range-select form-control select2me" id="changeTimeInterval">
                                                                                                            <option value="1" selected><?php echo $LANG['UI_VOL_CDP_RECENT_TENMINS']; ?></option>
                                                                                                            <option value="2"><?php echo $LANG['UI_VOL_CDP_RECENT_ONEHOUR']; ?></option>
                                                                                                            <option value="3"><?php echo $LANG['UI_DB_CDP_DATA_DISASTER_RECOVERY_RECENT_ONEDAY']; ?></option>
                                                                                                            <option value="4"><?php echo $LANG['UI_REPORT_RECENT_WEEK']; ?></option>
                                                                                                        </select>
                                                                                                    </div>
                                                                                                </div>
                                                                                                <div id="RecoveryTimeline" style="width: 880px;height:300px;"></div>
                                                                                            </div>
                                                                                            <div class="tab-pane"  id="transactionInfo">
                                                                                                <div class="row">
                                                                                                    <div class="col-md-12">
                                                                                                        <div class="alert alert-block alert-info fade in mb10">
                                                                                                            <button type="button" class="close" data-dismiss="alert"></button>
                                                                                                            <ul class="alert-ul">
                                                                                                                <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                                                                                                                <li><?php echo $LANG['UI_DB_CDP_DATA_DISASTER_RECOVERY_TRANSACTION_INFORMATION_TIP'];?></li>
                                                                                                            </ul>
                                                                                                        </div>
                                                                                                        <div class="portlet-body">
                                                                                                        </div>
                                                                                                        <table id="transactionInfoTable">
                                                                                                        </table>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-md-12 mt10 pd0">
                                                                                            <label class="floatl pt-0" style="margin: 5px 53px 0 0;"><?php echo $LANG['UI_RECOVERY_TYPE']; ?></label>
                                                                                            <div class="col-md-3">
                                                                                                <select id="recovery_time_type" class="recoveryTimeType form-control select2me">
                                                                                                </select>
                                                                                            </div>
                                                                                            <div class="col-md-2 mt5">
                                                                                                <a class="popovers" data-container="body" data-trigger="hover" data-placement="right" data-html="true" data-content="<?php echo $LANG['UI_DB_CDP_RECOVER_TIME_POINT_TYPE_TIPS'] ?>">
                                                                                                    <i class="viconfont vicon-tishi"></i>
                                                                                                </a>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-md-12 mt10 pd0 inputRecoveryTimeDIv display-none">
                                                                                            <label class="floatl pt-0" style="margin: 5px 39px 0 0;"><?php echo $LANG['UI_DB_RECOVERY_TIMEPOINT']; ?></label>
                                                                                            <div class="col-md-3">
                                                                                                <input type="text" class="inputRecoveryTimeInput form-control input-sm" readonly>
                                                                                            </div>
                                                                                            <div class="col-md-2 mt5">
                                                                                                <a class="popovers" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php echo $LANG['UI_DB_CDP_RECOVER_TIMEPOINT_TIPS']; ?>">
                                                                                                    <i class="viconfont vicon-tishi"></i>
                                                                                                </a>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-md-12 mt10 pd0 recoveryTimeDiv display-none">
                                                                                            <label class="floatl pt-0" style="margin: 5px 37px 0 0;"><?php echo $LANG['UI_DB_RECOVERY_TIMEPOINT']; ?></label>
                                                                                            <div class="col-md-3">
                                                                                                <div class="recoverytimepointview input-group date form_datetime">
                                                                                                    <input id="inputRecoveryTimepoint" class="form-control" type="text" size="16">
                                                                                                    <span class="input-group-btn">
                                                                                                        <button class="btn default date-set" type="button"><i class="viconfont vicon-ge_calendar"></i></button>
                                                                                                    </span>
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="col-md-2 mt5">
                                                                                                <a class="popovers" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php echo $LANG['UI_DB_CDP_RECOVER_TIMEPOINT_TIPS']; ?>">
                                                                                                    <i class="viconfont vicon-tishi"></i>
                                                                                                </a>
                                                                                            </div>
                                                                                            <div id="timePointValidity" class="control-label col-md-1 pl0"><?php echo $LANG['UI_DB_CDP_BACKUP_VALID_TIME_POINT_TYPE']; ?></div>
                                                                                        </div>
                                                                                        <div class="col-md-12 mt10 pd0 takeoverScnDIv display-none">
                                                                                            <label class="floatl pt-0" style="margin: 5px 57px 0 0;"><?php echo $LANG['UI_DB_CDP_RECOVERY_SCN'];?></label>
                                                                                            <div class="col-md-3 mt5">
                                                                                                <input type="number" class="form-control input-sm takeoverScn">
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
                                            <div class="tab-pane" id="tab2">
                                                <div class="row vol-cdp-backup-tab">
                                                    <div class="col-md-9">
                                                        <div class="form-group">
                                                            <div class="col-md-12" id="backupTarget"></div>
                                                        </div>
                                                        <div class="form-group nodediv">
                                                            <label class="control-label col-md-3"><?php echo $LANG['UI_DB_CDP_RECOVER_TARGET']; ?></label>
                                                            <div class="col-md-9">
                                                                <ul id="target_agent_tree" class="ztree"></ul>
                                                                <span class = "help-block"><?php echo $LANG['UI_DB_CDP_RECOVERY_USER_TIPS'];?></span>
                                                            </div>
                                                        </div>
                                                        <div class="form-group standByTips">
                                                            <div class="col-md-3"></div>
                                                            <div class="col-md-9">
                                                                <div class="alert alert-info">
                                                                    <button type="button" class="close"
                                                                            data-dismiss="alert"></button>
                                                                    <h4 class="alert-heading">
                                                                        <strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong>
                                                                    </h4>
                                                                    <ol class="alert-ol">
                                                                        <li><?php echo $LANG['UI_DB_CDP_RECOVER_APP_SELECT_TIP1']; ?></li>
                                                                        <li>
                                                                            <?php echo $LANG['UI_DB_CDP_RECOVER_APP_SELECT_TIP2']; ?>
                                                                        </li>
                                                                    </ol>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group dbMapDetaildiv">
                                                            <label class="control-label col-md-3"><?php echo $LANG['UI_DB_CDP_BACKUP_DATABASE_MAPPING_DETAILS'];?></label>
                                                            <div id="instanceMapList" class="col-md-9">
                                                            </div>
                                                        </div>
                                                        <div class="synNodediv">
                                                            <label class="control-label col-md-3"><?php echo $LANG['UI_DB_CDP_RECOVERY_DATA_NODE'];?></label>
                                                            <div id="synNode" class="col-md-9"></div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="control-label col-md-3"><?php echo $LANG['UI_DB_CDP_BACKUP_DATA_TABLE_SPACE_STORAGE_DIRECTORY'];?></label>
                                                            <div class="col-md-4">
                                                                <select id="tablespaceDir" class="form-control select2me" type="text">
                                                                    <option value=""><?php echo $LANG['UI_DB_CDP_BACKUP_SYSTEM_TABLE_SPACE_DIRECTORY'];?></option>
                                                                    <option value="1"><?php echo $LANG['UI_PLATFORM_SETTING_TEMPLATE3'];?></option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-2 mt8">
                                                                <a class="popovers ml15" data-container="body"
                                                                   data-trigger="hover"
                                                                   data-placement="right" data-content="<?php echo $LANG['UI_DB_CDP_RECOVERY_TABLE_SPACE_DIR_TIPS'];?>">
                                                                    <i class="viconfont vicon-tishi"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                        <div class="form-group customDirectory_div display-none">
                                                            <label class="col-md-3"></label>
                                                            <div class="col-md-4">
                                                                <input id="customDirectory" class="form-control select2me input-sm" type="text">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane" id="tab3" style="height: 95%;">
                                                <div class="row row-stepthree" style="height: 100%;">
                                                    <div class="nav-tabs-wrapper pdlr15 col-md-offset-1 col-md-10"
                                                         style="height: 100%;">
                                                        <ul class="nav nav-tabs nav-line-tabs">
                                                            <li class="commonLi active nav-item">
                                                                <a href="#recoveryAnyTime" class="popovers nav-link"
                                                                   data-content=""
                                                                   data-container="body" data-trigger="hover"
                                                                   data-placement="top"
                                                                   data-toggle="tab">
                                                                    <i class="viconfont vicon-a-chuanshu"></i> <?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?>
                                                                </a>
                                                            </li>
                                                            <li class="highLi nav-item">
                                                                <a href="#tab_high" class="popovers nav-link"
                                                                   data-content=""
                                                                   data-container="body" data-trigger="hover"
                                                                   data-placement="top"
                                                                   data-toggle="tab">
                                                                    <i class="viconfont vicon-gaojipeizhi"></i> <?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?>
                                                                </a>
                                                            </li>
                                                        </ul>
                                                        <div class="tab-content hover-scroll-y">
                                                            <div class="tab-pane" id="tab_common">
                                                                <div class="panel-body">
                                                                    <div class="form-group">
                                                                        <div class="col-md-offset-1 col-md-10 accordion strategyOne">
                                                                            <div class="panel panel-default strategy-panel">
                                                                                <div id="backupTime"
                                                                                     class="panel-collapse collapse in">
                                                                                    <div class="panel-body">
                                                                                        <div class="col-md-12">

                                                                                            <div class="form-group setStrategy">
                                                                                                <div class="col-md-10">
                                                                                                </div>


                                                                                                <div class="col-md-offset-2 col-md-10">
                                                                                                    <div class="portlet">
                                                                                                        <div class="portlet-body">
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>

                                                                                            <div class="form-group display-hide setOnceTime">
                                                                                                <div class="col-md-6">
                                                                                                </div>
                                                                                                <div class="col-md-2 mt5">
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                    </div>
                                                                    <div class="form-group speedlimitDiv">
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <div class="col-md-offset-1 col-md-10 accordion strategyOne">
                                                                            <div class="panel panel-default strategy-panel">
                                                                                <div id="store"
                                                                                     class="panel-collapse collapse ">
                                                                                    <div class="panel-body">
                                                                                        <div class="col-md-8">
                                                                                            <div class="form-group">
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                    </div>
                                                                    <div class="form-group">
                                                                        <div class="col-md-offset-1 col-md-10 accordion strategyOne">
                                                                            <div class="panel panel-default strategy-panel">
                                                                            </div>
                                                                        </div>

                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="tab-pane active" id="recoveryAnyTime">
                                                                <div class="panel-body">
                                                                    <div class="tabbable-custom pdlr15  col-md-10">
                                                                        <div class="form-group transfernetworkDiv">
                                                                            <label class="control-label col-md-3 transfernetworklabel"><?php echo $LANG['UI_NODE_TRANSFER_NETWORK'] ?></label>
                                                                            <div class="col-md-4">
                                                                                <ul class="ztree" id="transferNetworkTree"></ul>
                                                                            </div>
                                                                            <div class="col-md-2 mt4">
                                                                                <a class="popovers ml15" data-container="body"
                                                                                   data-trigger="hover" data-html="true"
                                                                                   data-placement="right"
                                                                                   data-content="<?php echo $LANG['UI_NODE_TRANSFER_NETWORK_TIPS'] ?>">
                                                                                    <i class="viconfont vicon-tishi"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group encrypttransferdiv">
                                                                            <label class="control-label col-md-3 encrypttransferlabel form-group-label"><?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER']; ?></label>
                                                                            <div class="col-md-4 form-group-content">
                                                                                <input type="checkbox" id="encrypttransfer"
                                                                                       class="make-switch"
                                                                                       data-on-color="primary"
                                                                                       data-off-color="info" data-size="small"
                                                                                       data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                <a class="popovers ml15" data-container="body"
                                                                                   data-trigger="hover" data-html="true"
                                                                                   data-placement="right"
                                                                                   data-content="<?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER_TIPS'] ?>">
                                                                                    <i class="viconfont vicon-tishi"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group encrypttransfermethoddiv display-none">
                                                                            <label class="control-label col-md-3 encrypttransfermethodlabel form-group-label"><?php echo $LANG['UI_DB_CDP_BACKUP_ENCRYPTED_TRANSMISSION_METHOD'] ?></label>
                                                                            <div class="col-md-4 form-group-content">
                                                                                <select name="" id="encrypttransfermethodselect" class="form-control select2me input-sm">
                                                                                    <option value="1"><?php echo $LANG['UI_BACKUP_TRANSFER_ENCRYPT_RSA']; ?></option>
                                                                                    <option value="2"><?php echo $LANG['UI_BACKUP_TRANSFER_ENCRYPT_SM']; ?></option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group compresstransferdiv">
                                                                            <label class="control-label col-md-3 compresstransferlabel form-group-label"><?php echo $LANG['UI_DB_CDP_BACKUP_COMPRESSED_TRANSMISSION']; ?></label>
                                                                            <div class="col-md-4 form-group-content">
                                                                                <input type="checkbox" id="compresstransfer"
                                                                                       class="make-switch"
                                                                                       data-on-color="primary"
                                                                                       data-off-color="info" data-size="small"
                                                                                       data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                <a class="popovers ml15" data-container="body"
                                                                                   data-trigger="hover" data-html="true"
                                                                                   data-placement="right"
                                                                                   data-content="<?php echo $LANG['UI_VOL_CDP_TRANSMISSION_COMPRESSION_TIPS'] ?>">
                                                                                    <i class="viconfont vicon-tishi"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group compressgradediv display-none">
                                                                            <label class="control-label col-md-3 compresstransferlabel form-group-label form-group-label"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE']; ?></label>
                                                                            <div class="col-md-4 form-group-content">
                                                                                <select name="" id="compresstransferselect" class="form-control select2me input-sm">
                                                                                    <option value="1"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_FAST']; ?></option>
                                                                                    <option value="2"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_NORMAL']; ?></option>
                                                                                    <option value="3"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_BETTER']; ?></option>
                                                                                    <option value="4"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_BEST']; ?></option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label class="control-label col-md-3 transferthreadlabel"><?php echo $LANG['UI_DB_CDP_BACKUP_EXP_THREADS'];?></label>
                                                                            <div class="col-md-4 exp_thread_div">
                                                                                <div class="input-group spinner-group">
                                                                                    <input type="num" id="exp_thread"
                                                                                           onkeyup="value=value.replace(/[^\d]/g,'')"
                                                                                           class="spinner-input form-control input-sm">
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
                                                                            <div class="col-md-2 mt8">
                                                                                <a class="popovers ml15"
                                                                                   data-container="body"
                                                                                   data-trigger="hover"
                                                                                   data-placement="right"
                                                                                   data-content="<?php echo $LANG['UI_DB_CDP_BACKUP_EXP_THREADS_TIPS'];?>">
                                                                                    <i class="viconfont vicon-tishi"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label class="control-label col-md-3 transferthreadlabel"><?php echo $LANG['UI_DB_CDP_BACKUP_IMP_THREADS'];?></label>
                                                                            <div class="col-md-4 imp_thread_div">
                                                                                <div class="input-group spinner-group">
                                                                                    <input type="num" id="imp_thread"
                                                                                           onkeyup="value=value.replace(/[^\d]/g,'')"
                                                                                           class="spinner-input form-control input-sm">
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
                                                                            <div class="col-md-2 mt8">
                                                                                <a class="popovers ml15"
                                                                                   data-container="body"
                                                                                   data-trigger="hover"
                                                                                   data-placement="right"
                                                                                   data-content="<?php echo $LANG['UI_DB_CDP_BACKUP_IMP_THREADS_TIPS'];?>">
                                                                                    <i class="viconfont vicon-tishi"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label class="control-label col-md-3 transferPacketSizelabel">
                                                                                <?php echo $LANG['UI_DB_CDP_BACKUP_TRANSMISSION_RATE_LIMIT']; ?>
                                                                            </label>
                                                                            <div class="col-md-9">
                                                                                <div class="form-group speedlimitDiv">
                                                                                    <div class="accordion strategyOne">
                                                                                        <div class="panel panel-default strategy-panel">
                                                                                            <div class="panel-heading">
                                                                                                <h4 class="panel-title">
                                                                                                    <a class="accordion-toggle accordion-toggle-styled collapsed popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#speed" aria-expanded="true">
                                                                                                        <i class="viconfont vicon-xiansucelve1 font-green-seagreen"></i>
                                                                                                        <span class="font-green-seagreen"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?></span>
                                                                                                        <span class="strategyDes speedlimitDes"></span>
                                                                                                    </a>
                                                                                                </h4>
                                                                                            </div>
                                                                                            <?php include_once('../../content/platform/global_strategy/speedJob.php'); ?>
                                                                                            <!--       全局限速策略组合的组件配置-->
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="tab-pane " id="tab_high">
                                                                <div class="advance-config-wrap">
                                                                    <div class="advance-config-wrap__row row m0">
                                                                        <div class="col-md-2 col-sm-2 col-xs-2 nav-tab-radios">
                                                                            <ul class="nav nav-tabs tabs-left">
                                                                                <li class="data_recover active">
                                                                                    <a href="#tab_data_recover" data-toggle="tab" aria-expanded="false"><?php echo $LANG['UI_DB_CDP_RECOVERY_DATA_STRATEGY'];?></a>
                                                                                </li>
                                                                                <li class="cache-strategy">
                                                                                    <a href="#tab_cache_strategy" data-toggle="tab" aria-expanded="false"><?php echo $LANG['UI_CM_CDP_BACKUP_CACHE_CONF'];?></a>
                                                                                </li>
                                                                            </ul>
                                                                        </div>
                                                                        <div class="col-md-10 col-sm-10 col-xs-10 advance-config-wrap__row__content">
                                                                            <div class="tab-content">
                                                                                <!--数据恢复策略-->
                                                                                <div class="tab-pane active" id="tab_data_recover">
                                                                                    <div class="col-md-12 pt40">
                                                                                        <div class="form-group">
                                                                                            <label class="control-label col-md-4"><?php echo $LANG['UI_DB_CDP_BACKUP_USE_DBMS'];?></label>
                                                                                            <div class="col-md-3 form-group-content">
                                                                                                <input type="checkbox" id="use_dbms_Switch" class="make-switch"
                                                                                                       data-on-color="primary" data-off-color="info"
                                                                                                       data-size="small"
                                                                                                       data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                                <a class="popovers ml15" data-container="body"
                                                                                                   data-trigger="hover"
                                                                                                   data-placement="right" data-content="<?php echo $LANG['UI_DB_CDP_BACKUP_USE_DBMS_TIPS'];?>">
                                                                                                    <i class="viconfont vicon-tishi"></i>
                                                                                                </a>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="form-group">
                                                                                            <label class="control-label col-md-4"><?php echo $LANG['UI_DB_CDP_BACKUP_SYNC_BIG_OBJ'];?></label>
                                                                                            <div class="col-md-3 form-group-content">
                                                                                                <input type="checkbox" id="sync_big_object_Switch" class="make-switch"
                                                                                                       data-on-color="primary" data-off-color="info"
                                                                                                       data-size="small"
                                                                                                       data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                                <a class="popovers ml15" data-container="body"
                                                                                                   data-trigger="hover"
                                                                                                   data-placement="right" data-content="<?php echo $LANG['UI_DB_CDP_RECOVERY_SYNC_BIG_OBJ_TIPS'];?>">
                                                                                                    <i class="viconfont vicon-tishi"></i>
                                                                                                </a>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="form-group display-none">
                                                                                            <label class="control-label col-md-4"><?php echo $LANG['UI_DB_CDP_BACKUP_TIMED_GEN_TXN'];?></label>
                                                                                            <div class="col-md-3 form-group-content">
                                                                                                <input type="checkbox" id="timed_gen_txn_Switch" class="make-switch"
                                                                                                       data-on-color="primary" data-off-color="info"
                                                                                                       data-size="small"
                                                                                                       data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                                <a class="popovers ml15" data-container="body"
                                                                                                   data-trigger="hover"
                                                                                                   data-placement="right" data-content="">
                                                                                                    <i class="viconfont vicon-tishi"></i>
                                                                                                </a>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="form-group">
                                                                                            <label class="control-label col-md-4"><?php echo $LANG['UI_DB_CDP_BACKUP_REPLAY_DDL'];?></label>
                                                                                            <div class="col-md-3 form-group-content">
                                                                                                <input type="checkbox" id="replay_ddl_Switch" class="make-switch"
                                                                                                       data-on-color="primary" data-off-color="info"
                                                                                                       data-size="small"
                                                                                                       data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                                <a class="popovers ml15" data-container="body"
                                                                                                   data-trigger="hover"
                                                                                                   data-placement="right" data-content="<?php echo $LANG['UI_DB_CDP_BACKUP_REPLAY_DDL_TIPS'];?>">
                                                                                                    <i class="viconfont vicon-tishi"></i>
                                                                                                </a>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="form-group">
                                                                                            <label class="control-label col-md-4"><?php echo $LANG['UI_DB_CDP_BACKUP_IGNORE_NONSUPPORT_DATA_TYPE'];?></label>
                                                                                            <div class="col-md-3 form-group-content">
                                                                                                <input type="checkbox" id="ignore_nonsupport_data_type_Switch" class="make-switch"
                                                                                                       data-on-color="primary" data-off-color="info"
                                                                                                       data-size="small"
                                                                                                       data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                                <a class="popovers ml15" data-container="body"
                                                                                                   data-trigger="hover"
                                                                                                   data-placement="right" data-content="<?php echo $LANG['UI_DB_CDP_BACKUP_IGNORE_NONSUPPORT_DATA_TYPE_TIPS'];?>">
                                                                                                    <i class="viconfont vicon-tishi"></i>
                                                                                                </a>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-md-12">
                                                                                            <div class="alert alert-info">
                                                                                                <button type="button" class="close" data-dismiss="alert"></button>
                                                                                                <h4 class="alert-heading">
                                                                                                    <strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong>
                                                                                                </h4>
                                                                                                <ol class="alert-ol">
                                                                                                    <li><?php echo $LANG['UI_DB_CDP_BACKUP_IGNORE_NONSUPPORT_DATA_TYPE_TIP'];?></li>
                                                                                                </ol>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <!--缓存配置-->
                                                                                <div class="tab-pane" id="tab_cache_strategy">
                                                                                    <div class="col-md-12 pt40">
                                                                                        <div class="form-group source_fileCacheSizeDiv">
                                                                                            <label class="control-label col-md-4 fileCacheSizelabel"><?php echo $LANG['UI_DB_CDP_BACKUP_SOURCE_MACHINE_FILE_CACHE_SIZE'];?></label>
                                                                                            <div class="col-md-4 source_custom_cache_size_div">
                                                                                                <div class="col-md-6 pl0">
                                                                                                    <div class="input-group spinner-group">
                                                                                                        <input id="source_fileCacheSize" class="spinner-input form-control" type="text" onkeyup="value=value.replace(/[^\d]/g,'')">
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
                                                                                                <div class="col-md-6 pr0">
                                                                                                    <select name="source_file_cache_unit" id="" class="form-control select2me">
                                                                                                        <option value="1">GB</option>
                                                                                                        <option value="2">TB</option>
                                                                                                    </select>
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="col-md-4 source_cache_size_unlimited_div display-none">
                                                                                                <input class="form-control input-sm" value="<?php echo $LANG['UI_DB_CDP_BACKUP_UNLIMITED_CACHE_SIZE'];?>" maxlength="128" readonly>
                                                                                            </div>
                                                                                            <div class="col-md-4">
                                                                                                <button class="fileCachePathButton unlimit_limit_toggle source_unlimit_limit_toggle" data-value = "source_cache_button"><?php echo $LANG['UI_DB_CDP_BACKUP_SWITCH_TO_UNLIMITED'];?></button>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="form-group backup_fileCacheSizeDiv">
                                                                                            <label class="control-label col-md-4 fileCacheSizelabel"><?php echo $LANG['UI_DB_CDP_BACKUP_STANDBY_FILE_CACHE_SIZE'];?></label>
                                                                                            <div class="col-md-4 backup_custom_cache_size_div">
                                                                                                <div class="col-md-6 pl0">
                                                                                                    <div class="input-group spinner-group">
                                                                                                        <input id="backup_fileCacheSize" class="spinner-input form-control" type="text" onkeyup="value=value.replace(/[^\d]/g,'')">
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
                                                                                                <div class="col-md-6 pr0">
                                                                                                    <select name="backup_file_cache_unit" id="" class="form-control select2me">
                                                                                                        <option value="1">GB</option>
                                                                                                        <option value="2">TB</option>
                                                                                                    </select>
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="col-md-4 backup_cache_size_unlimited_div display-none">
                                                                                                <input class="form-control input-sm" value="<?php echo $LANG['UI_DB_CDP_BACKUP_UNLIMITED_CACHE_SIZE'];?>" maxlength="128" readonly>
                                                                                            </div>
                                                                                            <div class="col-md-4">
                                                                                                <button class="fileCachePathButton unlimit_limit_toggle backup_unlimit_limit_toggle" data-value = "backup_cache_button"><?php echo $LANG['UI_DB_CDP_BACKUP_SWITCH_TO_UNLIMITED'];?></button>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="form-group">
                                                                                            <label class="control-label col-md-4 fileCachePathlabel"><?php echo $LANG['UI_DB_CDP_RECOVERY_STANDBY_FILE_CACHE_PATH'];?></label>
                                                                                            <div class="col-md-4">
                                                                                                <input id="backup_fileCachePath" class="form-control select2me input-sm" value="/opt/vinchin/agent/dbcdp_cache/" maxlength="128" readonly>
                                                                                            </div>
                                                                                            <div class="col-md-4">
                                                                                                <button id="backup_customFileCachePath" class="fileCachePathButton"><?php echo $LANG['UI_JOB_CHANGE_CUSTOM']; ?></button>
                                                                                                <button id="backup_defaultFileCachePath" class="fileCachePathButton display-none"><?php echo $LANG['UI_JOB_CHANGE_SYSTEM_DEFAULT']; ?></button>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="form-group">
                                                                                            <label class="control-label col-md-4 fileCachePathlabel"><?php echo $LANG['UI_BACKUP_DATA_REPORT_BACKUP_MODE'];?></label>
                                                                                            <div class="col-md-4">
                                                                                                <select class="form-control select2me dataType">
                                                                                                </select>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-md-12">
                                                                                            <div class="alert alert-info">
                                                                                                <button type="button" class="close" data-dismiss="alert"></button>
                                                                                                <h4 class="alert-heading">
                                                                                                    <strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong>
                                                                                                </h4>
                                                                                                <ol class="alert-ol">
                                                                                                    <li><?php echo $LANG['UI_DB_CDP_RECOVERY_STANDBY_FILE_TIP'];?></li>
                                                                                                </ol>
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
                                                        <div class="alert alert-danger display-none jobnametip">
                                                        </div>
                                                        <div class="form-group mb0  mt10">
                                                            <label class="control-label col-md-3"><?php echo $LANG['UI_JOB_RNAME'] ?></label>
                                                            <div class="col-md-9">
                                                                <div class="input-icon right">
                                                                    <i class="fa"></i>
                                                                    <input type="text" maxlength="64" class="form-control"
                                                                           id="jobname"/>
                                                                    <span class="help-block "><?php echo $LANG['UI_JOB_RNAME_TIPS'] ?></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <h4 class="form-section"><?php echo $LANG['UI_JOB_RECOVERY_SRC']; ?></h4>
                                                        <div class="form-group mb0  mt10">
                                                            <label class="control-label col-md-3"><?php echo $LANG['UI_DB_CDP_RECOVER_SOURCE']; ?></label>
                                                            <div class="col-md-9">
                                                                <ul class="ztree" id="dbcdpShowSourceTree" style="border: 1px solid #E6E6E6"></ul>
                                                            </div>
                                                        </div>
                                                        <div class="form-group mb0  mt10">
                                                            <label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_RECOVERY_TIME_POINT']; ?></label>
                                                            <div class="col-md-9">
                                                                <p class="form-control-static recoveryTimePointShow">
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div class="form-group mb0  mt10">
                                                            <label class="control-label col-md-3"><?php echo $LANG['UI_DB_CDP_RECOVER_RESTORE_APP']; ?></label>
                                                            <div class="col-md-9">
                                                                <p class="form-control-static  recoveryAppShow">
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div class="form-group mb0  mt10">
                                                            <label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_RECOVER_TARGET_MACHINE']; ?></label>
                                                            <div class="col-md-9">
                                                                <ul class="ztree" id="dbcdpShowTargetTree" style="border: 1px solid #E6E6E6"></ul>
                                                            </div>
                                                        </div>
                                                        <div class="form-group mb0  mt10">
                                                            <label class="control-label col-md-3"><?php echo $LANG['UI_DB_CDP_BACKUP_DATA_TABLE_SPACE_STORAGE_DIRECTORY']; ?></label>
                                                            <div class="col-md-9">
                                                                <p class="form-control-static tablespaceDirShow">
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <h4 class="form-section"><?php echo $LANG['UI_GLOBAL_STRATEGY_RECOVERY_STRATEGY']; ?></h4>
                                                        <div class="form-group mb0 speedlimitDiv mt10">
                                                            <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY']; ?></label>
                                                            <div class="col-md-9">
                                                                <p class="form-control-static transferStrategyShow">
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div class="form-group mb0 speedlimitDiv mt10">
                                                            <label class="control-label col-md-3"><?php echo $LANG['UI_GLOBAL_STRATEGY_HIGH']; ?></label>
                                                            <div class="col-md-9">
                                                                <p class="form-control-static highLevelStrategyShow">
                                                                </p>
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
                                                <i class="viconfont vicon-shangyibu"></i> <?php echo $LANG['UI_PUBLIC_PREV_STEP'] ?>
                                            </a>
                                            <a href="javascript:;" class="btn green-turquoise button-next next-btn-margin-left">
                                                <?php echo $LANG['UI_PUBLIC_NEXT_STEP'] ?> <i
                                                        class="viconfont vicon-xiayibu"
                                                        style="width: 16px;background-position-x:-27px "></i>
                                            </a>
                                            <a href="javascript:;" class="btn green-haze button-submit">
                                                <?php echo $LANG['UI_PUBLIC_SUBMIT'] ?> <i
                                                        class="viconfont vicon-xiayibu"></i>
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
                                    <div id="rateDiv" style="display:inline-flex;">
                                        <div class="input-icon">
                                            <div id="speedSpinnerNum">
                                                <div class="input-group spinner-group">
                                                    <input type="text" id="speedSpinnerNumInput" style="text-align: center;"
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
                                            <select id="unit" style="width: 80px; margin-left: 10px;height: 33px;text-align:center;border: 1px solid #E6E6E6;">
                                                <option value="1">KB/s</option>
                                                <option selected value="2">MB/s</option>
                                                <option value="3">GB/s</option>
                                            </select>
                                        </div>
                                        <a style="display:block; margin-top:8px;" class="popovers ml15" data-container="body"
                                           data-trigger="hover"
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
                <div id="eventDetailModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first"
                     data-backdrop="static">
                    <div class="modal-header ">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h4 class="modal-title"><i
                                    class="viconfont vicon-ge_configuration"></i> <?php echo $LANG['UI_DB_CDP_RECOVER_EVENT_DETAILS'];?>
                        </h4>
                    </div>
                    <div class="modal-body">
                        <div class="portlet-body">
                            <div class="eventContent"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BEGIN PAGE LEVEL PLUGINS -->
        <script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
        <script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
        <script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
        <script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
        <script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
        <script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/additional-methods.min.js"></script>
        <script type="text/javascript" src="./assets/global/plugins/bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
        <script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>

        <script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
        <script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
        <?php
        if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
            echo '<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>';
        }
        ?>
        <script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
        <script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
        <script type="text/javascript" src="./scripts/plugins/jquery/jquery.speedStrategy.js"></script>
        <script type="text/javascript" src="./scripts/plugins/jquery/jquery.dbStrategy.js"></script>
        <script type="text/javascript" src="./scripts/plugins/calendar/jquery.searchableSelect.js"></script>
        <script type="text/javascript" src="./scripts/platform/component/backup_target.js"></script>
        <script type="text/javascript" src="./scripts/platform/component/transfer_network.js"></script>
        <!-- END PAGE LEVEL PLUGINS -->
        <script src="./scripts/dbcdp/dbcdprecover.js" type="text/javascript"></script>
