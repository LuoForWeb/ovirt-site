        <?php include_once '../../tpl/permission.php'; ?>
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
        <!-- END PAGE HEADER-->
        <!-- BEGIN PAGE CONTENT-->
        <div class="row" style="height:100%;">
            <div class="col-md-12" style="height:100%;">
                <div class="portlet box blue-hoki backup-page" id="dbcdpbackupcontent">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="levelchild viconfont vicon-tongbu"></i> <?php echo $LANG['UI_DB_CDP_CREATE_BACKUP_JOB']; ?>
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
                                             <?php echo $LANG['UI_DB_CDP_BACKUP_SOURCE']; ?> <i class="fa fa-check"></i></span>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="#tab2" data-toggle="tab" class="step">
                                            <span class="number">
                                            2 </span>
                                                    <span class="desc">
                                             <?php echo $LANG['UI_DB_CDP_SYNC_TARGET']; ?> <i class="fa fa-check"></i></span>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="#tab3" data-toggle="tab" class="step active">
                                            <span class="number">
                                            3 </span>
                                                    <span class="desc">
                                             <?php echo $LANG['UI_DB_CDP_SYNC_STRATEGY']; ?> <i class="fa fa-check"></i></span>
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
                                                <div class="row tab-pane__row">
                                                    <div class="col-md-4 tab-pane__row__source">
                                                        <div class="form-group src-wrap">
                                                            <div class="src-wrap__title">
                                                                <i class="levelchild viconfont vicon-ge_backup_host"></i><?php echo $LANG['UI_DB_CDP_BACKUP_SELECT_HOST']; ?>
                                                            </div>
                                                            <div class="src-wrap__content">
                                                                <div class="src-wrap__content__search">
                                                                    <select class="bs-select mb10 form-control" style="height: 34px" data-show-subtext="true" id="dbtype">
                                                                    </select>
                                                                </div>
                                                                <div class="src-wrap__content__ztree">
                                                                    <div class="vcenter-tree">
                                                                        <ul id="agent_tree" class="ztree ztree-fa tree-scroll"></ul>
                                                                    </div>
                                                                    <div id="noagent" class="alert alert-block alert-info fade in display-hide">
                                                                        <button type="button" class="close" data-dismiss="alert">
                                                                        </button>
                                                                        <h4 class="alert-heading">
                                                                            <strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong>
                                                                        </h4>
                                                                        <ol class="alert-ol">
                                                                            <li>
                                                                                <?php echo $LANG['UI_BACKUP_NO_VALID_DB_HOST_TIPS'] ?>
                                                                            </li>
                                                                            <li>
                                                                                <a id="toaddAgent"><?php echo $LANG['UI_BACKUP_NO_VALID_DB_HOST_TIPS1'] ?></a>
                                                                            </li>
                                                                            <li>
                                                                                <?php echo $LANG['UI_BACKUP_NO_VALID_DB_HOST_TIPS2'] ?>
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
                                                            <h4 class="alert-heading">
                                                                <strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                                            <ol class="alert-ol">
                                                                <li>
                                                                    <?php echo $LANG['UI_DB_CDP_BACKUP_SELECT_HOST_TIP1']; ?>
                                                                </li>
                                                                <li>
                                                                    <?php echo $LANG['UI_DB_CDP_BACKUP_SELECT_HOST_TIP2']; ?>
                                                                </li>
                                                            </ol>
                                                            <div class="alert-line"></div>
                                                            <p><?php echo $LANG['UI_DB_CDP_BACKUP_SELECT_HOST_NOTES']; ?></p>
                                                        </div>
                                                        <!-- 没有可用数据库提示 -->
                                                        <div class="alert alert-block alert-info fade in display-hide" id="noDBTips">
                                                            <button type="button" class="close" data-dismiss="alert"></button>
                                                            <h4 class="alert-heading">
                                                                <strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                                            <ol class="alert-ol">
                                                                <li><?php echo $LANG['UI_DB_NO_DB_TIPS1'] ?></li>
                                                                <li><?php echo $LANG['UI_DB_NO_DB_TIPS2'] ?></li>
                                                            </ol>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-8 display-hide" id="dbtreediv">
                                                        <div class="form-group">
                                                            <div class="addTitle">
                                                                <span>
                                                                    <i class="levelchild viconfont vicon-huifu"></i>
                                                                    <span><?php echo $LANG['UI_DB_CDP_BACKUP_SOURCE_NOTES']; ?></span>
                                                                </span>
                                                            </div>
                                                            <div class="addIt-list" id="allDbTree">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane" id="tab2">
                                                <div class="row vol-cdp-backup-tab">
                                                    <div class="col-md-9">
                                                        <div class="from-group">
                                                            <div class="col-md-12" id="backupTarget"></div>
                                                        </div>
                                                        <div class="form-group standbydiv">
                                                            <label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_STANDBY']; ?></label>
                                                            <div class="col-md-9">
                                                                <ul id="target_agent_tree" class="ztree"></ul>
                                                                <span class = "help-block"><?php echo $LANG['UI_DB_CDP_BACKUP_USER_TIPS'];?></span>
                                                            </div>
                                                        </div>
                                                        <div class="form-group dbMapDetaildiv">
                                                            <label class="control-label col-md-3"><?php echo $LANG['UI_DB_CDP_BACKUP_DATABASE_MAPPING_DETAILS']; ?></label>
                                                            <div id="instanceMapList" class="col-md-9">
                                                            </div>
                                                        </div>
                                                        <div class="form-group synNodediv">
                                                            <label class="control-label col-md-3"><?php echo $LANG['UI_DB_CDP_BACKUP_DATA_SYNC_NODE']; ?></label>
                                                            <div id="synNode" class="col-md-9"></div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="control-label col-md-3"><?php echo $LANG['UI_DB_CDP_BACKUP_DATA_TABLE_SPACE_STORAGE_DIRECTORY']; ?></label>
                                                            <div class="col-md-4">
                                                                <select id="tablespaceDir" class="form-control select2me" type="text">
                                                                    <option value="0"><?php echo $LANG['UI_DB_CDP_BACKUP_SYSTEM_TABLE_SPACE_DIRECTORY']; ?></option>
                                                                    <option value="1"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_DIY']; ?></option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-2 mt8">
                                                                <a class="popovers ml15" data-container="body"
                                                                   data-trigger="hover"
                                                                   data-placement="right"
                                                                   data-content=<?php echo $LANG['UI_DB_CDP_BACKUP_DATA_TABLE_SPACE_STORAGE_DIRECTORY_TIPS']; ?>
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
                                                        <div class="form-group">
                                                            <label class="control-label col-md-3 form-group-label"><?php echo $LANG['UI_VOL_CDP_ENABLE_AUTO_TAKEOVER']; ?></label>
                                                            <div class="col-md-4 form-group-content">
                                                                <input type="checkbox" id="autoTakeOver" class="make-switch"
                                                                       data-on-color="primary" data-off-color="info"
                                                                       data-size="small"
                                                                       data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                <a class="popovers ml15" data-container="body"
                                                                   data-trigger="hover"
                                                                   data-placement="right" data-content="<?php echo $LANG['UI_DB_CDP_BACKUP_AUTO_TAKEOVER_NOTES']; ?>">
                                                                    <i class="viconfont vicon-tishi"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                        <div class="form-group standByTips">
                                                            <div class="col-md-3"></div>
                                                            <div class="col-md-9">
                                                                <div class="alert alert-info">
                                                                    <button type="button" class="close" data-dismiss="alert"></button>
                                                                    <h4 class="alert-heading">
                                                                        <strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                                                    <ol class="alert-ol">
                                                                        <li>
                                                                            <?php echo $LANG['UI_DB_CDP_BACKUP_SELECT_APP_TIP1']; ?>
                                                                        </li>
                                                                        <li>
                                                                            <?php echo $LANG['UI_DB_CDP_BACKUP_SELECT_APP_TIP2']; ?>
                                                                        </li>
                                                                    </ol>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group display-hide" id="takeoverConfigContent">
                                                            <label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_TAKEOVER_CONFIGURE']; ?></label>
                                                            <div class="col-md-9 accordion">
                                                                <div class="panel panel-default strategy-panel">
                                                                    <div class="panel-heading">
                                                                        <h4 class="panel-title">
                                                                            <a class="accordion-toggle accordion-toggle-styled popovers"
                                                                               data-container="body" data-trigger="hover"
                                                                               data-placement="top" data-toggle="collapse"
                                                                               href="#takeoverConfig" aria-expanded="true">
                                                                                <i class="viconfont vicon-jieguanfuwuqi font-green-seagreen"></i>
                                                                                <span class="font-green-seagreen"><?php echo $LANG['UI_VOL_CDP_TAKEOVER_SERVER']; ?></span>
                                                                                <span class="takeoverConfigDes font12"></span>
                                                                            </a>
                                                                        </h4>
                                                                    </div>
                                                                    <div id="takeoverConfig" class="panel-collapse collapse in">
                                                                        <div class="panel-body">
                                                                            <div class="serviceIpDriftDiv">
                                                                                <div class="form-group">
                                                                                    <label class="control-label col-md-3 form-group-label col-md-5_en"><?php echo $LANG['UI_DB_CDP_BACKUP_SERVICE_IP_DRIFT']; ?></label>
                                                                                    <div class="col-md-7 form-group-content col-md-6_en">
                                                                                        <input type="checkbox" id="serviceIpDrift"
                                                                                               class="make-switch"
                                                                                               data-on-color="primary"
                                                                                               data-off-color="info"
                                                                                               data-size="small"
                                                                                               data-on-text="
                                                                                                <?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                               data-off-text="
                                                                                                <?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                        <a class="popovers ml15"
                                                                                           data-container="body"
                                                                                           data-trigger="hover"
                                                                                           data-placement="right"
                                                                                           data-content="<?php echo $LANG['UI_DB_CDP_BACKUP_ENABLE_SERVICE_IP_DRIFT_TO_HOST']; ?>">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <!-- 接管网络配置 -->
                                                                            <div class="form-group takeoverNetConf display-none">
                                                                                <label class="control-label col-md-3 col-md-5_en applianceselectlabel"><?php echo $LANG['UI_VOL_CDP_TASK_TAKEOVER_NET_CONF']; ?></label>
                                                                                <div class="col-md-9 col-md-7_en">
                                                                                    <button type="button" class="btn green-haze" id="takeover_network_conf" data-toggle="drawer" data-target="#takeover_conf_drawer"><?php echo $LANG['UI_VOL_CDP_TASK_TAKEOVER_NET_CONF']; ?></button>
                                                                                    <ul id="takeover_ip_map_list" style="padding-left: 0;width:90%;"></ul>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group" id="failbackTargetIpContent">
                                                                                <label class="control-label col-md-3 col-md-5_en"><?php echo $LANG['UI_DB_CDP_BACKUP_FAILBACK_IP']; ?></label>
                                                                                <div class="col-md-4 pr8">
                                                                                    <input type="text" id="failbackTargetIp" class="form-control input-sm" value="">
                                                                                </div>
                                                                                <div class="col-md-2 mt8 pl24 col-md-1_en pl0_en">
                                                                                    <a class="popovers"
                                                                                       data-container="body"
                                                                                       data-trigger="hover"
                                                                                       data-placement="right"
                                                                                       data-content="<?php echo $LANG['UI_DB_CDP_BACKUP_FAILBACK_IP_NOTES']; ?>">
                                                                                        <i class="viconfont vicon-tishi"></i>
                                                                                    </a>
                                                                                </div>
                                                                                <div class="col-md-3 ml-50 col-md-2_en">
                                                                                    <button type="button" class="btn table-toolbar-btn" id="linkSelectIP">
                                                                                        <span class="font-green-seagreen"><?php echo $LANG['UI_VOL_CDP_TAKEOVER_FAILBACK_IP_PING']; ?></span>
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <label class="control-label col-md-3 col-md-5_en"><?php echo $LANG['UI_VOL_CDP_HEARTBEAT_NOT_ENABLE_TIME']; ?></label>
                                                                                <div class="heartFailureDiv col-md-4 pr8">
                                                                                    <div class="input-group spinner-group">
                                                                                        <input type="num" id="heartFailure_num"
                                                                                               onkeyup="value=value.replace(/[^\d]/g,'')"
                                                                                               class="spinner-input form-control input-sm">
                                                                                        <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                            <button type="button"
                                                                                                    class="btn spinner-up default">
                                                                                                <i class="fa fa-angle-up"></i>
                                                                                            </button>
                                                                                            <button type="button"
                                                                                                    class="btn spinner-down default ">
                                                                                                <i class="fa fa-angle-down"></i>
                                                                                            </button>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <label class="col-md-1 mt8 pl0 pr0 w-25px w-70px_en"><?php echo $LANG['WEB_UTILS_SECOND']; ?></label>
                                                                                <div class="col-md-2 mt8 pl0 col-md-1_en">
                                                                                    <a class="popovers"
                                                                                       data-container="body"
                                                                                       data-trigger="hover"
                                                                                       data-placement="right"
                                                                                       data-content="<?php echo $LANG['UI_VOL_CDP_HEARTBEAT_NOT_ENABLE_TIME_TIPS']; ?>">
                                                                                        <i class="viconfont vicon-tishi"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <label class="control-label col-md-3 col-md-5_en"><?php echo $LANG['UI_DB_CDP_BACKUP_APP_CONTINUOUS_FAILURE_TIMES']; ?></label>
                                                                                <div class="numberFailuresDiv col-md-4 pr8">
                                                                                    <div class="input-group spinner-group">
                                                                                        <input type="num" id="Failures_num"
                                                                                               onkeyup="value=value.replace(/[^\d]/g,'')"
                                                                                               class="spinner-input form-control input-sm">
                                                                                        <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                            <button type="button"
                                                                                                    class="btn spinner-up default">
                                                                                                <i class="fa fa-angle-up"></i>
                                                                                            </button>
                                                                                            <button type="button"
                                                                                                    class="btn spinner-down default ">
                                                                                                <i class="fa fa-angle-down"></i>
                                                                                            </button>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <label class="col-md-1 mt8 pl0 pr0 w-25px w-70px_en"><?php echo $LANG['UI_VOL_CDP_TIMES']; ?></label>
                                                                                <div class="col-md-1 mt8 pl0 pr0 w-25px">
                                                                                    <a class="popovers"
                                                                                       data-container="body"
                                                                                       data-trigger="hover"
                                                                                       data-placement="right"
                                                                                       data-content="<?php echo $LANG['UI_VOL_CDP_FAULT_TIMES_TIPS']; ?>">
                                                                                        <i class="viconfont vicon-tishi"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <label class="control-label col-md-3 col-md-5_en"><?php echo $LANG['UI_DB_CDP_BACKUP_APP_FAILURE_MONITORING_INTERVAL']; ?></label>
                                                                                <div class="faultMonitorIntervalDiv col-md-4 pr8">
                                                                                    <div class="input-group spinner-group">
                                                                                        <input type="num" id="monitor_Interval"
                                                                                               onkeyup="value=value.replace(/[^\d]/g,'')"
                                                                                               class="spinner-input form-control input-sm">
                                                                                        <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                            <button type="button"
                                                                                                    class="btn spinner-up default">
                                                                                                <i class="fa fa-angle-up"></i>
                                                                                            </button>
                                                                                            <button type="button"
                                                                                                    class="btn spinner-down default ">
                                                                                                <i class="fa fa-angle-down"></i>
                                                                                            </button>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <label class="col-md-1 mt8 pl0 pr0 w-25px w-70px_en"><?php echo $LANG['WEB_UTILS_SECOND']; ?></label>
                                                                                <div class="col-md-2 mt8 pl0 col-md-1_en">
                                                                                    <a class="popovers"
                                                                                       data-container="body"
                                                                                       data-trigger="hover"
                                                                                       data-placement="right"
                                                                                       data-content="<?php echo $LANG['UI_VOL_CDP_FAULT_INTERVA_TIPS']; ?>">
                                                                                        <i class="viconfont vicon-tishi"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <div class="col-md-12">
                                                                                    <div class="alert alert-info">
                                                                                        <button type="button" class="close" data-dismiss="alert"></button>
                                                                                        <h4 class="alert-heading">
                                                                                            <strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                                                                        <ol class="alert-ol">
                                                                                            <li>
                                                                                                <?php echo $LANG['UI_DB_CDP_BACKUP_FAILBACK_IP_TIP1']; ?>
                                                                                            </li>
                                                                                            <li>
                                                                                                <?php echo $LANG['UI_DB_CDP_BACKUP_FAILBACK_IP_TIP2']; ?>
                                                                                            </li>
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
                                            <div class="tab-pane" id="tab3" style="height: 95%;">
                                                <div class="row row-stepthree" style="height: 100%;">
                                                    <div class="nav-tabs-wrapper pdlr15 col-md-offset-1 col-md-10" style="height: 100%;">
                                                        <ul class="nav nav-tabs nav-line-tabs">
                                                            <li class="commonLi active nav-item">
                                                                <a href="#tab_transfer" class="popovers nav-link"
                                                                   data-content=""
                                                                   data-container="body"
                                                                   data-trigger="hover"
                                                                   data-placement="top"
                                                                   data-toggle="tab">
                                                                    <i class="viconfont vicon-a-chuanshu"></i> <?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?>
                                                                </a>
                                                            </li>
                                                            <li class="highLi nav-item">
                                                                <a href="#tab_high" class="popovers nav-link"
                                                                   data-content=""
                                                                   data-container="body"
                                                                   data-trigger="hover"
                                                                   data-placement="top"
                                                                   data-toggle="tab">
                                                                    <i class="viconfont vicon-gaojipeizhi"></i> <?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?>
                                                                </a>
                                                            </li>
                                                        </ul>
                                                        <div class="tab-content min-height300 hover-scroll-y" style="height: 90%;overflow-x:hidden;overflow-y: auto;">
                                                            <div class="tab-pane active" id="tab_transfer">
                                                                <div class="panel-body">
                                                                    <div class="tabbable-custom pdlr15  col-md-10">
                                                                        <div class="form-group transfernetworkDiv">
                                                                            <label class="control-label col-md-3 transportNetworklabel"><?php echo $LANG['UI_NODE_TRANSFER_NETWORK']; ?></label>
                                                                            <div class="col-md-4">
                                                                                <ul class="ztree" id="transferNetworkTree"></ul>
                                                                            </div>
                                                                            <div class="col-md-2 mt4">
                                                                                <a class="popovers ml15"
                                                                                   data-container="body"
                                                                                   data-trigger="hover"
                                                                                   data-placement="right" data-content="<?php echo $LANG['UI_NODE_TRANSFER_NETWORK_TIPS'] ?>">
                                                                                    <i class="viconfont vicon-tishi"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group encrypttransferdiv">
                                                                            <label class="control-label col-md-3 encrypttransferlabel form-group-label"><?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER'] ?></label>
                                                                            <div class="col-md-4 form-group-content">
                                                                                <input type="checkbox" id="encrypttransfer"
                                                                                       class="make-switch"
                                                                                       data-on-color="primary"
                                                                                       data-off-color="info" data-size="small"
                                                                                       data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                <a class="popovers ml15"
                                                                                   data-container="body"
                                                                                   data-trigger="hover"
                                                                                   data-html="true"
                                                                                   data-placement="right"
                                                                                   data-content="<?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER_TIPS'] ?>">
                                                                                    <i class="viconfont vicon-tishi"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group encrypttransfermethoddiv display-none">
                                                                            <label class="control-label col-md-3 encrypttransfermethodlabel form-group-label"><?php echo $LANG['UI_DB_CDP_BACKUP_ENCRYPTED_TRANSMISSION_METHOD']; ?></label>
                                                                            <div class="col-md-4 form-group-content">
                                                                                <select name="" id="encrypttransfermethodselect" class="form-control select2me input-sm">
                                                                                    <option value="1"><?php echo $LANG['UI_BACKUP_TRANSFER_ENCRYPT_RSA']; ?></option>
                                                                                    <option value="2"><?php echo $LANG['UI_BACKUP_TRANSFER_ENCRYPT_SM']; ?></option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group compresstransferdiv">
                                                                            <label class="control-label col-md-3 compresstransferlabel form-group-label form-group-label"><?php echo $LANG['UI_DB_CDP_BACKUP_COMPRESSED_TRANSMISSION']; ?></label>
                                                                            <div class="col-md-4 form-group-content">
                                                                                <input type="checkbox" id="compresstransfer"
                                                                                       class="make-switch"
                                                                                       data-on-color="primary"
                                                                                       data-off-color="info" data-size="small"
                                                                                       data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                <a class="popovers ml15"
                                                                                   data-container="body"
                                                                                   data-trigger="hover"
                                                                                   data-html="true"
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
                                                                                    <div class="accordion strategyOne" >
                                                                                        <div class="panel panel-default strategy-panel">
                                                                                            <div class="panel-heading">
                                                                                                <h4 class="panel-title">
                                                                                                    <a class="accordion-toggle accordion-toggle-styled popovers"
                                                                                                       data-container="body"
                                                                                                       data-trigger="hover"
                                                                                                       data-placement="top"
                                                                                                       data-toggle="collapse"
                                                                                                       data-parent=".strategyOne"
                                                                                                       href="#speed"
                                                                                                       aria-expanded="true">
                                                                                                        <i class="viconfont vicon-xiansucelve1 font-green-seagreen"></i>
                                                                                                        <span class="font-green-seagreen"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?></span>
                                                                                                        <span class="strategyDes speedlimitDes"></span>
                                                                                                    </a>
                                                                                                </h4>
                                                                                            </div>
                                                                                            <?php include_once('../../content/platform/global_strategy/speedJob.php'); ?>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="tab-pane" id="tab_high">
                                                                <div class="advance-config-wrap">
                                                                    <div class="advance-config-wrap__row row m0">
                                                                        <div class="col-md-2 col-sm-2 col-xs-2 nav-tab-radios">
                                                                            <ul class="nav nav-tabs tabs-left">
                                                                                <li class="data_copy active">
                                                                                    <a href="#tab_data_copy" data-toggle="tab" aria-expanded="false"><?php echo $LANG['UI_DB_CDP_BACKUP_DATA_STRATEGY'];?></a>
                                                                                </li>
                                                                                <li class="cache-strategy">
                                                                                    <a href="#tab_cache_strategy" data-toggle="tab" aria-expanded="false"><?php echo $LANG['UI_CM_CDP_BACKUP_CACHE_CONF'];?></a>
                                                                                </li>
                                                                                <li class="overload_protect">
                                                                                    <a href="#tab_overload_protect" data-toggle="tab" aria-expanded="false"><?php echo $LANG['UI_STRATEGY_OVER_LOAD_LABEL']?></a>
                                                                                </li>
                                                                            </ul>
                                                                        </div>
                                                                        <div class="col-md-10 col-sm-10 col-xs-10 pe-0 advance-config-wrap__row__content">
                                                                            <div class="tab-content level1">
                                                                                <!--数据复制策略-->
                                                                                <div class="tab-pane fade active in" id="tab_data_copy">
                                                                                    <div class="col-md-12 pt40">
                                                                                        <div class="form-group">
                                                                                            <label class="control-label col-md-4 delayedLoadlabel"><?php echo $LANG['UI_DB_CDP_BACKUP_DELAYED_LOADING'];?></label>
                                                                                            <div class="col-md-3">
                                                                                                <input type="checkbox" id="delayedLoad"
                                                                                                       class="make-switch" data-on-color="primary"
                                                                                                       data-off-color="info" data-size="small"
                                                                                                       data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                                <a class="popovers ml15"
                                                                                                   data-container="body"
                                                                                                   data-trigger="hover"
                                                                                                   data-html="true"
                                                                                                   data-placement="right"
                                                                                                   data-content="<?php echo $LANG['UI_DB_CDP_BACKUP_DELAYED_LOADING_TIP1'];?>">
                                                                                                    <i class="viconfont vicon-tishi"></i>
                                                                                                </a>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div id="delayedLoadTimeDiv" class="form-group display-none">
                                                                                            <label class="control-label col-md-4"><?php echo $LANG['UI_DB_CDP_BACKUP_DELAYED_LOADING_TIME'];?></label>
                                                                                            <div class="col-md-8 form-group-content custom-input-group">
                                                                                                <input type="number" class="form-control w-50px days" value="0">
                                                                                                <span class="separator-text"><?php echo $LANG['WEB_SYSTEM_AUTH_DAY'];?></span>
                                                                                                <input type="number" class="form-control w-45px hours" value="0">
                                                                                                <span class="separator-text"><?php echo $LANG['WEB_UTILS_HOUR'];?></span>
                                                                                                <input type="number" class="form-control w-45px minutes" value="30">
                                                                                                <span class="separator-text"><?php echo $LANG['WEB_UTILS_MINUTE'];?></span>
                                                                                                <input type="number" class="form-control w-45px seconds" value="0">
                                                                                                <span class="separator-text"><?php echo $LANG['WEB_UTILS_SECOND'];?></span>
                                                                                            </div>
                                                                                        </div>
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
                                                                                <!--缓存配置-->
                                                                                <div class="tab-pane fade" id="tab_cache_strategy">
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
                                                                                                    <select name="source_file_cache_unit" id="" class="form-control select2me pr0">
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
                                                                                        <div class="form-group">
                                                                                            <label class="control-label col-md-4 fileCachePathlabel"><?php echo $LANG['UI_DB_CDP_BACKUP_SOURCE_MACHINE_FILE_CACHE_PATH'];?></label>
                                                                                            <div class="col-md-4">
                                                                                                <input id="source_fileCachePath" class="form-control select2me input-sm" value="" maxlength="128" readonly>
                                                                                            </div>
                                                                                            <div class="col-md-4">
                                                                                                <button id="source_customFileCachePath" class="fileCachePathButton"><?php echo $LANG['UI_JOB_CHANGE_CUSTOM']; ?></button>
                                                                                                <button id="source_defaultFileCachePath" class="fileCachePathButton display-none"><?php echo $LANG['UI_JOB_CHANGE_SYSTEM_DEFAULT']; ?></button>
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
                                                                                                    <select name="backup_file_cache_unit" id="" class="form-control select2me pr0">
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
                                                                                            <label class="control-label col-md-4 fileCachePathlabel"><?php echo $LANG['UI_DB_CDP_BACKUP_STANDBY_FILE_CACHE_PATH'];?></label>
                                                                                            <div class="col-md-4">
                                                                                                <input id="backup_fileCachePath" class="form-control select2me input-sm" value="" maxlength="128" readonly>
                                                                                            </div>
                                                                                            <div class="col-md-4">
                                                                                                <button id="backup_customFileCachePath" class="fileCachePathButton"><?php echo $LANG['UI_JOB_CHANGE_CUSTOM']; ?></button>
                                                                                                <button id="backup_defaultFileCachePath" class="fileCachePathButton display-none"><?php echo $LANG['UI_JOB_CHANGE_SYSTEM_DEFAULT']; ?></button>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-md-12">
                                                                                            <div class="alert alert-info">
                                                                                                <button type="button" class="close" data-dismiss="alert"></button>
                                                                                                <h4 class="alert-heading">
                                                                                                    <strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong>
                                                                                                </h4>
                                                                                                <ol class="alert-ol">
                                                                                                    <li><?php echo $LANG['UI_DB_CDP_BACKUP_SOURCE_FILE_TIP'];?></li>
                                                                                                    <li><?php echo $LANG['UI_DB_CDP_BACKUP_STANDBY_FILE_TIP'];?></li>
                                                                                                </ol>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <!--过载保护-->
                                                                                <div class="tab-pane fade" id="tab_overload_protect">
                                                                                    <div class="col-md-12 pt40">
                                                                                        <div class="form-group">
                                                                                            <label class="control-label col-md-3 form-group-label"><?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE'];?></label>
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
                                                                                        <div class="form-group">
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
                                            </div>
                                            <div class="tab-pane" id="tab4">
                                                <div class="tab-pane__body">
                                                    <div class="tab-pane__body__form">
                                                        <div class="alert alert-danger display-none jobnametip">
                                                        </div>
                                                        <div class="form-group mb0 mb10">
                                                            <label class="control-label col-md-3"><?php echo $LANG['UI_JOB_RNAME'] ?></label>
                                                            <div class="col-md-9">
                                                                <div class="input-icon right">
                                                                    <i class="fa"></i>
                                                                    <input type="text" maxlength="64" class="form-control" id="jobname"/>
                                                                    <span class="help-block "><?php echo $LANG['UI_JOB_RNAME_TIPS'] ?></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <h4 class="form-section"><?php echo $LANG['UI_CBR_SYNC_SOURCE']; ?></h4>
                                                        <div class="form-group mb10 mt10">
                                                            <label class="control-label col-md-3"><?php echo $LANG['UI_DB_CDP_BACKUP_SOURCE']; ?></label>
                                                            <div class="col-md-9">
                                                                <ul class="ztree" id="dbcdpShowSourceTree" style="border: 1px solid #E6E6E6"></ul>
                                                            </div>
                                                        </div>
                                                        <div class="form-group mb0 mb10">
                                                            <label class="control-label col-md-3"><?php echo $LANG['UI_DB_CDP_BACKUP_TYPE'];?></label>
                                                            <div class="col-md-9">
                                                                <p class="form-control-static synAppShow">
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <h4 class="form-section"></h4>
                                                        <div class="form-group mb0 mb10">
                                                            <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TARGET_NODE']; ?></label>
                                                            <div class="col-md-9">
                                                                <p class="form-control-static computeNodeShow"></p>
                                                            </div>
                                                        </div>
                                                        <div class="form-group mb0 mb10 dataSynNodediv">
                                                            <label class="control-label col-md-3"><?php echo $LANG['UI_DB_CDP_BACKUP_DATA_SYNC_NODE']; ?></label>
                                                            <div class="col-md-9">
                                                                <p class="form-control-static dataSynNodeShow"></p>
                                                            </div>
                                                        </div>
                                                        <div class="form-group mb0 mb10">
                                                            <label class="control-label col-md-3"><?php echo $LANG['UI_DB_CDP_BACKUP_TARGET']; ?></label>
                                                            <div class="col-md-9">
                                                                <ul class="ztree" id="dbcdpShowTargetTree" style="border: 1px solid #E6E6E6"></ul>
                                                            </div>
                                                        </div>
                                                        <div class="form-group mb0 mb10 tablespaceDirdiv">
                                                            <label class="control-label col-md-3"><?php echo $LANG['UI_DB_CDP_BACKUP_DATA_TABLE_SPACE_STORAGE_DIRECTORY']; ?></label>
                                                            <div class="col-md-9">
                                                                <p class="form-control-static tablespaceDirShow"></p>
                                                            </div>
                                                        </div>
                                                        <h4 class="form-section"><?php echo $LANG['UI_VOL_CDP_TAKEOVER_CONFIGURE']; ?></h4>
                                                        <div class="form-group mb0 mb10">
                                                            <label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_AUTO_TAKEOVER']; ?></label>
                                                            <div class="col-md-9">
                                                                <p class="form-control-static  autoTakeoverShow"></p>
                                                            </div>
                                                        </div>
                                                        <div class="form-group mb0 mb10 display-hide" id="serviceIpShiftdiv">
                                                            <label class="control-label col-md-3"><?php echo $LANG['UI_DB_CDP_BACKUP_SERVICE_IP_DRIFT']; ?></label>
                                                            <div class="col-md-9">
                                                                <p class="form-control-static  serviceIpShiftshow"></p>
                                                            </div>
                                                        </div>
                                                        <div class="form-group mb0 mb10" id="takeOverNetdiv">
                                                            <label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_BACKUP_TAKEOVER_NET_CONF']; ?></label>
                                                            <div class="col-md-9">
                                                                <p class="form-control-static takeOverNetShow"></p>
                                                            </div>
                                                        </div>
                                                        <div class="form-group mb0 mb10 display-hide" id="failbackTargetIpdiv">
                                                            <label class="control-label col-md-3"><?php echo $LANG['UI_DB_CDP_BACKUP_FAILBACK_IP']; ?></label>
                                                            <div class="col-md-9">
                                                                <p class="form-control-static failbackTargetIpShow"></p>
                                                            </div>
                                                        </div>
                                                        <div class="form-group mb0 mb10 display-hide" id="heartFailurediv">
                                                            <label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_HEARTBEAT_NOT_ENABLE_TIME']; ?></label>
                                                            <div class="col-md-9">
                                                                <p class="form-control-static  heartFailureShow"></p>
                                                            </div>
                                                        </div>
                                                        <div class="form-group mb0 mb10 display-hide" id="numberConseFailurediv">
                                                            <label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_FAULT_TIMES']; ?></label>
                                                            <div class="col-md-9">
                                                                <p class="form-control-static  numberConseFailureShow"></p>
                                                            </div>
                                                        </div>
                                                        <div class="form-group mb0 mb10 display-hide" id="faultIntervaldiv">
                                                            <label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_FAULT_INTERVA']; ?></label>
                                                            <div class="col-md-9">
                                                                <p class="form-control-static faultIntervalShow"></p>
                                                            </div>
                                                        </div>
                                                        <h4 class="form-section"><?php echo $LANG['UI_CBR_SYNC_STRATEGY']; ?></h4>
                                                        <div class="form-group mb0 transferStrategydiv mb10">
                                                            <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY']; ?></label>
                                                            <div class="col-md-9">
                                                                <p class="form-control-static transferStrategyShow"></p>
                                                            </div>
                                                        </div>
                                                        <div class="form-group mb0 mb10" id="highLevelStrategydiv">
                                                            <label class="control-label col-md-3"><?php echo $LANG['UI_GLOBAL_STRATEGY_HIGH']; ?></label>
                                                            <div class="col-md-9">
                                                                <p class="form-control-static highLevelStrategyShow"></p>
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
                                                <?php echo $LANG['UI_PUBLIC_NEXT_STEP'] ?>
                                                <i class="viconfont vicon-xiayibu" style="width: 16px;background-position-x:-27px "></i>
                                            </a>
                                            <a href="javascript:;" class="btn green-haze button-submit">
                                                <?php echo $LANG['UI_PUBLIC_SUBMIT'] ?>
                                                <i class="viconfont vicon-xiayibu"></i>
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
                        <h4 class="modal-title">
                            <i class="viconfont vicon-xiansucelve1 font-green-seagreen"></i> <?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?>
                        </h4>
                    </div>
                    <div class="modal-body">
                        <div class="portlet-body">
                            <div class="form-group ">
                                <label class="control-label col-md-2">  <?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_TYPE'] ?></label>
                                <div class="col-md-6">
                                    <select class="form-control select2me inline-block" id="speedModeType" style="width:203px;">
                                        <option value="1"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_STRATEGY'] ?></option>
                                        <option value="2"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_FOREVER'] ?></option>
                                    </select>
                                    <a class="popovers ml15"
                                       data-container="body"
                                       data-trigger="hover"
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
                                <label class="control-label col-md-2"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_VALUE'] ?></label>
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
                        <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
                        <button type="button" class="btn btn-primary" id="speed_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
                    </div>
                </div>
                <!-- END RATE LIMIT MODAL -->
                <!-- drawer开始 -->
                <div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" style="width: 800px"
                     aria-labelledby="drawer-1_title" aria-hidden="true" id="takeover_conf_drawer">
                    <div class="drawer-content drawer-content-scrollable" role="document">
                        <div class="drawer-header">
                            <div class="drawer-title" id="drawer-1_title"
                                 style="display:flex;justify-content:space-between;align-items:center">
                                <div class="drawer-title-left">
                                    <i class="levelchild viconfont vicon-a-Setting-twoshezhi"></i>
                                    <span><?php echo $LANG['UI_VOL_CDP_TASK_TAKEOVER_NET_CONF'];?></span>
                                </div>
                                <div class="drawer-title-right drawer-close" data-dismiss="drawer" aria-label="Close">
                                    <i class="viconfont vicon-guanbi"></i>
                                </div>
                            </div>
                        </div>
                        <!-- BEGIN BACKUP TASK DETAIL DRAWER BODY -->
                        <div class="drawer-body">
                            <!-- BEGIN COMMON STRATEGY GROUP -->
                            <div class="col-md-12 accordion pl0">
                                <div class="portlet">
                                    <div class="portlet-body">
                                        <div id="" class="panel-collapse collapse in form-horizontal">
                                            <div class="nav-tabs-wrapper">
                                                <ul class="nav nav-tabs nav-line-tabs" id="">
                                                    <!-- 通用配置-->
                                                    <li class="nav-item active">
                                                        <a href="#ipConfig" class="nav-link" data-toggle="tab" aria-expanded="true">
                                                            <?php echo $LANG['UI_VOL_CDP_TASK_IP_SERVICE_CONF'];?>
                                                        </a>
                                                    </li>
                                                    <li class="nav-item backupGateway_li display-none">
                                                        <a href="#backupGateway" class="nav-link" data-toggle="tab" aria-expanded="true">
                                                            <?php echo $LANG['UI_VOL_CDP_STANDBY_GATEWAY_CONF'];?>
                                                        </a>
                                                    </li>
                                                </ul>
                                                <div class="tab-content border-bottom-none pt-0">
                                                    <div id="ipConfig" class="tab-pane active">
                                                        <div id="host_ip_server_conf" class="pt-24">
                                                        </div>
                                                    </div>
                                                    <div id="backupGateway" class="tab-pane backupGateway_div">
                                                        <div id="standby_gateway_conf">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="drawer-footer">
                            <div class="row">
                                <div class="col-md-6" style="float: right;padding-right: 10px;">
                                    <button type="button" class="btn green-haze btn-confirm" id="submit_host_network_conf">
                                        <?php echo $LANG['UI_PUBLIC_YES'] ?>
                                    </button>
                                    <button type="button" class="btn default cancel">
                                        <?php echo $LANG['UI_PUBLIC_NO'] ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- drawer结束 -->
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
        <!-- END PAGE LEVEL PLUGINS -->
        <script src="./scripts/dbcdp/takeover_ip_server_map.js"></script>
        <script src="./scripts/dbcdp/dbcdpCommonFun.js"></script>
        <script type="text/javascript" src="./scripts/platform/component/backup_target.js"></script>
        <script type="text/javascript" src="./scripts/platform/component/transfer_network.js"></script>
        <script src="./scripts/dbcdp/dbcdpbackup.js" type="text/javascript"></script>

