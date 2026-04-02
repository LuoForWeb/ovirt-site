<?php include_once '../../tpl/permission.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->

<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row page-content-row">
	<div class="col-md-12 page-content-col">
		<input id="task_uuid" value="<?php echo $_GET['uuid']; ?>" class="display-none"></input>
		<input id="hypervisor" value="<?php echo $_GET['submodule']; ?>" class="display-none"></input>
		<input id="tasktype" value="<?php echo $_GET['tasktype']; ?>" class="display-none"></input>
		<div class="portlet box blue-hoki" id="motioncontent">
			<div class="portlet-title">
				<div class="caption">
					<i class="viconfont vicon-ge_migration"></i>
					<div class="caption__theme">
						<span><?php echo $LANG['UI_MOTION_VM'] ?></span>
					</div>
					<div class="caption__remark">
						<span><?php echo $LANG['UI_MOTION_VM_DES'] ?></span>
					</div>
				</div>
			</div>
			<div class="portlet-body form">
				<form action="#" id="motionform" class="form-horizontal">
					<div class="form-body">
						<div class="alert alert-danger display-none setmotionVMname">
						</div>
						<div class="form-group host_tree_div" id="selectHost">
							<label class="control-label col-md-3">
								<span class="required">* </span>
								<?php echo $LANG['UI_RECOVERY_SELECT_HOST'] ?>
							</label>
							<div class="col-md-6 tree_div2">
								<ul id="host_tree" class="ztree bd1de5" name="hosttree"></ul>
								<div><span class="help-block ">
										<?php echo $LANG['UI_MOTION_VM_HOST'] ?>
									</span></div>
							</div>
						</div>
						<div class="form-group display-hide" id="nohosttips">
							<label class="control-label col-md-3">
								<span class="required">* </span>
								<?php echo $LANG['UI_RECOVERY_SELECT_HOST'] ?>
							</label>
							<div class="col-md-6 alert alert-block alert-info fade in">
								<button type="button" class="close" data-dismiss="alert"></button>
								<h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
								<ol class="alert-ol">
									<li>
										<?php echo $LANG['UI_RECOVERY_NO_HOST'] ?>
									</li>
									<li>
										<?php
										//if(empty($_SESSION['tenantuuid'])){
										echo '<p><a id="toaddvcenter"><small>' . $LANG['UI_RECOVERY_ADD_VCENTER_TIPS'] . '</small></a></p>';
										//}
										?>
									</li>
								</ol>
							</div>
						</div>

						<div class="form-group pt50 display-none usergroup_tree_div" id="selectUsergroup">
							<label class="control-label col-md-3">
								<span class="required">* </span>
								<?php echo $LANG['UI_RECOVERY_SELECT_USER_GROUP'] ?>
							</label>
							<div class="col-md-6 tree_div2">
								<ul id="usergroup_tree" class="ztree bd1de5"></ul>
								<div><span class="help-block ">
										<?php echo $LANG['UI_RECOVERY_SELECT_USER_GROUP_TIPS'] ?>
									</span></div>
							</div>
						</div>

						<div class="form-group display-none groupdiv">
							<label class="control-label col-md-3">
								<span class="required">* </span>
								<?php echo $LANG['UI_RECOVERY_INPUT_USER_NMAE'] ?>
							</label>
							<div class="col-md-3">
								<div class="input-icon right ">
									<i class="fa"></i>
									<select class="form-control" id="groupusername"></select>
									<div><span class="help-block "><?php echo $LANG['UI_RECOVERY_SELECT_GROUP_USER_NAME'] ?></span></div>
								</div>
							</div>
							<div class="col-md-3">
								<div class="input-icon right ">
									<i class="fa"></i>
									<input type="password" autocomplete="off" maxlength="128" class="form-control" id="grouppassword" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')" />
									<div><span class="help-block "><?php echo $LANG['UI_RECOVERY_INPUT_PASSWORD'] ?></span></div>
								</div>
							</div>
							<div class="col-md-3">
								<div class="btn-group" style="margin-top: 6px;">
									<button type="button" id="verifyuser" class="btn btn-sm green-haze">
										<?php echo $LANG['UI_RECOVERY_VERIFY_USER'] ?></button>
								</div>
							</div>
						</div>


						<div class="form-group display-none vmdiv">
							<label class="control-label col-md-3">
								<span class="required">* </span>
								<?php echo $LANG['UI_RECOVERY_VM_SETTING'] ?>
							</label>
							<!--     						<div class="col-md-6" id="vmconfig"> -->


							<!--     						</div> -->
							<div class="col-md-9" style="padding-right: 20px;">
								<div class="portlet">
									<div class="portlet-body" id="accordionvmdiv">
										<div class="panel-group accordion" id="accordionvm">
                                            <?php include_once('../../content/platform/component/vm_recovery.php'); ?>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="form-group display-none transportdiv">
							<label class="control-label col-md-3 transferlabel">
								<span class="required">* </span>
								<?php echo $LANG['UI_BACKUP_TRANSPORT_MODE'] ?>
							</label>
							<div class="col-md-2">
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
                            <div class="col-md-2 mt10">
                                <a class="popovers" data-container="body" data-trigger="hover" data-html="true"
                                   data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_VMWARE_SELECT_TIPS']?>">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                            </div>
						</div>

						<!-- 华为传输模式 -->
						<div class="form-group huaweikvmtransportdiv display-none">
							<label class="control-label col-md-3 huaweikvmtransferlabel"> <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE'] ?></label>
							<div class="col-md-2">
								<select class="form-control select2me" id="huaweikvmtransport_mode">
									<option value="1"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD'] ?></option>
									<option value="5"><?php echo $LANG['UI_BACKUP_TRANSPORT_ADAPTIVE'] ?></option>
								</select>
							</div>
							<div class="col-md-2 mt10">
								<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_HUAWEI_KVM_SELECT_TIPS'] ?>">
									<i class="viconfont vicon-tishi"></i>
								</a>
							</div>
						</div>

						<div class="form-group display-none huaweitransportdiv">
							<label class="control-label col-md-3 huaweitransferlabel">
								<span class="required">* </span>
								<?php echo $LANG['UI_BACKUP_TRANSPORT_MODE'] ?>
							</label>
							<div class="col-md-2">
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
							<div class="col-md-2">
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
							<div class="col-md-2">
								<select class="form-control select2me" id="leixentransmode">
									<option value="1"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD'] ?></option>
									<option value="2"><?php echo $LANG['UI_BACKUP_TRANSPORT_SAN'] ?></option>
								</select>
							</div>
						</div>

						<!-- oVirt/RHV传输模式 -->
						<div class="form-group redhattransdiv display-none">
							<label class="control-label col-md-3 redhattranslabel"> <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE'] ?></label>
							<div class="col-md-2">
								<select class="form-control select2me" id="redhattransmode">
									<option value="1"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD'] ?></option>
									<option value="2"><?php echo $LANG['UI_BACKUP_TRANSPORT_SAN'] ?></option>
									<option value="5">ImageIO</option>
								</select>
							</div>
							<div class="col-md-2 mt10">
								<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_RHV_SELECT_TIPS'] ?>">
									<i class="viconfont vicon-tishi"></i>
								</a>
							</div>
						</div>


						<!-- openstack传输模式 -->
						<div class="form-group openstacktransdiv display-none">
							<label class="control-label col-md-3 openstacktranslabel"> <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE'] ?></label>
							<div class="col-md-2">
								<select class="form-control select2me" id="openstacktransmode">
									<option value="1"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD'] ?></option>
									<option value="4"><?php echo $LANG['UI_BACKUP_TRANSPORT_SAN'] ?></option>
									<option value="3"><?php echo $LANG['UI_PLATFORM_APPLIANCE'] ?></option>
								</select>
							</div>
							<div class="col-md-2 mt10">
								<a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_LEIXEN_SELECT_TIPS'] ?>">
									<i class="viconfont vicon-tishi"></i>
								</a>
							</div>
						</div>

                        <!-- vmware传输压缩 -->
                        <div class="form-group transferCompressDiv display-none">
                            <label class="control-label col-md-3 form-group-label transferCompressLabel"> <?php echo $LANG['UI_BACKUP_TRANSFER_COMPRESS']?></label>
                            <div class="col-md-2 form-group-content">
                                <select class="form-control select2me" id="transferCompress">
                                    <option value="fastlz">fastlz</option>
                                    <option value="zlib">zlib</option>
                                    <option value="skipz">skipz</option>
                                    <option value="unzip"><?php echo $LANG['UI_BACKUP_TRANSFER_COMPRESS_NOT_USE']?></option>
                                </select>
                            </div>
                            <div class="col-md-2 mt5">
                                <a class="popovers" data-container="body" data-trigger="hover" data-html="true"
                                   data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_TRANSFER_COMPRESS_TIPS']?>">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                            </div>
                        </div>

                        <div class="form-group display-none ipSegmentDiv">
                            <label class="control-label col-md-3 ipsegmentlabel"><?php echo $LANG['UI_VM_DATA_TRANSFER_NET_SEGMENT'] ?>
                            </label>
                            <div class="col-md-2">
                                <input type="text" maxlength="128" class="form-control" id="ipSegment" />
                            </div>
                            <div class="col-md-1 mt10">
                                <a class="popovers" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php echo $LANG['UI_VM_DATA_TRANSFER_NET_SEGMENT_TIPS'] ?>">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                            </div>
                        </div>

                        <div class="form-group threadnumdiv display-none">
                            <label class="control-label col-md-3 threadnumlabel">
                                <span class="required">* </span>
                                <?php echo $LANG['UI_BACKUP_THREAD_NUM'] ?>
                            </label>
                            <div class="col-md-2">
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
							<div class="col-md-2 form-group-content">
								<input type="checkbox" id="appliancecheck" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
								<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_APPLIANCE_DES'] ?>">
									<i class="viconfont vicon-tishi"></i>
								</a>
							</div>
						</div>
						<div class="form-group display-hide applianceselectdiv">
							<label class="control-label col-md-3 applianceselectlabel"><?php echo $LANG['UI_APPLIANCE_SELECT'] ?></label>
							<div class="col-md-2">
								<select class="form-control select2me" id="applianceSelect">
								</select>
							</div>
						</div>

                        <!-- 传输加密开关 -->
                        <div class="form-group display-hide encrypttransferdiv">
                            <label class="control-label col-md-3 encrypttransferlabel"><?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER']?></label>
                            <div class="col-md-4">
                                <input type="checkbox" id="encrypttransfer"  class="make-switch" data-size="small" data-on-color="primary" data-off-color="info"
                                       data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>"
                                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
                                <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true"
                                   data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER_TIPS']?>">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                            </div>
                        </div>
                        <!-- 传输加密算法 -->
                        <div class="form-group transfer-encrypt-method-form display-none">
                            <label class="control-label transfer-encrypt-method-label col-md-3"><?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?></label>
                            <div class="col-md-2">
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
							<label class="control-label col-md-3 serveriplabel"><?php echo $LANG['UI_VM_BAKSYSTEM_IP'] ?>
							</label>
							<div class="col-md-2">
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
									<input type="text" maxlength="128" class="form-control" id="inputIp" />
									<div>
										<span class="help-block "><?php echo $LANG['UI_VM_INPUT_BAKNODE_IP'] ?>
											<a id="selectsystemip"><?php echo $LANG['UI_INSTANT_VM_TARGET_DIY2'] ?></a></span>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="form-actions">
						<div class="row">
							<div class="col-md-offset-6 col-md-6">
								<button type="button" id="cancelbtn" class="btn default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
								<button type="button" id="submitbtn" class="btn green-haze btn-confirm"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<?php
if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
	//如果不是英文,加载语言包
	echo '<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>';
}
if ($_SESSION['language'] != "en-us") {
	//如果不是英文,加载语言包
	echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
		$_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/fuelux/js/spinner.min.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script src="./scripts/vm/vmmotion.js" type="text/javascript"></script>