<?php include_once '../../../tpl/permission.php';?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/swiper/css/swiper.min.css"/>
<!-- END PAGE LEVEL STYLES -->
<style type="text/css">
    .drawer-body .details_more_box{
        margin-bottom: 20px;

    }
    .drawer-body .details_more_head{
        font-weight: 400;
        font-size: 14px;
        color: #333333;
        line-height: 14px;
        font-style: normal;
        text-transform: none;
        margin-left: 10px;
    }
    .drawer-body .details_more_content_body{
        padding: 20px 0;
    }
    .drawer-body .sub-title{
        padding: 0;
    }

    .drawer-body .details_more_hr{
        border-bottom: dashed 1px #E6E6E6;
        margin: 0 0 20px 0;
    }
    .drawer-body .static-info .name{
        font-size: 12px;
    }
    .drawer-body .static-info .value{
        font-size: 12px;
    }

    #vm-table li span{
        color: #999999;
    }

    .dropdown-menu li a i{
        margin-right: 4px;
    }
</style>
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="task" href=" ./content/platform/jobs/jobs.php">
            <span><?php echo $LANG['UI_PLATFORM_CURRENT_JOB']?></span>
        </a>
    </li>
    <span>></span>
    <span class="curent"><?php echo $LANG['UI_PLATFORM_JOB_DETAILS']?></span>
</h3>
<div class="row job-detail" id="jobDetail">
    <div class="col-md-12 job-detail__halftop">
        <div class="col-md-8 job-detail__halftop__charts">
            <input id="taskuuid" value="<?php echo $_GET['uuid'];?>" class="display-none"></input>
            <input id="pageUri" value="<?php echo $_SERVER['REQUEST_URI'];?>" class="display-none"></input>
            <!-- BEGIN DYNAMIC CHART PORTLET-->
            <div class="portlet portlet box blue-hoki portlet-instant-detail">
                <div class="portlet-charts__body">
                    <div class="portlet-charts__body__speedchart" style="position: relative;">
                        <div class="row" style="position: relative; top:50%;transform:translateY(-50%)">
                            <div class="col-md-12">
                                <div class="portlet-body width700 margin0auto">
                                    <div class="row detaildiv" style="display:flex;align-items:baseline;">
                                        <div class="col-md-3" style="position:relative;">
                                            <div style="text-align:center">
                                                <img src="./img/platform/recovery/backup_server.svg">
                                            </div>
                                            <div class="textonline" style="position:relative;left:50%;transform:translateX(-50%);text-align:center;width:130%;margin-top: 15px;
                                            background:#F8F8F8;line-height:20px;height:20px;font-size: 12px;">
                                                <span style="color:#B8C3CD;"><?php echo $LANG['UI_OS_BACKUP_SERVER']?>:</span>
                                                <span style="color:#7E8299" id="serverip"></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6" style="position: relative;z-index: 1;">
                                            <div id="speed" style="left: calc(50% - 6px);top: -16px;position: absolute;color: #88A1B9;font-size: 12px;"></div>
                                            <div id="tasknormal" style="position:relative;left:27.5%;top:50%;transform:translate(-47.5%,0%)">
                                                <img src="./img/platform/recovery/data_transmission.gif" style="width: 140%;" >
                                            </div>
                                        </div>


                                        <div class="col-md-3 right_div_item" style="position:relative;">
                                            <div style="text-align:center">
                                                <!-- <img id="instanthostimg" src="./img/vm/instant/host.png"> -->
                                                <img id="agent_online" src="./img/platform/recovery/agent_online.svg" style="display:none" >
                                                <img id="agent_offline" src="./img/platform/recovery/agent_offline.svg" style="display:none">
                                            </div>
                                            <div class="textonline show-motion-div" style="position:relative;left:50%;transform:translateX(-50%);text-align:center;margin-top: 15px;
                                            background:#F8F8F8;line-height:20px;height:20px;font-size: 12px;cursor: pointer;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">
                                               <span style="color:#B8C3CD;">
                                                   <span id="host_name_des" ><?php echo $LANG['UI_JOB_HOST']?></span>:
                                               </span>
                                                <span style="color:#7E8299" id="hostip"></span>
                                            </div>
                                            <div id="motion-div-block" class="display-none" style="width: 278px;background: #FFFFFF;box-shadow: 0px 4px 12px 0px rgba(0,0,0,0.12);
                                            border-radius: 4px 4px 4px 4px;position: absolute;right: -50px;top: 160px;padding: 8px;z-index: 9999;">
                                                <div style="font-size: 12px;line-height: 20px;">
                                                    <span style="color:#B8C3CD" id="host_name_des2"><?php echo $LANG['UI_OS_INSTANT_RECOVERY_HOST_NAME']?>:</span>
                                                    <span style="color:#7E8299 ;word-break: break-all;" id="host_name2"></span>
                                                </div>
                                                <div style="font-size: 12px;line-height: 20px;">
                                                    <span style="color:#B8C3CD"><?php echo $LANG['UI_OS_INSTANT_RECOVERY_HOST_NAME']?>:</span>
                                                    <span style="color:#7E8299;word-break: break-all;" id="recoverhostname"></span>
                                                </div>
                                                <div style="font-size: 12px;line-height: 20px;">
                                                    <span style="color:#B8C3CD"><?php echo $LANG['UI_OS_MIGRATION_HOST_NAME']?>:</span>
                                                    <span style="color:#7E8299;word-break: break-all;" id="motionhostname"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="progressDiv display-none">
                        <div class="progressDiv__label">
							<span>
								<?php echo $LANG['UI_JOB_TOTAL_PROGRESS'] ?>
							</span>
                        </div>
                        <div class="progress progress-striped active">
                            <div id="total-progress" class="progress-bar progress-bar-success" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                            </div>
                        </div>
                        <div class="taskprogress" id="progressright"></div>
                    </div>
                </div>
            </div>
            <!-- END DYNAMIC CHART PORTLET-->
        </div>

        <div class="col-md-4 job-detail__halftop__navtabs">
            <div class="portlet">
                <div class="portlet-body">
                    <div class="portlet-title init_tab_title">
                        <i class="viconfont vicon-gaiyao1"></i> <?php echo $LANG['UI_JOB_SUMMARY'] ?>
                    </div>
                    <div class="tab-content" style="padding:16px;overflow-y: scroll;">
                        <div class="portlet-body">
                            <div class="row static-info taskOperateDiv <?php if (!in_array("p_current_job_manager", $_SESSION['permissionArr'])) {echo 'display-none';}?>">
                                <div class="col-md-4 name">
                                    <?php echo $LANG['UI_EMERGENCY_PLAN_OPERATE']?>:
                                </div>
                                <div class="col-md-8 value">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-primary text-white btn-dropdown-operate dropdown-toggle hover-initialized" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true">
                                             <?php echo $LANG['UI_PUBLIC_OPERATION']?> <i class="fa fa-angle-down"></i>
                                        </button>
                                        <ul class="dropdown-menu min-width100" role="menu" id="" >
                                            <li id="stopJob"><a href="javascript:;" ><i class="viconfont vicon-ge_suspend-copy"></i><?php echo $LANG['UI_PLATFORM_RECOVERY_MOTION_STOP'];?></a></li>
                                            <li id="stopInstantJob"><a href="javascript:;" ><i class="viconfont vicon-qianyichenggong"></i><?php echo $LANG['UI_PLATFORM_RECOVERY_JOB_STOP_INSTANT_OPERATION'];?></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="row static-info">
                                <div class="col-md-4 name">
                                    <?php echo $LANG['UI_JOB_RNAME']?>:
                                </div>
                                <div class="col-md-8 value" id="taskName" style="word-break:break-word; max-width:300px;">
                                </div>
                            </div>
                            <div class="row static-info">
                                <div class="col-md-4 name">
                                    <?php echo $LANG['UI_PUBLIC_TASK_TYPE']?>:
                                </div>
                                <div class="col-md-8 value" id="taskType">
                                </div>
                            </div>
                            <div class="row static-info">
                                <div class="col-md-4 name">
                                    <?php echo $LANG['UI_PLATFORM_RECOVERY_MOTION_DIRECTION'];?>:
                                </div>
                                <div class="col-md-8 value" id="taskDirection">
                                </div>
                            </div>
                            <div class="row static-info">
                                <div class="col-md-4 name">
                                    <?php echo $LANG['UI_VOL_CDP_TIME_TYPE'];?>:
                                </div>
                                <div class="col-md-8 value" id="taskSourcePoint">
                                </div>
                            </div>
                            <div class="row static-info">
                                <div class="col-md-4 name">
                                    <?php echo $LANG['UI_PLATFORM_RECOVERY_MOTION_PHASE'];?>:
                                </div>
                                <div class="col-md-8 value" id="stage">
                                    --
                                </div>
                            </div>
                            <div class="row static-info">
                                <div class="col-md-4 name">
                                    <?php echo $LANG['UI_JOB_TYPE']?>:
                                </div>
                                <div class="col-md-8 value" id="status">
                                </div>
                            </div>
                            <div class="row static-info">
                                <div class="col-md-4 name">
                                    <?php echo $LANG['UI_PUBLIC_CAPACITY_TASK']?>:
                                </div>
                                <div class="col-md-8 value">
                                    <span id="currentSize"></span>/<span id="totalSize"></span>
                                </div>
                            </div>
                            <div class="row static-info">
                                <div class="col-md-4 name">
                                    <?php echo $LANG['UI_JOB_CURRENT_PROGRESS']?>:
                                </div>
                                <div class="col-md-8 value" id="progress">
                                </div>
                            </div>
                            <div class="row static-info">
                                <div class="col-md-4 name">
                                    <?php echo $LANG['UI_JOB_START_TIME']?>:
                                </div>
                                <div class="col-md-8 value" id="startTime">
                                </div>
                            </div>
                            <div class="row static-info">
                                <div class="col-md-4 name">
                                    <?php echo $LANG['UI_JOB_INTERVAL_TIME']?>:
                                </div>
                                <div class="col-md-8 value" id="intervalTime">
                                </div>
                            </div>

                            <!-- 更多详请 -->
                            <div class="row static-info show-more-div">
                                <div class="col-md-4 name">
                                    <?php echo $LANG['UI_PUBLIC_MORE_CONFIGURATION'];?>:
                                </div>
                                <div class="col-md-8 value">
                                    <a id="details_more" class="green-haze" data-toggle="drawer" data-target="#drawer-1" style="color:#0fbf98;">
                                        <?php echo $LANG['UI_COPY_JOB_DETAIL_LABEL_SETTING_DETAILS'];?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12 job-detail__halfbottom">
        <div class="job-detail__halfbottom__portlet">
            <div class="portlet-title">
                <ul class="nav nav-tabs">
                    <li class="active ">
                        <a href="#log" data-toggle="tab">
                            <i class="viconfont vicon-tasklog"></i> <?php echo $LANG['UI_JOB_RUNING_LOG']?> </a>
                    </li>
                    <li class="vm_tab">
                        <a href="#vms" data-toggle="tab">
                            <i class="viconfont vicon-pt_report_vm_report "></i> <?php echo $LANG['UI_PLATFORM_RECOVERY_JOB_OBJECT'];?></a>
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
                            <table class="table table-striped table-bordered table-hover" id="vm-table"></table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END PAGE CONTENT-->

<!-- drawer开始 -->
<div class="drawer slide aws-drawer-width_en" data-placement="right" tabindex="-1" role="dialog" style="width: 600px" aria-labelledby="drawer-1-title" aria-hidden="true" id="drawer-1">
    <div class="drawer-content drawer-content-scrollable strategy-group" role="document">
        <div class="drawer-header">
            <h4 class="drawer-title" id="drawer-1-title">
                <i class="viconfont vicon-tenant-detail mr8"></i><?php echo $LANG['UI_COPY_JOB_DETAIL_LABEL_SETTING_DETAILS'];?>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close"><i class="viconfont vicon-guanbi"></i></span>
            </h4>
        </div>
        <div class="drawer-body strategy-group__form config-detail-wrap">
            <div class="details_more_box" style="margin-top: 20px;">
                <div class="details_more_head">
                    <div class="row">
                        <i class="viconfont vicon-celve1 mr8"></i><?php echo $LANG['UI_BACKUP_COMMON_STRATEGY'];?>
                    </div>
                </div>
                <div class="details_more_content">
                    <div class="details_more_content_body">
                        <div class="row static-info">
                            <div class="advanced-conf-title-icon floatl mt2"></div>
                            <div class="floatl ">
                                <span class="ms-12"><?php echo $LANG['WEB_COMMON_TIME_STRATEGY'];?></span>
                            </div>
                        </div>
                        <div class="row static-info strategy-group__form__item">
                            <div class="col-md-4 strategy-group__form__item__label">
                                <?php echo $LANG['UI_GLOBAL_STRATEGY_CREATE_TIME']?>
                            </div>
                            <div class="col-md-8 strategy-group__form__item__value" id="createTime">
                            </div>
                        </div>

                        <div class="row static-info">
                            <div class="advanced-conf-title-icon floatl mt2"></div>
                            <div class="floatl ">
                                <span class="ms-12"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'];?></span>
                            </div>
                        </div>
                        <div class="row static-info strategy-group__form__item">
                            <div class="col-md-4 strategy-group__form__item__label">
                                <?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?>
                            </div>
                            <div class="col-md-8 strategy-group__form__item__value" id="speedlimit" style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 2;overflow: hidden;">
                            </div>
                        </div>
                    </div>
                    <hr class="details_more_hr">
                </div>
            </div>

            <div class="details_more_box">
                <div class="details_more_head">
                    <div class="row">
                        <i class="viconfont vicon-a-chuanshu mr8"></i><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'];?>
                    </div>
                </div>
                <div class="details_more_content">
                    <div class="details_more_content_body">
                       <span class="machine_strategy display-none">
                            <!--整机策略 start -->
                            <div class="transportpolicyview">
                                <!-- 传输加密 -->
                                <div class="row static-info strategy-group__form__item">
                                    <div class="col-md-4 strategy-group__form__item__label">
                                        <?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER'] ?>
                                    </div>
                                    <div class="col-md-8 strategy-group__form__item__value" id="transportEncrypt1">
                                    </div>
                                </div>
                                <!-- 传输加密算法 -->
                                <div class="row static-info transfer-encrypt-method-div1 strategy-group__form__item">
                                    <div class="col-md-4 strategy-group__form__item__label">
                                        <?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?>
                                    </div>
                                    <div class="col-md-8 strategy-group__form__item__value" id="transferEncryptMethod1">
                                    </div>
                                </div>

                                <!-- 传输线程个数 -->
                                <div class="row static-info  transportThreadNumdiv strategy-group__form__item">
                                    <div class="col-md-4 strategy-group__form__item__label">
                                        <?php echo $LANG['UI_VOL_CDP_NUMBERS_THREADS']; ?>
                                    </div>
                                    <div class="col-md-8 strategy-group__form__item__value" id="transportThreadNum1">
                                    </div>
                                </div>

                                <!-- 传输网络 -->
                                <div class="row static-info  transportNetworkdiv strategy-group__form__item" style="display: none;">
                                    <div class="col-md-4 strategy-group__form__item__label">
                                        <?php echo $LANG['UI_NODE_TRANSFER_NETWORK']; ?>
                                    </div>
                                    <div class="col-md-8 strategy-group__form__item__value" id="transportNetwork">
                                    </div>
                                </div>
                            </div>
                           <!--整机策略 end -->
                        </span>

                        <span class="vm_strategy strategy-group display-none">
                            <!--虚拟机策略 start -->
                            <?php include_once '../../platform/component/vm_job_transfer.php'; ?>
                            <!--虚拟机策略 end -->
                        </span>
                    </div>
                    <hr class="details_more_hr">
                </div>
            </div>

            <div class="details_more_box retry_div_block">
                <div class="details_more_head">
                    <div class="row">
                        <i class="viconfont vicon-gaojipeizhi mr8"></i><?php echo $LANG['UI_STRATEGY_RETRY'] ?>
                    </div>
                </div>
                <div class="details_more_content">
                    <div class="details_more_content_body">
                        <!---------- 重试策略 ---------->
                        <div class="retryDiv">
                            <div class="row static-info">
                                <div class="advanced-conf-title-icon floatl mt2"></div>
                                <div class="floatl ">
                                    <span class="ms-12"><?php echo $LANG['UI_STRATEGY_RETRY'] ?></span>
                                </div>
                            </div>
                            <!-- 网络重试次数 -->
                            <div class="row static-info strategy-group__form__item">
                                <div class="col-md-4 strategy-group__form__item__label">
                                    <?php echo $LANG['UI_STRATEGY_NETWORK_RETRY_TIMES'] ?>
                                </div>
                                <div class="col-md-8  strategy-group__form__item__value" id="network_retry_times">
                                </div>
                            </div>
                            <!-- 网络重连时间间隔 -->
                            <div class="row static-info strategy-group__form__item">
                                <div class="col-md-4  strategy-group__form__item__label">
                                    <?php echo $LANG['UI_STRATEGY_NETWORK_RETRY_INTERVAL'] ?>
                                </div>
                                <div class="col-md-8  strategy-group__form__item__value" id="network_retry_interval">
                                </div>
                            </div>
                            <!-- 操作异常自动重试 -->
                            <div class="row static-info strategy-group__form__item">
                                <div class="col-md-4  strategy-group__form__item__label">
                                    <?php echo $LANG['UI_STRATEGY_OP_AUTO_RETRY'] ?>
                                </div>
                                <div class="col-md-8  strategy-group__form__item__value" id="op_retry_flag">
                                </div>
                            </div>
                            <!-- 操作异常重连次数 -->
                            <div class="row static-info op_retry_div strategy-group__form__item">
                                <div class="col-md-4  strategy-group__form__item__label">
                                    <?php echo $LANG['UI_STRATEGY_OP_RETRY_TIMES'] ?>
                                </div>
                                <div class="col-md-8  strategy-group__form__item__value" id="op_retry_times">
                                </div>
                            </div>
                            <!-- 操作异常重连时间间隔 -->
                            <div class="row static-info op_retry_div strategy-group__form__item">
                                <div class="col-md-4  strategy-group__form__item__label">
                                    <?php echo $LANG['UI_STRATEGY_OP_RETRY_INTERVAL'] ?>
                                </div>
                                <div class="col-md-8  strategy-group__form__item__value" id="op_retry_interval">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="details_more_box">
                <div class="details_more_head">
                    <div class="row">
                        <i class="viconfont vicon-gaojipeizhi mr8"></i><?php echo $LANG['UI_BACKUP_HIGH_CONFIG'];?>
                    </div>
                </div>
                <div class="details_more_content">
                    <div class="details_more_content_body">
                        <div>
                            <div class="row static-info strategy-group__form__item">
                                <div class="col-md-4 strategy-group__form__item__label">
                                    <?php echo $LANG['UI_PLATFORM_RECOVERY_MOTION_STOP_COMPLETE'];?>
                                </div>
                                <div class="col-md-8 strategy-group__form__item__value" id="stop_instant_task_flag2">

                                </div>
                            </div>
                            <div class="row static-info strategy-group__form__item">
                                <div class="col-md-4 strategy-group__form__item__label">
                                    <?php echo $LANG['UI_PLATFORM_RECOVERY_MOTION_AUTO_COMPLETE'];?>
                                </div>
                                <div class="col-md-8 strategy-group__form__item__value" id="auto_migrate_flag2">

                                </div>
                            </div>
                            <!-- 忽略节点资源限制 -->
                            <div class="row static-info strategy-group__form__item">
                                <div class="col-md-4 strategy-group__form__item__label">
                                    <?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE'];?>:
                                </div>
                                <div class="col-md-8 strategy-group__form__item__value" id="ignore_resource_limit">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="drawer-footer">
            <button type="button" class="btn green-haze" data-dismiss="drawer"
                    aria-label="Close"><?php echo $LANG['UI_PUBLIC_CONFIRM'] ?></button>
        </div>
    </div>
</div>
<!-- drawer结束 -->

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

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./assets/global/plugins/echarts/V5.6.0/echarts.common.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script src="./assets/global/plugins/flot/jquery.flot.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/swiper/js/swiper.min.js"></script>
<script src="./assets/global/plugins/jsencrypt/jsencrypt.min.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/platform/component/job_running_log.js"></script>
<script type="text/javascript" src="./scripts/platform/recovery/motion_job.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
