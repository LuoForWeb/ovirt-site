<!-- 指定虚拟机/实例恢复的恢复目标组件，在恢复配置外层容器的div中引入该文件 -->
<div id="vm-recovery-target">
    <!-- drawer开始 -->
    <div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true" id="drawer-1" style="width: 600px">
        <div class="drawer-content drawer-content-scrollable" role="document">
            <div class="drawer-header">
                <h4 class="drawer-title" id="drawer-1-title">
                    <i class="viconfont vicon-mubiao mr8"></i><?php echo $LANG['UI_RECOVERY_GOAL'] ?>
                    <span data-dismiss="drawer" aria-label="Close" class="drawer-close"><i class="viconfont vicon-guanbi"></i></span>
                </h4>
            </div>
            <div class="drawer-body">
                <!-- 默认提示 -->
                <div class="alert alert-block alert-info fade in" id="vmtargettips">
                    <button type="button" class="close" data-dismiss="alert"></button>
                    <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                    <ol class="alert-ol">
                        <li>
                            <?php echo $_GET['sub_module_type'] == 2 ? $LANG['UI_RECOVERY_VM_SELECT_INSTANCE'] : $LANG['UI_RECOVERY_VM_SELECT_VM'] ?>
                        </li>
                        <li>
                            <?php echo $_GET['sub_module_type'] == 2 ? $LANG['UI_RECOVERY_VM_SELECT_INSTANCE_TIPS'] : $LANG['UI_RECOVERY_VM_SELECT_VM_TIPS'] ?>
                        </li>
                    </ol>
                </div>
                <!-- 没有恢复目标平台的提示 -->
                <div class="alert alert-block alert-warning fade in display-hide" id="nodatatips">
                    <button type="button" class="close" data-dismiss="alert"></button>
                    <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                    <ol class="alert-ol">
                        <li><?php echo $_GET['sub_module_type'] == 2 ? $LANG['UI_VM_RECOVERY_TARGET_NO_PLATFORM_TIPS1'] : $LANG['UI_VM_RECOVERY_TARGET_NO_PLATFORM_TIPS2']?></li>
                        <li>
                            <a id="toaddvcenter"><?php echo $_GET['sub_module_type'] == 2 ? $LANG['UI_VM_RECOVERY_TARGET_ADD_PLATFORM_TIPS1'] : $LANG['UI_VM_RECOVERY_TARGET_ADD_PLATFORM_TIPS2']?></a>
                        </li>
                    </ol>
                </div>
                <!-- 没有恢复目标虚拟机的提示 -->
                <div class="alert alert-block alert-warning fade in display-hide" id="novmdatatips">
                    <button type="button" class="close" data-dismiss="alert"></button>
                    <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                    <ol class="alert-ol">
                        <li><?php echo $_GET['sub_module_type'] == 2 ? $LANG['UI_VM_RECOVERY_TARGET_NO_VM_TIPS1'] : $LANG['UI_VM_RECOVERY_TARGET_NO_VM_TIPS2']?></li>
                    </ol>
                </div>
                <!-- 虚拟机树 -->
                <div id="vm_tree_div">
                    <ul id="vm_tree_hc" class="ztree bd1de5 tree_div" style="max-height: 600px;overflow: auto"></ul>
                </div>
                <!-- 验证平台密码 -->
                <div class="form-group mt30 display-none" id="verify_pwd">
                    <label class="control-label col-md-3 mt2"><span class="required">* </span><?php echo $LANG['WEB_VM_VCENTER_VERIFY_ACCOUNT_PASSWORD'] ?></label>
                    <div class="col-md-6 form-group-content">
                        <input type="password" class="form-control input-md" id="pwd">
                    </div>
                    <div class="col-md-3 mt7">
                        <a class="popovers" data-container="body" data-trigger="hover"
                           data-placement="right" data-content="<?php echo $LANG['WEB_VM_VCENTER_VERIFY_ACCOUNT_PASSWORD_TIPS'] ?>">
                            <i class="viconfont vicon-tishi"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="drawer-footer">
                <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-primary" id="recover-target-submit"><?php echo $LANG['UI_PUBLIC_YES']?></button>
                <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-default" id="recover-target-cancel"><?php echo $LANG['UI_PUBLIC_NO']?></button>
            </div>
        </div>
    </div>
    <!-- drawer结束 -->
</div>
<script src="./scripts/platform/component/vm_recovery_target.js" type="text/javascript"></script>