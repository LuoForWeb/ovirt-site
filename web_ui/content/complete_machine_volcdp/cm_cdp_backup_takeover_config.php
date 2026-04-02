<!-- <link rel="stylesheet" type="text/css" href="./css/complete/complete.css" /> -->
<div class="panel-body col-md-10 col-md-11_en">		
    <!--  接管开关 -->					
    <div class="form-group encrypttransferdiv">
        <label class="control-label col-md-3 encrypttransferlabel form-group-label takeoverstrategyswitch"><?php echo $LANG['UI_CM_CDP_TAKEOVER_MODE_EMERGENCY']; ?></label>
        <div class="col-md-4 form-group-content">
            <input type="checkbox" id="" class="make-switch backuptasktakeoverswitch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>">
            <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_CM_CDP_BACKUP_TAKEOVER_TIPS']; ?>">
                <i class="viconfont vicon-tishi"></i>
            </a>
        </div>
       
    </div>

    <!-- 接管相关配置 -->
    <div class = "backuptasktakeoverconfdiv display-none">
        <!--  接管方式 -->
        <div class="form-group backuptakeovertypediv">
            <label class="control-label col-md-3 encrypttransferlabel form-group-label"><?php echo $LANG['UI_CM_CDP_TAKEOVER_MODE']; ?></label>		
            <div class="col-md-9 ">
                <div>
                    <label class="control-label takeovermodecmlabel">
                        <input type="radio" name="cm_takeover_mode" class="cmtakeovermode takeovermodecm" value="1" checked /> <?php echo $LANG['UI_VIRTUAL_LAB_EMBEDDED_VM']; ?>
                    </label>
                    
                    <!-- <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_CM_CDP_CM_TAKEOVER_TIPS']; ?>">
                        <i class="viconfont vicon-tishi"></i>
                    </a> -->
                    <label class="control-label ml50 takeovermodemountlabel">
                        <input class="ml15" type="radio" name="cm_takeover_mode" class="cmtakeovermode takeovermodemount" value="2"  /> <?php echo $LANG['UI_COMPLETE_MACHINE']; ?>

                    </label>
                    <!-- <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_CM_CDP_MOUNT_TAKEOVER_TIPS']; ?>">
                        <i class="viconfont vicon-tishi"></i>
                    </a> -->
                </div>
               
                <span class = "help-block cmtakeovermodehelp "><?php echo $LANG['UI_CM_CDP_CM_TAKEOVER_TIPS']; ?></span>	
                <span class = "help-block mounttakeovermodehelp display-none"><?php echo $LANG['UI_CM_CDP_MOUNT_TAKEOVER_TIPS']; ?></span>
            </div>
            
        </div>
        <!-- 接管主备映射配置 -->
        <div class="form-group takeoverHostTostandbymapping ">
            <label class="control-label col-md-3 encrypttransferlabel form-group-label"><?php echo $LANG['UI_CM_CDP_TAKEOVER_HOST_STANDBY_MAP_CONF']; ?></label>	
            <div class="col-md-9 accordion strategyOne">
                <div class="accordion strategyOne store-strategy-form">
                    <div class="panel panel-default strategy-panel">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a class="accordion-toggle accordion-toggle-styled  popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#takeovermappingconf" aria-expanded="true">
                                    <i class="viconfont vicon-jieguanfuwuqi font-green-seagreen"></i>
                                    <span class="font-green-seagreen"><?php echo $LANG['UI_CM_CDP_CONFIG_TIPS'] ?></span>
                                    <span class="strategyDes takeoverStandbyDes"></span>
                                </a>
                            </h4>
                        </div>
                        <div id="takeovermappingconf" class="panel-collapse collapse in">
                            <div class="panel-body">
                                <div class="col-md-12 pl0_en">
                                    <!-- 备机配置 -->
                                    <div class="form-group takeoverstandbyhostconf">
                                        <label class="control-label col-md-2  col-md-3_en"><?php echo $LANG['UI_VOL_CDP_STANDBY']; ?>
                                        </label>
                                        <div class="col-md-9">
                                            <button type="button" class="btn btn-light-primary cmstandbymapconf">
                                                <span class = "font-green-seagreen"><?php echo $LANG['UI_VOL_CDP_STANDBY_CONF_MAP']; ?></span>
                                            </button>
                                            <ul id="" class="pl0 display-none mountTakeoverStandbyConfDes"></ul>
                                            <ul id="cmTakeoverStandbyConfDes" class="pl0 display-none mt-10 cmTakeoverStandbyConfDes"></ul>
                                        </div>
                                    </div>
                                    
                                    <!-- 接管网络 -->
                                    <div class="form-group display-none takeoverNetworkConf">
                                        <label class="control-label col-md-2 applianceselectlabel col-md-3_en"><?php echo $LANG['UI_CM_CDP_TAKEOVER_NET_CONF']; ?></label>
                                        <div class="col-md-9">
                                            <button type="button" class="btn btn-light-primary takeovernetworkconf" id="">
                                                <span class = "font-green-seagreen"><?php echo $LANG['UI_VOL_CDP_TASK_TAKEOVER_NET_CONF']; ?></span>
                                            </button>
                                            <ul id="takeover_ip_map_list" style="padding-left: 0;">

                                            </ul>
                                        </div>
                                    </div>
                                    <!-- 回切通讯IP -->
                                    <div class="form-group takeoverrestoreipview">
                                        <label class="control-label col-md-2 applianceselectlabel col-md-3_en"><?php echo $LANG['UI_CM_CDP_TAKEOVER_FAILBACK_IP_CONF']; ?></label>
                                        <div class="col-md-5">
                                            <input type="text" maxlength="64" class="form-control takeoverfailbackupip" id="" />
                                        </div>
                                        <div class="col-md-1" style="padding-left: 0;">
                                            <div class=" popovers mt8">
                                                <a class="popovers ml-8" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_BACKCUT_COMMUNICATION_IP_TIPS']; ?>">
                                                    <i class="viconfont vicon-tishi"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-md-3 ml-50 ml-r20_en">
                                            <button type="button" class="btn btn-light-primary" id="linkSelectIP">
                                                <span class="font-green-seagreen"><?php echo $LANG['UI_VOL_CDP_TAKEOVER_FAILBACK_IP_PING']; ?></span>
                                            </button>
                                        </div>
                                    </div>


                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <!-- 安全配置 -->
        <div id = "takeoverSafetyConf"></div>  
        
        <!--  应用接管开关 -->					
        <div class="form-group encrypttransferdiv">
            <label class="control-label col-md-3 encrypttransferlabel form-group-label"><?php echo $LANG['UI_VOL_CDP_ENABLE_APP_TAKEOVER']; ?></label>
            <div class="col-md-4 form-group-content">
                <input type="checkbox" id="" class="make-switch apptakeoverswitch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>">
                <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_CM_CDP_ENABLE_APP_TAKEOVER_TIPS']; ?>">
                    <i class="viconfont vicon-tishi"></i>
                </a>
            </div>
        </div>
        <!--  自动接管开关 -->					
        <div class="form-group encrypttransferdiv autotakeoverdiv">
            <label class="control-label col-md-3 encrypttransferlabel form-group-label"><?php echo $LANG['UI_CM_CDP_AUTO_TAKEOVER']; ?></label>
            <div class="col-md-4 form-group-content">
                <input type="checkbox" id="" class="make-switch cmautotakeoverswitch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>">
                <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_CM_CDP_AUTO_TAKEOVER_TIPS']; ?>">
                    <i class="viconfont vicon-tishi"></i>
                </a>
            </div>
        </div>
        <!--  自动接管配置 -->				
        <div class="form-group cmautotakeoverconfdiv display-none">
            <label class="control-label col-md-3 encrypttransferlabel form-group-label"><?php echo $LANG['UI_CM_CDP_AUTO_TAKEOVER_CONF_TITLE']; ?></label>	
            <div class="col-md-9 accordion strategyOne">
                <div class="accordion strategyOne store-strategy-form">
                    <div class="panel panel-default strategy-panel">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a class="accordion-toggle accordion-toggle-styled collapsed popovers" name = "cmAutoTakeoverCollapse" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#autotakeoverconf" aria-expanded="true">
                                    <i class="viconfont vicon-jieguanfuwuqi font-green-seagreen"></i>
                                    <span class="font-green-seagreen"><?php echo $LANG['UI_CM_CDP_CONFIG_TIPS'] ?></span>
                                    <span class="strategyDes takeoverStandbyDes"></span>
                                </a>
                            </h4>
                        </div>

                        <div id="autotakeoverconf" name = "autotakeoverconfview" class="panel-collapse collapse ">
                            <div class="panel-body">
                                <div class="tabbable-custom mx-15">
                                    <ul class="nav nav-tabs">
                                        <li class="commonLi active">
                                            <a href="#tab_autotakeover_common" class="popovers"  data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                <i class="viconfont vicon-tongyongpeizhi"></i> <?php echo $LANG['UI_CM_CDP_COMMON_SETTING'] ?> </a>
                                        </li>
                                        <li class="">
                                            <a href="#tab_takeover_app_conf" class="popovers" data-content="" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                            <i class="viconfont vicon-yingyong"></i> <?php echo $LANG['UI_CM_CDP_APP_SETTING'] ?> </a>
                                        </li>
                                    </ul>
                                    <div class="tab-content">
                                        <!-- 通用配置 -->
                                        <div class="tab-pane active" id="tab_autotakeover_common">
                                            <div class="form-group">
                                                <label class="control-label col-md-3 agentheartbeatfailuretimelabel col-md-5_en"><?php echo $LANG['UI_VOL_CDP_HEARTBEAT_NOT_ENABLE_TIME']; ?></label>
                                                <div class="col-md-5">
                                                    <div class="takeover_app_interval_div">
                                                        <div class="input-group spinner-group">
                                                            <input type="number" id="agent_heartbeat_failure_time" min="5" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" value="30">
                                                            <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                <button type="button" class="btn default spinner-up input-sm" id="addButton">
                                                                    <i class="fa fa-angle-up"></i>
                                                                </button>
                                                                <button type="button" class="btn default spinner-down input-sm" id="reduceButton">
                                                                    <i class="fa fa-angle-down"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <span class="control-label threadnumlabel"><?php echo $LANG['WEB_UTILS_SECOND']; ?> </span>
                                                    </div>
                                                </div>
                                                <div class="popovers mt8">
                                                    <a class="popovers ml-8" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_HEARTBEAT_NOT_ENABLE_TIME_TIPS']; ?>">
                                                        <i class="viconfont vicon-tishi"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- 应用配置 -->
                                        <div class="tab-pane " id="tab_takeover_app_conf">
                                            <!-- 应该故障监测 -->
                                            <div class="form-group appmonitorswitchdiv">
                                                <label class="control-label col-md-3 apptakeoverlabel form-group-label col-md-5_en"><?php echo $LANG['UI_VOL_CDP_ENABLE_FAULT_MONITOR_TAKEOVER']; ?></label>
                                                <div class="col-md-4 form-group-content">
                                                    <input type="checkbox" id="" class="make-switch appmonitorswitch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>">
                                                    <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_ENABLE_FAULT_MONITOR_TAKEOVER_TIPS']; ?>">
                                                        <i class="viconfont vicon-tishi"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <!-- 开启故障检测相关配置 -->
                                            <div class="display-none appmonitorconfigview">
                                                <!-- 连续故障次数 -->
                                                <div class="form-group  appfailurenumberview" style="display:flex;align-items:center;">
                                                    <label class="control-label col-md-3 appfailurenumberlabel col-md-5_en"><?php echo $LANG['UI_VOL_CDP_FAULT_TIMES']; ?> </label>
                                                    <div class="col-md-4 form-group-content">
                                                        <div class="app_failure_number_div">
                                                            <div class="input-group spinner-group">
                                                                <input type="number" id="app_failure_number" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" min="1" max="100">
                                                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                    <button type="button" class="btn default spinner-up input-sm" id="addFailureNumber">
                                                                        <i class="fa fa-angle-up"></i>
                                                                    </button>
                                                                    <button type="button" class="btn  default spinner-down  input-sm" id="reduceFailureNumber">
                                                                        <i class="fa fa-angle-down"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <span style="color:#666;"><?php echo $LANG['UI_VOL_CDP_TIMES']; ?> </span>
                                                    <div class="popovers" style="margin-left: 4px;">
                                                        <a class="popovers ml-8" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_FAULT_TIMES_TIPS']; ?>">
                                                            <i class="viconfont vicon-tishi"></i>
                                                        </a>
                                                    </div>
                                                </div>

                                                <!-- 故障检测频次 -->
                                                <div class="form-group  takeoverappintervalview" style="display:flex;align-items:center;">
                                                    <label class="control-label col-md-3 takeoverappintervallabel col-md-5_en"><?php echo $LANG['UI_VOL_CDP_FAULT_INTERVA']; ?> </label>
                                                    <div class="col-md-4">
                                                        <div class="takeover_app_interval_div">
                                                            <div class="input-group spinner-group">
                                                                <input type="number" id="takeover_app_interval" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" min="5" max="3600">
                                                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                    <button type="button" class="btn spinner-up addTakeover default input-sm" id="heartBeatspinnerUp">
                                                                        <i class="fa fa-angle-up"></i>
                                                                    </button>
                                                                    <button type="button" class="btn spinner-down   default input-sm" id="reduceTakeover">
                                                                        <i class="fa fa-angle-down"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <span style="color:#666;"><?php echo $LANG['WEB_UTILS_SECOND']; ?> </span>
                                                    <div class="popovers" style="margin-left: 4px;">
                                                        <a class="popovers ml-8" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_FAULT_INTERVA_TIPS']; ?>">
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
            </div>
        </div>
    </div>

    <div class="form-group" >
        <div class="col-md-3"></div>
        <div class="col-md-9">
            <div class="alert alert-block alert-info fade in" id="step1tips">
                <button type="button" class="close" data-dismiss="alert"></button>
                <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                <ol class="alert-ol">
                    <li>
                        <?php echo $LANG['UI_CM_CDP_TAKEOVER_DESC1']; ?>
                    </li>
                    
                    <li id="takeoverdesc2">
                            <?php echo $LANG['UI_CM_CDP_TAKEOVER_DESC2']; ?>
                    </li>
                     <li id="takeoverdesc3">
                            <?php echo $LANG['UI_CM_CDP_TAKEOVER_DESC3']; ?>
                    </li>
                    
                </ol>
                <!-- <div class="alert-line"></div>
                <p><?php echo $LANG['UI_VOL_CDP_BAK_TIPS_PS']; ?></p> -->
            </div>
        </div>
    </div>
    <!-- 整机接管 -->
    <?php include_once('cm_volcdp_vm_temp_config.php'); ?>
    
    <!-- 挂载接管备机view -->
    <div id="mountTakeoverStandbyConfModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
        <div class="modal-header ">
            <button type="button" class="close " data-dismiss="modal" aria-hidden="true"></button>
            <h4 class="modal-title"><i class="viconfont vicon-gaojipeizhi"></i> <?php echo $LANG['UI_VOL_CDP_AUTO_TAKEOVER_STANDBY_MACHINE']; ?>
            </h4>
        </div>
        <div class="modal-body">
            <div class="portlet-body">
                <div class="form-group takeoverNetworkConf">
                    <label class="control-label col-md-2 applianceselectlabel"><?php echo $LANG['UI_VOL_CDP_STANDBY_MACHINE_SELECT']; ?></label>
                    <div class="col-md-7">
                        <select class="form-control " id="cmBackupTaskTakeoverStandbySelect">
                        </select>
                    </div>
                </div>
                
                <!--  接管备机映射配置 -->				
                <div class="form-group cmtakeoverstandbymap ">
                    <label class="control-label col-md-2 encrypttransferlabel form-group-label"><?php echo $LANG['UI_CM_CDP_TAKEOVER_MAPPED_CONF_DESC']; ?></label>	
                    <div class="col-md-10 accordion strategystandbymap">
                        <div class="accordion strategystandbymap store-strategy-form me-20">
                            <div class="panel panel-default strategy-panel">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a class="accordion-toggle accordion-toggle-styled  popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategystandbymap" href="#autoTakeoverStandbyMapConf" aria-expanded="true">
                                            <i class="viconfont vicon-jieguanfuwuqi font-green-seagreen"></i>
                                            <span class="font-green-seagreen"><?php echo $LANG['WEB_PLATFORM_DES_HOST'] ?></span>
                                            <span class="strategyDes takeoverStandbyMapDes"></span>
                                        </a>
                                    </h4>
                                </div>
                                <div id="autoTakeoverStandbyMapConf" class="panel-collapse collapse in ">
                                    <div class="panel-body min-height250">
                                        <div id = "" class="display-none cmcdptakeovertargetmapdf">
                                            <span class = "textalignc flex-center flex-items-center"><?php echo $LANG['UI_CM_CDP_TAKEOVER_STANDBY_TIPS']; ?></span>
                                        </div>
                                        <div id = "" class="display-none cmcdptakeovertargetmapconf">
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- modal-footer -->
        <div class="modal-footer">
            <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']; ?></button>
            <button type="button" class="btn btn-primary submitmounttakeoverstandbyconf"><?php echo $LANG['UI_PUBLIC_YES']; ?></button>
        </div>
    </div>
</div>
<script src="./scripts/complete_machine_volcdp/cm_volcdp_vm_temp_config.js"></script>