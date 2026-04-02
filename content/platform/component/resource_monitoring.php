<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<link rel="stylesheet" type="text/css" href="./css/volcdp/stop_cache.css" />
<div class="col-md-12 high_source_config" style="overflow-y:scroll;padding-top: 5px;">
    <div class="advanced-conf-title-icon floatl mt2 linux_cache_config" style="border-radius: 4px !important;"></div>
    <div class="floatl linux_cache_config">
        <span class="ms-12"><?php echo $LANG['UI_CM_CDP_START_STOP_CONDITION_SET'];?></span>
    </div>
    <!-- 停止任务阈值 -->
    <div class="form-group file_cache_div windows_cache_config">
        <label class="control-label col-md-3 stoptasklable" style="padding-top: 7px;"><?php echo $LANG['UI_CM_CDP_BACKUP_STOP_TASK_THRESHOLD']; ?></label>
        <div class="col-md-9">
            <div class="takeover_app_interval_div ">
                <div class="stoptaskthreshold">
                    <div class="input-group spinner-group width140 ">
                        <input type="number" id="stopTaskThresholdVal" min="4" max = "99" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" value="10">
                        <div class="spinner-buttons input-group-btn spinner-group-btn">
                            <button type="button" class="btn default spinner-up input-sm" id="stopTaskThAddButton">
                                <i class="fa fa-angle-up"></i>
                            </button>
                            <button type="button" class="btn default spinner-down input-sm" id="stopTaskThReduceButton">
                                <i class="fa fa-angle-down"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="ms-8">
                    <select class="form-control " id="stopTaskThresholdTypeSelect">
                        <option value=1 selected> %  <?php echo "(" . $LANG['UI_CM_CDP_BACKUP_PERCENTAGE'] . ")"; ?></option>
                        <option value=2 > MB  <?php echo " (" . $LANG['UI_CM_CDP_DESIGNATED_VALUE'] . ")"; ?></option>
                        <option value=3 > GB  <?php echo " (" . $LANG['UI_CM_CDP_DESIGNATED_VALUE'] . ")"; ?></option>

                    </select>
                </div>

            </div>
            <span class = "help-block "><?php echo $LANG['UI_CM_CDP_DEMOTION_PROTECTION_CONF_TIPS']; ?></span>
        </div>
    </div>
    <!-- 停止任务设置 -->
    <div class="form-group stop_task_div linux_cache_config" style="clear:both;margin-top:35px;">
        <label class="control-label col-md-3 stoptasklable2"><?php echo $LANG['UI_CM_CDP_START_STOP_CONDITION']?></label>
        <div class="col-md-9">
            <div class="stop-item-block1">
                <input type="checkbox" data-checkbox="icheckbox_square-blue" class="icheck" name="stop_task_condition1" id="stop_task_condition1" value="1" checked disabled>
                <div class="stop-item-block1-div">
                    <span style="display: inline-block">
                        <span class="stop-item-block1-div-1"><?php echo $LANG['UI_CM_CDP_STOP_CONDITION_USED_MEM_MAX']?></span>
                        <div class=" stop-item-block1-div-1-1 stop-item-block1-percent">
                            <div class="input-group spinner-group">
                                <input type="number" id="stop_task_condition1_min" min="1" max="100" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" value="90">
                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                    <button type="button" class="btn default spinner-up input-sm" id="stopTaskThAddButton">
                                        <i class="fa fa-angle-up"></i>
                                    </button>
                                    <button type="button" class="btn default spinner-down input-sm" id="stopTaskThReduceButton">
                                        <i class="fa fa-angle-down"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <span class="stop-item-block1-div-2">%</span>
                     </span>
                    <span style="display: inline-block">
                        <span class="stop-item-block1-div-3"><?php echo $LANG['UI_CM_CDP_STOP_CONDITION_DURATION_MAX']?></span>
                        <div class=" stop-item-block1-div-3-1">
                            <div class="input-group spinner-group ">
                                <input type="number" id="stop_task_condition1_interval" min="1" max="999" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" value="30">
                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                    <button type="button" class="btn default spinner-up input-sm">
                                        <i class="fa fa-angle-up"></i>
                                    </button>
                                    <button type="button" class="btn default spinner-down input-sm">
                                        <i class="fa fa-angle-down"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <span class="stop-item-block1-div-4"><?php echo $LANG['WEB_UTILS_SECOND']?></span>
                    </span>
                </div>
            </div>

            <div class="stop-item-block1" style="margin-top: 4px;">
                <input type="checkbox" data-checkbox="icheckbox_square-blue" class="icheck" name="stop_task_condition2" id="stop_task_condition2" value="2">
                <div class="stop-item-block1-div">
                    <span style="display: inline-block">
                         <span class="stop-item-block1-div-1"><?php echo $LANG['UI_CM_CDP_STOP_CONDITION_IO_MAX']?></span>
                        <div class=" stop-item-block1-div-1-1 stop-item-block1-seconds">
                            <div class="input-group spinner-group">
                                <input type="number" id="stop_task_condition2_num" min="1" max="999" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" value="150">
                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                    <button type="button" class="btn default spinner-up input-sm">
                                        <i class="fa fa-angle-up"></i>
                                    </button>
                                    <button type="button" class="btn default spinner-down input-sm">
                                        <i class="fa fa-angle-down"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                       <span class="stop-item-block1-div-2"><?php echo $LANG['UI_CM_CDP_STOP_CONDITION_MILLOW']?></span>
                    </span>
                    <span style="display: inline-block">
                       <span class="stop-item-block1-div-3"><?php echo $LANG['UI_CM_CDP_STOP_CONDITION_DURATION_MAX']?></span>
                        <div class = " stop-item-block1-div-3-1">
                            <div class="input-group spinner-group">
                                <input type="number" id="stop_task_condition2_interval" min="4" max = "99" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" value="30">
                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                    <button type="button" class="btn default spinner-up input-sm">
                                        <i class="fa fa-angle-up"></i>
                                    </button>
                                    <button type="button" class="btn default spinner-down input-sm">
                                        <i class="fa fa-angle-down"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <span class="stop-item-block1-div-4"><?php echo $LANG['WEB_UTILS_SECOND']?></span>
                    </span>
                </div>
            </div>

            <div class="stop-item-block1" style="margin-top: 4px;">
                <input type="checkbox" data-checkbox="icheckbox_square-blue" class="icheck" name="stop_task_condition3" id="stop_task_condition3" value="3">
                <div class="stop-item-block1-div">
                    <span style="display: inline-block">
                        <span class="stop-item-block1-div-1"><?php echo $LANG['UI_CM_CDP_PARSE_CONDITION_CPU_MAX']?></span>
                        <div class=" stop-item-block1-div-1-1 stop-item-block1-percent">
                            <div class="input-group spinner-group">
                                <input type="number" id="stop_task_condition3_num" min="1" max="999" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" value="95">
                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                    <button type="button" class="btn default spinner-up input-sm">
                                        <i class="fa fa-angle-up"></i>
                                    </button>
                                    <button type="button" class="btn default spinner-down input-sm">
                                        <i class="fa fa-angle-down"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <span class="stop-item-block1-div-2">%</span>
                    </span>
                    <span style="display: inline-block">
                       <span class="stop-item-block1-div-3"><?php echo $LANG['UI_CM_CDP_STOP_CONDITION_DURATION_MAX']?></span>
                        <div class = " stop-item-block1-div-3-1">
                            <div class="input-group spinner-group">
                                <input type="number" id="stop_task_condition3_interval" min="4" max = "99" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" value="30">
                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                    <button type="button" class="btn default spinner-up input-sm">
                                        <i class="fa fa-angle-up"></i>
                                    </button>
                                    <button type="button" class="btn default spinner-down input-sm">
                                        <i class="fa fa-angle-down"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <span class="stop-item-block1-div-4"><?php echo $LANG['WEB_UTILS_SECOND']?></span>
                    </span>
                </div>
            </div>

        </div>
    </div>
    <div class="form-group vertical-dashed-line" style="clear: both;"></div>
    <!-- 持续数据保护降级-->
    <div class="form-group cdpdemotiondiv display-none">
        <label class="control-label col-md-3 cmCdpdemotionlable form-group-label"><?php echo $LANG['UI_CM_CDP_DEMOTION_CONF']; ?></label>
        <div class="col-md-9 form-group-content">
            <input type="checkbox" id="cmCdpDemotionSwitchResources" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" checked="checked" data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>">
            <span class = "help-block margintop-10"><?php echo $LANG['UI_CM_CDP_DEMOTION_CONF_TIPS']; ?></span>
        </div>
    </div>
    <!-- 持续数据保护降级阈值-->
    <div class="form-group mt45 windows_cache_config  cdpdemotionthresholdvaluediv">
        <label class="control-label col-md-3 cdpdemotionthresholdvaluelable form-group-label" style="padding-top: 7px;"><?php echo $LANG['UI_CM_CDP_DEMOTION_CONF_VALUE']; ?></label>
        <div class="col-md-9">
            <div class="takeover_app_interval_div ">
                <div class="cdpdemotionthreshold">
                    <div class="input-group spinner-group width140 ">
                        <input type="number" id="cdpDemotionThresholdValue" min="5"  max="100" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" value="20">
                        <div class="spinner-buttons input-group-btn spinner-group-btn">
                            <button type="button" class="btn default spinner-up  input-sm" id="cdpDemotionThValueAddButton">
                                <i class="fa fa-angle-up"></i>
                            </button>
                            <button type="button" class="btn default spinner-down input-sm" id="cdpDemotionThValueReduceButton">
                                <i class="fa fa-angle-down"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="ms-8">
                    <select class="form-control " id="cdpDemotionThresholdValueType">
                        <option value=1 selected> %  <?php echo "(" . $LANG['UI_CM_CDP_BACKUP_PERCENTAGE'] . ")"; ?></option>
                        <option value=2 > MB  <?php echo " (" . $LANG['UI_CM_CDP_DESIGNATED_VALUE'] . ")"; ?></option>
                        <option value=3 > GB  <?php echo " (" . $LANG['UI_CM_CDP_DESIGNATED_VALUE'] . ")"; ?></option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- 恢复持续数据保护监控间隔 -->
    <div class="form-group recoverycdpintervaldiv windows_cache_config" style="clear: both;">
        <label class="control-label col-md-3 recoverycdpintervallable form-group-label pl0_en" style="padding-top: 7px;"><?php echo $LANG['UI_CM_CDP_RESTORE_CDP_PROTECTION_INTERVAL']; ?></label>
        <div class="col-md-9">
            <div class="takeover_app_interval_div ">
                <div class="input-group spinner-group recoverycdpinterval width140">
                    <input type="number" id="recoveryCdpintervalValue" min="5" max="60"  onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" value="30">
                    <div class="spinner-buttons input-group-btn spinner-group-btn">
                        <button type="button" class="btn default  spinner-up input-sm" id="recoveryCdpintervalAddButton">
                            <i class="fa fa-angle-up"></i>
                        </button>
                        <button type="button" class="btn default spinner-down  input-sm" id="recoveryCdpintervalReduceButton">
                            <i class="fa fa-angle-down"></i>
                        </button>
                    </div>
                </div>
                <span class="control-label threadnumlabel"><?php echo $LANG['WEB_UTILS_MINUTE']; ?> </span>
            </div>
            <span class = "help-block "><?php echo $LANG['UI_CM_CDP_RESTORE_CDP_PROTECTION_INTERVAL_TIPS']; ?></span>
        </div>
    </div>

    <div class="advanced-conf-title-icon floatl mt2 linux_cache_config" style="border-radius: 4px !important;"></div>
    <div class="floatl linux_cache_config">
        <span class="ms-12"><?php echo $LANG['UI_CM_CDP_STOP_CHANGE_CONDITION_SET'];?></span>
    </div>

    <!-- 固定时间段 -->
    <div class="form-group cdpdemotiontimediv linux_cache_config" style="margin-bottom:0px;clear:both;margin-top:55px;">
        <div class="control-label col-md-3 form-group-label cdpdemotionresourcediv3-label" style="padding-top: 7px;"><?php echo $LANG['UI_CM_CDP_STOP_TIME_SET']?></div>
        <div class="col-md-9" style="line-height:30px;">
            <input type="checkbox" id="cmCdpDemotionSwitchTime" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>">
            <span class="help-block" style="margin-top:-4px;margin-bottom:-4px;"><?php echo $LANG['UI_CM_CDP_STOP_TIME_SET_TIPS']?></span>
        </div>
    </div>

    <!-- 时间点的具体配置 -->
    <div class="form-group cdpdemotiontimesetdiv display-none">
        <label class="control-label col-md-3 form-group-label"></label>
        <div class="col-md-9">
            <div class="interval_time_item">
                <span class="interval_time_item-1"><?php echo $LANG['UI_JOB_START_TIME']?></span>
                <div class=" interval_time_item-2 form-group-content">
                    <div class="input-group">
                        <input type="text" name="stop_task_condition_time" value="10:00:00" class="form-control timepicker timepicker-24 stop_task_condition_time">
                        <span class="input-group-btn">
                            <button class="btn default btn-time"  type="button"><i class="viconfont vicon-beifenshijiandian"></i></button>
                        </span>
                    </div>
                </div>
                <span class="interval_time_item-3"><?php echo $LANG['UI_CM_CDP_STOP_TIME_SET_DURATION']?></span>
                <div class=" interval_time_item-4">
                    <div class="input-group spinner-group">
                        <input type="number" name="stop_task_condition_time_interval" min="1" max="999" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm stop_task_condition_time_interval" value="30">
                        <div class="spinner-buttons input-group-btn spinner-group-btn">
                            <button type="button" class="btn default spinner-up input-sm"><i class="fa fa-angle-up"></i></button>
                            <button type="button" class="btn default spinner-down input-sm"><i class="fa fa-angle-down"></i></button>
                        </div>
                    </div>
                </div>
                <div class="ms-8 interval_time_item-5">
                    <select class="form-control" name="stop_task_condition_time_type">
                        <option value=1 selected><?php echo $LANG['WEB_UTILS_MINUTE']?></option>
                        <option value=2 ><?php echo $LANG['WEB_UTILS_HOUR']?></option>
                    </select>
                </div>
            </div>

            <div class="task_time_block">

            </div>
            <span class="help-block margintop-10" style="margin-top: 12px;"><?php echo $LANG['UI_CM_CDP_STOP_TIME_SET_TIPS2']?></span>
            <div style="margin-top: 12px;"><a id="addMoreTime"><i class="viconfont vicon-zhediekuanganniu"></i><?php echo $LANG['UI_CM_CDP_STOP_TIME_SET_ADD']?></a></div>
        </div>
    </div>

    <div class="form-group cdpdemotiontimediv linux_cache_config" style="margin-top:20px;">
        <div class="control-label col-md-3 form-group-label cdpdemotionresourcediv3-label" style="padding-top: 7px;"><?php echo $LANG['UI_CM_CDP_STOP_SOURCE_CONDITION']?></div>
        <div class="col-md-9 cdpdemotionresourcediv-label" style="line-height:30px;">
            <?php echo $LANG['UI_CM_CDP_PARSE_CONDITION']?>
        </div>
    </div>

    <!-- 任务暂停持续数据保护条件 -->
    <div class="form-group cdpdemotionresourcediv linux_cache_config" style="clear: both;">
        <label class="control-label col-md-3 form-group-label"></label>
        <div class="col-md-9">
            <div class="stop-item-block1">
                <input type="checkbox" data-checkbox="icheckbox_square-blue" class="icheck" id="stop_task_condition_source1" value="1" checked disabled>
                <div class="stop-item-block1-div">
                    <span style="display: inline-block">
                        <span class="stop-item-block1-div-1"><?php echo $LANG['UI_CM_CDP_PARSE_CONDITION_USED_MAX']?></span>
                        <div class=" stop-item-block1-div-1-1 stop-item-block1-percent">
                              <div class="input-group spinner-group">
                               <input type="number" id="stop_task_condition_source1_num" min="4" max="100" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" value="50">
                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                   <button type="button" class="btn default spinner-up input-sm"><i class="fa fa-angle-up"></i></button>
                                   <button type="button" class="btn default spinner-down input-sm"><i class="fa fa-angle-down"></i></button>
                                </div>
                             </div>
                        </div>
                        <span class="stop-item-block1-div-2">%</span>
                    </span>
                </div>
            </div>

            <div class="stop-item-block1" style="margin-top: 4px;">
                <input type="checkbox" data-checkbox="icheckbox_square-blue" class="icheck" id="stop_task_condition_source2" value="2" checked disabled>

                <div class="stop-item-block1-div">
                        <span style="display: inline-block">
                            <span class="stop-item-block1-div-1"><?php echo $LANG['UI_CM_CDP_STOP_CONDITION_USED_MEM_MAX']?></span>
                            <div class=" stop-item-block1-div-1-1 stop-item-block1-percent">
                                  <div class="input-group spinner-group ">
                                   <input type="number" id="stop_task_condition_source2_num" min="1" max="100" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" value="80">
                                    <div class="spinner-buttons input-group-btn spinner-group-btn">
                                       <button type="button" class="btn default spinner-up input-sm"><i class="fa fa-angle-up"></i></button>
                                       <button type="button" class="btn default spinner-down input-sm"><i class="fa fa-angle-down"></i></button>
                                    </div>
                                 </div>
                            </div>
                            <span class="stop-item-block1-div-2">%</span>
                        </span>
                    <span style="display: inline-block">
                        <span class="stop-item-block1-div-3"><?php echo $LANG['UI_CM_CDP_STOP_CONDITION_DURATION_MAX']?></span>
                        <div class = " stop-item-block1-div-3-1">
                           <div class="input-group spinner-group">
                                <input type="number" id="stop_task_condition_source2_interval" min="1" max="999" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" value="30">
                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                    <button type="button" class="btn default spinner-up input-sm" id="stopTaskThAddButton"><i class="fa fa-angle-up"></i></button>
                                    <button type="button" class="btn default spinner-down input-sm" id="stopTaskThReduceButton"><i class="fa fa-angle-down"></i></button>
                                </div>
                            </div>
                        </div>
                        <span class="stop-item-block1-div-4"><?php echo $LANG['WEB_UTILS_SECOND']?></span>
                    </span>
                </div>
            </div>

            <div class="stop-item-block1" style="margin-top: 4px;">
                <input type="checkbox" data-checkbox="icheckbox_square-blue" class="icheck" id="stop_task_condition_source3" value="3" checked>
                <div class="stop-item-block1-div">
                    <span style="display: inline-block">
                        <span class="stop-item-block1-div-1"><?php echo $LANG['UI_CM_CDP_STOP_CONDITION_IO_MAX']?></span>
                        <div class=" stop-item-block1-div-1-1 stop-item-block1-seconds">
                              <div class="input-group spinner-group">
                               <input type="number" id="stop_task_condition_source3_num" min="1" max="999" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" value="80">
                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                   <button type="button" class="btn default spinner-up input-sm"><i class="fa fa-angle-up"></i></button>
                                   <button type="button" class="btn default spinner-down input-sm"><i class="fa fa-angle-down"></i></button>
                                </div>
                             </div>
                        </div>
                        <span class="stop-item-block1-div-2"><?php echo $LANG['UI_CM_CDP_STOP_CONDITION_MILLOW']?></span>
                    </span>
                    <span style="display: inline-block">
                        <span class="stop-item-block1-div-3"><?php echo $LANG['UI_CM_CDP_STOP_CONDITION_DURATION_MAX']?></span>
                        <div class = " stop-item-block1-div-3-1">
                           <div class="input-group spinner-group ">
                                <input type="number" id="stop_task_condition_source3_interval" min="1" max="999" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" value="30">
                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                    <button type="button" class="btn default spinner-up input-sm" id="stopTaskThAddButton"><i class="fa fa-angle-up"></i></button>
                                    <button type="button" class="btn default spinner-down input-sm" id="stopTaskThReduceButton"><i class="fa fa-angle-down"></i></button>
                                </div>
                            </div>
                        </div>
                        <span class="stop-item-block1-div-4"><?php echo $LANG['WEB_UTILS_SECOND']?></span>
                    </span>
                </div>
            </div>

            <div class="stop-item-block1" style="margin-top: 4px;">
                <input type="checkbox" data-checkbox="icheckbox_square-blue" class="icheck" id="stop_task_condition_source4" value="4" checked>
                <div class="stop-item-block1-div">
                    <span style="display: inline-block">
                        <span class="stop-item-block1-div-1"><?php echo $LANG['UI_CM_CDP_PARSE_CONDITION_CPU_MAX']?></span>
                        <div class=" stop-item-block1-div-1-1 stop-item-block1-percent">
                            <div class="input-group spinner-group">
                                <input type="number" id="stop_task_condition_source4_num" min="1" max="999" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" value="90">
                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                    <button type="button" class="btn default spinner-up input-sm"><i class="fa fa-angle-up"></i></button>
                                    <button type="button" class="btn default spinner-down input-sm"><i class="fa fa-angle-down"></i></button>
                                </div>
                            </div>
                        </div>
                        <span class="stop-item-block1-div-2">%</span>
                    </span>
                    <span style="display: inline-block">
                        <span class="stop-item-block1-div-3"><?php echo $LANG['UI_CM_CDP_STOP_CONDITION_DURATION_MAX']?></span>
                        <div class = " stop-item-block1-div-3-1">
                            <div class="input-group spinner-group ">
                                <input type="number" id="stop_task_condition_source4_interval" min="1" max="999" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" value="30">
                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                    <button type="button" class="btn default spinner-up input-sm" id="stopTaskThAddButton"><i class="fa fa-angle-up"></i></button>
                                    <button type="button" class="btn default spinner-down input-sm" id="stopTaskThReduceButton"><i class="fa fa-angle-down"></i></button>
                                </div>
                            </div>
                        </div>
                        <span class="stop-item-block1-div-4"><?php echo $LANG['WEB_UTILS_SECOND']?></span>
                    </span>
                </div>
            </div>
            <div class="help-block"><?php echo $LANG['UI_CM_CDP_PARSE_CONDITION_ONE']?></div>
        </div>
    </div>
    <div class="form-group cdpdemotiontimediv linux_cache_config">
        <div class="control-label col-md-3 form-group-label cdpdemotionresourcediv3-label"></div>
        <div class="col-md-9 cdpdemotionresourcediv2-label" style="line-height:30px;">
            <?php echo $LANG['UI_CM_CDP_RECOVERY_CONDITION']?>
        </div>
    </div>

    <!-- 任务恢复持续数据保护条件 -->
    <div class="form-group cdpdemotionresourcediv2 linux_cache_config" style="clear: both;">
        <label class="control-label col-md-3 form-group-label "></label>
        <div class="col-md-9">
            <div class="stop-item-block1">
                <input type="checkbox" data-checkbox="icheckbox_square-blue" class="icheck" id="recover_task_condition_source1" value="1" checked disabled>
                <div class="stop-item-block1-div">
                    <span style="display: inline-block">
                        <span class="stop-item-block1-div-1"><?php echo $LANG['UI_CM_CDP_RECOVERY_CONDITION_USED_MEM_MIN']?></span>
                        <div class=" stop-item-block1-div-1-1 stop-item-block1-percent">
                              <div class="input-group spinner-group ">
                               <input type="number" id="recover_task_condition_source1_num" min="1" max="100" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" value="60">
                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                   <button type="button" class="btn default spinner-up input-sm"><i class="fa fa-angle-up"></i></button>
                                   <button type="button" class="btn default spinner-down input-sm"><i class="fa fa-angle-down"></i></button>
                                </div>
                             </div>
                        </div>
                        <span class="stop-item-block1-div-2">%</span>
                    </span>
                    <span style="display: inline-block">
                        <span class="stop-item-block1-div-3"><?php echo $LANG['UI_CM_CDP_STOP_CONDITION_DURATION_MAX']?></span>
                        <div class=" stop-item-block1-div-3-1">
                           <div class="input-group spinner-group ">
                                <input type="number" id="recover_task_condition_source1_interval" min="1" max="999" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" value="30">
                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                    <button type="button" class="btn default spinner-up input-sm" id="stopTaskThAddButton"><i class="fa fa-angle-up"></i></button>
                                    <button type="button" class="btn default spinner-down input-sm" id="stopTaskThReduceButton"><i class="fa fa-angle-down"></i></button>
                                </div>
                            </div>
                        </div>
                        <span class="stop-item-block1-div-4"><?php echo $LANG['WEB_UTILS_MINUTE']?></span>
                    </span>
                </div>
            </div>
            <div class="stop-item-block1" style="margin-top: 4px;">
                <input type="checkbox" data-checkbox="icheckbox_square-blue" class="icheck" id="recover_task_condition_source2" value="2" checked>
                <div class="stop-item-block1-div">
                    <span style="display: inline-block">
                        <span class="stop-item-block1-div-1"><?php echo $LANG['UI_CM_CDP_RECOVERY_CONDITION_IO_MIN']?></span>
                        <div class=" stop-item-block1-div-1-1 stop-item-block1-seconds">
                            <div class="input-group spinner-group ">
                               <input type="number" id="recover_task_condition_source2_num" min="1" max="999" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" value="50">
                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                   <button type="button" class="btn default spinner-up input-sm"><i class="fa fa-angle-up"></i></button>
                                   <button type="button" class="btn default spinner-down input-sm"><i class="fa fa-angle-down"></i></button>
                                </div>
                            </div>
                        </div>
                        <span class="stop-item-block1-div-2"><?php echo $LANG['UI_CM_CDP_STOP_CONDITION_MILLOW']?></span>
                    </span>
                    <span style="display: inline-block">
                        <span class="stop-item-block1-div-3"><?php echo $LANG['UI_CM_CDP_STOP_CONDITION_DURATION_MAX']?></span>
                        <div class = " stop-item-block1-div-3-1">
                           <div class="input-group spinner-group ">
                                <input type="number" id="recover_task_condition_source2_interval" min="1" max = "999" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" value="5">
                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                    <button type="button" class="btn default spinner-up input-sm"><i class="fa fa-angle-up"></i></button>
                                    <button type="button" class="btn default spinner-down input-sm"><i class="fa fa-angle-down"></i></button>
                                </div>
                            </div>
                        </div>
                        <span class="stop-item-block1-div-4"><?php echo $LANG['WEB_UTILS_MINUTE']?></span>
                    </span>
                </div>
            </div>
            <div class="stop-item-block1" style="margin-top: 4px;">
                <input type="checkbox" data-checkbox="icheckbox_square-blue" class="icheck" id="recover_task_condition_source3" value="3" checked>
                <div class="stop-item-block1-div">
                    <span style="display: inline-block">
                        <span class="stop-item-block1-div-1"><?php echo $LANG['UI_CM_CDP_RECOVERY_CONDITION_CPU_MIN']?></span>
                        <div class=" stop-item-block1-div-1-1 stop-item-block1-percent">
                            <div class="input-group spinner-group ">
                                <input type="number" id="recover_task_condition_source3_num" min="1" max="999" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" value="60">
                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                    <button type="button" class="btn default spinner-up input-sm"><i class="fa fa-angle-up"></i></button>
                                    <button type="button" class="btn default spinner-down input-sm"><i class="fa fa-angle-down"></i></button>
                                </div>
                            </div>
                        </div>
                        <span class="stop-item-block1-div-2">%</span>
                    </span>
                    <span style="display: inline-block">
                        <span class="stop-item-block1-div-3"><?php echo $LANG['UI_CM_CDP_STOP_CONDITION_DURATION_MAX']?></span>
                        <div class = " stop-item-block1-div-3-1">
                            <div class="input-group spinner-group ">
                                <input type="number" id="recover_task_condition_source3_interval" min="1" max = "999" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" value="5">
                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                    <button type="button" class="btn default spinner-up input-sm"><i class="fa fa-angle-up"></i></button>
                                    <button type="button" class="btn default spinner-down input-sm"><i class="fa fa-angle-down"></i></button>
                                </div>
                            </div>
                        </div>
                        <span class="stop-item-block1-div-4"><?php echo $LANG['WEB_UTILS_MINUTE']?></span>
                    </span>
                </div>
            </div>
            <div class="help-block"><?php echo $LANG['UI_CM_CDP_RECOVERY_CONDITION_ALL']?></div>
        </div>
    </div>

</div>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/platform/component/resource_monitoring.js"></script>
