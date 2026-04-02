<?php
include_once '../../tpl/permission.php';
include_once '../../content/platform/public/bs_table.php';
global $CONF, $LANG;
$vendor = $CONF['SYSTEM_INFO']['vendor'];
?>

<!-- BEGIN PAGE STYLE-->
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./css/client/log_download.css" />
<!-- END PAGE STYLE-->

<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <?php
        if ($vendor == $CONF['VENDOR_LIST']['gmp']) {  // GMP导航
            echo <<<EOF
            <li>
                <a class="ajaxify" name="clients" href="./content/client/client.php?tab=0&load_old_table_flag=1">
                    <span>{$LANG['UI_EQUIQMENT_MANAGER']}</span>
                </a>
            </li>
            <span>></span>
EOF;
        } else {
            echo <<<EOF
            <li>
                <a class="ajaxify" name="infrastructure" href="./content/platform/resource/infrastructure.php">
                    <span>{$LANG['UI_PLATFORM_INFRASTRUCTURE']}</span>
                </a>
            </li>
            <span>></span>
            <li>
                <a class="ajaxify" name="infrastructure" href="./content/client/client.php?tab=0&load_old_table_flag=1">
                    <span>{$LANG['UI_CLIENT_MANAGER']}</span>
                </a>
            </li>
            <span>></span>
EOF;
        }
    ?>
    <span class="current"><?php echo $LANG['UI_CLIENT_LOG_DOWNLOAD'] ?></span>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row" style="height: calc(100% - 22px)">
    <div class="col-md-12 height100p">
        <input id="clientUUID" value="<?php echo $_GET['uuid']; ?>" class="display-none"/>
        <span id="clientIp" class="display-none"><?php echo $_GET['ip'] ?></span>
        <input id="clientAgentType" value="<?php echo $_GET['agent_type']; ?>" class="display-none"/>
        <!-- BEGIN VALIDATION STATES-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <?php echo $LANG['UI_AGENT_HOST_NAME'] ?>--<span id="clientName"><?php echo $_GET['name'] ?></span>
                </div>
            </div>
            <div class="portlet-body min-height200" id="log-download">
                <div class="tab-content row margin10">
                    <div class="table-toolbar" style="margin-bottom: 12px">
                        <div class="vin_toolbar mb-0" id="vin_client_log_toolbar">
                            <div class="leftTool">
                            </div>
                            <div class="rightTool">
                                <div class="customBtn3"></div>
                                <div class="customBtn4"></div>
                                <div class="vin_btnLogToolbar"></div>
                            </div>
                        </div>
                        <div class="col-md-12 advancedSearchShow display-none __hide">
                            <div id="log_searchDiv" class="searchDiv mb-0">
                                <?php echo $LANG['UI_PUBLIC_SEARCH_CONDITION']; ?>
                                <span class="searchContent"></span>
                                <span class="clearSearch"><?php echo $LANG['UI_SEARCH_CLEAR'] ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="table-container reset-td-width log-download-content-wrapper">
                        <table id="logDownloadTable"></table>
                    </div>
                </div>
            </div>
        </div>
        <!-- END VALIDATION STATES-->
    </div>
</div>
<!-- END PAGE CONTENT-->

<!-- BEGIN PAGE MODEL -->

<!-- BEGIN ADVANCED MODEL -->

<!-- BEGIN ADVANCE SEARCH MODAL -->
<div id="advanceSearchModal" class="modal xmodal fade form-horizontal" tabindex="-1" data-backdrop="static">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">
            <i class="viconfont vicon-gaojisousuo1"></i>
            <span class="text"><?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?></span>
        </h4>
    </div>
    <div class="modal-body">
        <div class="portlet-body" style="height: auto"> <!-- 这里会撑开父元素，子元素有除了content之外的，不能用100% -->
            <!-- 客户端添加时间 -->
            <div class="list-option">
                <div class="row">
                    <div class="form-group">
                        <label class="control-label col-md-3" for="advanceSearchDateRangePicker">
                            <?php echo $LANG['UI_CLIENT_LOG_TIME'] ?>:
                        </label>
                        <div class="col-md-6 daterangepickerdiv">
                            <input type="text" id="advanceSearchDateRangePicker" class="form-control" autocomplete="off"
                                   readonly style="cursor: pointer"><!-- 时间选择禁用手动输入 -->
                            <i class="viconfont vicon-ge_calendar"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3" for="advanceSearchModule">
                            <?php echo $LANG['UI_CLIENT_LOG_MODULE'] ?>:
                        </label>
                        <div class="col-md-6">
                            <select class="form-control select2me" id="advanceSearchModule"></select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3" for="advanceSearchDbType">
                            <?php echo $LANG['UI_CLIENT_DB_TYPE'] ?>:
                        </label>
                        <div class="col-md-6">
                            <select class="form-control select2me" id="advanceSearchDbType"></select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- modal-footer -->
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default">
            <?php echo $LANG['UI_PUBLIC_NO'] ?>
        </button>
        <button type="button" class="btn btn-primary" id="advanceSearchSubmit">
            <?php echo $LANG['UI_PUBLIC_YES'] ?>
        </button>
    </div>
</div>
<!-- END ADVANCE SEARCH MODAL -->
<!-- END ADVANCED MODEL -->

<!-- END PAGE MODEL -->

<!-- BEGIN PAGE SCRIPT -->
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
<script src="./scripts/client/log_download.js" type="text/javascript"></script>
<!-- END PAGE SCRIPT -->
