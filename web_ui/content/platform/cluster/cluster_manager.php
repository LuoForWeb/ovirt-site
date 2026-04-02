<?php
include_once '../../../tpl/permission.php';
include_once '../../platform/public/bs_table.php';
global $LANG;

$operateAttr = in_array('p_cluster_manager_config', $_SESSION['permissionArr']) ? '' : 'disabled';
?>

<!-- BEGIN PAGE LEVEL STYLES -->
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="./css/platform/cluster/cluster_manager.css" />
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER -->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="backup_manager" href="./content/platform/backup/backupresources.php">
            <span><?php echo $LANG['UI_PLATFORM_BACKUP_RESOURCE'] ?></span>
        </a>
    </li>
    <span>
        >
    </span>
    <span class="curent"><?php echo $LANG['UI_CLUSTER_MANAGER'] ?></span>
</h3>
<!-- END PAGE HEADER -->
<!-- BEGIN PAGE CONTENT-->
<div id="clusterManager">
    <!-- Begin: life time stats -->
    <div class="hp-100">
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="levelchild viconfont vicon-jiqun1"></i>
                    <span><?php echo $LANG['UI_CLUSTER_MANAGER'] ?></span>
                </div>
            </div>
            <div class="portlet-body">
                <!-- 未配置集群 -->
                <div class="not-config">
                    <div class="not-config__wrapper">
                        <div class="not-config__left">
                            <img src="./img/platform/cluster/bg-cluster.png" alt="bg" />
                        </div>
                        <div class="not-config__center"></div>
                        <div class="not-config__right">
                            <span class="title"><?php echo $LANG['UI_CLUSTER_NOT_CONFIG_TITLE']; ?></span>
                            <ol class="tips">
                                <li><?php echo $LANG['UI_CLUSTER_NOT_CONFIG_TIPS1']; ?></li>
                                <li><?php echo $LANG['UI_CLUSTER_NOT_CONFIG_TIPS2']; ?></li>
                                <li><?php echo $LANG['UI_CLUSTER_NOT_CONFIG_TIPS3']; ?></li>
                            </ol>
                            <div class="btn btn-sm green-haze <?php echo $operateAttr; ?>" id="clusterConfigBtn" <?php echo $operateAttr; ?>>
                                <i class="viconfont vicon-gaojipeizhi"></i>
                                <?php echo $LANG['UI_CLUSTER_NOT_CONFIG_BUTTON']; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- 已配置集群 -->
                <div class="has-config">
                    <div class="has-config__wrapper">
                        <div class="has-config__left">
                            <!-- 集群服务 -->
                            <div class="form-group">
                                <label class="col-md-2 control-label"><?php echo $LANG['UI_CLUSTER_SERVER']; ?></label>
                                <div class="col-md-10 control-item">
                                    <span class="config-text config-item"><?php echo $LANG['UI_CLUSTER_CONFIG_ON']; ?></span>
                                    <div id="changeConfig" class="btn btn-sm green-haze <?php echo $operateAttr; ?>" <?php echo $operateAttr; ?>><?php echo $LANG['UI_CLUSTER_CONFIG_CHANGE']; ?></div>
                                </div>
                            </div>
                            <!-- 集群状态 -->
                            <div class="form-group">
                                <label class="col-md-2 control-label"><?php echo $LANG['UI_CLUSTER_STATUS']; ?></label>
                                <div class="col-md-10 control-item">
                                    <!-- 未启用 -->
                                    <span class="cluster-status-span label config-item" id="clusterStatusOffLabel"><?php echo $LANG['UI_CLUSTER_STATUS_OFF']; ?></span>
                                    <!-- 启动中 -->
                                    <span class="cluster-status-span label label-success config-item" id="clusterStatusStartingLabel"><?php echo $LANG['WEB_CLUSTER_STATUS_STARTING']; ?></span>
                                    <!-- 已启用 -->
                                    <span class="cluster-status-span label label-success config-item" id="clusterStatusOnLabel"><?php echo $LANG['UI_CLUSTER_STATUS_ON']; ?></span>
                                    <!-- 停止中 -->
                                    <span class="cluster-status-span label label-danger config-item" id="clusterStatusStoppingLabel"><?php echo $LANG['WEB_CLUSTER_STATUS_STOPPING']; ?></span>
                                    <!-- 故障 -->
                                    <span class="cluster-status-span label label-danger config-item" id="clusterStatusErrorLabel"><?php echo $LANG['UI_CLUSTER_STATUS_ERROR']; ?></span>
                                    <!-- 切换中 -->
                                    <span class="cluster-status-span label label-info config-item" id="clusterStatusSwitchingLabel"><?php echo $LANG['WEB_CLUSTER_STATUS_SWITCHING']; ?></span>
                                    <!-- 接管中 -->
                                    <span class="cluster-status-span label label-info config-item" id="clusterStatusTakeoverLabel"><?php echo $LANG['WEB_CLUSTER_STATUS_TAKEOVER']; ?></span>
                                    <div class="btn btn-sm green-haze cluster-operation-btn <?php echo $operateAttr; ?>" id="startCluster" <?php echo $operateAttr; ?>><?php echo $LANG['UI_CLUSTER_STATUS_START']; ?></div>
                                    <div class="btn btn-sm green-haze cluster-operation-btn <?php echo $operateAttr; ?>" id="stopCluster" <?php echo $operateAttr; ?>><?php echo $LANG['UI_CLUSTER_STATUS_STOP']; ?></div>
                                </div>
                            </div>
                            <!-- 集群主节点 -->
                            <div class="form-group">
                                <label for="clusterNodeSelect" class="col-md-2 control-label"><?php echo $LANG['UI_CLUSTER_MASTER_NODE']; ?></label>
                                <div class="col-md-10 control-item">
                                    <select class="form-control select2me" id="clusterNodeSelect"></select>
                                    <div class="btn btn-sm green-haze <?php echo $operateAttr; ?>" id="switchMasterNode" <?php echo $operateAttr; ?>><?php echo $LANG['UI_CLUSTER_MASTER_NODE_CHANGE']; ?></div>
                                </div>
                            </div>
                            <!-- 服务IP -->
                            <div class="form-group">
                                <label class="col-md-2 control-label"><?php echo $LANG['UI_CLUSTER_SERVICE_IP']; ?></label>
                                <div class="col-md-10 control-item">
                                    <span class="config-text" id="clusterServiceIp"></span>
                                </div>
                            </div>
                            <!-- 操作进度 -->
                            <div class="form-group" id="clusterOperateProgressDiv">
                                <label class="col-md-2 control-label"><?php echo $LANG['UI_CLUSTER_OPERATE_PROGRESS'] ?></label>
                                <div class="col-md-10 control-item">
                                    <div class="progress" id="clusterOperateProgress">
                                        <div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar"
                                             aria-valuenow="45" aria-valuemin="0" aria-valuemax="100" style="width: 45%"
                                             id="clusterOperateProgressValue"></div>
                                    </div>
                                </div>
                            </div>
                            <hr />
                            <!-- 集群操作日志 -->
                            <div class="form-group cluster-operate-log-label">
                                <?php echo $LANG['UI_CLUSTER_OPERATE_LOG']; ?>
                            </div>
                            <div class="form-group cluster-operate-log-div">
                                <div class="cluster-operate-log--content">
                                    <ul id="clusterOperateLogUl"></ul>
                                </div>
                            </div>
                        </div>
                        <div class="has-config__right">
                            <div class="cluster-topological__wrapper">
                                <div id="clusterTopological" data-chart="echarts"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End: life time stats -->
</div>
<!-- END PAGE CONTENT-->

<!-- BEGIN MODAL -->
<!-- END MODAL -->

<!-- BEGIN DRAWER -->
<!-- BEGIN CONFIG CLUSTER DRAWER -->
<div class="drawer slide " data-placement="right" tabindex="-1" role="dialog" id="configClusterDrawer" data-backdrop="static">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header">
            <h4 class="drawer-title">
                <span>
                    <i class="viconfont vicon-gaojipeizhi"></i>
                </span>
                <span class="title">
                    <?php echo $LANG['UI_CLUSTER_CONFIG_CLUSTER_TITLE'] ?>
                </span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close ">
                    <i class="viconfont vicon-guanbi"></i>
                </span>
            </h4>
        </div>
        <div class="drawer-body">
            <!-- BEGIN FORM-->
            <form action="#" id="config-cluster-form" class="form-horizontal">
                <div class="form-body">
                    <div class="alert alert-danger display-hide">
                        <button type="button" class="close" data-close="alert"></button>
                        <span class="message"></span>
                    </div>

                    <!-- 集群名称 -->
                    <div class="form-group">
                        <label for="clusterName" class="control-label drawer-item-label">
                            <span class="required">* </span>
                            <?php echo $LANG['UI_CLUSTER_NAME']; ?>
                        </label>
                        <div class="drawer-item-content">
                            <input type="text" class="form-control" id="clusterName" maxlength="20" />
                            <div class="help-block">
                                <?php echo $LANG['UI_CLUSTER_NAME_TIPS']; ?>
                            </div>
                        </div>
                    </div>

                    <!-- 集群虚拟IP地址 -->
                    <div class="form-group">
                        <label for="clusterVirtualIp" class="control-label drawer-item-label">
                            <span class="required">* </span>
                            <?php echo $LANG['UI_CLUSTER_VIRTUAL_IP']; ?>
                        </label>
                        <div class="drawer-item-content">
                            <input type="text" class="form-control" id="clusterVirtualIp" />
                            <div class="help-block">
                                <?php echo $LANG['UI_CLUSTER_VIRTUAL_IP_TIPS']; ?>
                            </div>
                        </div>
                    </div>

                    <!-- 集群心跳间隔 -->
                    <div class="form-group">
                        <label class="control-label drawer-item-label">
                            <span class="required">* </span>
                            <?php echo $LANG['UI_CLUSTER_ADVERT_INT']; ?>
                        </label>
                        <div class="drawer-item-content spinner__wrapper">
                            <div id="advertIntSpinner">
                                <div class="input-group spinner-group">
                                    <input type="number" id="advertInt" class="spinner-input form-control" onkeyup="value=value.replace(/[^\d]/g,'')">
                                    <div class="spinner-buttons input-group-btn spinner-group-btn">
                                        <button type="button"
                                                class="btn spinner-up default">
                                            <i class="fa fa-angle-up"></i>
                                        </button>
                                        <button type="button"
                                                class="btn spinner-down default">
                                            <i class="fa fa-angle-down"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="help-block">
                                <?php echo $LANG['UI_CLUSTER_ADVERT_INT_TIPS']; ?>
                            </div>
                        </div>
                        <div class="pt7"><?php echo $LANG['WEB_UTILS_SECOND'] ?></div>
                    </div>

                    <!-- 节点网络配置 -->
                    <div class="form-group">
                        <label class="control-label drawer-item-label">
                            <span class="required">* </span>
                            <?php echo $LANG['UI_CLUSTER_NODE_CONFIG']; ?>
                        </label>
                        <div class="drawer-item-content">
                            <div class="alert alert-block alert-info fade in">
                                <button type="button" class="close" data-dismiss="alert"></button>
                                <ul class="alert-ul">
                                    <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                                    <li>
                                        <?php echo $LANG['UI_CLUSTER_NODE_CONFIG_TIPS'] ?>
                                    </li>
                                </ul>
                            </div>
                            <div class="table-container reset-td-width">
                                <table id="clusterNodeTable"></table>
                            </div>
                        </div>
                    </div>

                    <!-- 切换优先级 -->
                    <div class="form-group config-priority-switch">
                        <label for="prioritySwitch" class="control-label drawer-item-label">
                            <?php echo $LANG['UI_CLUSTER_SWITCH_PRIORITY']; ?>
                        </label>
                        <div class="drawer-item-content flex-items-center">
                            <input type="checkbox" id="prioritySwitch" class="make-switch" data-on-color="primary"
                                   data-off-color="info" data-size="small"
                                   data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                   data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                            <a class="popovers ml12 mt-2" data-container="body" data-trigger="hover"
                               data-html="true" data-placement="right"
                               data-content="<?php echo $LANG['UI_CLUSTER_SWITCH_PRIORITY_TIPS'] ?>">
                                <i class="viconfont vicon-tishi"></i>
                            </a>
                        </div>
                    </div>

                    <!-- 配置优先级 -->
                    <div class="form-group config-priority display-none">
                        <label class="control-label drawer-item-label">
                            <?php echo $LANG['UI_CLUSTER_CONFIG_PRIORITY']; ?>
                        </label>
                        <div class="drawer-item-content config-priority__wrapper">
                            <ul id="configNodePriority"></ul>
                        </div>
                    </div>
                </div>
            </form>
            <!-- END FORM-->
        </div>
        <!-- footer -->
        <div class="drawer-footer">
            <div class="form-actions me-0">
                <button type="button" class="btn default cancel">
                    <?php echo $LANG['UI_PUBLIC_NO'] ?>
                </button>
                <button type="button" class="btn green-haze btn-confirm">
                    <?php echo $LANG['UI_PUBLIC_YES'] ?>
                </button>
            </div>
        </div>
    </div>
</div>
<!-- END CONFIG CLUSTER DRAWER -->
<!-- END DRAWER -->


<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="assets/global/plugins/echarts/V5.4.3/echarts.js"></script>
<script type="text/javascript" src="./scripts/plugins/sortable/Sortable.min.js"></script>
<script type="text/javascript" src="./scripts/platform/cluster/cluster_manager.js"></script>
<!-- END PAGE LEVEL PLUGINS -->