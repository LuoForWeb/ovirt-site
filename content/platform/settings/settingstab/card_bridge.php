<link rel="stylesheet" type="text/css" href="./css/platform/vm_machine/network.css"
      xmlns="http://www.w3.org/1999/html"/>
<div class="row" id="vm_network_list">
    <div class="col-md-12">
        <div class="portlet box blue-hoki">
            <div class="portlet-body mlr10">
                <div class="table-toolbar">
                    <div class="vin_toolbar" id="vin_network_current_toolbar">
                        <div class="leftTool leftTool_network" style="margin-bottom: -22px;">

                        </div>

                        <div class="rightTool">
                            <div class="vin_network_btnToolbar"></div>
                        </div>
                    </div>
                </div>

                <div class="table-container">
                    <table id="vm_network_table"></table>
                </div>
                <div class="alert alert-block alert-info fade in" id="marktips">
                    <button type="button" class="close" data-dismiss="alert"></button>
                    <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                    <ol class = "alert-ol">
                        <li>
                            <?php echo $LANG['UI_PLATGORM_CARD_BRIDEG_LIST_TIPS1'] ?>
                        </li>
                        <li>
                            <?php echo $LANG['UI_PLATGORM_CARD_BRIDEG_LIST_TIPS2'] ?>
                        </li>
                    </ol>
                </div>
            </div>

            <!--添加修改 start-->
            <div id="vm_network_modal" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
                <div class="modal-header ">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title" style="display:flex;align-items:center;">
                        <i id="show_class" class="viconfont vicon-danchuangtianjia1"></i>
                        <span id="show_network_name" style="margin-left: 5px;"><?php echo $LANG['UI_PLATFORM_CARD_BRIDEG_ADD_NAME']?></span>
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="">
                        <div class="list-option">
                            <div class="row">
                                <!-- 所在节点 -->
                                <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_DATA_REPORT_NODE_NAME']?></label>
                                <div class="col-md-7">
                                    <select class="form-control select2me" id="nodeSelect2" >
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="list-option">
                            <div class="row">
                                <!-- 网络名称 -->
                                <label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_CARD_BRIDEG_NAME']?></label>
                                <div class="col-md-7">
                                    <input id="name" type="text" maxlength="15" class="table-group-action-input form-control" aria-controls="example">
                                </div>
                            </div>
                        </div>

                        <div class="list-option" id="show_card_type">
                            <div class="row">
                                <!-- 网卡 -->
                                <label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_CARD_BRIDEG_CHOOSE_CARD']?></label>
                                <div class="col-md-7">
                                    <select id="card_type_id" class="table-group-action-input form-control">
                                        <option value="0"><?php echo $LANG['UI_PUBLIC_SELECT']?></option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="list-option">
                            <div class="row">
                                <label class="control-label col-md-3">
                                </label>
                                <div class="col-md-7">
                                    <div class="alert alert-block alert-info fade in" id="marktips">
                                        <button type="button" class="close" data-dismiss="alert"></button>
                                        <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS']?></strong></h4>
                                        <ol class="alert-ol">
                                            <li><?php echo $LANG['UI_PLATFORM_CARD_BRIDEG_ADD_TIPS']?></li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- modal-footer -->
                <div class="modal-footer">
                    <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
                    <button type="button" class="btn btn-primary" id="current_work_submit"><?php echo $LANG['UI_PUBLIC_YES']?></button>
                </div>
            </div>
            <!--添加修改 end-->

            <!--清除 start-->
            <div id="vm_resource_modal" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
                <input id="task_vcenteruuid" class="display-none"></input>
                <div class="modal-header ">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title"><i class="levelchild viconfont vicon-a-Close-oneguanbi"></i> <?php echo $LANG['UI_PLATFORM_CARD_BRIDEG_DELETE']?>
                    </h4>
                </div>
                <div class="modal-body">
                    <div style="margin: 0 20px">
                        <div class="list-option">
                            <div class="row">
                                <div class="col-md-12" style="font-size:14px;color:#666666;line-height:22px;">
                                    <?php echo $LANG['UI_PLATFORM_CARD_BRIDEG_DELETE_TIPS']?>
                                </div>
                            </div>
                        </div>
                        <div class="list-option">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="alert alert-block alert-info fade in" id="marktips">
                                        <button type="button" class="close" data-dismiss="alert"></button>
                                        <h4 class="alert-heading">
                                            <strong><?php echo $LANG['UI_PUBLIC_TIPS']?></strong>
                                        </h4>
                                            <?php echo $LANG['UI_PLATFORM_CARD_BRIDEG_ADD_TIPS']?>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- modal-footer -->
                <div class="modal-footer">
                    <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
                    <button type="button" class="btn btn-primary" id="current_sqr_submit"><?php echo $LANG['UI_PUBLIC_YES']?></button>
                </div>
            </div>
            <!--资源隔离 end-->

            <!--网段隔离 start-->
            <div id="edit_vm_network_modal" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
                <div class="modal-header ">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title" style="display:flex;align-items:center;">
                        <i id="show_class" class="viconfont vicon-wangluo"></i>
                        <span id="show_network_name" style="margin-left: 5px;"><?php echo $LANG['UI_PLATFORM_CARD_BRIDEG_ISOLATE']?></span>
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="">
                        <div class="list-option">
                            <div class="row">
                                <!-- start ip -->
                                <label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_CARD_BRIDEG_ISOLATE_IP_START']?></label>
                                <div class="col-md-7">
                                    <input id="ip_start" type="text" maxlength="64" class="table-group-action-input form-control" aria-controls="example">
                                </div>
                            </div>
                        </div>
                        <div class="list-option">
                            <div class="row">
                                <!-- start ip -->
                                <label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_CARD_BRIDEG_ISOLATE_IP_END']?></label>
                                <div class="col-md-7">
                                    <input id="ip_end" type="text" maxlength="64" class="table-group-action-input form-control" aria-controls="example">
                                </div>
                            </div>
                        </div>
                        <div class="list-option">
                            <div class="row">
                                <!-- start ip -->
                                <label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_CARD_BRIDEG_ISOLATE_IP_MASK']?></label>
                                <div class="col-md-7">
                                    <input id="ip_mask" type="text" maxlength="64" class="table-group-action-input form-control" aria-controls="example">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- modal-footer -->
                <div class="modal-footer">
                    <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
                    <button type="button" class="btn btn-primary" id="isolate_work_submit"><?php echo $LANG['UI_PUBLIC_YES']?></button>
                </div>
            </div>
            <!--添加修改 end-->

        </div>
        <!-- End: life time stats -->
    </div>
</div>
<script src="./assets/global/plugins/echarts/echarts5.5.0.js"></script>
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/platform/settings/settingstab/card_bridge.js"></script>
<!-- END PAGE LEVEL PLUGINS -->