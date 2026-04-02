<style>
    #show_vmRecovery .driver-check__wrapper .driver-config__label{
        padding-right: 15px;
    }
    #show_vmRecovery .driver-check__wrapper .driverCheckBtnDiv .driver-config__content{
        padding-left: 15px;
    }
    #show_vmRecovery .driver-check-content__wrapper {
        margin-left: 0px;
        padding-left: 15px;
    }
    #accordionvm .dropdown-menu.inner {
        max-height: 150px!important;
        overflow-x: hidden;
    }
</style>
<div id="show_vmRecovery_next">
<div class="form-group host_tree_div data-resource1 display-none" id="selectHost">
    <label class="control-label col-md-3">
        <span class="required">* </span>
        <span id="source_title"><?php echo $LANG['UI_RECOVERY_SELECT_HOST'] ?></span>
        <span id="source_title2" class="display-none"><?php echo $LANG['UI_RECOVERY_SELECT_PROJECT'] ?></span>
        <span id="source_title3" class="display-none"><?php echo $LANG['UI_RECOVERY_SELECT_AREA'] ?></span>
    </label>
    <div class="col-md-6 tree_div2">
        <ul id="host_tree" class="ztree bd1de5"></ul>
        <div>
            <span class="help-block" id="source_desc">
            <?php echo $LANG['UI_RECOVERY_VM_SELECT_HOST'] ?>
            </span>
            <span class="help-block display-none" id="source_desc2">
            <?php echo $LANG['UI_RECOVERY_INSTANCE_SELECT_PROJECT'] ?>
            </span>
            <span class="help-block display-none" id="source_desc3">
            <?php echo $LANG['UI_RECOVERY_INSTANCE_SELECT_AREA'] ?>
            </span>
        </div>
        <div class="alert alert-block alert-danger fade in display-none" id="show_instant_msg">
            <button type="button" class="close" data-dismiss="alert"></button>
            <ul class="alert-ul">
                <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS']?>:</strong>
                <li>
                    <?php echo $LANG['UI_RECOVERY_SELECT_OPENSTACK_TIPS']?>
                </li>
            </ul>
        </div>
    </div>
</div>
<div class="form-group display-hide" id="nohosttips">
    <label class="control-label col-md-3">
        <span class="required">* </span>
        <span id="source_titles"><?php echo $LANG['UI_RECOVERY_SELECT_HOST'] ?></span>
        <span id="source_titles2" class="display-none"><?php echo $LANG['UI_RECOVERY_SELECT_PROJECT'] ?></span>
        <span id="source_titles3" class="display-none"><?php echo $LANG['UI_RECOVERY_SELECT_AREA'] ?></span>
    </label>
    <div class="col-md-6" style="line-height:30px;">
        <span id="show_tips_">
            <?php echo $LANG['UI_RECOVERY_NO_HOST'] ?>
        </span>
        <span id="show_tips_2" class="display-none">
            <?php echo $LANG['UI_RECOVERY_NO_OBJECT']?>
        </span>
        <span id="show_tips_3" class="display-none">
            <?php echo $LANG['UI_RECOVERY_NO_AREA']?>
        </span>
    </div>
</div>

<!--统一配置虚拟机-->
<div class="form-group dndiv display-none" id="unifysettingdiv">
    <label class="control-label col-md-3 form-group-label">
        <span class="required">* </span>
        <?php echo $_GET['sub_module_type'] == 2 ? $LANG['UI_RECOVERY_VM_MORE_INSTANCE_SETTING'] : $LANG['UI_RECOVERY_VM_MORE_VM_SETTING'] ?>
    </label>
    <div class="col-md-3 form-group-content">
        <input type="checkbox" id="vmssetting"   class="make-switch" data-size="small" data-on-color="primary" data-off-color="info"
               data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">

        <a class="popovers ml15" data-container="body" data-trigger="hover"
           data-placement="right" data-content="<?php echo $_GET['sub_module_type'] == 2 ? $LANG['UI_RECOVERY_VM_MORE_INSTANCE_SETTING_TIPS'] : $LANG['UI_RECOVERY_VM_MORE_VM_SETTING_TIPS'] ?>">
            <i class="viconfont vicon-tishi"></i>
        </a>
    </div>
    <div class="col-md-12 mt10 display-hide vmssettingdiv">
        <div class="form-group">
            <label class="control-label col-md-3">
                <span class="required">* </span>
                <?php echo $LANG['UI_RECOVERY_SELECT_STORAGE'] ?>
            </label>
            <div class="col-md-6">
                <select class="form-control select2me selectpicker show-tick ignore" name="hoststorage" id="vmsstorage" data-live-search="true">
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-3">
                <span class="required" >* </span>
                <?php echo $LANG['UI_RECOVERY_VM_SELECT_NETWORK'] ?>
            </label>
            <div class="col-md-6">
                <select class="form-control select2me selectpicker show-tick ignore" name="hostnetwork" id="vmsnetwork" data-live-search="true">
                </select>
            </div>
        </div>
        <div class="form-group display-none" id="usedomainDiv">
            <label class="control-label col-md-3">
                <span class="required" >* </span>
                <?php echo $LANG['UI_PUBLIC_USE_DOMAIN'] ?>
            </label>
            <div class="col-md-6">
                <select class="form-control" name="usedomain" id="vmUsedomain">
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-3 form-group-label">
                <span class="required">* </span>
                <?php echo $LANG['UI_INSTANT_VM_POWER'] ?>
            </label>
            <div class="col-md-6 form-group-content">
                <input type="checkbox" id="powercheck" data-size="small" class="make-switch" data-on-color="primary" data-off-color="info"
                       data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                <div><span class="help-block "><?php echo $LANG['UI_RECOVERY_POWER_VM_TIPS'] ?></span></div>
            </div>
        </div>
    </div>
    <div class="col-md-12 mt10 display-hide applianceDiv">
        <div class="form-group">
            <label class="control-label col-md-3">
                <span class="required">* </span>
                <?php echo $LANG['UI_RECOVERY_V2V_AGENT'] ?>
            </label>
            <div class="col-md-6">
                <select class="form-control select2me" id="v2vAppliance">
                </select>
                <div><span class="help-block "><?php echo $LANG['UI_RECOVERY_SELECT_TRANSFER_AGENT'] ?></span></div>
            </div>
        </div>
    </div>
</div>

<!--虚拟机配置详情-->
<div class="form-group dndiv display-none" id="vmconfigs">
    <label class="control-label col-md-3">
        <span class="required">* </span>
        <?php echo $LANG['UI_RECOVERY_VM_SETTING']?>
    </label>
    <div class="col-md-7">
        <div class="portlet">
            <div class="portlet-body">
                <div class="panel-group accordion" id="accordionvm">
                    <?php include_once('../component/vm_recovery.php'); ?>
                </div>
            </div>
            <div class="margintop-15">
                <span class="help-block "><?php echo $LANG['UI_RECOVERY_VM_SETTING_TIPS']?></span>
            </div>
        </div>
    </div>
</div>

<!-- 公有云实例配置 -->
<div class="form-group display-none" id="awsconfigs">
    <label class="control-label col-md-3">
        <span class="required">* </span>
        <?php echo $LANG['UI_PLATFORM_PUBLIC_CLOUD_CONFIG']?>
    </label>
    <div class="col-md-7" style="padding-right:0px;padding-left:0;">
        <div class="portlet">
            <div class="portlet-body">
                <?php include_once('../component/aws_recovery.php'); ?>
            </div>
        </div>
    </div>
</div>

<div class="form-group">
    <div id="driverCheck_vm_button" class="display-none">
        <div class="form-group ">
            <label class="col-md-3 text-align-reverse pt7 driver-config__label">
                <?php echo $LANG['WEB_TOOL_DR_OP_CHECK_DIFF_PLAT_AND_DRIVER_EXIST'];?>
            </label>
            <div class="col-md-7 driver-config__content">
                <button class="btn btn-light-primary" type="button" id="driverCheck_vm_button_checkDriver"><?php echo $LANG['WEB_PLATFORM_DES_CHECK']?></button>
            </div>
        </div>
    </div>
    <div id="driverCheck_vm"></div>
    <div id="driverCheckFailContinue" class="display-none">
        <div class="form-group ">
            <label class="col-md-3">
            </label>
            <div class="col-md-7 driver-config__content">
                <label style="float:left;line-height:32px;margin-right:12px;"><?php echo $LANG['UI_PLATFORM_DRIVER_TIPS'];?>：</label>
                <div class="input-group " id="continue_driver_fail" style="margin-top: 4px;float: left;">
                    <!--<label style="padding-right: 20px;">
                        <input type="checkbox" id="notContinueCheck" data-checkbox="icheckbox_square-blue" data-mode="1" class="icheck"><?php /*echo $LANG['WEB_PLATFORM_PUBLIC_NO'];*/?>
                    </label>-->
                    <label>
                        <input type="checkbox" id="continueCheck" data-checkbox="icheckbox_square-blue" data-mode="2" class="icheck"><?php echo $LANG['WEB_PLATFORM_PUBLIC_YES'];?>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<script src="./scripts/platform/recovery/vm_recovery.js" type="text/javascript"></script>
