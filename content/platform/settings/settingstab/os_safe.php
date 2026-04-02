<div class="portlet box blue-hoki" id="addcontent">
    <div class="portlet-body form" style="height:100%;">
        <!-- BEGIN FORM-->
        <form action="#" id="ossafeform" class="form-horizontal">
            <div class="form-body  overflow-x-hidden">
                <div class="form-group">
                    <label class="control-label col-md-4"><?php echo $LANG['UI_PLATFORM_SETTING_SAFE_TEMPLATE'] ?>
                    </label>
                    <div class="col-md-4">
                        <select id="tempMode" class="form-control">
                            <option value="1"><?php echo $LANG['UI_PLATFORM_SETTING_TEMPLATE1'] ?></option>
                            <option value="2"><?php echo $LANG['UI_PLATFORM_SETTING_TEMPLATE2'] ?></option>
                            <option value="3"><?php echo $LANG['UI_PLATFORM_SETTING_TEMPLATE3'] ?></option>
                        </select>
                    </div>
                    <div class="col-md-1" style="margin-top: 10px;">
                        <a class="popovers" data-container="body" data-trigger="hover" data-placement="right"
                           data-content="<?php echo $LANG['UI_PLATFORM_SETTING_TEMPLATE_TIPS'] ?>">
                            <i class="viconfont vicon-tishi"></i>
                        </a>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-md-4"><?php echo $LANG['UI_PLATFORM_SETTING_FIREWALL'] ?>
                    </label>
                    <div class="col-md-4">
                        <input type="checkbox" id="firewallcheck" class="make-switch" data-on-color="primary"
                               data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                        <div><span class="help-block ">
                            <?php echo $LANG['UI_PLATFORM_SETTING_ON_OR_OFF_FIREWALL'] ?>
                        </span></div>
                    </div>
                </div>

                <div class="form-group ">
                    <label class="control-label col-md-4"><?php echo "SSH" ?>
                    </label>
                    <div class="col-md-4">
                        <input type="checkbox" id="sshcheck" class="make-switch" data-on-color="primary"
                               data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                        <div><span class="help-block ">
                            <?php echo $LANG['UI_PLATFORM_SETTING_ON_OR_OFF_SSH'] ?>
                        </span></div>
                    </div>
                </div>

                <div class="form-group ">
                    <label class="control-label col-md-4"><?php echo $LANG['UI_PLATFORM_SETTING_PORT_8080'] ?>
                    </label>
                    <div class="col-md-4">
                        <input type="checkbox" id="port8080check" class="make-switch" data-on-color="primary"
                               data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                        <span style="margin-top: 10px;margin-left: 30px;">
                        <a class="popovers" data-container="body" data-trigger="hover" data-placement="right"
                           data-content="<?php echo $LANG['UI_PLATFORM_SETTING_ON_OR_OFF_PORT'] ?>">
                            <i class="viconfont vicon-tishi"></i>
                        </a>
                    </span>
                        <div><span class="help-block ">
                            <?php echo $LANG['UI_PLATFORM_SETTING_ON_OR_OFF_PORT8080'] ?>
                        </span></div>
                    </div>

                </div>

                <div class="form-group ">
                    <label class="control-label col-md-4"><?php echo $LANG['UI_PLATFORM_SETTING_PORT_3306'] ?>
                    </label>
                    <div class="col-md-4">
                        <input type="checkbox" id="port3306check" class="make-switch" data-on-color="primary"
                               data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                        <span style="margin-top: 10px;margin-left: 30px;">
                        <a class="popovers" data-container="body" data-trigger="hover" data-placement="right"
                           data-content="<?php echo $LANG['UI_PLATFORM_SETTING_ON_OR_OFF_PORT'] ?>">
                            <i class="viconfont vicon-tishi"></i>
                        </a>
                    </span>
                        <div><span class="help-block ">
                            <?php echo $LANG['UI_PLATFORM_SETTING_ON_OR_OFF_PORT3306'] ?>
                        </span></div>
                    </div>

                </div>

                <div class="form-group ">
                    <label class="control-label col-md-4"><?php echo $LANG['UI_PLATFORM_SETTING_PORT_5000'] ?>
                    </label>
                    <div class="col-md-4">
                        <input type="checkbox" id="port5000check" class="make-switch" data-on-color="primary"
                               data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                        <span style="margin-top: 10px;margin-left: 30px;">
                        <a class="popovers" data-container="body" data-trigger="hover" data-placement="right"
                           data-content="<?php echo $LANG['UI_PLATFORM_SETTING_ON_OR_OFF_PORT'] ?>">
                            <i class="viconfont vicon-tishi"></i>
                        </a>
                    </span>
                        <div><span class="help-block ">
                            <?php echo $LANG['UI_PLATFORM_SETTING_ON_OR_OFF_PORT5000'] ?>
                        </span></div>
                    </div>

                </div>

                <div class="form-group ">
                    <label class="control-label col-md-4"><?php echo $LANG['UI_PLATFORM_SETTING_PORT_6080'] ?>
                    </label>
                    <div class="col-md-4">
                        <input type="checkbox" id="port6080check" class="make-switch" data-on-color="primary"
                               data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                        <span style="margin-top: 10px;margin-left: 30px;">
                        <a class="popovers" data-container="body" data-trigger="hover" data-placement="right"
                           data-content="<?php echo $LANG['UI_PLATFORM_SETTING_ON_OR_OFF_PORT'] ?>">
                            <i class="viconfont vicon-tishi"></i>
                        </a>
                    </span>
                        <div><span class="help-block ">
                            <?php echo $LANG['UI_PLATFORM_SETTING_ON_OR_OFF_PORT6080'] ?>
                        </span></div>
                    </div>

                </div>

                <div class="form-group ">
                    <label class="control-label col-md-4"><?php echo $LANG['UI_PLATFORM_SETTING_NFS'] ?>
                    </label>
                    <div class="col-md-4">
                        <input type="checkbox" id="nfscheck" class="make-switch" data-on-color="primary"
                               data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                        <span style="margin-top: 10px;margin-left: 30px;">
                        <a class="popovers" data-container="body" data-trigger="hover" data-placement="right"
                           data-content="<?php echo $LANG['UI_PLATFORM_SETTING_NFS_TIP'] ?>">
                            <i class="viconfont vicon-tishi"></i>
                        </a>
                    </span>
                        <div><span class="help-block ">
                            <?php echo $LANG['UI_PLATFORM_SETTING_ON_OR_OFF_NFS'] ?>
                        </span>
                        </div>
                    </div>
                </div>

                <div class="form-group ">
                    <label class="control-label col-md-4"><?php echo $LANG['UI_PLATFORM_SETTING_RPCBIND'] ?>
                    </label>
                    <div class="col-md-4">
                        <input type="checkbox" id="rpcbindcheck" class="make-switch" data-on-color="primary"
                               data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                        <span style="margin-top: 10px;margin-left: 30px;">
                        <a class="popovers" data-container="body" data-trigger="hover" data-placement="right"
                           data-content="<?php echo $LANG['UI_PLATFORM_SETTING_RPCBIND_TIP']; ?>">
                            <i class="viconfont vicon-tishi"></i>
                        </a>
                    </span>
                        <div><span class="help-block ">
                            <?php echo $LANG['UI_PLATFORM_SETTING_ON_OR_OFF_RPCBIND'] ?>
                        </span></div>
                    </div>
                </div>

                <div class="form-group ">
                    <label class="control-label col-md-4"><?php echo $LANG['UI_PLATFORM_SETTING_API'] ?>
                    </label>
                    <div class="col-md-4">
                        <input type="checkbox" id="apicheck" class="make-switch" data-on-color="primary"
                               data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                        <span style="margin-top: 10px;margin-left: 30px;">
                        <a class="popovers" data-container="body" data-trigger="hover" data-placement="right"
                           data-content="<?php echo $LANG['UI_PLATFORM_SETTING_API_TIP']; ?>">
                            <i class="viconfont vicon-tishi"></i>
                        </a>
                    </span>
                        <div><span class="help-block ">
                            <?php echo $LANG['UI_PLATFORM_SETTING_ON_OR_OFF_API'] ?>
                        </span></div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-md-4">
                    </label>
                    <div class="col-md-4">
                        <div class="alert alert-block alert-info fade in">
                            <button type="button" class="close" data-dismiss="alert"></button>
                            <ul class="alert-ul">
                                <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                                <li>
                                    <?php echo $LANG['UI_PLATFORM_SETTING_SYSTEM_SAFE_CONF_TIPS'] ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>


            </div>
            <div class="form-actions" style="width: 100%;margin:0px;padding-left:0;">
                <div class="row">
                    <div class="col-md-offset-4 pl24 col-md-6">
                        <button type="button" id="oscancel" class="btn default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
                        <?php
                        if (in_array("p_os_safe", $_SESSION['permissionArr'])) {
                            echo '<button type="button" id="ossubmit" class="btn green-haze btn-confirm">' . $LANG['UI_PUBLIC_YES'] .'</button>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </form>
        <!-- END FORM-->
    </div>
</div>
<!-- END FORM-->
<script type="text/javascript" src="./scripts/platform/settings/settingstab/os_safe.js"></script>