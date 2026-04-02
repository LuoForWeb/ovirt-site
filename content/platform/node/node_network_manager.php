<?php
include_once '../../../tpl/permission.php';
global $LANG;
?>
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css"
      href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./css/platform/node/node_network_manager.css"/>
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<input id="nodeUUID" value="<?php echo $_GET['uuid']; ?>" class="display-none"/>
<!-- Begin: life time stats -->
<div id="nodeNetworkList">
    <div class="table-toolbar mb-12">
        <div class="vin_toolbar mb-0" id="vin_node_network_toolbar">
            <div class="leftTool">
                <div class="btn-group">
                    <button type="button" id="delete" class="b-btn brr2 mr12 table-toolbar-btn">
                        <i class="icon-gray-delete"></i>
                    </button>
                </div>

                <div class="btn-group">
                    <button type="button" id="add" class="btn table-toolbar-btn">
                        <i class="viconfont vicon-biaogetianjia"></i>
                        <?php echo $LANG['UI_PUBLIC_ADD'] ?>
                    </button>
                </div>

                <div class="btn-group ">
                    <button type="button" id="edit" class="btn table-toolbar-btn">
                        <i class="viconfont vicon-xiugai"></i>
                        <?php echo $LANG['UI_PUBLIC_MODIFY'] ?>
                    </button>
                </div>

                <div class="btn-group">
                    <button type="button" id="order" class="btn table-toolbar-btn">
                        <i class="viconfont vicon-ge_sort"></i>
                        <?php echo $LANG['UI_NODE_NETWORK_ORDER'] ?>
                    </button>
                </div>
            </div>

            <div class="rightTool">
                <div class="vin_btnToolbar"></div>
            </div>
        </div>
    </div>

    <div class="table-container reset-td-width">
        <table class="table" id="nodeNetworkTable"></table>
    </div>

    <div class="alert alert-block alert-info fade in mb-0" id="nodeNetworkTips">
        <button type="button" class="close" data-dismiss="alert"></button>
        <h4 class="alert-heading">
            <strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong>
        </h4>
        <ol class="alert-ol">
            <li><?php echo $LANG['UI_NODE_NETWORK_MANAGER_TIPS1'] ?></li>
            <li><?php echo $LANG['UI_NODE_NETWORK_MANAGER_TIPS2'] ?></li>
            <li><?php echo $LANG['UI_NODE_NETWORK_MANAGER_TIPS3'] ?></li>
        </ol>
    </div>
</div>
<!-- End: life time stats -->

<!-- BEGIN ADD MODAL -->
<div id="addModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first"
     data-backdrop="static">
    <input id="networkuuid" class="display-none"/>
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title addDiv">
            <i class="viconfont vicon-biaogetianjia"></i>
            <span class="text"><?php echo $LANG['UI_NODE_NETWORK_ADD_MAP'] ?></span>
        </h4>
        <h4 class="modal-title display-none editDiv">
            <i class="viconfont vicon-xiugai"></i>
            <span class="text"><?php echo $LANG['UI_NODE_NETWORK_EDIT_MAP'] ?></span>
        </h4>
    </div>
    <div class="modal-body" style="padding-top;: 50px">
        <form action="#" id="networkForm" class="form-horizontal">
            <div class="form-group">
                <label class="col-md-3 control-label"><?php echo $LANG['UI_PUBLIC_IP_ADDRESS'] ?></label>
                <div class="col-md-8">
                    <div class="input-icon right">
                        <i class="fa"></i>
                        <input type="text" class="form-control" maxlength="64" name="ipaddr" id="ipaddr">
                        <div><span class="help-block ">
                        <?php echo $LANG['UI_NODE_NETWORK_IP_TIPS'] ?>
                        </span></div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label"><?php echo $LANG['UI_SETTINGS_NETWORK_TOOL_PORT_TIPS'] ?></label>
                <div class="col-md-8 ">
                    <div class="input-icon right">
                        <i class="fa"></i>
                        <input type="text" class="form-control" maxlength="64" name="connectport"
                               id=connectport>
                        <div><span class="help-block ">
                        <?php echo $LANG['UI_NODE_NETWORK_MAP_PORT_TIPS'] ?>
                        </span></div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label"><?php echo $LANG['UI_VCENTER_RNAME'] ?></label>
                <div class="col-md-8 ">
                    <div class="input-icon right">
                        <i class="fa"></i>
                        <input type="text" class="form-control" maxlength="64" name="nickname" id=nickname>
                        <div><span class="help-block ">
                        <?php echo $LANG['UI_NODE_NETWORK_NICKNAME_TIPS'] ?>
                        </span></div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <button type="button" data-dismiss="modal"
                class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
        <button type="button" class="btn btn-primary addDiv"
                id="addsubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
        <button type="button" class="btn btn-primary display-none editDiv"
                id="editsubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>
<!-- END ADD MODAL -->

<!-- BEGIN ORDER MODAL -->
<div id="orderModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first"
     data-backdrop="static">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">
            <i class="viconfont vicon-ge_sort"></i>
            <span class="text"><?php echo $LANG['UI_NODE_NETWORK_MAP_ORDER'] ?></span>
        </h4>
    </div>
    <div class="modal-body">
        <div class="alert alert-block alert-info fade in">
            <button type="button" class="close" data-dismiss="alert"></button>
            <ul class="alert-ul">
                <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                <li>
                    <?php echo $LANG['UI_NODE_NETWORK_MAP_ORDER_TIPS'] ?>
                </li>
            </ul>
        </div>
        <div id="networkList" class="list-group col">

        </div>
    </div>
    <div class="modal-footer">
        <button type="button" data-dismiss="modal"
                class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
        <button type="button" class="btn btn-primary"
                id="ordersubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>
<!-- END ORDER MODAL -->
<!-- END PAGE CONTENT-->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<?php
if ($_SESSION['language'] != "en-us") {
    //如果不是英文,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
        $_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script type="text/javascript" src="./scripts/plugins/sortable/Sortable.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript"
        src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/platform/node/node_network_manager.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	