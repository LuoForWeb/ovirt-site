<?php include_once '../../../tpl/permission.php'; ?>
<?php
$userAllPermission = $_SESSION['permissionArr'];
?>

<!-- BEGIN PAGE CONTENT--> 
<div class="jobs-wrapper">
    <div class="vin_toolbar" id="vin_current_toolbar">
        <div class="left-toolbar">
            <div class="left-toolbar-item me-10">
                <div class="search input-group">
                    <input id="current_job_seach_ipt" class="currentSearch customSearch" autocomplete="off" type="text" placeholder="<?php echo $LANG['UI_JOB_SEARCH_JOB_NAME'] ?>">
                    <div class="position0" style="width:auto;height:34px">
                        <button class="b-btn clear hide position0" id="current_job_clear_search"><i
                                class="icon-close-small"></i></button>
                    </div>
                    <div class="search-btn positionL0" style="width:auto;height:34px;">
                        <button id="current_job_search_btn" class="b-btn search-btn"><i
                                class="icon-search"></i></button>
                    </div>
                </div>
            </div>
    
            <div class="left-toolbar-item me-10 vinchin-toolbar-item <?php if (!in_array('p_current_job_manager', $userAllPermission)) {
                echo 'display-none';
            } ?>">
                <div id="current_job_task_menu_wrapper" class="position-relative"></div>
            </div>

            <div class="left-toolbar-item me-10 gmp-toolbar-item display-none <?php if (
                !in_array('p_current_job_manager', $userAllPermission)
            ) {
                echo 'display-none';
            } ?>">
                <div id="gmp_backup_job_menu_wrapper" class="position-relative"></div>
            </div>

            <div class="left-toolbar-item me-10 gmp-toolbar-item display-none <?php if (
                !in_array('p_current_job_manager', $userAllPermission)
            ) {
                echo 'display-none';
            } ?>">
                <div id="gmp_recover_job_menu_wrapper" class="position-relative"></div>
            </div>

            <div class="left-toolbar-item me-10">
                <div id="current_job_filter_wrapper" class="position-relative"></div>
            </div>

            <div class="left-toolbar-item me-10">
                <div id="current_job_daterangepicker_wrapper" class="position-relative"></div>
            </div>

           <div class="left-toolbar-item">
                <div id="current_pending_task_wrapper" class="position-relative">
                    <button id="current_job_task_btn" class="btn btn-dropdown-filter">
                        <i class="viconfont vicon-Frame10 me-8"></i><?php echo $LANG['WEB_TASK_PENDING']; ?>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="flex-items-center">
            <div class="position-relative" id="current_job_advanced_search_wrapper"></div>
            
            <div class="rightTool">
                <div class="vin_btnToolbar">
                </div>
            </div>
        </div>
    </div>
    
    <div class="table-container current-job-table-container">
        <table id="current_table"></table>
    </div>
</div>

<!-- BEGIN ADVANCED SEARCH MODAL -->
<div id="advanced_search_modal" class="modal xmodal fade form-horizontal" tabindex="-1" data-backdrop="static" aria-hidden="true">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title"><i class="fa fa-search"></i>
            <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?>
        </h4>
    </div>
    <div class="modal-body">
        <div class="portlet-body">
            <!-- 任务名 -->
            <div class="list-option">
                <div class="row">
                    <label
                        class="control-label col-md-4"><?php echo $LANG['UI_PUBLIC_TASK_RNAME']; ?>：
                    </label>
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="task_name" id="advanced_search_task_name">
                    </div>
                </div>
            </div>
            <!-- 用户名 -->
            <div class="list-option">
                <div class="row">
                    <label
                        class="control-label col-md-4"><?php echo $LANG['UI_LOGIN_USERNAME']; ?>：
                    </label>
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="user_name" id="advanced_search_user_name">
                    </div>
                </div>
            </div>
            <!-- 主机名 -->
            <div class="list-option">
                <div class="row">
                    <label
                        class="control-label col-md-4"><?php echo $LANG['UI_AGENT_HOST_NAME']; ?>：
                    </label>
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="host_name" id="advanced_search_host_name">
                    </div>
                </div>
            </div>
            <!-- 业务类型/模块类型/任务类型 级联 -->
            <div class="list-option">
                <div class="row">
                    <label
                        class="control-label col-md-4"><?php echo $LANG['UI_PUBLIC_TASK_TYPE']; ?>：
                    </label>
                    <div class="col-md-6" id="current_job_advanced_search_cascader">
                        
                    </div>
                </div>
            </div>
            <!-- 虚拟机名 -->
            <div class="list-option advanced-search-vm-name display-none">
                <div class="row">
                    <label
                        class="control-label col-md-4"><?php echo $LANG['UI_BACKUP_REPORT_NAME']; ?>：
                    </label>
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="vm_name" id="advanced_search_vm_name">
                    </div>
                </div>
            </div>
            <!-- 虚拟机类型 -->
            <div class="list-option advanced-search-vm-type display-none">
                <div class="row">
                    <label
                        class="control-label col-md-4"><?php echo $LANG['UI_VM_TYPE']; ?>：
                    </label>
                    <div class="col-md-6">
                        <select class="form-control" id="advanced_search_vm_type"></select>
                    </div>
                </div>
            </div>
            <!-- 数据库类型 -->
            <div class="list-option advanced-search-db-type display-none">
                <div class="row">
                    <label
                        class="control-label col-md-4"><?php echo $LANG['UI_DB_DATABASE_TYPE']; ?>：
                    </label>
                    <div class="col-md-6">
                        <select class="form-control" id="advanced_search_db_type"></select>
                    </div>
                </div>
            </div>
            <!-- 所在节点 -->
            <div class="list-option">
                <div class="row">
                    <label
                        class="control-label col-md-4"><?php echo $LANG['UI_STORAGE_IN_NODE']; ?>：
                    </label>
                    <div class="col-md-6">
                        <select class="form-control" id="advanced_search_current_nodes"></select>
                    </div>
                </div>
            </div>
            <!-- 所在存储 -->
            <div class="list-option">
                <div class="row">
                    <label
                        class="control-label col-md-4"><?php echo $LANG['UI_DATA_OF_STORAGE']; ?>：
                    </label>
                    <div class="col-md-6">
                        <select class="form-control" id="advanced_search_current_storages"></select>
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
            id="current_job_advanced_search_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>
<!-- END ADVANCED SEARCH MODAL -->

<script src="./scripts/platform/jobs/current_job.js"></script>

<!--- 挂起任务---->
<?php
include_once 'pending_job.php';
?>