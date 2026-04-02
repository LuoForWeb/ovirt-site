<!-- 在恢复配置外层容器的div中引入该文件 -->
<div id="aws-recover">
    <!-- 实例恢复设置 -->
    <div class="form-group" id="instance-div">
        <div class="col-md-12 table-container">
            <table id="instance-table"></table>
        </div>
    </div>
    <!-- 卷恢复设置 -->
    <div class="form-group display-none" id="vol-div">
        <div class="col-md-12 table-container">
            <table id="vol-table"></table>
        </div>
    </div>
    <!-- 实例恢复设置抽屉 -->
    <!-- drawer开始 -->
    <div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true" id="drawer-1" style="width: 600px">
        <div class="drawer-content drawer-content-scrollable" role="document">
            <div class="drawer-header">
                <h4 class="drawer-title" id="drawer-1-title">
                    <i class="viconfont vicon-ge_modify mr8"></i><?php echo $LANG['UI_RECOVERY_AWS_EDIT_INSTANCE'] ?>
                    <span data-dismiss="drawer" aria-label="Close" class="drawer-close"><i class="viconfont vicon-guanbi"></i></span>
                </h4>
            </div>
            <div class="drawer-body">
                <!-- 实例名称 -->
                <div class="form-group mt30">
                    <label class="control-label col-md-3 col-md-4_en"><span class="required">* </span><?php echo $LANG['UI_RECOVERY_AWS_INSTANCE_NAME'] ?></label>
                    <div class="col-md-6">
                        <input class="form-control" name="instance_name" maxlength="256">
                        <span class="huweicloud-vol-tips mt5" style="display: block"><?php echo $LANG['UI_RECOVERY_HUAWEICLOUD_VOL_NAME_ERROR_TIPS'] ?></span>
                    </div>
                </div>
                <!-- 选择企业项目，华为云 -->
                <div class="form-group mt30 display-none project-div">
                    <label class="control-label col-md-3 col-md-4_en">
                        <span class="required">* </span>
                        <?php echo $LANG['UI_RECOVERY_AWS_SELECT_ENTERPRISE_PROJECT'] ?>
                    </label>
                    <div class="col-md-6">
                        <select class="form-control project-select" name="project">
                        </select>
                    </div>
                </div>
                <!-- 区域 -->
                <div class="form-group mt30">
                    <label class="control-label col-md-3 col-md-4_en"><span class="required">* </span><?php echo $LANG['UI_RECOVERY_AWS_REGION'] ?></label>
                    <div class="col-md-6">
                        <select class="form-control" name="region"></select>
                    </div>
                </div>
                <!-- 可用区 -->
                <div class="form-group mt30 display-none az-div">
                    <label class="control-label col-md-3 col-md-4_en"><?php echo $LANG['UI_RECOVERY_AWS_AVAILABLE_ZONE'] ?></label>
                    <div class="col-md-6">
                        <select class="form-control" name="az"></select>
                    </div>
                </div>
                <!-- cpu架构 -->
                <div class="form-group mt30 display-none cpu-arch">
                    <label class="control-label col-md-3 col-md-4_en"><span class="required">* </span><?php echo $LANG['UI_PUBLIC_CPU_ARCH'] ?></label>
                    <div class="col-md-6">
                        <select class="form-control" name="cpu_arch">
                            <option value=""><?php echo $LANG['UI_PUBLIC_SELECT'] ?></option>
                            <option value="1" data-des="x86">x86</option>
                            <option value="2" data-des="x86_64">x86_64</option>
                            <option value="3" data-des="arm">ARM</option>
                            <option value="4" data-des="arm64">ARM64</option>
                        </select>
                    </div>
                </div>
                <!-- 操作系统 -->
                <div class="form-group mt30 display-none os-type">
                    <label class="control-label col-md-3 col-md-4_en"><span class="required">* </span><?php echo $LANG['UI_PUBLIC_OS'] ?></label>
                    <div class="col-md-6">
                        <select class="form-control" name="os_type">
                            <option value=""><?php echo $LANG['UI_PUBLIC_SELECT'] ?></option>
                            <option value="Mac OS" data-os_value="1">Mac OS</option>
                            <option value="Windows" data-os_value="2">Windows</option>
                            <option value="Linux" data-os_value="3">Linux</option>
                            <option value="Other" data-os_value="4">Other</option>
                        </select>
                    </div>
                </div>
                <!-- 操作系统版本 -->
                <div class="form-group mt30 display-none os-version">
                    <label class="control-label col-md-3 col-md-4_en"><span class="required">* </span><?php echo $LANG['UI_PUBLIC_OS_VERSION'] ?></label>
                    <div class="col-md-6">
                        <select class="bootstrap-mutiple-select select2me selectpicker show-tick ignore" name="os_version" data-live-search="true">
                        </select>
                    </div>
                </div>
                <!-- 实例类型 -->
                <div class="form-group mt30">
                    <label class="control-label col-md-3 col-md-4_en"><span class="required">* </span><?php echo $LANG['UI_RECOVERY_AWS_INSTANCE_TYPE'] ?></label>
                    <div class="col-md-6">
                        <select class="bootstrap-mutiple-select select2me selectpicker show-tick ignore" name="instance_type" data-live-search="true"></select>
                    </div>
                </div>
                <!-- VPC -->
                <div class="form-group mt30">
                    <label class="control-label col-md-3 col-md-4_en"><span class="required">* </span>VPC</label>
                    <div class="col-md-6">
                        <select class="form-control" name="vpc"></select>
                    </div>
                    <div class="col-md-1 mt10">
                        <a class="popovers" data-container="body" data-trigger="hover"
                           data-placement="top" data-content="<?php echo $LANG['UI_RECOVERY_AWS_VPC_TIPS'] ?>">
                            <i class="viconfont vicon-tishi"></i>
                        </a>
                    </div>
                </div>
                <!-- 子网 -->
                <div class="form-group mt30">
                    <label class="control-label col-md-3 col-md-4_en"><?php echo $LANG['UI_RECOVERY_AWS_SUBNET'] ?></label>
                    <div class="col-md-6">
                        <select class="form-control" name="subnet"></select>
                    </div>
                    <div class="col-md-1 mt10">
                        <a class="popovers" data-container="body" data-trigger="hover"
                           data-placement="top" data-content="<?php echo $LANG['UI_RECOVERY_AWS_SUBNET_TIPS'] ?>">
                            <i class="viconfont vicon-tishi"></i>
                        </a>
                    </div>
                </div>
                <!-- 安全组 -->
                <div class="form-group mt30">
                    <label class="control-label col-md-3 col-md-4_en"><span class="required display-none">* </span><?php echo $LANG['UI_RECOVERY_AWS_SECURITY_GROUP'] ?></label>
                    <div class="col-md-6">
                        <select class="bootstrap-mutiple-select select2me selectpicker show-tick ignore" name="security" multiple data-live-search="true" data-actions-box="true" ></select>
                    </div>
                    <div class="col-md-1 mt10">
                        <a class="popovers" data-container="body" data-trigger="hover"
                           data-placement="top" data-content="<?php echo $LANG['UI_RECOVERY_AWS_SECURITY_GROUP_TIPS'] ?>">
                            <i class="viconfont vicon-tishi"></i>
                        </a>
                    </div>
                </div>
                <!-- 加密密码 -->
                <div class="form-group mt30 display-none encrypt-password">
                    <label class="control-label col-md-3 col-md-4_en"><span class="required">* </span><?php echo $LANG['UI_RECOVERY_AWS_ENCRYPT_PWD'] ?></label>
                    <div class="col-md-6">
                        <input class="form-control" type="password" name="encrypt_password" maxlength="256">
                    </div>
                </div>

                <!-- 分配公有ip -->
                <div class="form-group mt30">
                    <label class="control-label col-md-3 col-md-4_en"></label>
                    <label class="control-label col-md-6" style="text-align: left;padding-top: 0;">
                        <input type="checkbox" name="is_public_ip">
                        <?php echo $LANG['UI_RECOVERY_AWS_ALLOCATE_PUBLIC_IP'] ?>
                    </label>
                    <div class="col-md-1">
                        <a class="popovers" data-container="body" data-trigger="hover"
                           data-placement="top" data-content="<?php echo $LANG['UI_RECOVERY_AWS_ALLOCATE_PUBLIC_IP_TIPS'] ?>">
                            <i class="viconfont vicon-tishi"></i>
                        </a>
                    </div>
                </div>

                <!-- 公有ip付费方式 -->
                <div class="form-group mt30 display-none public-ip-billing">
                    <label class="control-label col-md-3 col-md-4_en"><?php echo $LANG['BILLING_BUY_WAY'] ?></label>
                    <div class="col-md-6">
                        <select class="form-control" name="billing_type">
                            <option value="bandwidth"><?php echo $LANG['UI_RECOVERY_CLOUD_BILLING_WAY_BANDWIDTH'] ?></option>
                            <option value="traffic"><?php echo $LANG['UI_RECOVERY_CLOUD_BILLING_WAY_TRAFFIC'] ?></option>
                        </select>
                        <div class="alert alert-info" style="margin: 10px 0 0!important;">
                            <button type="button" class="close" data-dismiss="alert"></button>
                            <ul class = "alert-ul">
                                <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>: </strong>
                                <li>
                                    <?php echo $LANG['UI_RECOVERY_CLOUD_BILLING_TIPS2'] ?>
                                    <?php
                                    if ($_SESSION['language'] != "zh-cn" && $_SESSION['language'] != "zh-tw") {
                                        echo '<a href="https://www.huaweicloud.com/intl/en-us/pricing/calculator.html#/eip"
																					target="_blank">
																					 ' . $LANG['UI_RECOVERY_CLOUD_BILLING_HUAWEI_CLOUD_LINK'] . '</a>';
                                    } else {
                                        echo '<a href="https://www.huaweicloud.com/pricing/calculator.html#/eip"
																					target="_blank">
																					 ' . $LANG['UI_RECOVERY_CLOUD_BILLING_HUAWEI_CLOUD_LINK'] . '</a>';
                                    }
                                    ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-1 mt10">
                        <a class="popovers" data-container="body" data-trigger="hover"
                           data-placement="top" data-content="<?php echo $LANG['UI_RECOVERY_CLOUD_BILLING_TIPS'] ?>">
                            <i class="viconfont vicon-tishi"></i>
                        </a>
                    </div>
                </div>
                <!-- 带宽/流量大小 -->
                <div class="form-group mt30 display-none public-ip-billing">
                    <label class="control-label col-md-3 col-md-4_en">
                        <span class="label-title"><?php echo $LANG['UI_RECOVERY_CLOUD_BANDWIDTH_SIZE'] ?></span>
                    </label>
                    <div class="col-md-6">
                        <input class="spinner-input form-control" type="text" name="billing_value"
                               min="1" max="2000" step="1" maxlength="4" onkeyup="value=value.replace(/[^\d]/g,'')">
                    </div>
                    <div class="col-md-1 mt10">
                        <span>Mbit/s</span>
                    </div>
                </div>

                <!-- 恢复完成后是否启动 -->
                <div class="form-group mt30">
                    <label class="control-label col-md-3 col-md-4_en"></label>
                    <label class="control-label col-md-6" style="text-align: left;padding-top: 0;">
                        <input type="checkbox" name="start_flag">
                        <?php echo $LANG['UI_RECOVERY_AWS_IS_AUTO_START'] ?>
                    </label>
                </div>
            </div>
            <div class="drawer-footer">
                <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-primary" id="recover-ins-submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
                <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-default" id="recover-ins-cancel"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
            </div>
        </div>
    </div>
    <!-- drawer结束 -->
    <!-- 卷恢复设置抽屉 -->
    <!-- drawer开始 -->
    <div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-2-title" aria-hidden="true" id="drawer-2" style="width: 600px">
        <div class="drawer-content drawer-content-scrollable" role="document">
            <div class="drawer-header">
                <h4 class="drawer-title" id="drawer-2-title">
                    <i class="viconfont vicon-ge_modify mr8"></i><?php echo $LANG['UI_RECOVERY_AWS_EDIT_VOL'] ?>
                    <span data-dismiss="drawer" aria-label="Close" class="drawer-close"><i class="viconfont vicon-guanbi"></i></span>
                </h4>
            </div>
            <div class="drawer-body">
                <!-- 卷名称 -->
                <div class="form-group mt30">
                    <label class="control-label col-md-3"><span class="required">* </span><?php echo $LANG['UI_RECOVERY_AWS_VOL_NAME'] ?></label>
                    <div class="col-md-6">
                        <input class="form-control" name="vol_name" maxlength="256">
                        <span class="huweicloud-vol-tips mt5" style="display: block"><?php echo $LANG['UI_RECOVERY_HUAWEICLOUD_VOL_NAME_ERROR_TIPS'] ?></span>
                    </div>
                </div>
                <!-- 卷类型 -->
                <div class="form-group mt30">
                    <label class="control-label col-md-3"><span class="required">* </span><?php echo $LANG['UI_RECOVERY_AWS_VOL_TYPE'] ?></label>
                    <div class="col-md-6">
                        <select class="form-control" name="volume_disk_type"></select>
                    </div>
                </div>
                <!-- IOPS -->
                <div class="form-group mt30 disk-iops-div">
                    <label class="control-label col-md-3"><span class="required">* </span><?php echo $LANG['UI_RECOVERY_AWS_VOL_IOPS'] ?></label>
                    <div class="col-md-6">
                        <div id="diskIopsDiv">
                            <div class="input-group spinner-group">
                                <input type="text" id="diskIops" name="disk_iops" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" >
                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                    <button type="button" class="btn spinner-up default input-sm">
                                        <i class="fa fa-angle-up"></i>
                                    </button>
                                    <button type="button" class="btn spinner-down default input-sm">
                                        <i class="fa fa-angle-down"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- 区域 -->
                <div class="form-group mt30">
                    <label class="control-label col-md-3"><span class="required">* </span><?php echo $LANG['UI_RECOVERY_AWS_REGION'] ?></label>
                    <div class="col-md-6">
                        <select class="form-control" name="region"></select>
                    </div>
                </div>
                <!-- 可用区 -->
                <div class="form-group mt30 az-div">
                    <label class="control-label col-md-3"><span class="required">* </span><?php echo $LANG['UI_RECOVERY_AWS_AVAILABLE_ZONE'] ?></label>
                    <div class="col-md-6">
                        <select class="form-control" name="az"></select>
                    </div>
                </div>
                <!-- kms -->
                <div class="form-group mt30 kms-div">
                    <label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_AWS_KMS_KEY'] ?></label>
                    <div class="col-md-6">
                        <select class="form-control kms-selector" name="kms"></select>
                    </div>
                    <div class="col-md-1 kms-tips pt-8">
                        <a class="popovers" data-container="body" data-trigger="hover"
                           data-placement="top" data-content="<?php echo $LANG['UI_RECOVERY_AWS_KMS_KEY_TIPS'] ?>">
                            <i class="viconfont vicon-tishi"></i>
                        </a>
                    </div>
                </div>
                <!-- 加密密码 -->
                <div class="form-group mt30 display-none encrypt-password">
                    <label class="control-label col-md-3"><span class="required">* </span><?php echo $LANG['UI_RECOVERY_AWS_ENCRYPT_PWD'] ?></label>
                    <div class="col-md-6">
                        <input class="form-control" type="password" name="encrypt_password" maxlength="256">
                    </div>
                </div>
                <!-- 实例终止时删除卷 -->
                <div class="form-group mt30 display-none">
                    <label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_CLOUD_DISK_DELETE_ON_TERMINATION'] ?></label>
                    <div class="col-md-6 mt3">
                        <input type="checkbox" id="diskDeleteOnTermination" name="disk_delete_on_termination" class="make-switch" data-on-color="primary" data-off-color="info" data-size="small"
                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                    </div>
                    <div class="col-md-1 ddot-tips pt-5 display-none">
                        <a class="popovers" data-container="body" data-trigger="hover"
                           data-placement="top" data-content="<?php echo $LANG['UI_RECOVERY_CLOUD_DISK_DELETE_ON_TERMINATION_HUAWEICLOUD_TIPS'] ?>">
                            <i class="viconfont vicon-tishi"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="drawer-footer">
                <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-primary" id="recover-vol-submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
                <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
            </div>
        </div>
    </div>
    <!-- drawer结束 -->
</div>
<script src="./scripts/platform/component/aws_recovery.js" type="text/javascript"></script>