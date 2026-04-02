<?php

include_once '../../../tpl/permission.php';
include_once '../public/bs_table.php';

global $LANG;

//权限管理,根据用户的权限,显示tab
$permission = $_SESSION['permission'];
$tab = intval($_GET['tab'] ?? 0);
$nodeModule = [
    'node_manager',
    'node_pool',
];
$permissionModuleClass = [
    'node_manager' => in_array('node_manager', $permission) ? '' : 'display: none;',
    'node_pool' => in_array('node_pool', $permission) ? '' : 'display: none;',
];
$tabExistsFlag = !$permissionModuleClass[$nodeModule[$tab]];
$showTabCount = 0;
foreach ($permissionModuleClass as $module => $style) {
    if (!$style) {
        $showTabCount++;
        if (!$tabExistsFlag) {
            $tab = array_search($module, $nodeModule);
        }
    }
}
$tabActive = ['', ''];
$tabActive[$tab] = "active";

?>
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
    <span class="curent"><?php echo $LANG['UI_PALTFORM_NODE'] ?></span>
</h3>
<!-- END PAGE HEADER -->

<!-- BEGIN PAGE CONTENT-->
<div class="vinchin-wrapper" style="height: calc(100% - 22px)">
    <div class="vinchin-wrapper__tabs">
        <ul class="nav nav-tabs nav-line-tabs">
            <li class="<?php echo $tabActive[0]; ?> nav-item" style="<?php echo $permissionModuleClass['node_manager'] ?>">
                <a href="#nodeManagerDiv" data-toggle="tab" class="nav-link">
                    <i class="levelchild viconfont vicon-node_manager"></i>
                    <?php echo $LANG['UI_PALTFORM_NODE']; ?>
                </a>
            </li>
            <li class="<?php echo $tabActive[1]; ?> nav-item" style="<?php echo $permissionModuleClass['node_pool'] ?>">
                <a href="#nodePoolDiv" data-toggle="tab" class="nav-link">
                    <i class="levelchild viconfont vicon-jisuanziyuanchi"></i>
                    <?php echo $LANG['UI_PLATFORM_NODE_POOL']; ?>
                </a>
            </li>
        </ul>
        <div class="tab-content hover-scroll-y overflow-x-hidden">
            <div class="tab-pane <?php echo $tabActive[0]; ?> " id="nodeManagerDiv" >
                <?php
                if (!$permissionModuleClass['node_manager']) {
                    include_once './node_manager.php';
                }
                ?>
            </div>
            <div class="tab-pane <?php echo $tabActive[1]; ?> " id="nodePoolDiv" >
                <?php
                if (!$permissionModuleClass['node_pool']) {
                    include_once './node_pool.php';
                }
                ?>
            </div>
        </div>
    </div>
</div>
<!-- END PAGE CONTENT-->


<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<!-- END PAGE LEVEL PLUGINS -->