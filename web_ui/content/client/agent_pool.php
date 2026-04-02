<?php
include_once '../../tpl/permission.php';
global $LANG;
?>

<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./css/platform/common_delete.css" />
<link rel="stylesheet" type="text/css" href="./css/client/agent_pool.css" />
<!-- END PAGE LEVEL STYLES -->

<!-- BEGIN PAGE CONTENT -->
<div class="table-toolbar" id="agentPoolList">
    <div class="vin_toolbar mb-0" id="vin_agent_pool_toolbar">
        <div class="leftTool"></div>
        <div class="rightTool">
            <div class="vin_btnToolbar"></div>
        </div>
    </div>
</div>
<div class="table-container reset-td-width agent-pool-table-container">
    <table class="table" id="agentPoolTable"></table>
</div>
<!-- END PAGE CONTENT -->

<!-- BEGIN MODAL -->
<!-- END MODAL -->

<!-- BEGIN DRAWER -->

<!-- BEGIN ADD/EDIT STORAGE POOL DRAWER -->
<div class="drawer slide " data-placement="right" tabindex="-1" role="dialog" id="modifyAgentPoolDrawer" data-backdrop="static">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header">
            <h4 class="drawer-title">
                <span>
                    <i class="viconfont vicon-biaogetianjia icon"></i>
                </span>
                <span class="title">
                    <?php echo $LANG['UI_STORAGE_POOL_ADD_TITLE'] ?>
                </span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close ">
                    <i class="viconfont vicon-guanbi"></i>
                </span>
            </h4>
        </div>
        <div class="drawer-body">
            <!-- BEGIN FORM-->
            <form action="#" id="modifyAgentPoolForm" class="form-horizontal" data-lang="" data-type="" data-uuid="">
                <div class="form-body">
                    <div class="alert alert-danger display-hide">
                        <button type="button" class="close" data-close="alert"></button>
                        <span class="message"></span>
                    </div>

                    <!-- 资源池名称 -->
                    <div class="form-group">
                        <label for="agentPoolName" class="control-label drawer-item-label">
                            <span class="required">* </span>
                            <?php echo $LANG['UI_AGENT_POOL_NAME']; ?>
                        </label>
                        <div class="drawer-item-content">
                            <input type="text" class="form-control" id="agentPoolName" maxlength="60" />
                            <div class="help-block">
                                <?php echo $LANG['UI_AGENT_POOL_NAME_TIPS']; ?>
                            </div>
                        </div>
                    </div>

                    <!-- 选择传输代理 -->
                    <div class="form-group">
                        <label class="control-label drawer-item-label">
                            <span class="required">* </span>
                            <?php echo $LANG['UI_AGENT_POOL_AGENT']; ?>
                        </label>
                        <div class="drawer-item-content">
                            <ul class="ztree" id="agentListTree"></ul>
                        </div>
                    </div>

                    <!-- 备注 -->
                    <div class="form-group">
                        <label for="agentPoolRemark" class="control-label drawer-item-label">
                            <?php echo $LANG['UI_AGENT_POOL_REMARK']; ?>
                        </label>
                        <div class="drawer-item-content">
                            <input type="text" class="form-control" id="agentPoolRemark"
                                   placeholder="<?php echo $LANG['UI_AGENT_POOL_REMARK_PLACEHOLDER'] ?>" />
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
<!-- END ADD/EDIT STORAGE POOL DRAWER -->

<!-- END DRAWER -->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/client/agent_pool.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
