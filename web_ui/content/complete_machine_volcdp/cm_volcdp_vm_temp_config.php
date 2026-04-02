<link rel="stylesheet" type="text/css" href="./css/platform/component/network_config.css" />

<!-- drawer开始 -->
<div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" style="width: 800px"
     aria-labelledby="drawer-1_title" aria-hidden="true" id="standby_map_conf_drawer">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header">
            <div class="drawer-title" id="drawer-1_title"
                 style="display:flex;justify-content:space-between;align-items:center">
                <div class="drawer-title-left">
                    <i class="viconfont vicon-a-Serverfuwuqi mr10"></i>
                    <span><?php echo $LANG['UI_VOL_CDP_TAKE_TEMP_AGENT_DESC'] ?></span>
                </div>
                <div class="drawer-title-right drawer-close" data-dismiss="drawer" aria-label="Close">
                    <i class="viconfont vicon-guanbi"></i>
                </div>
            </div>
        </div>
        <!-- BEGIN BACKUP TASK DETAIL DRAWER BODY -->
        <div class="drawer-body">
            <div class="col-md-12 accordion pl0">
                <div class="portlet">
                    <div class="portlet-body">
                        <div class="">
                            <div id="takeover_config" class="panel-collapse takeovertimecollapseview collapse in form-horizontal">
                                <div class="">
                                    <div class="nav-tabs-wrapper">
                                        <ul class="nav nav-tabs nav-line-tabs" id="">
                                            <!-- 通用配置-->
                                            <li class="nav-item active">
                                                <a href="#generalConfig" class="nav-link" data-toggle="tab" aria-expanded="true">
                                                    <i class="viconfont vicon-tongyongpeizhi"></i>
                                                    <?php echo $LANG['UI_VOL_CDP_GENERAL_CONFIGURE'];?>
                                                </a>
                                            </li>

                                            <li class="nav-item">
                                                <a href="#diskConfig" class="nav-link" data-toggle="tab" aria-expanded="true">
                                                    <i class="viconfont vicon-cipanpeizhi"></i>
                                                    <?php echo $LANG['UI_VIRTUAL_MACHINE_DISK_CONFIGURATION'];?>
                                                </a>
                                            </li>

                                            <!-- 网络配置-->
                                            <li class="nav-item">
                                                <a href="#ipConfig" class="nav-link" data-toggle="tab" aria-expanded="true">
                                                    <i class="viconfont vicon-wangluo"></i>
                                                    <?php echo $LANG['UI_VOL_CDP_FAILBACK_NETWORK_CONF_TEXT'];?>
                                                </a>
                                            </li>

                                            <!--  高级配置-->
                                            <li class="nav-item">
                                                <a href="#highConfig" class="nav-link" data-toggle="tab" aria-expanded="true">
                                                    <i class="viconfont vicon-gaojipeizhi"></i>
                                                    <?php echo $LANG['UI_BACKUP_HIGH_CONFIG'];?>
                                                </a>
                                            </li>
                                        </ul>
                                        <div class="tab-content border-bottom-none pt-0">
                                            <div id="generalConfig" class="tab-pane active">
                                                <div class="col-md-3 col-sm-3 col-xs-3 nav-tab-radios">
                                                    <ul class="nav nav-tabs tabs-left">
                                                        <li class="dwn active" data-type="1">
                                                            <a href="#name_config" data-toggle="tab" aria-expanded="true"><?php echo $LANG['UI_RECOVERY_STORAGE_NAME'];?></a>
                                                        </li>
                                                        <li class="dwn" data-type="2">
                                                            <a href="#cpu_config" data-toggle="tab" aria-expanded="true">CPU</a>
                                                        </li>
                                                        <li class="dwn" data-type="3">
                                                            <a href="#memory_config" data-toggle="tab" aria-expanded="true"><?php echo $LANG['UI_DRILLS_MEMORY'];?></a>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="col-md-9 col-sm-9 col-xs-9">
                                                    <div class="tab-content">
                                                        <div class="tab-pane fade in active" id="name_config">
                                                            <div class="panel-body">
                                                                <label class="control-label col-md-4 pr5">
                                                                    <span class="required">*</span>
                                                                    <?php echo $LANG['UI_VM_MACHINE_TITLE'];?>
                                                                </label>
                                                                <div class="col-md-6 flex-items-center">
                                                                    <input type="text" class="form-control" name="modal_vm_name" id="modal_vm_name" maxlength="36">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="tab-pane fade in" id="cpu_config">
                                                            <div class="form-group mb-16">
                                                                <label class="control-label control-label col-md-4 pr5">
                                                                    <span class="required">*</span>
                                                                    <?php echo $LANG['UI_VIRTUAL_MACHINE_SLOT_NUMBER'];?>
                                                                </label>
                                                                <div class="col-md-6 flex-items-center">
                                                                    <select id="slots_num_select" class="form-control select2me col-md-6">
                                                                        <option value="1">1</option>
                                                                        <option value="2">2</option>
                                                                        <option value="3">3</option>
                                                                        <option value="4">4</option>
                                                                        <option value="5">5</option>
                                                                        <option value="6">6</option>
                                                                        <option value="7">7</option>
                                                                        <option value="8">8</option>
                                                                        <option value="9">9</option>
                                                                        <option value="10">10</option>
                                                                        <option value="11">11</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label control-label col-md-4 pr5">
                                                                    <span class="required">*</span>
                                                                    <?php echo $LANG['UI_VIRTUAL_MACHINE_CORE_NUMBER_PER_SLOT'];?>
                                                                </label>
                                                                <div class="col-md-6">
                                                                    <div class="input-group width-100">
                                                                        <input id="modal_v_cpu" name="modal_v_cpu" value="1" type="text"  onkeyup="value=value.replace(/[^\d]/g,'')" maxlength="64" class="table-group-action-input form-control" aria-controls="example">
                                                                        <span class="input-group-btn">
                                                                            <button class="btn" type="button"><?php echo $LANG['UI_VM_MACHINE_CORS']?></button>
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="cores-tip help-block display-none">
                                                                            <?php echo $LANG['UI_VM_MACHINE_MAX_CORS']?>:<span id="modal_least_cpu">0</span><?php echo $LANG['UI_VM_MACHINE_CORS']?>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label control-label col-md-4 pr5">
                                                                    <span class="required">*</span>
                                                                    <?php echo $LANG['UI_VIRTUAL_MACHINE_CPU_MODE'];?>
                                                                </label>
                                                                <div class="col-md-6 flex-items-center">
                                                                    <select id="cpu_type_select" class="form-control select2me col-md-6">
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="tab-pane fade in" id="memory_config">
                                                            <div class="panel-body">
                                                                <label class="control-label col-md-3 pr5">
                                                                    <span class="required">*</span>
                                                                    <?php echo $LANG['UI_VERIFY_MEMORY_SIZE'];?>
                                                                </label>
                                                                <div class="col-md-4 form-group-content pl15 pr12">
                                                                    <div class="backupThreadDiv">
                                                                        <input type="text" id="modal_v_memory" value="" class="form-control input-sm" maxlength="16" oninput="value=(value.replace(/[^0-9.]/g,'').replace(/^\./,'0.').replace(/(\..*?)\./g,'$1'))">
                                                                        <div class="line-height12">
                                                                            <span class="help-block" style="width: 150%;">
                                                                                <?php echo $LANG['UI_VM_MACHINE_MAX_MEMS'];?>:
                                                                                <span id="modal_least_memory"></span><span id="modal_least_memory_unit"><?php echo $LANG['UI_VM_MACHINE_MEMS_UNIT']?></span>
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3 padding-l-w pl0">
                                                                    <select class="form-control" id="modal_v_memory_type" name="modal_v_memory_type">
                                                                        <option value="0">MB</option>
                                                                        <option value="1" selected="">GB</option>
                                                                        <option value="2">TB</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="diskConfig" class="tab-pane ps-20 pe-20">
                                                <table id="diskConfigTable">
                                                </table>
                                            </div>
                                            <div id="ipConfig" class="tab-pane px-12">
                                                <div id="node_div" class="form-group">
                                                    <label class="control-label col-md-4" style="margin-left: -195px;"><?php echo $LANG['UI_STORAGE_REMOTE_NODE'];?></label>
                                                    <div class="col-md-5 pr12">
                                                        <select id="node_select" class="form-control select2me">
                                                        </select>
                                                    </div>
                                                    <div class="col-md-1 pl0" style="margin-top: 10px;">
                                                        <a class="popovers" data-container="body" data-trigger="hover" data-html="true"
                                                           data-placement="right" data-content="<?php echo $LANG['UI_VIRTUAL_MACHINE_NETWORK_TIPS'] ?>">
                                                            <i class="viconfont vicon-tishi"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="highConfig" class="tab-pane">
                                                <div class="form-group mb-16">
                                                    <div class="control-label col-md-4 user-width30_en pr5"><?php echo $LANG['UI_VIRTUAL_MACHINE_SYSTEM_FIRMWARE_TYPE'];?></div>
                                                    <div class="col-md-4 user-width60_en">
                                                        <select name="modal_boot_firmware" id="" class="form-control select2me">
                                                            <option value="1">BIOS</option>
                                                            <option value="2">UEFI</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group mb-16">
                                                    <div class="control-label col-md-4 user-width30_en pr5"><?php echo $LANG['UI_VIRTUAL_MACHINE_MAXIMUM_BOOT_WAITING_TIME'];?></div>
                                                    <div id="max_wait_time" class="col-md-4 user-width60_en pr8">
                                                        <div class="input-group spinner-group">
                                                            <input type="num" class="spinner-input form-control input-sm" name="max_wait_time_input" onkeyup="value=value.replace(/[^\d]/g,'')">
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
                                                    <label class="col-md-1 mt8 pl0"><?php echo $LANG['WEB_UTILS_MINUTE'];?></label>
                                                </div>
                                                <div class="form-group mb-16">
                                                    <div class="control-label col-md-4 user-width30_en pr5"><?php echo $LANG['UI_VIRTUAL_MACHINE_RESET_HOSTNAME'];?></div>
                                                    <div class="col-md-4 user-width60_en">
                                                        <input type="checkbox" id="rename" class="make-switch"
                                                               data-on-color="primary" data-off-color="info" data-size="small"
                                                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="control-label col-md-4 user-width30_en"></div>
                                                    <div class="col-md-4 hostname display-none">
                                                        <input class="form-control" type="text" name="rehostname">
                                                    </div>
                                                </div>
                                                <div id="driverCheck">
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
        <div class="drawer-footer">
            <div class="row">
                <div class="col-md-6" style="float: right;padding-right: 10px;">
                    <button type="button" id="builtInVmSubmit" class="btn green-haze btn-confirm">
                        <?php echo $LANG['UI_PUBLIC_YES'] ?>
                    </button>
                    <button type="button" id="builtInVmClose" class="btn default cancel">
                        <?php echo $LANG['UI_PUBLIC_NO'] ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
