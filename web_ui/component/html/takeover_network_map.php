<!-- 接管網絡配置--modal START -->
<div id="" class="modal xmodal fade form-horizontal takeoverNetworkConfModal" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title"><i class="iconfont icon-xiansucelve"></i> <?php echo $LANG['UI_VOL_CDP_HOST_IP_SERIVCE_CONF']; ?>
        </h4>
    </div>
    <div class="modal-body">
        <div class="portlet-body">
            <label class="control-label col-md-2"> <?php echo $LANG['UI_VOL_CDP_TASK_IP_SERVICE_CONF']; ?></label>
            <div class="col-md-10">
                <div class="portlet">
                    <div class="portlet-body panel panel-default strategy-panel">
                        <div class="panel-group accordion mt10 min-height150" style="overflow-y:auto" id="host_ip_server_conf">

                        </div>
                        <div class="alert alert-block alert-info fade in mt-20" style="margin-bottom:0px;" id="">
                            <button type="button" class="close" data-dismiss="alert"></button>
                            <ul class="alert-ul">
                                <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS']?>:</strong>
                                <li><?php echo $LANG['UI_VOL_CDP_NET_CARD_CONF_TIP']; ?> </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <label class="control-label col-md-2"> <?php echo $LANG['UI_VOL_CDP_STANDBY_GATEWAY_CONF']; ?></label>
            <div class="col-md-10">
                <div class="portlet">
                    <div class="portlet-body panel panel-default strategy-panel">
                        <div class="panel-group accordion mt10 min-height150" style="overflow-y:auto" id="standby_gateway_conf">

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- modal-footer -->
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']; ?></button>
        <button type="button" class="btn btn-primary" id="submit_host_network_conf"><?php echo $LANG['UI_PUBLIC_YES']; ?></button>
    </div>
</div>
<script type="text/javascript" src="./scripts/platform/component/takeover_network_map.js"></script>
<!-- 接管網絡配置 modal end -->