<link rel="stylesheet" type="text/css" href="./css/platform/vm_machine/machine.css" />
<div class="panel-body" id="vm-machine-modal-body">
    <!-- 基本信息 -->
    <div class="form-group verifyDiv">
        <div class="col-md-12 accordion strategyOne" >
            <div class="panel panel-default strategy-panel">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        <a class="accordion-toggle accordion-toggle-styled"
                           data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#basicInfo" aria-expanded="true">
                            <i class="viconfont vicon-ge_summary font-green-seagreen"></i>
                            <span class="font-green-seagreen"><?php echo $LANG['UI_VM_MACHINE_BASIC_INFO']?></span>
                            <span class="strategyDes backupVerifyDes"></span>
                        </a>
                    </h4>
                </div>
                <div id="basicInfo" class="panel-collapse collapse in">
                    <div class="panel-body">
                        <div class="col-md-12">
                            <div class="form-group">
                                <!-- 所在节点 -->
                                <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_DATA_REPORT_NODE_NAME']?></label>
                                <div class="col-md-6">
                                    <select class="form-control select2me" id="nodeSelect_vm" >
                                    </select>
                                </div>
                            </div>

                            <div class="form-group threadDiv">
                                <label class="control-label col-md-3 form-group-label"><?php echo $LANG['UI_VM_MACHINE_TITLE']?></label>
                                <div class="col-md-6">
                                    <div class="">
                                        <div class="input-group spinner-group">
                                            <input type="text" id="modal_vm_name" maxlength="36" name="modal_vm_name" class="spinner-input form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group threadDiv">
                                <!-- cpu总核数 -->
                                <label class="control-label col-md-3"><?php echo $LANG['UI_VM_MACHINE_CPU_USE']?></label>
                                <div class="col-md-6">
                                    <div class="input-group width-100">
                                        <input id="modal_v_cpu" name="modal_v_cpu" value="1" type="text"  onkeyup="value=value.replace(/[^\d]/g,'')" maxlength="64" class="table-group-action-input form-control" aria-controls="example">
                                        <span class="input-group-btn">
                					        <button class="btn" type="button"><?php echo $LANG['UI_VM_MACHINE_CORS']?></button>
                					    </span>
                                    </div>
                                    <div>
                                    <span class="help-block">
                                        <?php echo $LANG['UI_VM_MACHINE_MAX_CORS']?>:<span id="modal_least_cpu">0</span><?php echo $LANG['UI_VM_MACHINE_CORS']?>
                                    </span>
                                    </div>
                                </div>
                            </div>

                            <!--内存大小-->
                            <div class="form-group threadDiv">
                                <label class="control-label col-md-3 threadnumlabel form-group-label"><?php echo $LANG['UI_VM_MACHINE_MEMS']?></label>
                                <div class="col-md-4 form-group-content">
                                    <div class="backupThreadDiv">
                                        <input type="text" id="modal_v_memory" value="1024" class="form-control input-sm" maxlength="16">
                                        <div class="line-height12">
                                                <span class="help-block" style="width: 150%;">
                                                    <?php echo $LANG['UI_VM_MACHINE_MAX_MEMS']?>:<span id="modal_least_memory">0</span><span id="modal_least_memory_unit"><?php echo $LANG['UI_VM_MACHINE_MEMS_UNIT']?></span>
                                                </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 padding-l-w">
                                    <select class="form-control" id="modal_v_memory_type" name="modal_v_memory_type">
                                        <option value="1"><?php echo $LANG['UI_VM_MACHINE_MEMS_UNIT']?></option>
                                        <option value="2" selected><?php echo $LANG['UI_VM_MACHINE_MEMS_UNIT2']?></option>
                                        <option value="3"><?php echo $LANG['UI_VM_MACHINE_MEMS_UNIT3']?></option>
                                    </select>
                                </div>
                            </div>

                            <!--引导固件-->
                            <div class="form-group threadDiv" style="margin-top:35px;">
                                <label class="control-label col-md-3"><?php echo $LANG['UI_VM_MACHINE_BOOT_FIREWARE']?></label>
                                <div class="col-md-6">
                                    <select class="form-control select2me" name="modal_boot_firmware" id="modal_boot_firmware">
                                        <option value="1">BIOS</option>
                                        <option value="2">UEFI</option>
                                    </select>
                                </div>
                            </div>

                            <!--cpu模式-->
                            <div class="form-group threadDiv">
                                <label class="control-label col-md-3"><?php echo $LANG['UI_VM_MACHINE_CPU_MODEL']?></label>
                                <div class="col-md-6">
                                    <select class="form-control select2me" name="modal_cpu_mode" id="modal_cpu_mode">

                                    </select>
                                </div>
                                <div class="col-md-2 mt3 vm-popovers">
                                    <a class="popovers modal_cpu_mode" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="" data-original-title="" title="">
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

    <!-- 网络配置 -->
    <div class="form-group verifyDiv">
        <div class="col-md-12 accordion strategyOne" >
            <div class="panel panel-default strategy-panel">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        <a class="accordion-toggle accordion-toggle-styled collapsed popovers"
                           data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#networkInfo" aria-expanded="true">
                            <i class="viconfont vicon-wangluopeizhi font-green-seagreen"></i>
                            <span class="font-green-seagreen"><?php echo $LANG['UI_VM_MACHINE_NETWORKS']?></span>
                            <span class="strategyDes vmMachineDes"></span>
                        </a>
                    </h4>
                </div>
                <div id="networkInfo" class="panel-collapse collapse ">
                    <div class="panel-body">
                        <div class="col-md-12" id="show_card_list">
                            <div class="network_card">
                                <div class="show_card_del">
                                    <span class="del_card del_network_card" data-num="0"></span>
                                    <span class="alert_card_del">
                                        <div><?php echo $LANG['UI_VM_MACHINE_SURE_DEL']?></div>
                                        <div class="alert_card_btn_group">
                                             <div class="alert_card_submit"><?php echo $LANG['UI_VM_MACHINE_SURE_CONFIRM']?></div>
                                            <div class="alert_card_cacel"><?php echo $LANG['UI_VM_MACHINE_SURE_CANCEL']?></div>
                                        </div>
                                    </span>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3 "><?php echo $LANG['UI_VM_MACHINE_NETWORK_SET']?></label>
                                    <div class="col-md-6">
                                        <select class="form-control select2me" name="modal_network_list">
                                        </select>
                                    </div>
                                    <div class="col-md-2 mt3 vm-popovers">
                                        <a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VM_MACHINE_NEWWORK_CONFIGURE']?>" data-original-title="" title="">
                                            <i class="viconfont vicon-tishi"></i>
                                        </a>
                                    </div>
                                </div>
                                <input type="hidden" name="modal_network_uuid" class="form-control">


                                <div class="form-group">
                                    <label class="control-label col-md-3 "><?php echo $LANG['UI_VM_MACHINE_CARD_NAME']?></label>
                                    <div class="col-md-6">
                                        <input type="text" name="modal_network_name" class="form-control" maxlength="64">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3"><?php echo $LANG['UI_VM_MACHINE_NETWORK_INTETFACE']?></label>
                                    <div class="col-md-6">
                                        <select class="form-control select2me" name="modal_model_type" id="modal_model_type">
                                            <option value="1">virtio</option>
                                            <option value="2">e1000</option>
                                            <option value="3">rtl8139</option>
                                            <option value="4">ne2k_pci</option>
                                            <option value="5">virtio_net_pci</option>
                                            <option value="6">pcnet</option>
                                            <option value="7">vmxnet3</option>
                                        </select>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="modal-network-footer">
                        <div class="left_btn">
                            <!--
                            <a class="popovers vm-popovers-padd-left" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="" data-original-title="" title="">
                                <i class="viconfont vicon-tishi"></i>
                            </a>-->
                        </div>
                        <div class="right_btn">
                            <?php echo $LANG['UI_VM_MACHINE_NETWORK_ADD']?>
                            <a class="popovers vm-popovers-padd-left" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VM_MACHINE_NETWORK_ADD_TIPS']?>" data-original-title="" title="">
                                <i class="viconfont vicon-tishi"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 高级配置 -->
    <div class="form-group verifyDiv display-none">
        <div class="col-md-12 accordion strategyOne" >
            <div class="panel panel-default strategy-panel">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        <a class="accordion-toggle accordion-toggle-styled collapsed"
                           data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#highInfo" aria-expanded="true">
                            <i class="viconfont vicon-gaojipeizhi font-green-seagreen"></i>
                            <span class="font-green-seagreen"><?php echo $LANG['UI_VM_MACHINE_HIGH_SETT']?></span>
                            <span class="strategyDes backupVerifyDes"></span>
                        </a>
                    </h4>
                </div>
                <div id="highInfo" class="panel-collapse collapse">
                    <div class="panel-body">
                        <div class="col-md-12">


                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo $LANG['UI_VM_MACHINE_BOOT_MODE']?></label>
                                <div class="col-md-6">
                                    <select class="form-control select2me" name="modal_boot_mode" id="modal_boot_mode">
                                        <option value="2"><?php echo $LANG['UI_VM_MACHINE_BOOT_MODE_CDROWS']?></option>
                                        <option value="1"><?php echo $LANG['UI_VM_MACHINE_BOOT_MODE_DISKS']?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group display-none" id="modal_cd_dvd_show">
                                <label class="control-label col-md-3"><?php echo $LANG['UI_VM_MACHINE_CD_DVD']?></label>
                                <div class="col-md-6">
                                    <select class="form-control select2me" id="cddvdSelect" >
                                    </select>
                                </div>
                            </div>



                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="./scripts/platform/vm_machine/machine.js"></script>
