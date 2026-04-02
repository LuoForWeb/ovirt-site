<?php include_once '../../tpl/permission.php'; ?>
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css"
      href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
<link rel="stylesheet" type="text/css"
      href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="./scripts/plugins/ztree/css/metroStyle/metroStyle.css"/>
<link rel="stylesheet" type="text/css"
      href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css"/>
<link rel="stylesheet" type="text/css" href="./scripts/plugins/popModal/popModal.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/daterangepicker/daterangepicker.css"/>
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
    <div class="col-md-12">
        <!-- Begin: life time stats -->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="levelchild viconfont vicon-vmdata"></i><?php echo $LANG['UI_DB_CDP_DATA_DISASTER_RECOVERY_DATA_MANAGEMENT'];?>
                </div>
            </div>
            <div class="portlet-body mlr10">
                <div class="row">
                    <div class="col-md-4">
                        <div class="portlet">
                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="viconfont vicon-beiji"></i><?php echo $LANG['UI_VOL_CDP_STANDBY'];?>
                                </div>
                            </div>
                            <div class="portlet-body batchDeleteDiv">
                                <div id="nodeCheck" style="margin-top:10px;margin-bottom:10px;">
                                    <div class="input-icon right width155p" style="display: -webkit-inline-box;">
                                        <input type="text" maxlength="128"
                                               placeholder="<?php echo $LANG['UI_COPY_SEARCH_AS_KEYWORD'] ?>"
                                               class="form-control width60p"/>
                                        <button type="button" id="filterSearch" class="btn btn-sm green-haze"
                                                style="height: 34px;width: 34px;">
                                            <i class="viconfont vicon-gaojisousuo1"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="vcenter-tree" style="border: 1px solid #E6E6E6;height: 545px;">
                                    <p id="markStr"></p>
                                    <div class="alert alert-block alert-info fade in display-hide" id="noagenttips">
                                        <button type="button" class="close" data-dismiss="alert"></button>
                                        <ul class="alert-ul">
                                            <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>
                                                :</strong>
                                            <li>
                                                <?php echo $LANG['UI_DATA_NODATA_TIPS'] ?>
                                            </li>
                                        </ul>

                                    </div>
                                    <div class="alert alert-block alert-info fade in display-hide" id="nosearchtips">
                                        <button type="button" class="close" data-dismiss="alert"></button>
                                        <ul class="alert-ul">
                                            <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>
                                                :</strong>
                                            <li>
                                                <?php echo $LANG['UI_DATA_NOSEARCH_TIPS'] ?>
                                            </li>
                                        </ul>
                                    </div>
                                    <ul id="agent_tree" class="ztree ztree-fa "></ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8 pd0">
                        <div class="portlet ">
                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="viconfont vicon-shuju"></i><?php echo $LANG['UI_DB_CDP_DATA_DISASTER_RECOVERY_DATA'];?>
                                    <span style="margin-left: 10px;color:#4ad1cd;font-size:14px;" id="dbdataUrl"></span>
                                </div>
                            </div>
                            <div class="alert alert-block alert-info fade in" id="tabletips">
                                <button type="button" class="close" data-dismiss="alert"></button>
                                <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                <ol class="alert-ol">
                                    <li><?php echo $LANG['UI_DB_CDP_DATA_DISASTER_RECOVERY_DATA_TIP1'];?></li>
                                    <li><?php echo $LANG['UI_DB_CDP_DATA_DISASTER_RECOVERY_DATA_TIP2'];?></li>
                                </ol>
                            </div>
                            <div id="drData" class="display-hide"
                                 style="height: 589px;border: 1px solid #E6E6E6;overflow-y: auto">
                                <div class="pd20">
                                    <div class="col-md-2 font14 colorgray"><?php echo $LANG['UI_DB_CDP_DATA_DISASTER_RECOVERY_BACKUP_HOST'];?>:</div>
                                    <div class="col-md-5 font14 colordark" id="drBackup">
                                    </div>
                                </div>
                                <div class="pd20">
                                    <div class="col-md-2 font14 colorgray"><?php echo $LANG['UI_VOL_CDP_MONITOR_DEVICE'];?>:</div>
                                    <div class="col-md-5 font14 colordark" id="monitorApp">
                                    </div>
                                </div>
                                <div class="pd20">
                                    <div class="col-md-2 font14 colorgray"><?php echo $LANG['UI_JOB_ALREADY_COMPLETED_SIZE'];?>:</div>
                                    <div class="col-md-5 font14 colordark" id="processedCapacity">
                                    </div>
                                </div>
                                <div class="pd20">
                                    <div class="col-md-2 font14 colorgray"><?php echo $LANG['UI_DB_CDP_DATA_DISASTER_RECOVERY_RECOVERABLE_TIME'];?>:</div>
                                    <div class="col-md-10 font14 colordark" id="recoverTime">
                                        <div class="accordion">
                                            <div class="panel panel-default strategy-panel">
                                                <div class="panel-heading">
                                                    <h4 class="panel-title">
                                                        <a class="accordion-toggle accordion-toggle-styled collapsed popovers"
                                                           data-container="body"
                                                           data-trigger="hover" data-placement="top"
                                                           data-toggle="collapse" href="#hostDetail"
                                                           aria-expanded="true">
                                                            <i class="viconfont vicon-shijian font-green-seagreen"></i>
                                                            <span class="font-green-seagreen"><?php echo $LANG['UI_JOB_TIME_RANGE'];?></span>
                                                            <span id="timeRange" class="fs12"></span>
                                                        </a>
                                                    </h4>
                                                </div>
                                                <div id="hostDetail" class="panel-collapse collapse in">
                                                    <div class="panel-body min-height300 pl15 pr15">
                                                        <div class="col-md-12 tabbable-custom">
                                                            <ul class="nav nav-tabs " id="timepointType">
                                                                <li class="commonLi active" id="recoveryAnytimeLi">
                                                                    <a href="#anyPointTime"
                                                                       class="popovers"
                                                                       data-content=""
                                                                       data-container="body"
                                                                       data-trigger="hover"
                                                                       data-placement="top"
                                                                       data-toggle="tab">
                                                                        <i class="viconfont vicon-renyishijiandian"></i>
                                                                        <?php echo $LANG['UI_VOL_CDP_ANY_TIME_POINT'];?>
                                                                    </a>
                                                                </li>
                                                                <li class="highLi" id="agentTransactionInfoLi">
                                                                    <a href="#transactionInfo"
                                                                       class="popovers"
                                                                       data-content="<?php echo $LANG['UI_BACKUP_HIGH_CONFIG_DES'] ?>
                                                                       data-container=" body"
                                                                       data-trigger="hover"
                                                                       data-placement="top"
                                                                       data-toggle="tab">
                                                                    <i class="viconfont vicon-shijian1"></i>
                                                                    <?php echo $LANG['UI_DB_CDP_DATA_DISASTER_RECOVERY_TRANSACTION_INFORMATION'];?>
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                            <div class="tab-content min-height360"
                                                                 style="height: 90%;overflow-x:hidden;overflow-y: auto;">
                                                                <div class="tab-pane active " id="anyPointTime">
                                                                    <div class="form-group" style="position: relative;z-index: 10;">
                                                                        <div class="col-md-2 pl30 pt10"><?php echo $LANG['UI_PLATFORM_THIRD_PUSH_TIME_TYPE'];?></div>
                                                                        <div class="col-md-4">
                                                                            <select class="volcdp-time-range-select form-control select2me" id="changeTimeInterval">
                                                                                <option value="1" selected><?php echo $LANG['UI_VOL_CDP_RECENT_TENMINS'];?></option>
                                                                                <option value="2"><?php echo $LANG['UI_VOL_CDP_RECENT_ONEHOUR'];?></option>
                                                                                <option value="3"><?php echo $LANG['UI_DB_CDP_DATA_DISASTER_RECOVERY_RECENT_ONEDAY'];?></option>
                                                                                <option value="4"><?php echo $LANG['UI_REPORT_RECENT_WEEK'];?></option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div id="RecoveryTimeline" style="width: 880px;height:300px;"></div>
                                                                </div>
                                                                <div class="tab-pane" id="transactionInfo">
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <div class="portlet-body">
                                                                            </div>
                                                                            <table id="transactionInfoTable">
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
                            <!-- BEGIN MODAL -->
                            <!-- END MODAL -->
                            <!-- popover stard -->
                            <!-- PopoverX content -->
                            <!-- popover end -->
                            <div id="eventDetailModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first"
                                 data-backdrop="static">
                                <div class="modal-header ">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                                    <h4 class="modal-title"><i class="viconfont vicon-ge_configuration"></i> <?php echo $LANG['UI_DB_CDP_RECOVER_EVENT_DETAILS'];?>
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
                    <!-- END PAGE CONTENT-->


<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<?php
if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
echo '<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>';} ?>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
<script type="text/javascript" src="./scripts/plugins/popModal/popModal.js"></script>
 <script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/dbcdp/dbcdp_data_manager.js"></script>
<!-- END PAGE LEVEL PLUGINS -->