<?php
include_once '../public/bs_table.php';
?>
<link rel="stylesheet" type="text/css" href="./css/platform/component/vol_cdp_backup_set_info.css" />
<!-- BEGIN PAGE LEVEL STYLES -->
 
<div class="panel-body progress height100p vol_cdp_backup_set_data_info">
    <div class="col-md-12">
        <label class="  diynodelabel backup-set-time-range-lable floatl mt-0"><?php echo $LANG['UI_CM_CDP_BACKUP_SET']; ?></label>
        <div class="col-md-4 " > 
            <select class="form-control select2me" id="volCdpBackupSetRange">
                <!-- <option value="1" selected><?php echo $LANG['UI_VOL_CDP_RECENT_10MINS']; ?></option> -->
            </select>
        </div>
        <div class="col-md-1 mt8">
            <a class="popovers ml-10 " data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_CM_CDP_BACKUP_SET_TIPS']; ?>">
                <i class="viconfont vicon-tishi"></i>
            </a>
        </div>
    </div>

    <div class="col-md-12 accordion mt20 h-500px conftimepointmap">
        <div class="panel panel-default strategy-panel hp-100">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a class="accordion-toggle accordion-toggle-styled  popovers volcdpbackupsetcollapseview" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".confrecoverytime" href="#recovery_timepoint_view" aria-expanded="true">
                        <i class="viconfont vicon-ge_vm font-green-seagreen"></i>
                        <span class="font-green-seagreen"><?php echo $LANG['UI_VOL_CDP_TIME_FRAME']; ?></span>
                        <span class=" timerange fs12">--</span>

                        <!-- <span class="control-label colorgreen fs12"><?php echo $LANG['UI_VOL_CDP_CONFIGURE_TIME']; ?></span>
                        <span class="strategyDes fs12 recoveryTimepointDes">--</span> -->
                    </a>
                </h4>
            </div>
            <div id="recovery_timepoint_view" class="panel-collapse collapse in volcdpbackupsetcollapseview min-height430">

                    <div id="recovery_timepoint_view" class="panel-collapse collapse in volcdpbackupsetcollapseview">
                        <div class="panel-body mb-15">
                            <div class="nav-tabs-wrapper mb-15">
                                <ul class="nav nav-tabs nav-line-tabs task_data_timepoint_type " id="">
                                    <!-- 任务数据的任意时间点-->
                                    <li class="active nav-item task_data_any_time" > 
                                        <a href="#vol_cdp_task_data_any_time" class="nav-link" data-toggle="tab" aria-expanded="true">
                                            <i class="viconfont vicon-renyishijiandian"></i> <?php echo $LANG['UI_VOL_CDP_ANY_TIME_POINT']; ?> </a>
                                    </li>

                                    <!-- 任务数据标签点-->
                                    <li class="nav-item task_data_tag_point">
                                        <a href="#vol_cdp_task_data_tag_point" class="nav-link" data-toggle="tab" aria-expanded="false">
                                            <i class="viconfont vicon-ge_tag_point"></i> <?php echo $LANG['UI_VOL_CDP_LABEL_POINTS']; ?> </a>
                                    </li>
                                    
                                    <li class="nav-item cm_cdp_task_data_safe_point">
                                        <a href="#cm_cdp_task_data_safe_point" class="nav-link" data-toggle="tab" aria-expanded="false">
                                            <i class="viconfont vicon-yanzhengpeizhi"></i> <?php echo $LANG['UI_BACKUP_DATA_LABEL_VIRUS_HISTORY']; ?> </a>
                                    </li>
                                    <!-- 任务数据的验证点-->
                                    <li class="nav-item cm_cdp_task_data_verify_point">
                                        <a href="#cm_cdp_task_data_verify_point" class="nav-link" data-toggle="tab" aria-expanded="false">
                                            <i class="viconfont vicon-yanzhengpeizhi"></i> <?php echo $LANG['UI_BACKUP_DATA_LABEL_DATA_VERIFY']; ?> </a>
                                    </li>
                                </ul>
                                <div class="tab-content border-bottom-none">
                            
                                    <div class="tab-pane active " id="vol_cdp_task_data_any_time">
                                        <div class="portlet-body min-height280 hp-100" id="volCdpbackupSetTabPortletBody">
                                            <div class="form-group" style="margin-bottom: 10px;">
                                                <div class="col-md-12">
                                                    <label class="  diynodelabel backup-set-time-range-lable floatl mt-0"><?php echo $LANG['UI_VOL_CDP_TIME_FRAME']; ?></label>
                                                    <div class="col-md-3 " > 
                                                        <select class="volcdp-time-range-select " id="changeTimeInterval">
                                                            <!-- <option value="1" selected><?php echo $LANG['UI_VOL_CDP_RECENT_10MINS']; ?></option> -->
                                                            <option value="2" selected><?php echo $LANG['UI_VOL_CDP_RECENT_ONEHOUR'] ?></option>
                                                            <option value="3"><?php echo $LANG['UI_TENANT_HOME_HISTORY_PROTECT1']; ?></option>
                                                            <option value="4"><?php echo $LANG['UI_TENANT_HOME_HISTORY_PROTECT7']; ?></option>
                                                            <option value="5"><?php echo $LANG['UI_TENANT_HOME_HISTORY_PROTECT30']; ?></option>
                                                            <option value="6"><?php echo $LANG['UI_VOL_CDP_RECENT_90dayes']; ?></option>
                                                            <option value="7"><?php echo $LANG['UI_VOL_CDP_TIME_RANGE']; ?></option>
                                                        </select>
                                                    </div>
                                                            <!-- 自定义时间点范围 -->
                                                    <div class="form-group display-hide selectrecoverytimeview col-md-8 ml-15" style="margin-bottom: 0;">
                                                        <div class="col-md-12 time-select-group " style="z-index: 99999;">
                                                            <div class="time-select-group__item">
                                                                <div class="input-group date width200 starttimeview">
                                                                    <input type="text" style="display:none;">
                                                                    <input type="text" placeholder="<?php echo $LANG['UI_VOL_CDP_CONFIGURE_START_TIME']; ?>" size="16" class="form-control input-sm volCdpBackupSetStartTime">
                                                                    <span class="input-group-btn">
                                                                        <button class="btn default date-set input-sm-vol-cdp" type="button"><i class="viconfont vicon-ge_calendar"></i></button>
                                                                    </span>
                                                                </div>
                                                            </div>

                                                            <div class="time-select-group__item">
                                                                <div class="input-group date width200 endtimeview">
                                                                    <input type="text" style="display:none;">
                                                                    <input type="text" placeholder="<?php echo $LANG['UI_VOL_CDP_CONFIGURE_END_TIME']; ?>" size="16" id="" class="form-control input-sm volCdpBackupSetEndTime">
                                                                    <span class="input-group-btn">
                                                                        <button class="btn default date-set input-sm-vol-cdp inputendtime" id='inputendtime' type="button"><i class="viconfont vicon-ge_calendar"></i></button>
                                                                    </span>
                                                                </div>
                                                            </div> 

                                                            <div class="time-select-group__btns">
                                                                <button class="btn default input-sm" id="resetSelectTimePoint" type="button" title=<?php echo $LANG['UI_VOL_CDP_RESET']; ?>> <i class="fa fa-times"></i></button>
                                                                <button class="btn default input-sm green-turquoise confirmSelectTimePoint" id="" type="button" title=<?php echo $LANG['UI_VOL_CDP_CONFIRM']; ?>><i class="fa fa-check"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- backup set chart -->
                                            <div id="volCdpBackupSetTimeline" class="height280" > </div>

                                           
                                            <div class="col-md-10  pt30">
                                                <label class=" diynodelabel backup-set-time-range-lable floatl mt-0"><?php echo $LANG['UI_VOL_CDP_TIME_TYPE']; ?>:</label>
                                                <label class=" diynodelabel backup-set-time-range-lable floatl mt-0 colorgreen" id = "inputRecoveryTimepoindes"><?php echo $LANG['UI_VOL_CDP_ANY_TIME_POINT']; ?></label>
                                                <!-- <div class="col-md-4 backup-set-time-range-change " > 
                                                    <p class="form-control-static colorgreen" id="inputRecoveryTimepoindes">
                                                        <?php echo $LANG['UI_VOL_CDP_ANY_TIME_POINT']; ?>
                                                    </p>
                                                </div> -->
                                            </div>


                                        </div>
                                    </div>
                                    <!-- 标签点 -->
                                    <div class="tab-pane  p-10 height360" id="vol_cdp_task_data_tag_point">
                                        <div class="table-container" id="taskTagPointDiv" >
                                            <table id="taskTagPointTable" > </table>
                                        </div>
                                    </div>
                                    
                                    <!-- 病毒扫描点 -->
                                    <div class="tab-pane  p-10 height360" id="cm_cdp_task_data_safe_point">
                                        
                                        <div class="table-container" id="cmCdpDataSafePointTableDiv">
                                            <table id="cmCdpDataSafePointTable"> </table>
                                        </div>
                                        <!-- <div class="table-container" id="cmCdpPointVirusListTableDiv">
                                            <table id="cmCdpPointVirusListTable"> </table>
                                        </div> -->
                                    </div>
                                    <!-- 验证点 -->
                                    <div class="tab-pane  p-10 height360" id="cm_cdp_task_data_verify_point">
                                        
                                        <div class="table-container" id="taskVerifyPointDiv">
                                            <table id="taskVerifyPointTable"> </table>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <div class="col-md-12 form-group h-30px mt10">
        <label class="control-label  diynodelabel backup-set-time-range-lable floatl pt-0 backupsettimepoint"><?php echo $LANG['UI_VOL_CDP_RECOVERY_TIME_POINT']; ?></label>
        <div class="col-md-7 form-group-timepoint backupsettimecontrol" style="z-index: 99999;">
            <div class="input-group date backupsettimepointview" >
                <input type="text" style="display:none;">
                <input type="text" size="16" id="" class="form-control input-sm selecttimepoint">
                <span class="input-group-btn">
                    <!-- <button class="btn default input-sm-vol-cdp" id="resetRecoveryTimepoint" type="button"><i class="fa fa-times"></i></button> -->
                    <button class="btn default date-set input-sm-vol-cdp" type="button"><i class="viconfont vicon-ge_calendar"></i></button>
                </span>
            </div>
            <span class="backupsetlockdiv">
                <i class="fa fa-lock backupsetlock backupsetlockview "></i>
            </span>
            <span class="control-label text-danger" id="timePointValidity">
                <?php echo $LANG['UI_VOL_CDP_UNCHECKED']; ?>
            </span>
        </div>
    </div>
</div>

<!-- BEGIN SAFE HISTORY CONTENT DRAWER -->
<div class="drawer slide min-width850" data-placement="right" tabindex="-1" role="dialog" id="dataSafeDetailContentDrawer">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header">
            <h4 class="drawer-title  ml-15">
                <span>
                    <i class="viconfont vicon-jiaobenguanli mr8 ml15"></i>
                </span>
                <?php echo $LANG['UI_CM_CDP_SAFE_SCAN_HISTORY_INFO']; ?>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close"><i class="viconfont vicon-guanbi"></i></span>
            </h4>
        </div>
        <div class="drawer-body">
            <div class="portlet-body" style="height: 100%">
                <div class="form-group" style="height: 20px; margin-bottom: 16px">
                    <div class="table-container" id="cmCdpPointVirusListTablediv">
                        <table id="cmCdpPointVirusListTable"> </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- footer -->
        <div class="drawer-footer">
            <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-primary" id="addsubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
        </div>
    </div>
</div>
<!-- END SCRIPT CONTENT DRAWER -->
<script type="text/javascript" src="./scripts/components/timePointDetail/js/virusHistoryTable.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/platform/component/vol_cdp_backup_set_info.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="./assets/global/plugins/moment.min.js"></script>
<script src="./assets/global/plugins/echarts/echarts.min.js"></script> 
<!-- BEGIN PAGE LEVEL PLUGINS -->
<!-- <script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script> -->

<script>
    /**
     * 初始化卷实时模块备份集时间组件
     * 参数: 
     *      task_uuid:任务UUId
     *      agent_uuid:客户端uuid
     *      create_task_type:创建任务类型接管、恢复
     */
    var params = {
        'task_uuid': '313723b6-d9e3-4f77-b3a2-ea2c5ed19371',
        'agent_uuid': '607a0df9-5ec3-442d-a406-125666f509e8',
        'create_task_type': '34',
        'node_uuid': "8f2d4476-1311-42af-8f49-fddb242d4e39",
    }
    volCdpBackupSetTimeLineInfo.init(params);

    // 获取配置信息 示例
    setTimeout(function(){
        var info = volCdpBackupSetTimeLineInfo.getBackupSetConfigInfo();  //根据实际使用情况，调用该方法
        console.log(info);
    },5000);
    
</script>