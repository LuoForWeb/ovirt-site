<?php
include_once '../../../tpl/permission.php';
include_once '../../../content/platform/public/bs_table.php';
?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/jquery-file-upload/css/jquery.fileupload.css" rel="stylesheet"/>
<?php
$tab = intval($_GET['tab']);
$tabActive = array("", "", );
$tabActive[$tab] = "active";
?>
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="task" href="./content/platform/jobs/jobs.php">
            <span><?php echo $LANG['UI_PLATFORM_CURRENT_JOB'] ?></span>
        </a>
    </li>
    <span>></span>
    <li>
        <a id="graininess_job">
            <span><?php echo $LANG['UI_PLATFORM_JOB_DETAILS'] ?></span>
        </a>
    </li>
    <span>></span>
    <span class="curent"><?php echo $LANG['UI_RECOVER_TASK'] ?></span>
</h3>
<div class="row">
    <input id="data_uuid" value="<?php echo $_GET['id']; ?>" class="display-none">
    <div class="col-md-12">
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <ul class="nav nav-tabs floatl">
                    <li class = <?php echo $tabActive[0] ?> id="storage_li">
                        <a href="#storage_list_div" data-toggle="tab">
                            <i class="levelchild viconfont vicon-chuanshu"></i>
                            <?php echo $LANG['UI_RECOVER_TRANSLATE_TASK'] ?>
                        </a>
                    </li>
                    <li class = <?php echo $tabActive[1] ?> id="lun_storage_li">
                        <a href="#lun_storage_list_div" data-toggle="tab">
                            <i class="levelchild viconfont vicon-gongxiang"></i>
                           <?php echo $LANG['UI_RECOVER_SHARE_TASK'] ?>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="tab-content row margin10">
                <div class="tab-pane <?php echo $tabActive[0]; ?>" id="storage_list_div">
                    <?php include_once './transfer-task.php' ?>
                </div>

                <div class="tab-pane <?php echo $tabActive[1]; ?>" id="lun_storage_list_div">
                    <?php include_once './share-task-tab.php' ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/platform/recovery/share-task.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
