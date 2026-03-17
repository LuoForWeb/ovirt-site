<!-- 虚拟机迁移的传输策略，在传输策略tab-pane中引入该文件 -->
<link rel="stylesheet" type="text/css" href="./css/platform/component/resource_pool.css" />

<div class="panel-body">
    <div class="tabbable-custom pdlr15  col-md-10" >
        <div class="form-group display-none transportdiv">
            <label class="control-label col-md-3 transferlabel">
                <span class="required">* </span>
                <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE'] ?>
            </label>
            <div class="col-md-4">
                <select class="form-control select2me" id="transport_mode">
                    <option value="nbd"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD_VMWARE'] ?></option>
                    <option value="nbdssl"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBDSSL_VMWARE'] ?></option>
                    <option value="san"><?php echo $LANG['UI_BACKUP_TRANSPORT_SAN'] ?></option>
                    <option value="hotadd"><?php echo $LANG['UI_BACKUP_TRANSPORT_HOT_VMWARE'] ?></option>
                </select>
                <div>
                    <span class="font-danger display-none" id="vmware_hotadd_tips"><?php echo $LANG['UI_BACKUP_TRANSPORT_HOT_VMWARE_TIPS']?></span>
                </div>
            </div>
            <div class="col-md-2 form-group-content">
                <a>
                    <i class="viconfont vicon-tishi" data-container="body" data-toggle="tooltip" data-placement="right" data-html="true" title="<?php echo $LANG['UI_BACKUP_TRANSPORT_VMWARE_SELECT_TIPS']?>"></i>
                </a>
            </div>
        </div>

        <!-- ICS VVDK传输模式 -->
        <div class="form-group icsvvdktransportdiv display-none">
            <label class="control-label col-md-3 transferlabel form-group-label"> <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE']?></label>
            <div class="col-md-4 form-group-content">
                <select class="form-control select2me" id="icsvvdktransport_mode">
                    <option value="1"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD']?></option>
                    <option value="3"><?php echo $LANG['UI_PLATFORM_APPLIANCE']?></option>
                </select>
            </div>
            <div class="col-md-2 form-group-content">
                <a>
                    <i class="viconfont vicon-tishi" data-container="body" data-toggle="tooltip" data-placement="right" data-html="true" title="<?php echo $LANG['UI_BACKUP_TRANSPORT_ICS_VVDK_SELECT_TIPS'] . '<br><br>' . $LANG['UI_BACKUP_TRANSPORT_SELECT_NBD_TIPS']?>"></i>
                </a>
            </div>
        </div>

        <!-- 华为传输模式 -->
        <div class="form-group huaweikvmtransportdiv display-none">
            <label class="control-label col-md-3 huaweikvmtransferlabel"> <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE'] ?></label>
            <div class="col-md-4">
                <select class="form-control select2me" id="huaweikvmtransport_mode">
                    <option value="1"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD'] ?></option>
                    <option value="5"><?php echo $LANG['UI_BACKUP_TRANSPORT_ADAPTIVE'] ?></option>
                </select>
            </div>
            <div class="col-md-2 form-group-content">
                <a>
                    <i class="viconfont vicon-tishi" data-container="body" data-toggle="tooltip" data-placement="right" data-html="true" title="<?php echo $LANG['UI_BACKUP_TRANSPORT_HUAWEI_KVM_SELECT_TIPS']?>"></i>
                </a>
            </div>
        </div>

        <div class="form-group display-none huaweitransportdiv">
            <label class="control-label col-md-3 huaweitransferlabel">
                <span class="required">* </span>
                <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE'] ?>
            </label>
            <div class="col-md-4">
                <select class="form-control select2me" id="huaweitransport_mode">
                    <option value="nbd"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD'] ?></option>
                    <option value="nbdssl"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBDSSL'] ?></option>
                </select>
            </div>
        </div>

        <!-- XenServer传输模式 -->
        <div class="form-group  xentransdiv display-none">
            <label class="control-label col-md-3 xentranslabel">
                <span class="required">* </span>
                <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE'] ?>
            </label>
            <div class="col-md-4">
                <select class="form-control select2me" id="xentransmode">
                    <option value="1"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD'] ?></option>
                    <option value="2"><?php echo $LANG['UI_BACKUP_TRANSPORT_SAN'] ?></option>
                    <!-- <option value="3"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD_XENSERVER'] ?></option>
								    <option value="4"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD_SAN_XENSERVER'] ?></option> -->
                </select>
            </div>
        </div>

        <!-- 类XenServer传输模式 -->
        <div class="form-group  leixentransdiv display-none">
            <label class="control-label col-md-3 leixentranslabel">
                <span class="required">* </span>
                <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE'] ?>
            </label>
            <div class="col-md-4">
                <select class="form-control select2me" id="leixentransmode">
                    <option value="1"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD'] ?></option>
                    <option value="2"><?php echo $LANG['UI_BACKUP_TRANSPORT_SAN'] ?></option>
                </select>
            </div>
        </div>

        <!-- oVirt/RHV传输模式 -->
        <div class="form-group redhattransdiv display-none">
            <label class="control-label col-md-3 redhattranslabel"> <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE'] ?></label>
            <div class="col-md-4">
                <select class="form-control select2me" id="redhattransmode">
                    <option value="1"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD'] ?></option>
                    <option value="2"><?php echo $LANG['UI_BACKUP_TRANSPORT_SAN'] ?></option>
                    <option value="5">ImageIO</option>
                </select>
            </div>
            <div class="col-md-2 form-group-content">
                <a>
                    <i class="viconfont vicon-tishi" data-container="body" data-toggle="tooltip" data-placement="right" data-html="true" title="<?php echo $LANG['UI_BACKUP_TRANSPORT_RHV_SELECT_TIPS']?>"></i>
                </a>
            </div>
        </div>

        <!-- openstack传输模式 -->
        <div class="form-group openstacktransdiv display-none">
            <label class="control-label col-md-3 openstacktranslabel"> <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE'] ?></label>
            <div class="col-md-4">
                <select class="form-control select2me" id="openstacktransmode">
                    <option value="1"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD'] ?></option>
                    <option value="4"><?php echo $LANG['UI_BACKUP_TRANSPORT_SAN'] ?></option>
                    <option value="3"><?php echo $LANG['UI_PLATFORM_APPLIANCE'] ?></option>
                </select>
            </div>
            <div class="col-md-2 form-group-content">
                <a>
                    <i class="viconfont vicon-tishi" data-container="body" data-toggle="tooltip" data-placement="right" data-html="true" title="<?php echo $LANG['UI_BACKUP_TRANSPORT_LEIXEN_SELECT_TIPS']?>"></i>
                </a>
            </div>
        </div>

        <!-- vmware传输压缩 -->
        <div class="form-group transferCompressSwitchDiv display-none">
            <label class="control-label col-md-3 form-group-label transferCompressSwitchLabel"> <?php echo $LANG['UI_BACKUP_TRANSFER_COMPRESS']?></label>
            <div class="col-md-4 form-group-content">
                <input type="checkbox" id="transferCompressSwitch"
                       class="make-switch"
                       data-on-color="primary"
                       data-off-color="info" data-size="small"
                       data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                <a class="ml15">
                    <i class="viconfont vicon-tishi" data-container="body" data-toggle="tooltip" data-placement="right" data-html="true" title="<?php echo $LANG['UI_BACKUP_TRANSFER_COMPRESS_TIPS']?>"></i>
                </a>
            </div>
        </div>
        <div class="form-group transferCompressDiv display-none">
            <label class="control-label col-md-3 form-group-label transferCompressLabel"> <?php echo $LANG['UI_BACKUP_TRANSFER_COMPRESS_METHOD']?></label>
            <div class="col-md-4 form-group-content">
                <select class="form-control select2me" id="transferCompress">
                    <option value="fastlz">fastlz</option>
                    <option value="zlib">zlib</option>
                    <option value="skipz">skipz</option>
                    <option value="unzip" class="display-none"><?php echo $LANG['UI_BACKUP_TRANSFER_COMPRESS_NOT_USE']?></option>
                </select>
            </div>
        </div>

        <div class="form-group threadnumdiv display-none">
            <label class="control-label col-md-3 threadnumlabel">
                <span class="required">* </span>
                <?php echo $LANG['UI_BACKUP_THREAD_NUM'] ?>
            </label>
            <div class="col-md-4">
                <div id="threadNumDiv">
                    <div class="input-group spinner-group">
                        <input type="text" id="threadNum" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control">
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
            </div>
        </div>

        <div class="form-group appliancediv display-none">
            <label class="control-label col-md-3 appliancelabel form-group-label"><?php echo $LANG['UI_PLATFORM_APPLIANCE'] ?></label>
            <div class="col-md-4 form-group-content">
                <input type="checkbox" id="appliancecheck" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                <a>
                    <i class="viconfont vicon-tishi" data-container="body" data-toggle="tooltip" data-placement="right" data-html="true" title="<?php echo $LANG['UI_BACKUP_APPLIANCE_DES']?>"></i>
                </a>
            </div>
        </div>

        <div class="form-group display-hide applianceselectdiv">
            <label class="control-label col-md-3 applianceselectlabel"><?php echo $LANG['UI_APPLIANCE_SELECT'] ?></label>
            <div class="col-md-4">
                <ul class="ztree" id="transferAgentTree"></ul>
            </div>
        </div>

        <!-- 传输加密开关 -->
        <div class="form-group display-hide encrypttransferdiv">
            <label class="control-label col-md-3 encrypttransferlabel"><?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER']?></label>
            <div class="col-md-4">
                <input type="checkbox" id="encrypttransfer"  class="make-switch" data-size="small" data-on-color="primary" data-off-color="info"
                       data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>"
                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
                <a>
                    <i class="viconfont vicon-tishi" data-container="body" data-toggle="tooltip" data-placement="right" data-html="true" title="<?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER_TIPS']?>"></i>
                </a>
            </div>
        </div>

        <!-- 传输加密算法 -->
        <div class="form-group transfer-encrypt-method-form display-none">
            <label class="control-label transfer-encrypt-method-label col-md-3"><?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?></label>
            <div class="col-md-4">
                <select class="form-control select2me" id="transferEncryptMethod">
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

        <div class="form-group display-none systemIpdiv">
            <label class="control-label col-md-3 serveriplabel"><?php echo $LANG['UI_BACKUP_NODE_TRANSFER_IP'] ?>
            </label>
            <div class="col-md-4">
                <div class="selectipdiv">
                    <select class="form-control" id="systemIp">
                    </select>
                    <div>
										<span class="help-block "><?php echo $LANG['UI_VM_SELEC_BAKNODE_IP'] ?>
											<a id="diysystemip"><?php echo $LANG['UI_INSTANT_VM_TARGET_DIY'] ?></a></span>
                    </div>
                </div>
                <div class="input-icon right mb15 display-none inputipdiv">
                    <i class="fa"></i>
                    <input type="text" maxlength="128" class="form-control" id="inputIp" onkeyup="customInputValidate('ip', this.value, $(this))"/>
                    <div>
										<span class="help-block "><?php echo $LANG['UI_VM_INPUT_BAKNODE_IP'] ?>
											<a id="selectsystemip"><?php echo $LANG['UI_INSTANT_VM_TARGET_DIY2'] ?></a></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group display-none ipSegmentDiv">
            <label class="control-label col-md-3 ipsegmentlabel"><?php echo $LANG['UI_VM_DATA_TRANSFER_NET_SEGMENT'] ?>
            </label>
            <div class="col-md-4">
                <input type="text" maxlength="128" class="form-control" id="ipSegment" />
            </div>
            <div class="col-md-1 form-group-content">
                <a>
                    <i class="viconfont vicon-tishi" data-container="body" data-toggle="tooltip" data-placement="right" data-html="true" title="<?php echo $LANG['UI_VM_DATA_TRANSFER_NET_SEGMENT_TIPS']?>"></i>
                </a>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="./scripts/platform/component/transfer_agent.js"></script>
<script src="./scripts/platform/component/vm_motion_transfer_strategy.js" type="text/javascript"></script>
