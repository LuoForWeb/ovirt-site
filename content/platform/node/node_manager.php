<?php
include_once '../../../tpl/permission.php';
include_once '../public/bs_table.php';
global $LANG;
?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/component/custom_time_strategy.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/node/node_manager.css" />
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<!-- Begin: life time stats -->
<div id="nodeList">
    <div class="vin_toolbar" id="vin_node_toolbar">
        <div class="leftTool">
            <?php
            if (in_array("p_node_manager_delete", $_SESSION['permissionArr'])) {
                // 删除
                echo ' <div class="">
                    <button type="button" id="delete" class="b-btn brr2 mr12 table-toolbar-btn">
                        <i class="icon-gray-delete"></i>
                    </button>
                </div>';
            }
            if (in_array("p_node_manager_add", $_SESSION['permissionArr'])) {
                // 添加
                echo '<div class="btn-group">
                    <button type="button" id="add" class="btn table-toolbar-btn">
                        <i class="viconfont vicon-biaogetianjia"></i> ' . $LANG['UI_PUBLIC_ADDNEW'] . '
                    </button>
                </div>';
            }

            if (in_array("p_node_manager_edit", $_SESSION['permissionArr'])) {
                // 修改
                echo '<div class="btn-group ">
                    <button type="button" id="edit" class="btn table-toolbar-btn">
                        <i class="viconfont vicon-xiugai"></i> ' . $LANG['UI_PUBLIC_MODIFY'] . '
                    </button>
                </div>';
            }
            if (in_array("p_node_manager_network", $_SESSION['permissionArr'])) {
                // 网络配置
                echo '<div class="btn-group">
                    <button type="button" id="setnetwork" class="btn table-toolbar-btn">
                        <i class="viconfont vicon-wangluo"></i> ' . $LANG['UI_NODE_TRANSFER_NETWORK_CONFIG'] . '
                    </button>
                </div>';
            }

            if (in_array("p_node_manager_cache", $_SESSION['permissionArr'])) {
                // 缓存配置
                echo '<div class="btn-group">
                    <button type="button" id="setnodecache" class="btn table-toolbar-btn">
                        <i class="viconfont vicon-cache-card"></i> ' . $LANG['UI_NODE_CACHE_CONFIG'] . '
                    </button>
                </div>';
            }

            if (in_array("p_node_manager_resource_limit", $_SESSION['permissionArr'])) {
                // 资源限制
                echo '<div class="btn-group">
                <button type="button" id="resourceLimit" class="btn table-toolbar-btn">
                    <i class="viconfont vicon-ziyuanxianzhi"></i> ' . $LANG['UI_NODE_RESOURCE_LIMIT'] . '
                </button>
            </div>';
            }
            ?>
        </div>

        <div class="rightTool">
            <div class="vin_btnToolbar"></div>
        </div>
    </div>

    <div class="table-container reset-td-width" id="node_manager_div">
        <table class="table" id="nodeTable"></table>
    </div>
    <div class="alert alert-block alert-info fade in" id="marktips">
        <button type="button" class="close" data-dismiss="alert"></button>
        <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
        <ul class="alert-ul">
            <li>
                <?php echo $LANG['UI_NODE_LIST_TIPS'] ?>
            </li>
        </ul>
    </div>
</div>
<!-- End: life time stats -->

<!-- BEGIN MODAL -->
<div id="modaldiv" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title"><i class="viconfont vicon-ge_modify"></i> <?php echo $LANG['UI_NODE_EDIT_NAME'] ?></h4>
    </div>
    <div class="modal-body min-height360">
        <div class="form-group">
            <label class="col-md-4 control-label"><span class="required"> * </span><?php echo $LANG['UI_NODE_NAME'] ?></label>
            <div class="col-md-6">
                <input type="text" class="form-control display-none" maxlength="128" id="nodeuuid">
                <input type="text" class="form-control" maxlength="128" id="nodename">
                <span class="help-block">
                <?php echo $LANG['UI_NODE_EDIT_NAME_TIPS'] ?> </span>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-4"><span class="required"> * </span><?php echo $LANG['UI_NODE_SERVER_IPADDR'] ?>
            </label>
            <div class="col-md-6">
                <div class="input-icon right">
                    <i class="fa"></i>
                    <input type="text" id="serverIp" disabled maxlength="128"  class="form-control" />
                    <div><span class="help-block "><?php echo $LANG['UI_NODE_SERVER_IPADDR_TIPS'] ?></span></div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-4"><span class="required"> * </span><?php echo $LANG['UI_NODE_SERVER_PORT'] ?>
            </label>
            <div class="col-md-6">
                <div id="serverPort">
                    <div class="input-group spinner-group" style="width:150px;">
                        <input type="text"  disabled  onkeyup="value=value.replace(/[^\d]/g,'')"  class="spinner-input form-control" maxlength="5">
                        <div class="spinner-buttons input-group-btn spinner-group-btn">
                            <button type="button" class="btn spinner-up default">
                                <i class="fa fa-angle-up"></i>
                            </button>
                            <button type="button" class="btn spinner-down default">
                                <i class="fa fa-angle-down"></i>
                            </button>
                        </div>
                    </div>
                    <div><span class="help-block "><?php echo $LANG['UI_NODE_SERVER_PORT_TIPS'] ?></span></div>
                </div>
            </div>
        </div>
        <div class="form-group child-node-ip-view displaynone">
            <label class="control-label col-md-4"><span class="required"> * </span><?php echo $LANG['UI_NODE_REMOTE_IPADDR'] ?>
            </label>
            <div class="col-md-6">
                <div class="input-icon right">
                    <i class="fa"></i>
                    <select  class=" form-control"  disabled>
                    </select>
                    <div><span class="help-block "><?php echo $LANG['UI_NODE_REMOTE_IPADDR_TIPS'] ?></span></div>
                </div>
            </div>
        </div>
        <div class="form-group child-node-port-view displaynone">
            <label class="control-label col-md-4"><span class="required"> * </span><?php echo $LANG['UI_NODE_REMOTE_PORT'] ?>
            </label>
            <div class="col-md-6">
                <div id="">
                    <div class="input-group spinner-group" style="width:150px;">
                        <input type="text" disabled  onkeyup="value=value.replace(/[^\d]/g,'')"  class="spinner-input form-control" maxlength="5">
                        <div class="spinner-buttons input-group-btn spinner-group-btn">
                            <button type="button" class="btn spinner-up default">
                                <i class="fa fa-angle-up"></i>
                            </button>
                            <button type="button" class="btn spinner-down default">
                                <i class="fa fa-angle-down"></i>
                            </button>
                        </div>
                    </div>
                    <div><span class="help-block "><?php echo $LANG['UI_NODE_REMOTE_PORT_TIPS'] ?></span></div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-4">
                <span class="required">* </span>
                <?php echo $LANG['UI_NODE_FUNCTION']?>
            </label>
            <div class="col-md-6">
                <div>
                    <label class="checkbox-inline pl0 pt0" for="masterNodeFunctionManagement">
                        <input type="checkbox" class="icheck" id="masterNodeFunctionManagement" value="1" name="master_node_function" />
                        <?php echo $LANG['UI_NODE_FUNCTION_MANAGEMENT'] ?>
                    </label>
                </div>
                <div>
                    <label class="checkbox-inline pl0 pt0" for="masterNodeFunctionCalculation">
                        <input type="checkbox" class="icheck" id="masterNodeFunctionCalculation" value="2" name="master_node_function" />
                        <?php echo $LANG['UI_NODE_FUNCTION_CALCULATION'] ?>
                    </label>
                </div>
                <!-- <div class="help-block "><?php echo $LANG['UI_NODE_FUNCTION_HELP_BLOCK']?></div> -->
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
        <button type="button" class="btn btn-primary" id="submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>
<!-- END MODAL -->


<div id="remotemodaldiv" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">
            <i class="viconfont vicon-ge_modify"></i>
            <span class="text"><?php echo $LANG['UI_NODE_EDIT_NAME'] ?></span>
        </h4>
    </div>
    <div class="modal-body">
        <div class="form-group">
            <label class="col-md-4 control-label"><span class="required"> * </span><?php echo $LANG['UI_NODE_NAME'] ?></label>
            <div class="col-md-6">
                <input type="text" class="form-control display-none" maxlength="128" id="remoteNodeuuid">
                <input type="text" class="form-control" maxlength="128" id="remoteNodename">
                <span class="help-block">
                <?php echo $LANG['UI_NODE_EDIT_NAME_TIPS'] ?> </span>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-4"><span class="required"> * </span><?php echo $LANG['UI_NODE_SERVER_IPADDR'] ?>
            </label>
            <div class="col-md-6 display-none masternodeipinput ">
                <div class="input-icon right ">
                    <i class="fa"></i>
                    <input type="text" maxlength="128" id="nodeEditInput" class="form-control" name="ipedit" placeholder = "127.0.0.1"/>
                    <span class="update-error display-none" id = "nodeIpError"><?php echo $LANG['UI_NODE_INPUT_WELL_FORMED_NODE_IP'] ?></span>

                    <div><span class="help-block "><?php echo $LANG['UI_NODE_REMOTE_CHANGE_MASTER_NODE_IP'] ?>
                        <a id = "switchToSelectMasterNodeIp"><?php echo $LANG['UI_NODE_SWITCH_CHILD_NODE'] ?></a>
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-md-6 masternodeipselect">
                <div class="input-icon right">
                    <i class="fa"></i>
                    <select id="nodeEditSelect" class=" form-control" name="childnodeip">
                    </select>
                    <div><span class="help-block "><?php echo $LANG['UI_NODE_REMOTE_INPUT_MASTER_NODE_IP'] ?>
                            <a id = "switchToInptMasterNodeIp"><?php echo $LANG['UI_NODE_INPUT_CHILD_NODE'] ?></a>
                        </span>
                    </div>
                </div>
            </div>

        </div>
        <div class="form-group">
            <label class="control-label col-md-4"><span class="required"> * </span><?php echo $LANG['UI_NODE_SERVER_PORT'] ?>
            </label>
            <div class="col-md-6">
                <div id="editNode">
                    <div class="input-group spinner-group" style="width:150px;">
                        <input type="text" id="editNumInput" onkeyup="value=value.replace(/[^\d]/g,'')" name="editport" class="spinner-input form-control" maxlength="5">
                        <div class="spinner-buttons input-group-btn spinner-group-btn">
                            <button type="button" class="btn spinner-up default">
                                <i class="fa fa-angle-up"></i>
                            </button>
                            <button type="button" class="btn spinner-down default">
                                <i class="fa fa-angle-down"></i>
                            </button>
                        </div>
                    </div>
                    <div><span class="help-block "><?php echo $LANG['UI_NODE_SERVER_PORT_TIPS'] ?></span></div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-4"><span class="required"> * </span><?php echo $LANG['UI_NODE_REMOTE_IPADDR'] ?>
            </label>
            <div class="col-md-6 backupchildnodeselect">
                <div class="input-icon right">
                    <i class="fa"></i>
                    <select id="remoteEditInput" class=" form-control" name="childnodeip">
                    </select>
                    <div><span class="help-block "><?php echo $LANG['UI_NODE_REMOTE_IPADDR_TIPS'] ?>
                            <a id = "switchToInputNodeIp"><?php echo $LANG['UI_NODE_INPUT_CHILD_NODE'] ?></a>
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-md-6 display-none backupchildnodeinput">
                <input type="text" class="form-control " maxlength="128" id="inputchildnode" name = "childnodeip" placeholder = "127.0.0.1">
                <span class="update-error display-none" id = "childNodeIpError"><?php echo $LANG['UI_NODE_INPUT_WELL_FORMED_NODE_IP'] ?></span>
                <span class="help-block"><?php echo $LANG['UI_NODE_REMOTE_INPUT_IPADDR_TIPS'] ?>
                    <a id = "switchToSelectNodeIp"><?php echo $LANG['UI_NODE_SWITCH_CHILD_NODE'] ?></a>
                </span>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-4"><span class="required"> * </span><?php echo $LANG['UI_NODE_REMOTE_PORT'] ?>
            </label>
            <div class="col-md-6">
                <div id="remoteEdit">
                    <div class="input-group spinner-group" style="width:150px;">
                        <input type="text" id="editRemoteInput" onkeyup="value=value.replace(/[^\d]/g,'')" name="remoteeditport" class="spinner-input form-control" maxlength="5">
                        <div class="spinner-buttons input-group-btn spinner-group-btn">
                            <button type="button" class="btn spinner-up default">
                                <i class="fa fa-angle-up"></i>
                            </button>
                            <button type="button" class="btn spinner-down default">
                                <i class="fa fa-angle-down"></i>
                            </button>
                        </div>
                    </div>
                    <div><span class="help-block "><?php echo $LANG['UI_NODE_REMOTE_PORT_TIPS'] ?></span></div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-4">
                <span class="required">* </span>
                <?php echo $LANG['UI_NODE_FUNCTION']?>
            </label>
            <div class="col-md-6">
                <div>
                    <label class="checkbox-inline pl0 pt0" for="subNodeFunctionManagement">
                        <input type="checkbox" class="icheck" id="subNodeFunctionManagement" value="1" name="sub_node_function" />
                        <?php echo $LANG['UI_NODE_FUNCTION_MANAGEMENT'] ?>
                    </label>
                </div>
                <div>
                    <label class="checkbox-inline pl0 pt0" for="subNodeFunctionCalculation">
                        <input type="checkbox" class="icheck" id="subNodeFunctionCalculation" value="2" name="sub_node_function" />
                        <?php echo $LANG['UI_NODE_FUNCTION_CALCULATION'] ?>
                    </label>
                </div>
                <!-- <div class="help-block "><?php echo $LANG['UI_NODE_FUNCTION_HELP_BLOCK']?></div> -->
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
        <button type="button" class="btn btn-primary" id="remoteSubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>

<!--设置缓存modal Start -->
<div id="setNodeCacheDiv" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">
            <i class="viconfont vicon-cache-card"></i>
            <span class="text"><?php echo $LANG['UI_NODE_SET_CACHE_NAME'] ?></span>
        </h4>
    </div>
    <div class="modal-body min-height390">
        <div class="form-group">
            <label class="col-md-4 control-label"><?php echo $LANG['UI_NODE_IPADDR'] ?></label>
            <div class="col-md-6">
                <input type="text" class="form-control" maxlength="128" id="nodeinfo" disabled>
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label"><span class="required"> * </span><?php echo $LANG['UI_NODE_CACHE_TYPE'] ?></label>
            <div class="col-md-6">
                <select id="cacheType" class=" form-control" name="cachetypeinfo">
                    <option value="1"><?php echo $LANG['UI_NODE_CATALOGUE_CACHE']; ?></option>
                    <option value="2"><?php echo $LANG['UI_NODE_STORAGE_CACHE']; ?></option>
                </select>
            </div>
        </div>
        <div class="form-group nodecachepathinfoview">
            <label class="control-label col-md-4"><span class="required"> * </span><?php echo $LANG['UI_NODE_CACHE_PATH'] ?>
            </label>
            <div class="col-md-6">
                <input type="text" class="form-control" maxlength="128" id="nodeCachePathInfo">
            </div>
        </div>
        <div class="form-group localStoragedirectoryview">
            <label class="control-label col-md-4"><span class="required"> * </span><?php echo $LANG['UI_NODE_STORAGE_CACHE'] ?>
            </label>
            <div class="col-md-6">
                <select id="localStorageDirectory" class=" form-control" name="nodeCacheStorage">
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-4"><span class="required"> * </span><?php echo $LANG['UI_NODE_STRATEGY_CONF'] ?></label>
            <div class="col-md-6 d-flex">
                <select id="cacheStrategyType" class=" form-control" name="cachetypeinfo">
                    <option value="1"><?php echo $LANG['UI_NODE_STRATEGY_TYPE_MANUAL']; ?></option>
                    <option value="2"><?php echo $LANG['UI_NODE_STRATEGY_TYPE_AUTO']; ?></option>
                </select>
                <a class="popovers ml12 pt7" data-container="body" data-trigger="hover" data-html="true"
                   data-placement="right" data-content="<?php echo $LANG['UI_NODE_STRATEGY_TYPE_CONF_TIPS']; ?>">
                    <i class="viconfont vicon-tishi"></i>
                </a>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-md-4 "><span class="required"> * </span><?php echo $LANG['UI_NODE_ALARM_THRESHOLD_CONF'] ?>
            </label>
            <div class="col-md-4 form-group-content flex-items-center">
                <input type="checkbox" id="nodeAlarmThresholdConf" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON']; ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']; ?>">
                <a class="popovers ml12 mb-5" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_NODE_ALARM_THRESHOLD_CONF_TIPS']; ?>">
                    <i class="viconfont vicon-tishi"></i>
                </a>
            </div>

        </div>
        <div class="form-group nodealarmthresholdview">
            <label class="control-label col-md-4 passfilepercentlabel"><span class="required"> * </span><?php echo $LANG['UI_NODE_ALARM_THRESHOLD_VALUE'] ?></label>
            <div class="col-md-3">
                <div id="spinnerpercent">
                    <div class="input-group spinner-group">
                        <input type="text" id="nodeAlarmThresholdValue" style="text-align: left;" class="input-sm spinner-input form-control" maxlength="3" onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}" onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'0')}else{this.value=this.value.replace(/\D/g,'')}">
                        <div class="spinner-buttons input-group-btn spinner-group-btn">
                            <button type="button" class="input-sm btn spinner-up default">
                                <i class="fa fa-angle-up"></i>
                            </button>
                            <button type="button" class="input-sm btn spinner-down default">
                                <i class="fa fa-angle-down"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-1 mt5 ml-20" style="line-height: 28px;">GB</div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
        <button type="button" class="btn btn-primary" id="nodeCacheSubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>

<!-- BEGIN RESOURCE LIMIT MODAL -->
<div id="resourceLimitModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">
            <i class="viconfont vicon-a-Pie-twojindu2"></i>
            <span class="text"><?php echo $LANG['UI_NODE_RESOURCE_LIMIT'] ?></span>
        </h4>
    </div>
    <div class="modal-body min-height360">
        <!-- 配置状态 -->
        <div class="form-group">
            <label for="configStatus" class="col-md-3 control-label">
                <?php echo $LANG['UI_NODE_RESOURCE_LIMIT_CONFIG_STATUS'] ?>
            </label>
            <div class="col-md-9">
                <input type="checkbox" id="configStatus" class="make-switch" data-size="small"
                       data-on-color="primary" data-off-color="info"
                       data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                <div class="help-block">
                    <?php echo $LANG['UI_NODE_RESOURCE_LIMIT_CONFIG_STATUS_TIPS'] ?>
                </div>
            </div>
        </div>

        <!-- 任务最大并发数 -->
        <div class="form-group">
            <label for="taskMaxConcurrent" class="control-label col-md-3">
                <span class="required"> * </span>
                <?php echo $LANG['UI_NODE_RESOURCE_LIMIT_TASK_MAX_CONCURRENT'] ?>
            </label>
            <div class="col-md-4">
                <div id="taskMaxConcurrentSpinner">
                    <div class="input-group spinner-group">
                        <input type="text" id="taskMaxConcurrent"
                               onkeyup="value=value.replace(/[^\d]/g,'')"
                               class="spinner-input form-control" >
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
                <div class="help-block">
                    <?php echo $LANG['UI_NODE_RESOURCE_LIMIT_TASK_MAX_CONCURRENT_TIPS'] ?>
                </div>
            </div>
        </div>

        <!-- 任务禁止运行时间段 -->
        <div class="form-group">
            <label class="control-label col-md-3">
                <?php echo $LANG['UI_NODE_RESOURCE_LIMIT_TASK_PROHIBIT_PERIOD'] ?>
            </label>
            <div class="col-md-9">
                <div id="prohibitTimePeriod">
                </div>
            </div>
        </div>

        <!-- 已配置时间段 -->
        <div class="form-group" id="prohibitTimePeriodShowDiv">
            <label class="control-label col-md-3">
                <?php echo $LANG['UI_NODE_RESOURCE_LIMIT_TASK_PROHIBIT_PERIOD_CONFIGURED'] ?>
            </label>
            <div class="col-md-9">
                <ul id="prohibitTimePeriodShowUl" style="padding: 0"></ul>
            </div>
        </div>

        <!-- 应用到其他节点 -->
        <div class="form-group">
            <label for="applyOtherNode" class="col-md-3 control-label">
                <?php echo $LANG['UI_NODE_RESOURCE_LIMIT_APPLY_OTHER_NODE'] ?>
            </label>
            <div class="col-md-9">
                <input type="checkbox" id="applyOtherNode" class="make-switch" data-size="small"
                       data-on-color="primary" data-off-color="info"
                       data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
            </div>
        </div>

        <!-- 选择节点 -->
        <div class="form-group selectLimitNodeDiv display-none">
            <label class="col-md-3 control-label">
                <span class="required"> * </span>
                <?php echo $LANG['UI_NODE_RESOURCE_LIMIT_APPLY_OTHER_NODE_SELECT'] ?>
            </label>
            <div class="col-md-9">
                <ul class="ztree" id="applyOtherNodeTree" style="border: 1px solid #f0f0f0"></ul>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default">
            <?php echo $LANG['UI_PUBLIC_NO'] ?>
        </button>
        <button type="button" class="btn btn-primary" id="resourceLimitSubmit">
            <?php echo $LANG['UI_PUBLIC_YES'] ?>
        </button>
    </div>
</div>
<!-- END RESOURCE LIMIT MODAL -->
<!-- END PAGE CONTENT-->


<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/platform/component/custom_time_strategy.js"></script>
<script type="text/javascript" src="./scripts/platform/node/node_manager.js"></script>
<!-- END PAGE LEVEL PLUGINS -->