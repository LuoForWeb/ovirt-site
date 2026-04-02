<link rel="stylesheet" type="text/css" href="./css/platform/vm_machine/network.css" />
<style>
    #add-vm_network:hover{
        color:#393C4D !important;
    }
</style>
<!-- BEGIN PAGE CONTENT-->
<div class="row network-row">
    <div class="col-md-12">
        <div id="vm_machine_list">
            <div class="portlet box blue-hoki">
                <div class="portlet-body mlr10">
                    <div>
                        <div class="topHead">
                            <div class="topPoint"></div>
                            <div class="topTitle"><?php
                                if ( $CONF['SYSTEM_INFO']['vendor'] == $CONF['VENDOR_LIST']['gmp']){
                                    echo $LANG['WEB_PLATFORM_INDUSTRY_REPORT_MACHINE'];
                                }else{
                                    echo $LANG['UI_VM_MACHINE_LIST'];
                                }
                                ?></div>
                        </div>
                        <?php
                        if(in_array("vm_machine_list", $_SESSION['permissionArr'])) {
                            echo '<div class="topHead2" id="moreMachine">
                            '.$LANG['UI_VM_MACHINE_MORE'].'<i class="viconfont vicon-a-Leftzuo"></i>
                        </div>';
                        }
                        ?>

                    </div>
                    <div id="network_echars1">
                    </div>
                    <div>
                        <div class="table-container">
                            <table id="vm_machine_table"></table>
                        </div>
                        <!-- End: life time stats -->
                    </div>
                </div>
            </div>
        </div>
        <div id="vm_node_list">
            <div class="portlet box blue-hoki">
                <div class="portlet-body mlr10">
                    <div>

                        <div class="topHead">
                            <div class="topPoint"></div>
                            <div class="topTitle"><?php echo $LANG['UI_VM_MACHINE_RESOURCE']?></div>
                        </div>
                        <?php
                        if(in_array("p_vm_machine_partition", $_SESSION['permissionArr'])) {
                            echo '<div id="quar-vm_list">
                            '.$LANG['UI_PLATFORM_SERVER_SRC'].'<i class="viconfont vicon-ziyuangeli"></i>
                        </div>';
                        }
                        ?>

                    </div>
                    <div class="item-echarts">
                        <div id="network_echars2"></div>
                        <div id="network_echars3"></div>
                    </div>
                    <div>
                        <div class="table-container">
                            <table id="vm_node_table"></table>
                        </div>
                        <!-- End: life time stats -->
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="row" id="vm_network_list">
    <div class="col-md-12">
        <div class="portlet box blue-hoki">
            <div class="portlet-body mlr10">
                <!-- Begin: life time stats grey-cararra -->
                <div class="topHead">
                    <div class="topPoint"></div>
                    <div class="topTitle"><?php echo $LANG['UI_VM_MACHINE_NETWORK_MANAGE']?></div>
                </div>
            </div>
            <div class="portlet-body mlr10">
                <div class="table-toolbar">
                    <div class="vin_toolbar" id="vin_network_current_toolbar" style="height: 3px;margin-bottom: 18px;">
                        <div class="leftTool leftTool_network">

                        </div>

                        <div class="rightTool">
                            <div class="vin_network_btnToolbar"></div>
                        </div>
                    </div>
                </div>

                <div class="table-container">
                    <table id="vm_network_table"></table>
                </div>
            </div>

            <!--提示信息-->
            <div class="alert alert-block alert-info fade in" style="margin-left: 20px;margin-right: 20px;">
                <button type="button" class="close" data-dismiss="alert"></button>
                <h4 class="alert-heading">
                    <strong><?php echo $LANG['UI_PUBLIC_TIPS']?></strong>
                </h4>
                <ol class="alert-ol">
                    <li>
                        <?php echo $LANG['UI_VM_MACHINE_NETWORK_MANAGE_LIST_TIPS1']?>
                    </li>
                    <li>
                        <?php echo $LANG['UI_VM_MACHINE_NETWORK_MANAGE_LIST_TIPS2']?>
                    </li>
                    <li>
                        <?php echo $LANG['UI_VM_MACHINE_NETWORK_MANAGE_LIST_TIPS3']?>
                    </li>
                </ol>
            </div>

            <!-- BEGIN SEARCH MODAL -->
            <div id="vm_network_searchmodal" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
                <div class="modal-header ">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title"><i class="fa fa-search"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH']?>
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="">
                        <div class="list-option">
                            <div class="row">
                                <!-- 所在节点 -->
                                <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_DATA_REPORT_NODE_NAME']?></label>
                                <div class="col-md-7">
                                    <select class="form-control select2me" id="nodeSelectNetwork" >
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="list-option">
                            <div class="row">
                                <!-- 网络类型 -->
                                <label class="control-label col-md-3"><?php echo $LANG['UI_VM_MACHINE_NETWORK_TYPE']?></label>
                                <div class="col-md-7">
                                    <select id="card_type" class="table-group-action-input form-control">
                                        <option value="0"><?php echo $LANG['UI_PUBLIC_ALL']?></option>
                                        <option value="3"><?php echo $LANG['UI_VM_MACHINE_NETWORK_TYPE_BRIDGE']?></option>
                                        <option value="4"><?php echo $LANG['UI_VM_MACHINE_NETWORK_TYPE_DIVIDE']?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- modal-footer -->
                <div class="modal-footer">
                    <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
                    <button type="button" class="btn btn-primary" id="current_network_search_submit"><?php echo $LANG['UI_PUBLIC_YES']?></button>
                </div>
            </div>
            <!-- END SEARCH MODAL -->

            <!--添加修改 start-->
            <div id="vm_network_modal" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
                <div class="modal-header ">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title"><i id="show_class" class="viconfont vicon-danchuangtianjia1"></i> <span id="show_network_name"><?php echo $LANG['UI_VM_MACHINE_NETWORK_ADDS']?></span>
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
                                <label class="control-label col-md-3"><?php echo $LANG['UI_VM_MACHINE_NETWORK_NAME']?></label>
                                <div class="col-md-7">
                                    <input id="carkName" type="text" maxlength="64" class="table-group-action-input form-control" aria-controls="example">
                                </div>
                            </div>
                        </div>

                        <div class="list-option" id="show_card_type">
                            <div class="row">
                                <!-- 网卡 -->
                                <label class="control-label col-md-3"><?php echo $LANG['UI_VM_MACHINE_NETWORK_CHOOSE']?></label>
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
                                        <ul class="alert-ul">
                                            <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS']?>:</strong>
                                            <li><?php echo $LANG['UI_VM_MACHINE_NETWORK_ADDS_TIPS']?></li>
                                        </ul>
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

            <!--资源隔离 start-->
            <div id="vm_resource_modal" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
                <input id="task_vcenteruuid" class="display-none"></input>
                <div class="modal-header ">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title"><i class="levelchild viconfont vicon-ziyuangeli"></i> <?php echo $LANG['UI_PLATFORM_SERVER_SRC']?>
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="">
                        <div class="list-option">
                            <div class="row">
                                <!-- 所在节点 -->
                                <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_DATA_REPORT_NODE_NAME']?></label>
                                <div class="col-md-7">
                                    <select class="form-control select2me" id="nodeSelectResource" >
                                    </select>
                                    <div>
                                        <span class="help-block">

                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="list-option">
                            <div class="row">
                                <!-- cpu总核数 -->
                                <label class="control-label col-md-3"><?php echo $LANG['UI_VM_MACHINE_CPU']?></label>
                                <div class="col-md-7">
                                    <div class="input-group width-100">
                                        <input id="cpus" type="text" maxlength="10" class="table-group-action-input form-control" aria-controls="example">
                                        <span class="input-group-btn">
                					        <button class="btn" type="button"><?php echo $LANG['UI_VM_MACHINE_CORS']?></button>
                					    </span>
                                    </div>
                                    <div>
                                        <span class="help-block">
                                            <?php echo $LANG['UI_VM_MACHINE_HOST_CORS']?><span id="cpu_total"></span><?php echo $LANG['UI_VM_MACHINE_CORS']?>，
                                            <?php echo $LANG['UI_VM_MACHINE_USED']?><span id="used_cpu"></span><?php echo $LANG['UI_VM_MACHINE_CORS']?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="list-option">
                            <div class="row">
                                <!-- 内存总大小 -->
                                <label class="control-label col-md-3"><?php echo $LANG['UI_VM_MACHINE_MEMS_TOTAL']?></label>
                                <div class="col-md-5">
                                    <input id="mems" type="text" maxlength="10" class="table-group-action-input form-control" aria-controls="example">
                                    <div>
                                        <span class="help-block" style="width: 150%;">
                                            <?php echo $LANG['UI_VM_MACHINE_HOST_MEMS_TOTAL']?><span id="memory_total"></span><?php echo $LANG['UI_VM_MACHINE_MEMS_UNIT2']?>，
                                            <?php echo $LANG['UI_VM_MACHINE_USED']?><span id="used_memory"></span><?php echo $LANG['UI_VM_MACHINE_MEMS_UNIT2']?>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-2 padding-l-w">
                                    <select class="form-control" id="modal_resource_memory_type" name="modal_resource_memory_type">
                                        <option value="1"><?php echo $LANG['UI_VM_MACHINE_MEMS_UNIT']?></option>
                                        <option value="2" selected><?php echo $LANG['UI_VM_MACHINE_MEMS_UNIT2']?></option>
                                        <option value="3" ><?php echo $LANG['UI_VM_MACHINE_MEMS_UNIT3']?></option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="list-option display-none">
                            <div class="row">
                                <!-- 病毒沙箱开关 2 -->
                                <label class="control-label col-md-3"><?php echo $LANG['UI_VM_MACHINE_VIRUS_CONFIG'];?></label>
                                <div class="col-md-7">
                                    <div class="input-group width-100">
                                        <input type="checkbox" id="virus_witch" checked class="make-switch" data-size="small"
                                               data-on-color="primary" data-off-color="info"
                                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                    </div>
                                    <div>
                                        <span class="help-block">
                                            <?php echo $LANG['UI_VM_MACHINE_VIRUS_CONFIG_TIPS'];?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="list-option virus_item_div">
                            <div class="row">
                                <!-- 病毒沙箱cpu总核数 2 -->
                                <label class="control-label col-md-3"><?php echo $LANG['UI_VM_MACHINE_VIRUS_CONFIG_CPU'];?></label>
                                <div class="col-md-7">
                                    <div class="input-group width-100">
                                        <input id="cpus_virus" type="text" maxlength="10" class="table-group-action-input form-control" aria-controls="example">
                                        <span class="input-group-btn">
                					        <button class="btn" type="button"><?php echo $LANG['UI_VM_MACHINE_CORS']?></button>
                					    </span>
                                    </div>
                                    <div>
                                        <!-- <span class="help-block">
                                            <?php /*echo $LANG['UI_VM_MACHINE_VIRUS_CONFIG_CPU_TIPS'];*/?>
                                        </span>-->
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="list-option virus_item_div">
                            <div class="row">
                                <!-- 病毒沙箱内存总大小 4 -->
                                <label class="control-label col-md-3"><?php echo $LANG['UI_VM_MACHINE_VIRUS_CONFIG_MEMS'];?></label>
                                <div class="col-md-5">
                                    <input id="mems_virus" type="text" maxlength="10" class="table-group-action-input form-control" aria-controls="example">
                                    <div>
                                        <!--<span class="help-block" style="width: 150%;">
                                           <?php /*echo $LANG['UI_VM_MACHINE_VIRUS_CONFIG_MEMS_TIPS'];*/?>
                                        </span>-->
                                    </div>
                                </div>
                                <div class="col-md-2 padding-l-w">
                                    <select class="form-control" id="modal_resource_memory_type_virus" name="modal_resource_memory_type_virus">
                                        <option value="1"><?php echo $LANG['UI_VM_MACHINE_MEMS_UNIT']?></option>
                                        <option value="2" selected><?php echo $LANG['UI_VM_MACHINE_MEMS_UNIT2']?></option>
                                        <option value="3" ><?php echo $LANG['UI_VM_MACHINE_MEMS_UNIT3']?></option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="list-option">
                            <div class="row">
                                <!-- cpu模式 -->
                                <label class="control-label col-md-3"><?php echo $LANG['UI_VM_MACHINE_CPU_MODEL']?></label>
                                <div class="col-md-7">
                                    <select class="form-control select2me" id="emdCpuMode" >
                                    </select>
                                    <div>
                                        <span class="help-block">

                                        </span>
                                    </div>
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
                                        <ul class="alert-ul">
                                            <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS']?>:</strong>
                                            <li><?php echo $LANG['UI_VM_MACHINE_RESOURCES_TIPS']?></li>
                                        </ul>
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

        </div>
        <!-- End: life time stats -->
    </div>
</div>
<script src="./assets/global/plugins/echarts/echarts5.5.0.js"></script>
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/platform/vm_machine/network.js"></script>
<!-- END PAGE LEVEL PLUGINS -->