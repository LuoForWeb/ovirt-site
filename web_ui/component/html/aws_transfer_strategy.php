<!-- 公有云恢复的传输策略，在传输策略tab-pane中引入该文件 -->
<div class="panel-body">
    <div class="tabbable-custom col-md-10" style="overflow: visible">
        <!-- 传输模式 -->
        <div class="form-group transportmodediv display-none">
            <label class="control-label col-md-3 transferlabel"> <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE'] ?></label>
            <div class="col-md-4">
                <select class="form-control select2me" id="transport_mode">
                    <option value="nbd"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD'] ?></option>
                    <option value="nbdssl"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBDSSL'] ?></option>
                </select>
            </div>
            <div class="col-md-2 form-group-content">
                <a>
                    <i class="viconfont vicon-tishi" data-container="body" data-toggle="tooltip" data-placement="right" data-html="true" title="<?php echo $LANG['UI_BACKUP_TRANSPORT_VMWARE_SELECT_TIPS'] ?>"></i>
                </a>
            </div>
        </div>

        <!-- 传输代理实例类型 -->
        <div class="form-group awsapplianceselectdiv">
            <label class="control-label col-md-3 awsapplianceselectlabel form-group-label"><?php echo $LANG['UI_BACKUP_AWS_APPLIANCE_SELECT'] ?></label>
            <div class="col-md-4 content">
                <select class="bootstrap-mutiple-select select2me selectpicker show-tick ignore" id="awsApplianceSelect" data-live-search="true">
                </select>
            </div>
            <div class="col-md-2 form-group-content">
                <a>
                    <i class="viconfont vicon-tishi" data-container="body" data-toggle="tooltip" data-placement="right" data-html="true" title="<?php echo $LANG['UI_BACKUP_AWS_APPLIANCE_SELECT_TIPS'] ?>"></i>
                </a>
            </div>
        </div>

        <!-- 传输线程 -->
        <div class="form-group">
            <label class="control-label col-md-3 threadnumlabel"><?php echo $LANG['UI_BACKUP_THREAD_NUM'] ?></label>
            <div class="col-md-4">
                <div id="awsRecoveryThreadDiv">
                    <div class="input-group spinner-group">
                        <input type="text" id="awsRecoveryThreadNum" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" >
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
            <div class="col-md-2 form-group-content">
                <a>
                    <i class="viconfont vicon-tishi" data-container="body" data-toggle="tooltip" data-placement="right" data-html="true" title="<?php echo $LANG['UI_BACKUP_THREAD_NUM_TIPS'] ?>"></i>
                </a>
            </div>
        </div>

        <!-- 传输代理绑定公有ip -->
        <div class="form-group display-none agent-public-ip">
            <label class="control-label col-md-3 publiciplabel form-group-label"><?php echo $LANG['UI_BACKUP_AWS_APPLIANCE_BIND_IP'] ?></label>
            <div class="col-md-4 content">
                <select class="form-control select2me" name="agent_public_ip" id="publicIpSelect">
                    <option value="" data-address=""><?php echo $LANG['UI_PUBLIC_SELECT'] ?></option>
                </select>
            </div>
            <div class="col-md-2 form-group-content">
                <a>
                    <i class="viconfont vicon-tishi" data-container="body" data-toggle="tooltip" data-placement="right" data-html="true" title="<?php echo $LANG['UI_BACKUP_AWS_APPLIANCE_BIND_IP_TIPS'] ?>"></i>
                </a>
            </div>
        </div>
        <!-- 公有ip付费方式 -->
        <div class="form-group display-none agent-public-ip-billing">
            <label class="control-label col-md-3"><?php echo $LANG['BILLING_BUY_WAY'] ?></label>
            <div class="col-md-4">
                <select class="form-control" name="agent_billing_type">
                    <option value="bandwidth"><?php echo $LANG['UI_RECOVERY_CLOUD_BILLING_WAY_BANDWIDTH'] ?></option>
                    <option value="traffic" selected><?php echo $LANG['UI_RECOVERY_CLOUD_BILLING_WAY_TRAFFIC'] ?></option>
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
            <div class="col-md-1 form-group-content">
                <a>
                    <i class="viconfont vicon-tishi" data-container="body" data-toggle="tooltip" data-placement="right" data-html="true" title="<?php echo $LANG['UI_RECOVERY_CLOUD_BILLING_TIPS'] ?>"></i>
                </a>
            </div>
        </div>
        <!-- 带宽/流量大小 -->
        <div class="form-group display-none agent-public-ip-billing">
            <label class="control-label col-md-3">
                <span class="label-title"><?php echo $LANG['UI_RECOVERY_CLOUD_BANDWIDTH_SIZE'] ?></span>
            </label>
            <div class="col-md-4">
                <input class="spinner-input form-control" type="text" name="agent_billing_value"
                       min="1" max="300" step="1" maxlength="3" onkeyup="value=value.replace(/[^\d]/g,'')" value="300">
            </div>
            <div class="col-md-2 mt5">
                <span>Mbit/s</span>
            </div>
        </div>
        <!-- 传输代理网络配置 -->
        <div class="applianceNetwork">

        </div>

        <!-- 重连次数 -->
        <div class="form-group reconnect-times-wrapper display-none">
            <label class="control-label col-md-3 reconnect-times-Label"><?php echo $LANG['UI_BACKUP_RECONNECT_TIMES'] ?></label>
            <div class="col-md-4">
                <input type="text" id="reconnect_time" class="spinner-input form-control input-sm" value="5" maxlength="3" onkeyup="value=value.replace(/[^\d]/g,'')">
            </div>
            <div class="col-md-2 form-group-content">
                <a>
                    <i class="viconfont vicon-tishi" data-container="body" data-toggle="tooltip" data-placement="right" data-html="true" title="<?php echo $LANG['UI_BACKUP_RECONNECT_TIMES_TIPS'] ?>"></i>
                </a>
            </div>
        </div>
        <!-- 重连时间间隔 -->
        <div class="form-group reconnect-Interval-wrapper display-none">
            <label class="control-label col-md-3 reconnect-Interval-Label"><?php echo $LANG['UI_BACKUP_RECONNECT_INTERVAL'] ?></label>
            <div class="col-md-4">
                <input type="text" id="reconnect_interval" class="spinner-input form-control input-sm" value="5" onkeyup="value=value.replace(/[^\d]/g,'')">
            </div>
            <div class="col-md-2 form-group-content">
                <a>
                    <i class="viconfont vicon-tishi" data-container="body" data-toggle="tooltip" data-placement="right" data-html="true" title="<?php echo $LANG['UI_BACKUP_RECONNECT_INTERVAL_TIPS'] ?>"></i>
                </a>
            </div>
        </div>
        <!-- 传输加密开关 -->
        <div class="form-group encrypttransferdiv">
            <label class="control-label col-md-3 encrypttransferlabel form-group-label"><?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER'] ?></label>
            <div class="col-md-4 form-group-content">
                <input type="checkbox" id="awsencrypttransfer"  class="make-switch" data-size="small"  data-on-color="primary" data-off-color="info"
                       data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                <a class="ml15">
                    <i class="viconfont vicon-tishi" data-container="body" data-toggle="tooltip" data-placement="right" data-html="true" title="<?php echo $LANG['UI_BACKUP_RECONNECT_INTERVAL_TIPS'] ?>"></i>
                </a>
            </div>
        </div>
        <!-- 传输加密算法 -->
        <div class="form-group transfer-encrypt-method-form display-none">
            <label class="control-label transfer-encrypt-method-label col-md-3"><?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?></label>
            <div class="col-md-4">
                <select class="form-control select2me input-sm" id="awsTransferEncryptMethod">
                    <?php
                    foreach ($CONF['TRANSPORT_ENCRYPT_METHOD'] as $index => $encryptMethod) {
                        if ($_SESSION['language'] != "zh-cn" && $_SESSION['language'] != "zh-tw" && $index == 2) {
                        } else {
                            echo '<option value="' . $index . '">' . $encryptMethod . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
        </div>
    </div>
</div>
<script src="./scripts/platform/component/aws_recovery_transfer.js" type="text/javascript"></script>