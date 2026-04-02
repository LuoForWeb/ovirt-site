 <!-- 复制 备机view -->
 <div id="copyStandbyConfModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
        <div class="modal-header ">
            <button type="button" class="close " data-dismiss="modal" aria-hidden="true"></button>
            <h4 class="modal-title"><i class="viconfont vicon-gaojipeizhi"></i> <?php echo $LANG['UI_CM_CDP_COPY_STANDBY_CONF']; ?>
            </h4>
        </div>
        <div class="modal-body">
            <div class="portlet-body" style="overflow-y:scroll;padding-bottom:35px;">
                <div class="form-group ">
                    <label class="control-label col-md-2 applianceselectlabel col-md-3_en"><?php echo $LANG['UI_VOL_CDP_STANDBY_MACHINE_SELECT']; ?></label>
                    <div class="col-md-7">
                        <select class="form-control " id="cmBackupTaskCopyStandbySelect">
                        </select>
                    </div>
                </div>

                <div class ="drivercheckview display-none">
                    <div class="form-group ">
                        <label class="control-label col-md-2 applianceselectlabel col-md-3_en"><?php echo $LANG['WEB_TOOL_DR_OP_CHECK_DIFF_PLAT_AND_DRIVER_EXIST']; ?></label>
                        <div class="col-md-7">
                            <button class="btn btn-light-primary" type="button" id="cmReplicationCheckDriver"><?php echo $LANG['WEB_PLATFORM_DES_CHECK']; ?></button>
                        </div>
                    </div>

                    <div class="form-group me-20">
                        <div  style="border: 0px solid black;" id="replicationDriverCheck"></div>
                    </div>
                </div>
               
               
                <!--  备机映射配置 -->				
                <div class="form-group cmcopystandbymap  ">
                    <label class="control-label col-md-2 encrypttransferlabel form-group-label col-md-3_en"><?php echo $LANG['UI_CM_CDP_TAKEOVER_MAPPED_CONF_DESC']; ?></label>	
                    <div class="col-md-10 accordion strategystandbymap col-md-9_en">
                        <div class="accordion strategystandbymap store-strategy-form me-20">
                            <div class="panel panel-default strategy-panel">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a class="accordion-toggle accordion-toggle-styled collapsed popovers" name = "strategystandbymap" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategystandbymap" href="#copyTaskStandbyMapConf" aria-expanded="true">
                                            <i class="viconfont vicon-jieguanfuwuqi font-green-seagreen"></i>
                                            <span class="font-green-seagreen"><?php echo $LANG['WEB_PLATFORM_DES_HOST'] ?></span>
                                            <span class="strategyDes takeoverStandbyMapDes"></span>
                                        </a>
                                    </h4>
                                </div>
                                
                                <div id="copyTaskStandbyMapConf" name = "copyTaskStandbyMapConfCollapse" class="panel-collapse collapse in">
                                    <div class="panel-body min-height250">
                                        <div id = "" class="display-none cmcdpcopytargetmapconfdefault">
                                            <span class = "textalignc flex-center flex-items-center"><?php echo $LANG['UI_VOL_CDP_TAKEOVER_TARGET_MACHINE_SELECT']; ?></span>
                                        </div>
                                        <div id = "" class="display-none cmcdpcopytargetmapconf">
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-block alert-info fade in" id="cmCopyMachineTips" style="position: absolute;bottom:-15px;left: 19%;width: calc(81% - 50px);">
                    <button type="button" class="close" data-dismiss="alert"></button>
                    <ul class="alert-ul">
                        <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'];?>:</strong>
                        <li><?php echo $LANG['UI_CM_CDP_COPY_MACHINE_TIPS']?></li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- modal-footer -->
        <div class="modal-footer">
            <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']; ?></button>
            <button type="button" class="btn btn-primary submitcopystandbyconf"><?php echo $LANG['UI_PUBLIC_YES']; ?></button>
        </div>
    </div>