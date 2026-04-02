<?php
include_once '../../tpl/permission.php';
include_once '../../content/platform/public/bs_table.php';
global $LANG;
?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link rel="stylesheet" type="text/css" href="./css/virus/virus-page.css" />
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="backup_manager" href="./content/platform/backup/backupresources.php" >
            <span><?php echo $LANG['UI_PLATFORM_BACKUP_RESOURCE'] ?></span>
        </a>
    </li>
    <span>></span>
    <span class="curent"><?php echo $LANG['WEB_PLATFORM_VIRUS_MANAGE'] ?></span>
</h3>
<!-- END PAGE HEADER -->

<!-- BEGIN PAGE CONTENT-->
<div class="virus-body"></div>
<!-- END PAGE CONTENT -->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/virus/virus.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	