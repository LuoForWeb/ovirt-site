<link rel="stylesheet" type="text/css" href="./css/platform/component/style.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/component/driver_check.css" />

<link href="/assets/global/plugins/select2/select2-4.1.0/css/select2.min.css"rel="stylesheet" type="text/css"/>
<div class="drawer slide aws-drawer-width_en min-width1000" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true" id="cmCdpFailbackTaskConf" data-backdrop="static">
	<div class="drawer-content drawer-content-scrollable" role="document">
		<div class="drawer-header">
			<h4 class="drawer-title ml-15" id="drawer-1-title">
				<i class="viconfont vicon-tenant-detail mr8"></i><?php echo $LANG['UI_JOB_FAILBACK_CONFIGURE']; ?>
				<span data-dismiss="drawer" aria-label="Close" class="drawer-close"><i class="viconfont vicon-guanbi"></i></span>
			</h4>
		</div>
		<div class="drawer-body">
           <div class="details_more_box">
				
				<div class="details_more_content">
					<div class="details_more_content_body">
						<!-- 目标主机  -->
						<div class="row static-info">
							<div class="col-md-2 colorgray mt8">
								<?php echo $LANG['UI_JOB_TARGET_HOST']; ?>:
							</div>
							<div class="col-md-9 value" id="">
                                <select class="form-control select2me width400" id="failBackHost">
                                </select>
							</div>
						</div>

                        <!-- 驱动监测 -->
                        <div class="row static-info mt25  display-none drivercheckview">
                           <div class="col-md-2 colorgray mt8">
                                <?php echo $LANG['WEB_TOOL_DR_OP_CHECK_DIFF_PLAT_AND_DRIVER_EXIST']; ?>:
                            </div>
                            <div class="col-md-9 value " id="">
                                <button class="btn btn-light-primary" type="button" id="cmReplicationCheckDriver"><?php echo $LANG['WEB_PLATFORM_DES_CHECK']; ?></button>
                            </div>

                            <div class="form-group me-20 col-md-12 p-0">
                                <div  style="border: 0px solid black;" id="replicationDriverCheck"></div>
                            </div>
                        </div>

                        <!-- 是否备份镜像数据到备份服务器  -->
						<div class="row static-info mt25  fbmirrordatatoserverdiv">
							<div class="col-md-2 colorgray mt8">
								<?php echo $LANG['UI_PLATFORM_VOL_CDP_BACKUPSET']; ?>:
							</div>
							<div class="col-md-9 value mt5" id="">
                                <input type="checkbox" id="fbMirrorDataToServer" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" checked="checked" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>" data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>">
								<a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_COL_CDP_BACKUP_MIRROR_DATA_TIPS'] ?>">
									<i class="viconfont vicon-tishi"></i>
								</a>
							</div>
						</div>

                        <div class="row static-info  mt20   display-none  selectstoragediv">
                            <label class="col-md-2 colorgray mt8 diystoragelabel"><?php echo $LANG['UI_COPY_BACK_TARGET_STORAGE']; ?></label>
                            <div class="col-md-10 accordion confvolmap mt8">
                                <select class="form-control select2me width400" id="selectstorage">
                                </select>
                            </div>
                        </div>

                        <!-- 回切传输策略配置  -->
						<div class="row static-info mt20  ">
                            <div class="col-md-2 colorgray mt8">
								<?php echo $LANG['UI_VOL_CDP_FAILBACK_TRANSFER_CONF_TEXT']; ?>:
							</div>
                            <div class=" col-md-10 accordion confvolmap mt8" >
                                <div class="panel panel-default strategy-panel">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a class="accordion-toggle accordion-toggle-styled popovers mapvolumeview "
                                                 data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".confvolmap" href="#failback_transfer_conf" aria-expanded="true">
                                                <i class="viconfont vicon-ge_transfer font-green-seagreen"></i>
                                                <label class="font-green-seagreen"><?php echo  $LANG['UI_JOB_TRANSMISSION']; ?></label>
                                                <!--                                                <span class="strategyDes standbyhostDes"></span>-->
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="failback_transfer_conf" class="panel-collapse  stdmapvolume collapse in" aria-expanded="true">
                                        <div class="panel-body">
                                            <div class="col-md-12">
                                                <!-- 传输加密 -->
                                                <div class="form-group col-md-12 takeovertranencryptdiv ml-0_en">
                                                    <label class="control-label col-md-4 transferencrypt"><?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER']; ?></label>
                                                    <div class="col-md-5">
                                                        <input type="checkbox" id="takeoverTranEncryptSwitch"  class="make-switch" data-on-color="primary" data-off-color="info"
                                                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>"
                                                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>">
                                                        <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true"
                                                           data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_ENCRYPTED_TRANSMISSION_TIPS']; ?>">
                                                            <i class="viconfont vicon-tishi"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                                <!-- 传输加密算法 -->
                                                <div class="form-group col-md-12 transfer-encrypt-method-form display-none ml-0_en">
                                                    <label class="control-label transfer-encrypt-method-label col-md-4"><?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?></label>
                                                    <div class="col-md-5">
                                                        <select class="form-control select2me input-sm" id="cutbackTransferEncryptMethod">
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
                                                <!-- 传输压缩 -->
                                                <div class="form-group takeovertrancompressdiv ml-0_en col-md-12">
                                                    <label class="control-label col-md-4 transfercompress"><?php echo $LANG['UI_PLATFORM_SRC_COMPRESS']; ?></label>
                                                    <div class="col-md-5">
                                                        <input type="checkbox" id="takeoverTranCompressSwitch"  class="make-switch" data-on-color="primary" data-off-color="info"
                                                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>"
                                                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>">
                                                        <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true"
                                                           data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_TRANSMISSION_COMPRESSION_TIPS']; ?>">
                                                            <i class="viconfont vicon-tishi"></i>
                                                        </a>
                                                    </div>
                                                </div>

                                                <!-- 压缩等级选择 -->
                                                <div class="form-group  transferCompressGradeDiv display-none ml-0_en col-md-12">
                                                    <label class="control-label col-md-4"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE'] ?></label>
                                                    <div class="col-md-5">
                                                        <select class="form-control select2me input-sm" id="cutBacktransferCompressGrade">
                                                            <option value="1" selected><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_FAST'] ?></option>
                                                            <option value="2"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_NORMAL'] ?></option>
                                                            <option value="3"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_BETTER'] ?></option>
                                                            <option value="4"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_BEST'] ?></option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <!-- 传输线程个数 -->
                                                <div class="form-group takeovertranencryptdiv ml-0_en col-md-12">
                                                    <label class="control-label col-md-4 mt5 transferthread"><?php echo $LANG['UI_VOL_CDP_NUMBERS_THREADS']; ?>
                                                    </label>
                                                    <div class="col-md-5">
                                                        <div id="transfer_thread_div">
                                                            <div class="input-group">
                                                                <input type="text" id="transfer_thread_number"  oninput="if(!/^[1-4]+$/.test(value)) value=value.replace(/\D/g,'');if(value>4)value=4;if(value<1)value=1" class="spinner-input form-control height36" maxlength="1"  >
                                                                <div class="spinner-buttons input-group-btn">
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
                                                    <div class="col-md-2 mt5">
                                                        <a class="popovers" data-container="body" data-trigger="hover" data-html="true"
                                                           data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_NUMBERS_THREADS_TIPS']; ?>">
                                                            <i class="viconfont vicon-tishi"></i>
                                                        </a>
                                                    </div>
                                                </div>

                                                <!-- 传输数据包大小 -->
                                                <div class="form-group takeovertranencryptdiv ml-0_en col-md-12">
                                                    <label class="control-label col-md-4 transferdatapackage"><?php echo $LANG['UI_VOL_CDP_TRANSMISSION_PACKET_SIZE']; ?>
                                                    </label>
                                                    <div class="col-md-5">
                                                        <select class="form-control select2me" id="transfer_datapackage_size">
                                                            <option value=1> 1 MB</option>
                                                            <option value=2>2 MB</option>
                                                            <option value=4 selected>4 MB</option>
                                                            <option value=8>8 MB</option>
                                                            <option value=16>16 MB</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <!-- 传输网络 -->
                                                <div class="form-group ml-90 display-none tasktransfernetworkDiv ml-0_en col-md-12">
                                                    <label class="control-label col-md-4 tasktransfernetworklabel"><?php echo $LANG['UI_NODE_TRANSFER_NETWORK']; ?></label>
                                                    <div class="col-md-5">
                                                        <select class="form-control select2me" id="taskTransferNetwork">
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                         <!-- 回切网络配置  -->
						<div class="row static-info mt20  ">
                            <div class="col-md-2 colorgray mt8">
								<?php echo $LANG['UI_VOL_CDP_FAILBACK_NETWORK_CONF_TEXT']; ?>:
							</div>
                            <div class=" col-md-10 accordion confvolmap mt8">
                                <div class="panel panel-default strategy-panel">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a class="accordion-toggle accordion-toggle-styled popovers mapvolumeview collapsed"
                                            data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".confvolmap" href="#failback_network_conf" aria-expanded="true">
                                                <i class="viconfont vicon-pt_setting_service_network font-green-seagreen"></i>
                                                <label class="font-green-seagreen"><?php echo  $LANG['UI_VOL_CDP_FAILBACK_CACHE_NETWORK']; ?></label>
                                                <!--                                                <span class="strategyDes standbyhostDes"></span>-->
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="failback_network_conf" class="panel-collapse collapse stdmapvolume">
                                        <div class="panel-body">
                                            <div class="col-md-12">
                                                <!-- 接管网络配置 -->
                                                <div class="form-group applianceselectview ml-50 ">
                                                    <div class="col-md-12 ml25">
                                                        <button type="button" class="btn green-haze takeovernetworkconf" id="netconfbtn"><?php echo $LANG['UI_VOL_CDP_TASK_TAKEOVER_NET_CONF']; ?></button>
                                                        <ul id="" style="padding-left: 0;width:90%;" class = "failback_takeover_ip_map_list">
                                                        </ul>
                                                    </div>
                                                </div>
                                            
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!--  资源监测配置 -->
                        <div class="row static-info mt20">
                            <div class="col-md-2 colorgray mt8">
                                <?php echo $LANG['UI_CM_CDP_RESOURCES_MONITOR_CONF'];?>:
                            </div>
                            <div class="col-md-10 accordion confvolmap mt8">
                                <div class="panel panel-default strategy-panel">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a class="accordion-toggle accordion-toggle-styled popovers mapvolumeview collapsed"
                                               data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".confvolmap" href="#tab_resources_monitor_conf" aria-expanded="true">
                                                <i class="viconfont vicon-a-Group1321315332 font-green-seagreen"></i>
                                                <label class="font-green-seagreen"><?php echo $LANG['UI_CM_CDP_BACKUP_RESOURCES_MONITOR_CONF'];?></label>
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="tab_resources_monitor_conf" class="panel-collapse collapse stdmapvolume">
                                        <div class="panel-body">
                                            <?php require_once  '../platform/component/resource_monitoring.php'?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                         <!-- 回切高级策略配置  -->
						<div class="row static-info mt20  ">
                            <div class="col-md-2 colorgray mt8">
								<?php echo $LANG['UI_VOL_CDP_FAILBACK_ADVANCED_CONF_TEXT']; ?>:
							</div>
                            <div class=" col-md-10 accordion confvolmap mt8">
                            <div class="panel panel-default strategy-panel">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a class="accordion-toggle accordion-toggle-styled popovers mapvolumeview collapsed"
                                               data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".confvolmap" href="#failback_cache_conf" aria-expanded="true">
                                                <i class="viconfont vicon-cache-card font-green-seagreen"></i>
                                                <label class="font-green-seagreen"><?php echo  $LANG['UI_VOL_CDP_FAILBACK_ADVANCED_CONF_LABLE']; ?></label>
                                                <!--                                                <span class="strategyDes standbyhostDes"></span>-->
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="failback_cache_conf" class="panel-collapse collapse stdmapvolume">
                                        <div class="panel-body">
                                            <div class="col-md-12">
                                                <!--是否开启内存缓存-->
                                                <div class="form-group col-md-12 fbmirrordatatoserverdiv ml-0_en">
                                                    <label class="control-label col-md-4 doublehostmirrorlabel mt5"><?php echo $LANG['UI_VOL_CDP_MEMORY_CACHE']; ?></label>
                                                    <div class="col-md-5" id="memorycachechange">
                                                        <input type="checkbox" id="memorycacheset"  class="make-switch" data-on-color="primary" data-off-color="info" checked="checked"
                                                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>"
                                                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>">
                                                        <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true"
                                                           data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_CLIENT_MEMORY_CACHE_SIZE_TIPS'] ?>">
                                                            <i class="viconfont vicon-tishi"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                                <!-- 设置客户端内存缓存大小-->
                                                <div class="form-group col-md-12 takeovermemorycachediv ml-0_en mt5">
                                                    <label class="control-label col-md-4 memorycachesizelabel"><?php echo $LANG['UI_VOL_CDP_CLIENT_MEMORY_CACHE_SIZE']; ?>
                                                    </label>
                                                    <div class="col-md-5">
                                                        <select class="form-control select2me" id="memory_cache_size">
                                                            <option value=128 >128 MB</option>
                                                            <option value=512 selected>512 MB</option>
                                                            <option value=1024>1 GB</option>
                                                            <option value=2048>2 GB</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <!-- 客户端文件缓存 路径-->
                                                <div class="form-group col-md-12 takeoverfilecache_div ml-0_en">
                                                    <label class="control-label col-md-4 filecachepathlabel mt5"><?php echo $LANG['UI_JOB_FILE_CACHE_PATH']; ?>
                                                    </label>

                                                    <div class="col-md-5">
                                                        <input type="text" maxlength="1024" readonly class="form-control" id="takeoverFileCachePath" title=""  value =<?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_MODE_DEFAULT'];?>  />
                                                    </div>

                                                    <div class="col-md-3 mt10 ml-20">
                                                        <a href = "javascript:void(0)" id = "customFileCachePath" class = "colorgreen">
                                                            <i class="levelchild viconfont vicon-ge_editable_item"></i>
                                                            <?php echo $LANG['UI_JOB_CHANGE_CUSTOM']; ?>
                                                        </a>
                                                        <a href = "javascript:void(0)" id = "defaultFileCachePath" class = "colorgreen display-none">
                                                            <i class="levelchild viconfont vicon-ge_editable_item"></i>
                                                            <?php echo $LANG['UI_JOB_CHANGE_SYSTEM_DEFAULT']; ?>
                                                        </a>
                                                    </div>
                                                    <div class="col-md-2 mt5 display-none">
                                                        <a class="popovers" data-container="body" data-trigger="hover" data-html="true"
                                                           data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_CLIENT_CACHE_PATH_TIPS']; ?>">
                                                            <i class="viconfont vicon-tishi"></i>
                                                        </a>
                                                    </div>
                                                </div>

                                                <!-- 客户端文件缓存 -->
                                                <div class="form-group col-md-12 takeoverfilecachediv ml-0_en">
                                                    <label class="control-label col-md-4 filecachesizelabel mt5"><?php echo $LANG['UI_JOB_FILE_CACHE_SIZE']; ?>
                                                    </label>
                                                    <div class="col-md-5">
                                                        <select class="form-control select2me" id="file_cache_size">
                                                            <option value=4096 selected> 4 GB </option>
                                                            <option value=8192> 8 GB </option>
                                                            <option value=16384> 16 GB</option>
                                                            <option value=32768> 32 GB </option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2 mt5">
                                                        <a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_CLIENT_CACHE_SIZE_TIPS']; ?>">
                                                            <i class="viconfont vicon-tishi"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                                <!-- 数据IO复制模式 -->
                                                <div class="form-group col-md-12 taskioreplicationdiv ml-0_en">
                                                    <label class="control-label col-md-4 ioreplicationlable mt5"><?php echo $LANG['UI_VOL_CDP_IO_REPLICATION_MODE']; ?></label>
                                                    <div class="col-md-8 mt2">
                                                        <label class="control-label ">
                                                            <input type="radio" name="task_io_replication_modle" id = "ioReplicationSyncModle" value="1"  /> <?php echo $LANG['UI_VOL_CDP_SYN']; ?>
                                                        </label>
                                                        <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true"
                                                           data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_SYN_TIPS']; ?>">
                                                            <i class="viconfont vicon-tishi"></i>
                                                        </a>
                                                        <label class="control-label ml50 ml-0_en">
                                                            <input class = "ml15" type="radio" name="task_io_replication_modle" id = "ioReplicationAsyncModle" value="2" /> <?php echo $LANG['UI_VOL_CDP_ASY']; ?>
                                                        </label>
                                                        <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true"
                                                           data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_ASY_TIPS']; ?>">
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
                         <!-- live-cd 重建分区-->
                         <div class="row static-info mt20  display-none rebuildPartDiv ">
                            <div class="col-md-2 colorgray mt8"><?php echo $LANG['WEB_OS_RESTORE_VOLUME']; ?>:</div>
                            <div class=" col-md-10 accordion confvolmap mt8 rebuildPartChange" >
                                <input type="checkbox" id="rebuildPartSwitch" class="make-switch" data-size="small"
                                       data-on-color="primary" data-off-color="info"
                                       data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>"
                                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>">
                                <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true"
                                   data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_RESTORE_VOLUME_TIPS']; ?>">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                            </div>
                        </div>

                        <!-- 主备磁盘映射关系
                        <div class="row static-info mt20  ">
                            <div class="col-md-2 name mt8">
								<?php echo $LANG['UI_VOL_CDP_MAP_RELATION']; ?>:
							</div>
                            <div class=" col-md-10 accordion confvolmap mt8" >
                                <div id = "cmCdpFailbackTargetDiskInfo"></div>                                   
                            </div>
                        </div>
                        -->                                 
                        <!-- 回切传输策略配置  -->
						<div class="row static-info mt20  ">
                            <div class="col-md-2 colorgray mt8">
								<?php echo $LANG['UI_VOL_CDP_MAP_RELATION']; ?>:
							</div>
                            <div class=" col-md-10 accordion confvolmap mt8" >
                                <div class="panel panel-default strategy-panel">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a class="accordion-toggle accordion-toggle-styled popovers mapvolumeview collapsed "
                                                 data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".confvolmap" href="#failback_disk_conf" aria-expanded="true">
                                                <i class="viconfont vicon-ge_transfer font-green-seagreen"></i>
                                                <label class="font-green-seagreen"><?php echo  $LANG['UI_CM_CDP_FAILBACK_TARGET_DISK_CONF']; ?></label>
                                                <!--                                                <span class="strategyDes standbyhostDes"></span>-->
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="failback_disk_conf" class="panel-collapse  stdmapvolume collapse " aria-expanded="true">
                                        <div class="panel-body">
                                            <div class="col-md-12">
                                                <div id = "cmCdpFailbackTargetDiskInfo"></div>                       
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
			<button type="button" class="btn btn-primary" id="failbackupAddsubmit"><?php echo $LANG['UI_PUBLIC_YES']?></button>
			<button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
		</div>
    </div>
</div>

<!-- 接管網絡配置--modal START -->
<div id="failbackTakeoverNetworkConfModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
	<div class="modal-header ">
		<button type="button" class="close closenetworkconfmodal" data-dismiss="modal" aria-hidden="true"></button>
		<h4 class="modal-title"><i class="iconfont icon-xiansucelve"></i> <?php echo $LANG['UI_VOL_CDP_TASK_FAILBACK_IP_SERVICE_CONF']; ?>
		</h4>
	</div>
	<div class="modal-body">
		<div class="portlet-body">
			<label class="control-label col-md-2"><?php echo $LANG['UI_VOL_CDP_TASK_FAILBACK_IP_SERVICE_CONF']; ?></label>

            <div class="col-md-10">
                <div class="portlet">
                    <div class="portlet-body panel panel-default strategy-panel">
                        <div class="panel-group accordion mt10 height200" style= "overflow-y:auto" id="failback_takeover_ip_server_conf">

                        </div>
                        <div class="alert alert-block alert-info fade in mt-20" style="margin-bottom:0px;" id="">
                            <button type="button" class="close" data-dismiss="alert"></button>
                            <ul class="alert-ul">
                                <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS']?>:</strong>
                                <li><?php echo $LANG['UI_VOL_CDP_TASK_FAILBACK_IP_SERVICE_CONF_TIP']; ?> </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

			<label class="control-label col-md-2 width150 ml-15"> <?php echo $LANG['UI_VOL_CDP_FAILBACK_TARGET_GATEWAY_CONF']; ?></label>
			<div class="col-md-10">
				<div class="portlet">
					<div class="portlet-body panel panel-default strategy-panel">
						<div class="panel-group accordion mt10 height200" style="overflow-y:auto" id="failback_standby_gateway_conf">

						</div>
					</div>
				</div>
			</div>

		</div>
	</div>
	<!-- modal-footer --> 
	<div class="modal-footer">
		<button type="button" data-dismiss="modal" class="btn btn-default cancelnetworkconfmodal"><?php echo $LANG['UI_PUBLIC_NO']; ?></button>
		<button type="button" class="btn btn-primary" id="submit_failback_takeover_network_conf"><?php echo $LANG['UI_PUBLIC_YES']; ?></button>
	</div>
</div>
<?php include_once('../../content/platform/component/takeover_network_map.php'); ?>
<!-- 接管網絡配置  --modal end> -->
<script type="text/javascript" src="./scripts/platform/component/targetPlug.js"></script>
<script type="text/javascript" src="./scripts/complete_machine_volcdp/cm_cdp_failbackup_task_conf.js"></script>
<script type="text/javascript" src="./scripts/platform/component/driver_check.js"></script>
