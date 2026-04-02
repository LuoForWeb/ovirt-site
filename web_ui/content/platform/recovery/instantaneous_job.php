<?php include_once '../../../tpl/permission.php';?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="./scripts/plugins/popModal/popModal.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/swiper/css/swiper.min.css"/>
<style>
    #vm-table li span{
        color: #999999;
    }
    .dropdown-menu li a i{
        margin-right: 4px;
    }
</style>
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="task" href=" ./content/platform/jobs/jobs.php">
            <span><?php echo $LANG['UI_PLATFORM_CURRENT_JOB']?></span>
        </a>
    </li>
    <span>></span>
    <span class="curent"><?php echo $LANG['UI_PLATFORM_JOB_DETAILS'] ?></span>
</h3>
<!-- BEGIN PAGE CONTENT-->
<div class="row job-detail" id="instant_job_detail">
    <div class="col-md-12 job-detail__halftop">
        <input id="taskuuid" value="<?php echo $_GET['uuid'];?>" class="display-none"></input>
        <input id="pageUri" value="<?php echo $_SERVER['REQUEST_URI'];?>" class="display-none"></input>
        <input id="instantflag" class="display-none"></input>
        <!-- BEGIN VALIDATION STATES-->
        <div class="col-md-8 showDiv job-detail__halftop__charts">
            <div class="portlet box blue-hoki portlet-instant-detail">
                <div class="portlet-body">
                    <div class="tab-content row ">
                        <div class="tab-pane active " id="info">
                            <div class="portlet-no-bgcolor col-md-12" style="position:relative;top: 50%;transform: translateY(-50%);">
                                <div class="portlet-body width700 margin0auto">
                                    <div class="row" style="display:flex;align-items:baseline;">
                                        <div class="col-md-3" style="position:relative;">
                                            <div style="text-align:center">
                                                <img src="./img/platform/recovery/backup_server.svg">
                                            </div>
                                            <div class="textonline" style="position:relative;left:50%;transform:translateX(-50%);width:180px;text-align:center;margin-top: 15px;
                                            background:#F8F8F8;line-height:20px;height:20px;font-size: 12px;">
                                                <span style="color:#B8C3CD;"><?php echo $LANG['UI_OS_BACKUP_SERVER']?>:</span>
                                                <span style="color:#7E8299" id="serverip"></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6" style="position: relative;z-index: 1;">
                                            <div id="tasknormal" style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%)">
                                                <img src="./img/platform/recovery/data_transmission.gif" style="width:456px;margin-left:-17.5px">
                                            </div>
                                        </div>
                                        <div class="col-md-3" style="position:relative;margin-left: -15px;">
                                            <div style="text-align:center">
                                                <img id="agent_online" src="./img/platform/recovery/agent_line.svg" style="display:none">
                                                <img id="agent_offline" src="./img/platform/recovery/agent_stop.svg">
                                            </div>
                                            <div class="textonline" style="position:relative;left:50%;transform:translateX(-50%);width:180px;text-align:center;margin-top: 15px;
                                            background:#F8F8F8;line-height:20px;height:20px;font-size: 12px;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">
                                                <span style="color:#B8C3CD;">
                                                   <span id="host_name_des" ><?php echo $LANG['UI_JOB_HOST']?></span>:
                                               </span>
                                                <span style="color:#7E8299" id="hostip"></span>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 configDiv job-detail__halftop__navtabs">
            <!-- BEGIN PORTLET-->
            <div class="portlet">
                <div class="portlet-body">
                    <!--BEGIN TABS-->
                    <div class="tabbable tabbable-custom swiper-detail">
                        <ul class="nav nav-tabs">
                            <li class="active nolb">
                                <a href="#tab_1_1" data-toggle="tab" aria-expanded="true">
                                    <i class="viconfont vicon-gaiyao1"></i> <?php echo $LANG['UI_JOB_SUMMARY'] ?> </a>
                            </li>
                            <li class="" id="modeli">
                                <a href="#tab_1_3" data-toggle="tab" aria-expanded="false">
                                    <i class="viconfont vicon-ge_advanced"></i> <?php echo $LANG['UI_JOB_HIGH'] ?> </a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="tab_1_1">
                                <div class="portlet-body">
                                    <div class="row static-info <?php if (!in_array("p_current_job_manager", $_SESSION['permissionArr'])) {echo 'display-none';}?>">
                                        <div class="col-md-4 name">
                                            <?php echo $LANG['UI_EMERGENCY_PLAN_OPERATE'] ?>:
                                        </div>
                                        <div class="col-md-8 col-operate value">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-primary text-white btn-dropdown-operate dropdown-toggle hover-initialized" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true">
                                                     <?php echo $LANG['UI_PUBLIC_OPERATION']?> <i class="fa fa-angle-down"></i>
                                                </button>
                                                <ul class="dropdown-menu min-width100" role="menu">
                                                    <li id="startJob"><a href="javascript:;"><i class="viconfont vicon-ge_play"></i><?php echo $LANG['WEB_JOB_START'] ?></a></li>
                                                    <li id="motionJob"><a href="javascript:;"><i class="viconfont vicon-ge_migration"></i><?php echo $LANG['WEB_PLATFORM_DES_MOTION'] ?></a></li>
                                                    <!--<li id="pointJob"><a href="javascript:;"><i class="viconfont vicon-a-Cameraxiangji"></i><?php /*echo $LANG['UI_PLATFORM_RECOVERY_MAKE_POINT'] */?></a></li>-->
                                                    <li id="stopJob"><a href="javascript:;"><i class="viconfont vicon-ge_suspend-copy"></i><?php echo $LANG['UI_VERIFY_STOP'] ?></a></li>
                                                    <li id="delJob"><a href="javascript:;"><i class="viconfont vicon-ge_delete"></i><?php echo $LANG['UI_PUBLIC_DELETE'] ?></a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row static-info">
                                        <div class="col-md-4 name">
                                            <?php echo $LANG['UI_JOB_RNAME'] ?>:
                                        </div>
                                        <div class="col-md-8 value" id="taskName" style="word-break:break-word; max-width:300px;">
                                        </div>
                                    </div>
                                    <div class="row static-info">
                                        <div class="col-md-4 name">
                                            <?php echo $LANG['UI_PUBLIC_TASK_TYPE'] ?>:
                                        </div>
                                        <div class="col-md-8 value" id="taskType">
                                        </div>
                                    </div>
                                    <div class="row static-info">
                                        <div class="col-md-4 name">
                                            <?php echo $LANG['UI_JOB_TASK_STAGE'] ?>:
                                        </div>
                                        <div class="col-md-8 value" id="taskStage">
                                        </div>
                                    </div>
                                    <div class="row static-info">
                                        <div class="col-md-4 name">
                                            <?php echo $LANG['UI_PLATFORM_RECOVERY_JOB_TARGET_TYPE'];?>:
                                        </div>
                                        <div class="col-md-8 value" id="goalType">
                                        </div>
                                    </div>
                                    <div class="row static-info">
                                        <div class="col-md-4 name">
                                            <?php echo $LANG['UI_BACKUP_DATA_EXPORT_HYPERVISOR'];?>:
                                        </div>
                                        <div class="col-md-8 value" id="hypervisorType">
                                        </div>
                                    </div>
                                    <div class="row static-info">
                                        <div class="col-md-4 name">
                                            <?php echo $LANG['UI_JOB_TYPE'] ?>:
                                        </div>
                                        <div class="col-md-8 value" id="status">
                                        </div>
                                    </div>
                                   <!-- <div class="row static-info">
                                        <div class="col-md-4 name">
                                            <?php /*echo $LANG['UI_JOB_START_TIME'] */?>:
                                        </div>
                                        <div class="col-md-8 value" id="startTime">
                                        </div>
                                    </div>
                                    <div class="row static-info">
                                        <div class="col-md-4 name">
                                            <?php /*echo $LANG['UI_JOB_INTERVAL_TIME'] */?>:
                                        </div>
                                        <div class="col-md-8 value" id="intervalTime">
                                        </div>
                                    </div>-->

                                </div>
                            </div>

                            <div class="tab-pane" id="tab_1_3">
                                <div class="portlet-body">
                                    <div class="row static-info">
                                        <div class="col-md-4 name">
                                            <?php echo $LANG['UI_JOB_CREATE_TIME'] ?>:
                                        </div>
                                        <div class="col-md-8 value" id="createTime">
                                        </div>
                                    </div>

                                    <div class="row static-info amoutInfo">
                                        <div class="col-md-4 name">
                                            <?php echo $LANG['UI_VERIFY_STORAGE_MOUNT'] ?>:
                                        </div>
                                        <div class="col-md-8 value" id="serverIP">
                                        </div>
                                    </div>
                                    <div class="row static-info amoutInfo">
                                        <div class="col-md-4 name">
                                            <?php echo $LANG['UI_PLATFORM_MOUNT_PROTOCOL'];?>:
                                        </div>
                                        <div class="col-md-8 value" id="serverType">
                                        </div>
                                    </div>
                                    <div class="row static-info virusConfig">
                                        <div class="col-md-4 name">
                                            <?php echo $LANG['UI_SAFE_STRATEGY_VIRUS_CHECK'];?>:
                                        </div>
                                        <div class="col-md-8 value" id="virusConfig">
                                        </div>
                                    </div>
                                    <div class="row static-info completeConfig">
                                        <div class="col-md-4 name">
                                            <?php echo $LANG['UI_RECOVERY_STRATEGY_COMPLETE'];?>:
                                        </div>
                                        <div class="col-md-8 value" id="completeConfig">
                                        </div>
                                    </div>
                                    <!-- 忽略节点资源限制 -->
                                    <div class="row static-info ignore_resource_limit">
                                        <div class="col-md-4 name">
                                            <?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE'];?>:
                                        </div>
                                        <div class="col-md-8 value" id="ignore_resource_limit">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--END TABS-->
                </div>
            </div>
            <!-- END PORTLET-->
        </div>
    </div>

    <div class="col-md-12 job-detail__halfbottom">
        <!-- BEGIN TAB PORTLET-->
        <div class="job-detail__halfbottom__portlet">
            <div class="portlet-title">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#log" data-toggle="tab">
                            <i class="viconfont vicon-tasklog"></i> <?php echo $LANG['UI_JOB_RUNING_LOG'] ?> </a>
                    </li>
                    <li class="vm_tab" style="display: none;">
                        <a href="#vms" data-toggle="tab">
                            <i class="viconfont vicon-pt_report_vm_report "></i> <?php echo $LANG['UI_PLATFORM_RECOVERY_JOB_OBJECT'];?> </a>
                    </li>
                </ul>
            </div>
            <div class="portlet-body">
                <div class="tab-content">
                    <div class="tab-pane active running-log-pane" id="log">
                        <div class="time-range-wrapper">
                            <div id="running_log_daterangepicker_wrapper">
                                <i class="viconfont vicon-shijiankongjian"></i>
                                <span class="running-log-search"><?php echo $LANG['UI_TOOLS_TABLE_START_END_TIME'] ?></span>
                            </div>
                        </div>
                        <div class="running-log-content">
                            <ul id="runninglog"></ul>
                        </div>
                    </div>

                    <div class="tab-pane" id="vms">
                        <div class="table-container">
                            <table id="vm-table"></table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END TAB PORTLET-->

    </div>
</div>
<!-- END PAGE CONTENT-->

<!-- BEGIN SCRIPT CONTENT DRAWER -->
<div class="drawer slide " data-placement="right" tabindex="-1" role="dialog" id="scriptContentDrawer">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header">
            <h4 class="drawer-title">
                <span>
                    <i class="viconfont vicon-jiaobenguanli"></i>
                </span>
                <span style="position: relative; top: -1px" class="name"></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close ">
                    <i class="viconfont vicon-guanbi"></i>
                </span>
            </h4>
        </div>
        <div class="drawer-body">
            <div class="portlet-body" style="height: 100%">
                <div class="form-group" style="height: 20px; margin-bottom: 16px">
                    <label class="col-md-3" for=""><?php echo $LANG['UI_PUBLIC_SCRIPT_TYPE'] ?></label>
                    <div class="col-md-6">
                        <div id="scriptContentType"></div>
                    </div>
                </div>
                <div class="col-md-12 pd0" style="height: calc(100% - 36px)">
                    <pre id="scriptContent" style="height: 100%"></pre>
                </div>
            </div>
        </div>
        <!-- footer -->
        <div class="drawer-footer">
            <button type="button" class="btn default" data-dismiss="drawer" aria-label="Close"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
        </div>
    </div>
</div>
<!-- END SCRIPT CONTENT DRAWER -->

<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script src="./assets/global/plugins/jsencrypt/jsencrypt.min.js" type="text/javascript"></script>
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./scripts/platform/component/safe_virus_detection.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/platform/component/job_running_log.js"></script>
<script type="text/javascript" src="./scripts/platform/recovery/instantaneous_job.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
