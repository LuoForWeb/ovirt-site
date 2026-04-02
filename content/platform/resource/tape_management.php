<!-- BEGIN TAB PANE CONTENT -->
<div class="row tape-management">
    <div class="col-md-4 tape-management__left">
        <div class="header">
            <i class="viconfont vicon-a-Chart-graphguanxitu me-8"></i>
            <span class="title"><?php echo $LANG['UI_TAPE_MANAGEMENT_STRUCTURE'] ?></span>
        </div>
        <div class="structure-management-wrapper">
            <div class="structure-management-wrapper__header">
                <button type="button" id="scan" class="btn green-haze <?php if (!in_array('p_tape_device_scan', $_SESSION['permissionArr'])) {echo 'displaynone';}?>">
                    <i class="viconfont vicon-a-Scanning-twosaomiao me-4"></i>
                    <span class="btn-text"><?php echo $LANG['UI_TAPE_SCAN'] ?></span>
                </button>
                
                <div class="scan-wrap">
                    <a id="check_log" class="<?php if (!in_array('p_tape_monitor_list', $_SESSION['permissionArr'])) {echo 'displaynone';}?>">
                        <i class="viconfont vicon-log"></i><?php echo $LANG['UI_TAPE_CHECK_LOG'] ?></a>
                    </a>
                    <span id="status_span" class="help-block mt-0 mb-0"></span>
                </div>
            </div>
            <select class="bs-select" data-show-subtext="true" id="nodeselect"></select>
            <div class="tree-wrapper">
                <div class="alert alert-block alert-info fade in display-hide h-50px" id="notapetips">
                    <button type="button" class="close" data-dismiss="alert"></button>
                    <ul class="alert-ul">
                        <strong class="alert-ul-head">
                            <?php echo $LANG['UI_PUBLIC_TIPS'] ?>：
                        </strong>
                        <li>
                            <?php echo $LANG['UI_TAPE_NO_TAPE_TIP'] ?>
                        </li>
                    </ul>
                </div>
                <ul id="tape_tree" class="ztree"></ul>
            </div>
        </div>
    </div>
    <div class="col-md-8 tape-management__right">
        <div class="header">
            <i class="viconfont vicon-a-View-listxiangqingliebiao me-8"></i>
            <span class="title"><?php echo $LANG['UI_TAPE_LIB_LIST'] ?></span>
        </div>
        <div class="list-management-wrapper" id="tape_lib">
            <div class="vin_toolbar mb-10" id="vin_tape_toolbar">
                <div class="leftTool">
                    <div class="customBtn1">
                        <?php
                        if (in_array("p_tape_device_add", $_SESSION['permissionArr'])) {
                            echo ' <button class="dropdown-toggle btn-whitespace flex_center addTask p-lr8" id="addTask" aria-haspopup="true" aria-expanded="false" style="width:118px;height:34px;border:0px;display: none;">'.
                                '<i class="addNewTask"></i>'.
                                '<span>' . $LANG['UI_PUBLIC_ADD'] . '</span>'.
                                '</button>';
                        }
                        if (in_array("p_tape_device_delete", $_SESSION['permissionArr'])) {
                            echo '<div style="cursor:not-allowed;" style="display: none;">
                                    <button class="btn viconfont vicon-a-Deleteshanchu1 b-btn brr2 mr12 exch-forbid-event" id="delete_backupset"></button>
                                </div>';
                        }
                        ?>
                    </div>
                </div>
                <div class="rightTool">
                    <div class="vin_btnToolbar"></div>
                </div>
            </div>

            <div class="table-container">
                <table id="tape_lib_table"></table>
            </div>

            <!-- 磁带提示 -->
            <div class="alert alert-block alert-info fade in h-110px mt-10 mb-0" id="tapeTips">
                <button type="button" class="close" data-dismiss="alert"></button>
                <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                <ol class="alert-ol">
                    <li>
                        <?php echo $LANG['UI_TAPE_TIP1'] ?>
                    </li>
                    <li>
                        <?php echo $LANG['UI_TAPE_TIP2'] ?>
                    </li>
                </ol>
            </div>

            <!-- 磁带组策略提示 -->
            <div class="alert alert-block alert-info fade in display-none h-280px mt-10 mb-0" id="groupStrategyTips">
                <button type="button" class="close" data-dismiss="alert"></button>
                <h4 class="alert-heading"><strong><?php echo $LANG['UI_TAPE_GROUP_STRATEGY'] ?></strong>
                </h4>
                <ol class="alert-ol">
                    <li>
                        <?php echo $LANG['UI_TAPE_GROUP_GENERATE_STRATEGY_TIP1'] ?>
                    </li>
                    <li>
                        <?php echo $LANG['UI_TAPE_GROUP_GENERATE_STRATEGY_TIP2'] ?>
                    </li>
                    <li>
                        <?php echo $LANG['UI_TAPE_GROUP_GENERATE_STRATEGY_TIP3'] ?>
                    </li>
                    <li>
                        <?php echo $LANG['UI_TAPE_GROUP_RESERVE_STRATEGY_TIP1'] ?>
                    </li>
                    <li>
                        <?php echo $LANG['UI_TAPE_GROUP_RESERVE_STRATEGY_TIP2'] ?>
                    </li>
                    <li>
                        <?php echo $LANG['UI_TAPE_GROUP_RESERVE_STRATEGY_TIP3'] ?>
                    </li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!-- END TAB PANE CONTENT -->