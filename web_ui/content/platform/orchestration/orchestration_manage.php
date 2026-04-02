<?php include_once '../../../tpl/permission.php'; ?>
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>

<div class="jobs-wrapper">
    <div class="vin_toolbar" id="vinOrchestrationToolbar">
        <div class="left-toolbar">
            <div class="left-toolbar-item me-10">
                <div class="search input-group">
                    <input id="orchestration_job_seach_ipt" class="table-toolbar-search-ipt" autocomplete="off" type="text"
                        placeholder="<?php echo $LANG['UI_JOB_SEARCH_JOB_NAME'] ?>">
                    <div class="position0" style="width:auto;height:34px">
                        <button class="b-btn clear hide position0" id="orchestration_job_clear_search"><i
                                class="icon-close-small"></i></button>
                    </div>
                    <div class="search-btn positionL0" style="width:auto;height:34px;">
                        <button id="orchestration_job_search_btn" class="b-btn search-btn"><i
                                class="icon-search"></i></button>
                    </div>
                </div>
            </div>
            <div class="left-toolbar-item">
                <?php
                //检查屏蔽只读观察者的操作按钮
                if (in_array("p_task_orchestration_manager", $_SESSION['permissionArr'])) {
                    echo '<button type="button" id="addOrchestration" class="dropdown-toggle btn-font btn-title btn-whitespace">
                            <i class="viconfont vicon-biaogetianjia mr5"></i> ' . $LANG['UI_PUBLIC_ADD'] . '
                        </button>';
                }
                ?>
            </div>
        </div>

        <div class="flex-items-center">
            <div class="rightTool">
                <div class="vin_orchestrationToolbar"></div>
            </div>
        </div>
    </div>
    <?php
    //检查屏蔽只读观察者的操作按钮
    if (in_array("p_task_orchestration_manager", $_SESSION['permissionArr'])) {
        // 删除
        echo '<div class="plan-batch-operation">
                <div class="batch-operation-left">
                    <div class="mr12" id="batchStartStrategy">
                        <i class="icon-start-disable"></i>
                        <span class="ml5">' . $LANG['UI_JOB_TASK_ORCHESTRATION_OPERATE_START_STRATEGY'] . '</span>
                    </div>
                    <div class="mr12" id="batchStop">
                        <i class="icon-stop-disable"></i>
                        <span class="ml5">' . $LANG['UI_JOB_TASK_ORCHESTRATION_OPERATE_STOP'] . '</span>
                    </div>
                    <div class="mr12" id="batchDelete">
                        <i class="icon-delete-disable"></i>
                        <span class="ml5">' . $LANG['UI_PUBLIC_DELETE'] . '</span>
                    </div>
                </div>
            </div>';
    }
    ?>

    <div class="table-container orchestration-job-table-container">
        <table id="jobOrchestrationTable"></table>
    </div>

    <div class="alert alert-block alert-info fade in h-100px mb-0" id="addTaskTips">
        <button type="button" class="close" data-dismiss="alert" id="close_orchestration_tips"></button>
        <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
        <ol class="alert-ol">
            <li><?php echo $LANG['UI_JOB_TASK_ORCHESTRATION_TIPS_ONE'] ?></li>
            <li><?php echo $LANG['UI_JOB_TASK_ORCHESTRATION_TIPS_SEVEN'] ?></li>
        </ol>
    </div>
</div>

<script type="text/javascript" src="./scripts/platform/orchestration/orchestration_manage.js"></script>