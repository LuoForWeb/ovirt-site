<?php
include_once '../../tpl/permission.php';
include_once '../../content/platform/public/bs_table.php';
global $LANG, $CONF;
?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<?php

//权限管理,根据用户的权限,显示tab
$permission = $_SESSION['permission'];
$tab = intval($_GET['tab']);
$clientModule = [
    'agent_manager',
    'client_group',
    'agent_pool',
    'client_mirror',
];
$permissionModuleClass = [
    'agent_manager' => in_array('agent_manager', $permission) ? '' : 'display: none;',
    'client_group' => in_array('client_group', $permission) ? '' : 'display: none;',
    'client_mirror' => in_array('client_mirror', $permission) ? '' : 'display: none;',
    'agent_pool' => in_array('agent_pool', $permission) ? '' : 'display: none;',
];
$tabExistsFlag = !$permissionModuleClass[$clientModule[$tab]];
$showTabCount = 0;
foreach ($permissionModuleClass as $module => $style) {
    if (!$style) {
        $showTabCount++;
        if (!$tabExistsFlag) {
            $tab = array_search($module, $clientModule);
        }
    }
}
$tabActive = array("", "", "", "");
$tabActive[$tab] = "active";

?>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<h3 class="breadcrumb" <?php if ($CONF['SYSTEM_INFO']['vendor'] == $CONF['VENDOR_LIST']['gmp']) {echo 'style="display:none;"';}?>>
    <li>
        <a class="ajaxify" name="infrastructure" href="./content/platform/resource/infrastructure.php">
            <span><?php echo $LANG['UI_PLATFORM_INFRASTRUCTURE'] ?></span>
        </a>
    </li>
    <span>
        >
    </span>
    <span class="curent"><?php echo $LANG['UI_CLIENT_MANAGER'] ?></span>
</h3>

<!-- BEGIN TAB PORTLET-->
<div class="vinchin-wrapper" style="height: calc(100% - 22px)">
    <div class="vinchin-wrapper__tabs">
        <ul class="nav nav-tabs nav-line-tabs" style="<?php if ($showTabCount == 1) echo 'display: none;' ?>">
            <li class="<?php echo $tabActive[0] ?> nav-item" style="<?php echo $permissionModuleClass['agent_manager'] ?>">
                <a href="#clientmanagerdiv" data-toggle="tab" class="nav-link">
                    <i class="levelchild viconfont vicon-client"></i>
                    <?php echo $LANG['UI_CLIENT_MANAGER'] ?>
                </a>
            </li>
            <li class="<?php echo $tabActive[1] ?> nav-item" style="<?php echo $permissionModuleClass['client_group'] ?>">
                <a href="#clientgroupdiv" data-toggle="tab" class="nav-link">
                    <i class="levelchild viconfont vicon-client_groups"></i>
                    <?php echo $LANG['UI_CLIENT_GROUP_MANAGER'] ?>
                </a>
            </li>
            <li class="<?php echo $tabActive[2] ?> nav-item" style="<?php echo $permissionModuleClass['agent_pool'] ?>">
                <a href="#agentPoolDiv" data-toggle="tab" class="nav-link">
                    <i class="levelchild viconfont vicon-chuanshudailiziyuanchi"></i>
                    <?php echo $LANG['UI_AGENT_POOL'] ?>
                </a>
            </li>
            <li class="<?php echo $tabActive[3] ?> nav-item" style="<?php echo $permissionModuleClass['client_mirror'] ?>">
                <a href="#clientmirrordiv" data-toggle="tab" class="nav-link">
                    <i class="levelchild viconfont vicon-yindaojingxiang"></i>
                    <?php echo $LANG['UI_CLIENT_MIRROR'] ?>
                </a>
            </li>
        </ul>
        <div class="tab-content hover-scroll-y overflow-x-hidden">
            <div class="tab-pane <?php echo $tabActive[0]; ?> " id="clientmanagerdiv" >
                <?php
                if (!$permissionModuleClass['agent_manager']) {
                    include_once './client_manager.php';
                }
                ?>
            </div>
            <div class="tab-pane <?php echo $tabActive[1]; ?> row" id="clientgroupdiv" >
                <?php
                if (!$permissionModuleClass['client_group']) {
                    include_once './client_group.php';
                }
                ?>
            </div>
            <div class="tab-pane <?php echo $tabActive[2]; ?> " id="agentPoolDiv" >
                <?php
                if (!$permissionModuleClass['agent_pool']) {
                    include_once './agent_pool.php';
                }
                ?>
            </div>
            <div class="tab-pane <?php echo $tabActive[3]; ?> " id="clientmirrordiv" >
                <?php
                if (!$permissionModuleClass['client_mirror']) {
                    include_once './client_mirror.php';
                }
                ?>
            </div>
        </div>
    </div>
</div>
<!-- END TAB PORTLET-->
<!-- END PAGE CONTENT-->


<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<!-- END PAGE LEVEL PLUGINS -->