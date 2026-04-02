<?php include_once '../../tpl/permission.php';?>
<!--代理客户端  -->
<div id="proxyClientModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title"><i class="viconfont vicon-beiji"></i> <?php echo $LANG['UI_VOL_CDP_STANDBY_CONF_MAP']; ?>
        </h4>
    </div>
    <div class="modal-body">
        <div class="portlet-body">
            <div class="form-group settagPointStrategy">
                <div class="form-group">
                    <label class="control-label col-md-2 applianceselectlabel"><?php echo $LANG['UI_VOL_CDP_STANDBY_MACHINE_SELECT']; ?></label>
                    <div class="col-md-7">
                        <select class="form-control select2me" id="standbyHostSelect">
                        </select>
                        <p class="form-control-static colorgreen width600 display-none targetHostUsedPartition">
                        </p>
                    </div>
                </div>
                <!-- 镜像数据备机，是否重建分区 -->
                <div class="form-group diskgenView display-none">
                    <label class="control-label col-md-2 diynodelabel form-group-label"><?php echo $LANG['WEB_OS_RESTORE_VOLUME']; ?></label>
                    <div class="col-md-4 form-group-content">
                        <input type="checkbox" id="rebuildPartSwitch" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>">
                        <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VOL_CDP_RESTORE_VOLUME_TIPS']; ?>">
                            <i class="viconfont vicon-tishi"></i>
                        </a>
                    </div>
                </div>

                <div class="form-group proxyclientmapvoldiv">
                    <label class="control-label col-md-2 diystoragelabel"><?php echo $LANG['UI_VOL_CDP_CONFIGURE_MAPPED_VOLUMES']; ?></label>
                    <div class=" col-md-9 accordion confvolmap">
                        <div class="panel panel-default strategy-panel">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a class="accordion-toggle accordion-toggle-styled popovers mapvolumeview" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".confvolmap" href="#map_volume_view" aria-expanded="true">
                                        <i class="fa fa-desktop font-green-seagreen"></i>
                                        <span class="font-green-seagreen"><?php echo  $LANG['UI_VOL_CDP_HOST']; ?></span>
                                        <span class="strategyDes standbyhostDes h-20px"></span>
                                    </a>
                                </h4>
                            </div>
                            <div id="map_volume_view" class="panel-collapse collapse stdmapvolume">
                                <div class="panel-body">
                                    <div class="col-md-12">
                                        <div class="table-container">
                                            <div class="table-container">
                                                <table class="table table-striped table-bordered table-hover" id="backup_host_vol_table">
                                                    <thead>
                                                    <tr role="row" class="heading">
                                                        <th width="25%">
                                                            <?php echo $LANG['UI_VOL_CDP_NAME']; ?>
                                                        </th>
                                                        <th width="20%">
                                                            <?php echo $LANG['UI_STORAGE_TOTAL_SIZE']; ?>
                                                        </th>
                                                        <th width="55%">
                                                            <?php echo $LANG['UI_VOL_CDP_INFORMATION']; ?>
                                                        </th>
                                                    </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="alert alert-block alert-info fade in" id="takeoverStandbyMountpointTips">
                                                <button type="button" class="close" data-dismiss="alert"></button>
                                                <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                                <ol class="alert-ol">
                                                    <li><?php echo $LANG['UI_VOL_CDP_TAKEOVER_TIPSONE']; ?>
                                                    </li>
                                                    <li><?php echo $LANG['UI_VOL_CDP_TAKEOVER_TIPSTWO']; ?>
                                                    </li>
                                                    <li><?php echo $LANG['UI_VOL_CDP_TAKEOVER_TIPSTHREE']; ?>
                                                    </li>
                                                    <li><?php echo $LANG['UI_VOL_CDP_TAKEOVER_MOUNT_POINT_TIPS']; ?>
                                                    </li>
                                                </ol>
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
    <!-- modal-footer -->
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default" id="proxyClientClose"><?php echo $LANG['UI_PUBLIC_NO']; ?></button>
        <button type="button"class="btn btn-primary" id="proxyClientSubmit"><?php echo $LANG['UI_PUBLIC_YES']; ?></button>
    </div>
</div>

<!-- 内置虚拟机 -->
<div id="vm_machine_modal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title"><i class="viconfont vicon-beiji"></i> <?php echo $LANG['UI_VOL_CDP_STANDBY_CONF_MAP']; ?>
        </h4>
    </div>
    <div class="modal-body">
        <?php include_once(ROOT_PATH . '/content/platform/vm_machine/machine.php'); ?>
    </div>
    <!-- modal-footer -->
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default" id="builtInVmClose"><?php echo $LANG['UI_PUBLIC_NO']; ?></button>
        <button type="button"class="btn btn-primary" id="builtInVmSubmit"><?php echo $LANG['UI_PUBLIC_YES']; ?></button>
    </div>
</div>