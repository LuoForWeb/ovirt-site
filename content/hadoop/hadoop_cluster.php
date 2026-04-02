<?php include_once '../../tpl/permission.php'; ?>
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>


<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="infrastructure" href="./content/platform/resource/infrastructure.php">
            <span><?php echo $LANG['UI_PLATFORM_INFRASTRUCTURE'] ?></span>
        </a>
    </li>
    <span>
        >
    </span>
    <span class="curent"><?php echo $LANG['UI_PLATFORM_HADOOP_HDFS'] ?></span>
</h3>
<!-- END PAGE HEADER-->

<!-- BEGIN PAGE CONTENT-->
<div class="resource-manager-wrap">
    <div class="resource-manager-wrap__header">
        <span>
            <i class="levelchild viconfont vicon-a-Elephantdaxiang-01 me-10"></i>
            <span class="resource-manager-wrap__header__text"><?php echo $LANG['WEB_HADOOP_CLUSTER_MANAGE'] ?></span>
        </span>
    </div>
    <div class="resource-manager-wrap__content">
        <div class="table-toolbar">
            <div class="vin_toolbar" id="vin_hadoop_cluster_toolbar">
                <!-- 表格 -->
                <div class="leftTool">
                    <!-- 删除 -->
                    <div style="cursor: not-allowed">
                        <button type="button" id="deleteSelect" class="btn viconfont vicon-a-Deleteshanchu1 b-btn brr2 mr12 exch-forbid-event">
                        </button>
                    </div>
                    <!-- 搜索 -->
                    <!-- <div class="btn-group">
                        <div style="position: relative;">
                            <div class="search_glass">
                                <div type="button" class="b-btn search-btn" id="searchSubmit"><i class="icon-search"></i></div>
                            </div>
                            <input class="search_glass_input" id="searchVal"  autocomplete="off" type="text" placeholder="<?php echo $LANG['WEB_HADOOP_SEARCH_BY_CLUSTER_NAME'] ?>">
                        </div>
                    </div> -->
                    <div class="search input-group mr12">
                        <input type="search" maxlength="128" id="searchVal" class="searchinput customSearch" autocomplete="off"
                            style="padding-right:32px;min-width: 220px;" maxlength="64" type="text"
                            placeholder="<?php echo $LANG['WEB_HADOOP_SEARCH_BY_CLUSTER_NAME'] ?>">
                        <div class="position0" style="width:auto;height:34px">
                            <button class="b-btn clear hide position0" id="clearSearchBtn">
                            <i class="icon-close-small"></i></button>
                        </div>
                        <div class="search-btn positionL0" style="width:auto;height:34px;">
                            <button class="b-btn search-btn" id="searchSubmit"><i class="icon-search"></i></button>
                        </div>
                    </div>
                    <!-- 添加 -->

                    <div class="btn-group">
                        <button type="button"class="btn table-toolbar-btn" id="addCluster">
                            <i class="viconfont vicon-danchuangtianjia1"></i>
                            <span><?php echo $LANG['UI_PUBLIC_ADDNEW'] ?></span>
                        </button>
                    </div>
                    <!-- 授权 -->
                    <div class="btn-group">
                        <button type="button"class="btn table-toolbar-btn" id="authorizationCluster" style="display: none;">
                            <i class="viconfont vicon-danchuangshouquan"></i>
                            <span><?php echo $LANG['UI_SETTINGS_AUTH'] ?></span>
                        </button>
                    </div>
                    <!-- 自动刷新 -->
                    <div class="btn-group">
                        <button type="button"class="btn table-toolbar-btn" id="refreshInterval">
                            <i class="viconfont vicon-ge_refresh"></i>
                            <span><?php echo $LANG['UI_VCENTER_SET_AUTO_REFRESH'] ?></span>
                        </button>
                    </div>
                </div>
                <div class="rightTool">
                    <button type="button" class="btn btn-primary adv_btn brr2 p-lr8" id="advanceSearchBtn">
                        <i class="adv-search"></i>
                        <span><?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH']; ?></span>
                    </button>
                    <div class="vin_btnToolbar"></div>
                </div>
            </div>
        </div>

        <div id="current_searchDiv" class="search-content searchDiv display-none">
            <?php echo $LANG['UI_PUBLIC_SEARCH_CONDITION']; ?>
            <span class="searchContent"></span>
            <span class="clearSearch"><?php echo $LANG['UI_SEARCH_CLEAR'] ?></span>
        </div>

        <div class="table-container hadoop-cluster-table-container">
            <table id="cluster_table"></table>
        </div>

        <div class="alert alert-block alert-info fade in h-100px m0" id="hadoop_cluster_tip">
            <button id="hadoop_cluster_tip_close" type="button" class="close" data-dismiss="alert"></button>
            <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
            <ol class="alert-ol">
                <li>
                    <?php echo $LANG['WEB_HADOOP_AUTH_CLUSTER_TIPS1'] ?>
                </li>
                <li>
                    <?php echo $LANG['WEB_HADOOP_AUTH_CLUSTER_TIPS2'] ?>
                </li>
            </ol>
        </div>
    </div>
</div>

<!-- start refresh modal -->
<div id="refreshModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title"><i class="viconfont vicon-ge_refresh"></i><?php echo $LANG['UI_HADOOP_REFRESH_SET']; ?>
        </h4>
    </div>
    <div class="modal-body">
        <div class="portlet-body">
            <div class="list-option">
                <div class="row">
                    <!-- 虚拟化中心 -->
                    <label class="control-label col-md-5"><?php echo $LANG['UI_HADOOP_REFRESH_DESCRIPTION']; ?>
                    </label>
                    <div class="col-md-5">
                        <div class="input-group">
                            <input type="text" id="refreshValue" style="text-align: center;" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control" maxlength="2" onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}" onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'0')}else{this.value=this.value.replace(/\D/g,'')}">
                            <div class="spinner-buttons input-group-btn">
                                <button type="button" class="btn spinner-up default" style="height: 34px;">
                                    <i class="fa fa-angle-up"></i>
                                </button>
                                <button type="button" class="btn spinner-down default" style="height: 34px;">
                                    <i class="fa fa-angle-down"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <label class="control-label col-md-2 left-padding"><?php echo $LANG['WEB_UTILS_MINUTE'] ?>
                    </label>
                </div>
            </div>
        </div>
        
        <div class="list-option">
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-block alert-info fade in" id="marktips">
                        <button type="button" class="close" data-dismiss="alert"></button>
                        <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                        <ol class="alert-ol">
                            <li>
                                <?php echo $LANG['UI_HADOOP_REFRESH_TIP1'] ?>
                            </li>
                            <li>
                                <?php echo $LANG['UI_HADOOP_REFRESH_TIP2'] ?>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- modal-footer -->
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" id="cancelRefreshTime" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
        <button type="button"class="btn btn-primary" id="refreshTime"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>

<!-- end refresh modal -->
<div id="advanced_search_modal" class="modal xmodal fade form-horizontal" tabindex="-1">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title"><i class="fa fa-search"></i>
            <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?>
        </h4>
    </div>
    <div class="modal-body">
        <div class="portlet-body" style="height: auto">
            <!-- 集群添加时间 -->
            <div class="list-option">
                <div class="row">
                    <label class="control-label col-md-4"> <?php echo $LANG['UI_HADOOP_CLUSTER_ADD_TIME'] ?>：
                    </label>
                    <div class="col-md-6 daterangepickerdiv">
                        <input type="text" id="advanced_search_daterangepicker" class="form-control"
                            autocomplete="off" readonly style="cursor: pointer;background-color:transparent;color:#666"><!-- 时间选择禁用手动输入 -->
                        <i class="viconfont vicon-ge_calendar"></i>
                    </div>
                </div>
            </div>
            <!-- 状态 -->
            <div class="list-option">
                <div class="row">
                    <label class="control-label col-md-4"><?php echo $LANG['UI_PUBLIC_STATUS'] ?> ：
                    </label>
                    <div class="col-md-6">
                        <select class="form-control select2me" id="advanced_search_status">
                            <option value=""><?php echo $LANG['UI_PUBLIC_ALL'] ?></option>
                            <option value="online"><?php echo $LANG['WEB_AGENT_STATUS_ONLINE'] ?></option>
                            <option value="3"><?php echo $LANG['WEB_AGENT_STATUS_OFFLINE'] ?></option>
                            <option value="4"><?php echo $LANG['WEB_PLATFORM_DES_ABNORMAL'] ?></option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- modal-footer -->
    <div class="modal-footer">
        <button type="button" data-dismiss="modal"
            class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
        <button type="button" class="btn btn-primary"
            id="advanceSearchSubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>

<!-- BEGIN AUTH MODAL -->
<div id="hadoopAuthModal" class="modal xmodal fade form-horizontal" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title"><i class="icon-badge"></i> <?php echo $LANG['UI_HADOOP_CLUSTER_AUTH'] ?>
        </h4>
    </div>
    <div class="modal-body">
        <div class="portlet-body">
            <div class="form-group alldirdiv">
                <div class="col-md-12">
                    <!-- 添加授权 -->
                    <div class="btn-group mb-20">
                        <button type="button" id="authCluster" class="btn btn-sm green-haze">
                        <i class="viconfont vicon-ge_authorization2"></i> <?php echo $LANG['UI_VCENTER_AUTH_ADD'] ?>
                        </button>
                    </div>
                    <!-- 取消授权 -->
                    <div class="btn-group mb-20">
                        <button type="button" id="authClusterRemove" class="btn btn-sm green-haze">
                        <i class="viconfont vicon-guanbi"></i> <?php echo $LANG['UI_VCENTER_AUTH_DELETE'] ?>
                        </button>
                    </div>
                    <span id="hadoopDes" class="floatRight mb-20" style="margin-top: 7px;"></span>
                    <table id="auth_table"></table>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_OFF_TWO'] ?></button>
    </div>
</div>	
<!-- END AUTH MODAL -->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script src="./scripts/hadoop/hadoop_cluster.js" type="text/javascript">

