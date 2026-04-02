<div class="drawer tape-monitor-drawer slide" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true"
     id="tap_monitor_log_drawer" style="width: 60%">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header ">
            <h4 class="drawer-title" id="drawer-1-title" style="vertical-align: top;padding-left:0;font-weight:400;font-size:16px;">
                <i class="viconfont vicon-xiangqing" style="padding-top: 3px"></i>
                <span style="vertical-align: top;"><?php echo $LANG['UI_TAPE_LOG_DETAIL']; ?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i class="viconfont vicon-guanbi"></i></span>
            </h4>
        </div>
        <!-- BEGIN FORM-->
        <div class="drawer-body form-horizontal">
            <div class="vin_toolbar" id="vin_monitor_toolbar">
                <div class="leftTool" style="width: 180px;height: 34px;">
                </div>

                <div class="rightTool">
                    <div class="vin_btnToolbar">
                    </div>
                </div>
            </div>
            <div class="table-container">
                <table id="tape_monitor_table"></table>
            </div>
        </div>
        <div class="drawer-footer">
            <div id="bottom-btn">
                <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-default" style="margin-right:0;"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
            </div>
        </div>
    </div>
</div>

<script src="./scripts/platform/resource/tape_monitor.js"></script>
