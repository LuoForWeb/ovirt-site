<?php
include_once '../../../tpl/permission.php';
include_once '../public/bs_table.php';
?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css" />
<link rel="stylesheet" type="text/css" href="./css/fs/filetype-small.css" />
<!-- END PAGE LEVEL STYLES -->
<style>
    #filediv .btn-list{height: 34px;
        width: 100%;
        line-height: 34px;
        color: #666666;
        padding: 0 10px;
    }
    #filediv .btn-list button{
        border: none;
        border-radius: 2px !important;
        /*padding: 0 15px;*/
        color: #666666;
        font-size: 14px;
        background: #EBEBEB;
        font-weight: 400;
    }
    #filediv .btn-list .download{
        color: #fff;
        background: #0FBF98;
    }
    #filediv .btn-list .other-btn{
        text-align: left;
        font-style: normal;
        text-transform: none;
        margin-left: 12px;
        background: none;
    }
    #filediv .btn-list .other-btn:hover{
        background-color: rgba(15, 191, 152, .05) !important;
    }
    #filediv .btn-list .other-btn i{
        margin-right: 3px;
    }
    .control-label {
        margin-top: 5px;
        text-align: right;
    }
    .path-tree {
        width: 100%;
        height: 400px;
        background: #FFFFFF;
        border-radius: 2px 2px 2px 2px;
        border: 1px solid #E6E6E6;
        overflow: scroll;
    }
    #drawer-1 .col-md-12{margin-bottom: 24px;}
    #drawer-client .col-md-12{margin-bottom: 24px;}
    #storagemount_component_body{padding-bottom: 0;}
    .dropdown-menu li a i{
        margin-right: 4px;
    }
    #fileDownload i{
        margin-right: 4px;
    }
    .share-title{
        height: 22px;
        font-family: Microsoft YaHei UI, Microsoft YaHei UI;
        font-weight: 400;
        font-size: 14px;
        color: #666666;
        line-height: 22px;
        text-align: left;
        font-style: normal;
        text-transform: none;
    }
    .share-content{
        line-height: 22px;
        font-family: Microsoft YaHei UI, Microsoft YaHei UI;
        font-weight: 400;
        font-size: 14px;
    }
    #shareSuccess .modal-body i{
        position: relative;
        display: inline;
        cursor: pointer;
        color: #999999;
    }
    #shareSuccess .modal-body i:hover{
        color: #666666;
    }
    .clipboard-popup{
        top: 66px;
        display: none;
        margin-left: -7%
    }
</style>
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="task" href="./content/platform/jobs/jobs.php">
            <span><?php echo $LANG['UI_PLATFORM_CURRENT_JOB']; ?></span>
        </a>
    </li>
    <span>></span>
    <span class="curent"><?php echo $LANG['UI_PLATFORM_JOB_DETAILS'] ?></span>
</h3>
<!-- BEGIN PAGE CONTENT-->
<div class="row job-detail" id="jobDetail">
    <div class="col-md-12 col-detail-grain" style="height: calc(100% - 178px)">
        <input id="task_uuid" value="<?php echo $_GET['uuid']; ?>" class="display-none"></input>
        <div class="col-md-4 col-detail-grain__left">
            <div class="portlet box blue-hoki">

                <div class="portlet-body" style="padding:0">
                    <!--BEGIN TABS-->
                    <div class="tabbable tabbable-custom" style="height: 100%;">
                        <ul class="nav nav-tabs">
                            <li class="active nolb">
                                <a href="#tab_1_1" data-toggle="tab" aria-expanded="true">
                                    <i class="viconfont vicon-gaiyao1"></i> <?php echo $LANG['UI_JOB_SUMMARY'] ?> </a>
                            </li>
                            <li class="" id="strategyli">
                                <a href="#tab_1_2" data-toggle="tab" aria-expanded="false">
                                    <i class="viconfont vicon-celve1"></i> <?php echo $LANG['UI_JOB_STRATEGY'] ?> </a>
                            </li>
                        </ul>
                        <div class="tab-content" style="padding: 15px;border: none;border-top: 1px solid #eee;">
                            <div class="tab-pane active" id="tab_1_1" style="height: 100%;">
                                <div class="portlet-body">
                                    <div class="row static-info <?php if (!in_array("p_current_job_manager", $_SESSION['permissionArr'])) {echo 'display-none';}?>">
                                        <div class="col-md-4 name">
                                            <?php echo $LANG['UI_BACKUP_DATA_EXPORT_OPERATION'] ?> :
                                        </div>
                                        <div class="col-md-8 value textvalue">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-primary text-white btn-dropdown-operate dropdown-toggle hover-initialized" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true">
                                                    <?php echo $LANG['UI_PUBLIC_OPERATION']?> <i class="fa fa-angle-down"></i>
                                                </button>
                                                <ul class="dropdown-menu min-width100" role="menu">
                                                    <li id="startJob"><a href="javascript:;"><i class="viconfont vicon-ge_play"></i><?php echo $LANG['WEB_JOB_START'] ?></a></li>
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
                                            <?php echo $LANG['UI_JOB_TYPE'] ?>:
                                        </div>
                                        <div class="col-md-8 value textvalue" id="status">

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
                                        <div class="col-md-4 name modul_name_show">
                                            <?php echo $LANG['UI_VCENTER_MACHINE_NAME'] ?>:
                                        </div>
                                        <div class="col-md-8 value textvalue col-taskname" id="vmname"></div>
                                    </div>
                                    <div class="row static-info">
                                        <div class="col-md-4 name">
                                            <?php echo $LANG['UI_RECOVERY_BACKUP_POINT'] ?>:
                                        </div>
                                        <div class="col-md-8 value textvalue" id="timepoint"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane" id="tab_1_2" style="height:100%;">
                                <div class="portlet-body">

                                    <div class="row static-info">
                                        <div class="col-md-4 name">
                                            <?php echo $LANG['UI_PLATFORM_RECOVERY_OS_TYPE'] ?>:
                                        </div>
                                        <div class="col-md-8 value textvalue col-taskname">
                                            <label id="osType"></label>
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

            <div class="portlet box blue-hoki">
                <div class="portlet-title" style="padding-left: 15px;">
                    <div class="caption">
                        <i class="levelchild viconfont vicon-tasklog"></i> <?php echo $LANG['UI_JOB_RUNING_LOG'] ?>
                    </div>
                </div>
                <div class="portlet-body" style="height: calc(100% - 48px) !important;">
                    <div class="tab-content row">
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
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8 col-detail-grain__right">
            <!-- BEGIN TAB PORTLET-->
            <div class="portlet box blue-hoki">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="levelchild viconfont vicon-vmrecovera"></i> <?php echo $LANG['UI_GRAIN_FILE_RECOVERY'] ?>
                    </div>
                </div>
                <div class="portlet-body" id="filediv">
                    <div class="btn-list">
                        <button class="btn btn-primary download" id="fileDownload"><i class="viconfont vicon-xiazai"></i><?php echo $LANG['UI_PUBLIC_DOWNLOAD'] ?></button>
                        <div style="float: right;">
                            <button class="other-btn client_transfer <?php if (!in_array("p_current_job_manager", $_SESSION['permissionArr'])) {echo 'display-none';}?>">
                                <i class="viconfont vicon-kehuduanchuanshu"></i><?php echo $LANG['UI_RECOVER_CLIENT_TRANSLATE'] ?>
                            </button>
                            <button class="other-btn share_network <?php if (!in_array("p_current_job_manager", $_SESSION['permissionArr'])) {echo 'display-none';}?>">
                                <i class="viconfont vicon-gongxiang"></i> <?php echo $LANG['UI_RECOVER_INTER_SHARE'] ?>
                            </button>
                            <button class="other-btn share_task">
                                <i class="viconfont vicon-task"></i> <?php echo $LANG['UI_RECOVER_TASK'] ?>
                            </button>
                        </div>
                    </div>
                    <div style="margin: 12px;border: 1px solid #E6E6E6;height: calc(100% - 48px);">
                        <div style="padding: 10px;height: calc(100% - 48px);overflow: scroll">
                            <div>
                                <ul id="recoverFileTree" class="ztree"><ul>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <!-- END TAB PORTLET-->
        </div>

        <!-- END DYNAMIC CHART PORTLET-->

        <div style="padding-top:20px;float:left;width: 100%;">
            <div class="alert alert-block alert-info fade in" id="grain_marktips1">
                <button type="button" class="close" data-dismiss="alert"></button>
                <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS']?></strong></h4>
                <ol class="alert-ol">
                    <li><?php echo $LANG['UI_GRAIN_INSTRUCTIONS_TIPS_1'] . ',' . $LANG['UI_GRAIN_INSTRUCTIONS_TIPS_2']?></li>
                    <li><?php echo $LANG['WEB_GRAIN_RECOVERY_CLIENT_TRANSFER_TIPS']?></li>
                    <li><?php echo $LANG['WEB_GRAIN_RECOVERY_NETWORK_SHARE_TIPS']?></li>
                    <li><?php echo $LANG['WEB_GRAIN_RECOVERY_TRANSFER_SHARE_TIPS']?></li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!-- END PAGE CONTENT-->

<!--添加-->
<div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true" id="drawer-network" style="width: 650px;">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header ">
            <h4 class="drawer-title" id="drawer-1-title" style="vertical-align: top">
                <i class="addNewTask" style="padding-top: 3px"></i>
                <span style="vertical-align: top"><?php echo $LANG['UI_RECOVER_SHARE_SETTING'] ?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i class="viconfont vicon-guanbi"></i></span>
            </h4>

        </div>
        <div class="drawer-body">
            <?php include_once  '../component/storage_mount.php';?>

            <div class="col-md-12 paddding-l-r-0 grainness_user_name" style="padding-left: 10px;">
                <div class="form-group">
                    <label class="control-label col-md-3">
                        <span class="required">* </span>
                        <?php echo $LANG['UI_NAS_CLOUD_USERNAME'] ?>
                    </label>
                    <div class="col-md-8">
                        <div class="input-icon right">
                            <input type="text" maxlength="128" class="form-control" id="grainness_user_name"/>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12 paddding-l-r-0 mt-20 grainness_password" style="padding-left: 10px;">
                <div class="form-group">
                    <label class="control-label col-md-3">
                        <span class="required">* </span>
                        <?php echo $LANG['UI_NAS_CLOUD_PASSWORD'] ?>
                    </label>
                    <div class="col-md-8">
                        <div class="input-icon right">
                            <input type="password" maxlength="128" class="form-control" id="grainness_password"/>
                        </div>
                        <div>
                            <span class="help-block "><?php echo $LANG['UI_RECOVER_CHOOSE_BACKUP_TYPE'] ?> </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="drawer-footer">
            <button type="button" class="btn btn-primary" id="graininess_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
            <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
        </div>
    </div>
</div>

<!--客户端传输-->

<div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true" id="drawer-client" style="width: 600px;">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header ">
            <h4 class="drawer-title" id="drawer-1-title" style="vertical-align: top">
                <i class="addNewTask" style="padding-top: 3px"></i>
                <span style="vertical-align: top"><?php echo $LANG['UI_RECOVER_TRANSLATE_SETTING'] ?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i class="viconfont vicon-guanbi"></i></span>
            </h4>

        </div>
        <div class="drawer-body">
            <div class="col-md-12 paddding-l-r-0">
                <div class="form-group">
                    <label class="control-label col-md-3">
                        <span class="required">* </span>
                        <?php echo $LANG['UI_RECOVER_TRANSLATE_TASK_NAME'] ?>
                    </label>
                    <div class="col-md-8">
                        <div class="input-icon right">
                            <input type="text" maxlength="128" class="form-control" id="tranlateName"/>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 选择客户端 -->
            <div class="col-md-12 paddding-l-r-0">
                <div class="form-group form-select-host">
                    <label class="control-label col-md-3">
                        <span class="required">* </span>
                        <span
                                class="devicelabel"><?php echo $LANG['UI_BACKUP_FILE_CHOOSE_AGENT'] ?></span>
                    </label>
                    <div class="col-md-8">
                        <input type="text" id="searchHost" class="form-control"
                               placeholder="<?php echo $LANG['UI_RECOVERY_FILE_SEARCH_CONDITION_TIPS'] ?>">
                    </div>
                </div>
            </div>
            <!-- 客户端tree -->
            <div class="col-md-12 paddding-l-r-0">
                <div class="form-group form-group-group-client-tree">
                    <label class="control-label col-md-3"></label>
                    <div class="col-md-8">
                        <ul id="host_tree" class="ztree max-h-200px overflow-auto"></ul>
                        <div class="col-md-12 alert alert-block alert-info fade in mt10"
                             id="noagenttips">
                            <button type="button" class="close"
                                    data-dismiss="alert"></button>
                            <p></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12 paddding-l-r-0 form-recover-path display-none" id="selectPath">
                <div class="form-group">
                    <label class="control-label col-md-3">
                        <span class="required">* </span>
                        <?php echo $LANG['UI_RECOVER_SAVE_PATH'] ?>
                    </label>
                    <div class="col-md-8" id="pathtreediv">
                        <ul class="path-tree ztree" id="savePath" >
                        </ul>
                    </div>
                    <div class="col-md-4 alert alert-block alert-warning fade in ml15 display-none"
                         id="nopathtips">
                        <button type="button" class="close"
                                data-dismiss="alert"></button>
                        <p>
                            <?php echo $LANG['UI_RECOVERY_FILE_GET_PATH_FAILURE'] ?>
                        </p>

                    </div>
                </div>
            </div>

            <!-- 保持文件权限 -->
            <div class="col-md-12 paddding-l-r-0">
                <div class="form-group">
                    <label class="control-label col-md-3"><?php echo $LANG['UI_GRAIN_FILE_AUTH']?></label>
                    <div class="col-md-8">
                        <input type="checkbox"
                               id="reserve_file_permission_flag"
                               class="make-switch" data-on-color="primary"
                               data-size="small" data-off-color="info"
                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                        <a class="popovers ml15" data-container="body"
                           data-trigger="hover" data-html="true"
                           data-placement="right"
                           data-content="<?php echo $LANG['UI_GRAIN_FILE_AUTH_TIPS']?>">
                            <i class="viconfont vicon-tishi"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- 对象存储 跳过目录树恢复 -->
            <div class="col-md-12 paddding-l-r-0">
                <div class="form-group">
                    <label
                            class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_FILE_SKIP_DIR_TREE_RECOVERY'] ?></label>
                    <div class="col-md-8">
                        <input type="checkbox"
                               id="job_skip_dir_tree_flag"
                               class="make-switch" data-on-color="primary"
                               data-size="small" data-off-color="info"
                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                        <a class="popovers ml15" data-container="body"
                           data-trigger="hover" data-html="true"
                           data-placement="right"
                           data-content="<?php echo $LANG['UI_OBS_REMOVE_PREFIX_TIP'] ?>">
                            <i class="viconfont vicon-tishi"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-12 paddding-l-r-0">
                <div class="form-group">
                    <label class="control-label col-md-3">
                        <?php echo $LANG['UI_RECOVERY_FILE_PROCESS_SAME_FILE'] ?>
                    </label>
                    <div class="col-md-8">
                        <select id="coverType" class="form-control select-with">
                            <option value="4">
                                <?php echo $LANG['UI_RECOVERY_FILE_PROCESS_SAME_FILE_RENAME'] ?>
                            </option>
                            <option value="3">
                                <?php echo $LANG['WEB_PLATFORM_DES_SKIP'] ?>
                            </option>
                            <option value="1">
                                <?php echo $LANG['UI_RECOVERY_FILE_PROCESS_SAME_FILE_COVER'] ?>
                            </option>

                        </select>
                    </div>
                    <div class="col-md-1 pd0" style="margin-top: 10px;">
                        <a class="popovers" data-container="body"
                           data-trigger="hover" data-html="true"
                           data-placement="left"
                           data-content="<?php echo $LANG['UI_RECOVERY_FILE_PROCESS_SAME_FILE_TIPS2'] ?>"
                           data-original-title="" title="">
                            <i class="viconfont vicon-tishi"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-12 paddding-l-r-0">
                <!-- 校验方式 -->
                <div class="form-group recover-way-form">
                    <label class="control-label col-md-3 check-mode-label"><?php echo $LANG['UI_FILE_COPY_PARITY'] ?>
                    </label>
                    <div class="col-md-8">
                        <select class="form-control" id="check_mode">
                            <option value="1">
                                <?php echo $LANG['UI_FILE_COPY_METADATA'] ?>
                            </option>
                        </select>
                    </div>
                    <div class="col-md-1 pd0" style="margin-top: 10px;">
                        <a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="left" data-content="<?php echo $LANG['UI_FILE_COPY_METADATA_TIPS'] ?>" data-original-title="" title="">
                            <i class="viconfont vicon-tishi"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-12 paddding-l-r-0">
                <!-- 覆盖规则 -->
                <div class="form-group recover-way-form">
                    <label class="control-label col-md-3 sync-rule-label"><?php echo $LANG['UI_FILE_COPY_RULE'] ?>
                    </label>
                    <div class="col-md-8">
                        <select class="form-control" id="sync_rule">
                            <option value="1">
                                <?php echo $LANG['UI_FILE_COPY_RULE1'] ?>
                            </option>
                            <option value="2">
                                <?php echo $LANG['UI_FILE_COPY_RULE2'] ?>
                            </option>
                        </select>
                    </div>
                </div>
            </div>

        </div>
        <div class="drawer-footer">
            <button type="button" class="btn btn-primary" id="agent_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
            <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
        </div>
    </div>
</div>
<!-- END MODAL -->

<div id="shareSuccess" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title" style="font-weight:400;font-size:16px;"><i class="viconfont vicon-tenant-detail" style="margin-right: 4px;"></i><?php echo $LANG['UI_RECOVER_SHARE_SETTING'] ?></h4>
    </div>
    <div class="modal-body">
        <div class="portlet-body" style="padding:20px 0;background: #FAFAFA;border-radius: 4px !important;">
            <div class="col-md-12">
                <div class="col-md-2 share-title">
                    <?php echo $LANG['UI_LOGIN_USERNAME']?>
                </div>
                <div class="input-icon col-md-10 share-content" id="account_name">
                </div>
            </div>
            <div class="col-md-12" style="margin-top: 16px;">
                <div class="col-md-2 share-title">
                    <?php echo $LANG['UI_LOGIN_PASSWORD']?>
                </div>
                <div class="input-icon col-md-10 share-content" id="account_pwd">
                </div>
            </div>
            <div class="col-md-12" style="margin-top: 16px;">
                <div class="col-md-2 share-title">
                    <?php echo $LANG['WEB_GRAIN_RECOVERY_SHARE_NAME']?>
                </div>
                <div class="input-icon col-md-10 share-content" id="share_name">
                </div>
            </div>
            <div class="col-md-12" style="margin-top: 16px;">
                <div class="col-md-2 share-title">
                    <?php echo $LANG['UI_NAS_MANAGE_SHARE_PATH']?>
                </div>
                <div class="input-icon col-md-10 share-content" id="account_path">
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_CLOSE']?></button>
    </div>
    <div class="clipboard-popup">
        <i class="viconfont vicon-Frame-15"></i>
        <span class="popup-text"><?php echo $LANG['UI_MICROSOFT365_COPY_SUCCESS']?></span>
    </div>



</div>

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/additional-methods.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>

<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script src="./assets/global/plugins/jsencrypt/jsencrypt.min.js" type="text/javascript"></script>
<script src="./scripts/platform/component/safe_virus_detection.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/platform/component/job_running_log.js"></script>
<script src="./scripts/platform/recovery/graininess_job.js" type="text/javascript"></script>
<!-- END PAGE LEVEL PLUGINS -->
