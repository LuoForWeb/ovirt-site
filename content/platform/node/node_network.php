<?php

include_once '../../../tpl/permission.php';
include_once '../public/bs_table.php';

global $LANG;

//权限管理,根据用户的权限,显示tab
$permission = $_SESSION['permission'];
$tab = intval($_GET['tab'] ?? 0);
$tabActive = ['', ''];
$tabActive[$tab] = "active";

?>

<!-- BEGIN PAGE LEVEL STYLES -->
<!-- END PAGE LEVEL STYLES -->

<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="backup_manager" href="./content/platform/backup/backupresources.php">
            <span><?php echo $LANG['UI_PLATFORM_BACKUP_RESOURCE'] ?></span>
        </a>
    </li>
    <span>
        >
    </span>
    <li>
        <a class="ajaxify" name="backup_manager" href="./content/platform/node/node.php" >
            <span><?php echo $LANG['UI_PALTFORM_NODE']?></span>
            <span>></span>
        </a>
    </li>
    <span style="color: #575962;"><?php echo $_GET['nodename']?></span>
</h3>
<!-- END PAGE HEADER-->

<!-- BEGIN PAGE CONTENT-->
<label for="nodeUUID" class="display-none"></label>
<input id="nodeUUID" value="<?php echo $_GET['uuid'];?>" class="display-none" />
<!-- BEGIN TAB PORTLET-->
<div class="vinchin-wrapper" style="height: calc(100% - 22px)">
    <div class="vinchin-wrapper__tabs">
        <ul class="nav nav-tabs nav-line-tabs">
            <li class="<?php echo $tabActive[0]; ?> nav-item">
                <a href="#nodeNetworkManagerDiv" data-toggle="tab" class="nav-link">
                    <i class="levelchild viconfont vicon-wangluo"></i>
                    <?php echo $LANG['UI_NODE_NETWORK_LIST']; ?>
                </a>
            </li>
            <li class="<?php echo $tabActive[1]; ?> nav-item">
                <a href="#nodeNetworkPoolDiv" data-toggle="tab" class="nav-link">
                    <i class="levelchild viconfont vicon-a-Pie-twojindu2"></i>
                    <?php echo $LANG['WEB_NODE_NETWORK_POOL']; ?>
                </a>
            </li>
        </ul>
        <div class="tab-content hover-scroll-y overflow-x-hidden">
            <div class="tab-pane <?php echo $tabActive[0];?> " id="nodeNetworkManagerDiv" >
                <?php include_once './node_network_manager.php'; ?>
            </div>
            <div class="tab-pane <?php echo $tabActive[1];?> " id="nodeNetworkPoolDiv" >
                <?php include_once './node_network_pool.php'; ?>
            </div>
        </div>
    </div>
</div>
<!-- END TAB PORTLET-->
<!-- END PAGE CONTENT-->


<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<!-- END PAGE LEVEL PLUGINS -->