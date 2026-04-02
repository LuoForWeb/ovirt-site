<?php include_once '../../../tpl/permission.php'; ?>
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="safety" href="./content/platform/users/safety_manager.php">
            <span><?php echo $LANG['UI_PLATFORM_SAFETY'] ?></span>
        </a>
    </li>
    <span>></span>
    <span class="curent"><?php echo $LANG['UI_PLATFORM_SAFETY_DOMAIN'] ?></span>
</h3>


<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
    <div class="col-md-12">
        <div class="portlet box blue-hoki" id='domainDiv'>
            <div class="portlet-title">
                <div class="caption">
                    <i class="iconfont icon-domain"></i><?php echo $LANG['UI_DOMAIN_SERVER_LIST'] ?>
                </div>

            </div>

            <div class="portlet-body margin10">
                <div class="table-container" >
                    <div class="vin_toolbar" id="vin_domain_toolbar">
                        <div class="leftTool">
                            <div class="customBtn1">
                                <div style="cursor: not-allowed">
                                    <?php
                                    if (in_array("p_safety_domain_delete", $_SESSION['permissionArr'])) {
                                        // 删除
                                        echo '<div style="cursor:not-allowed;"><button class="btn viconfont vicon-a-Deleteshanchu1 b-btn brr2 mr12 exch-forbid-event" id="delete_domain"></button></div>';
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="customBtn2">
                                <?php
                                if (in_array("p_safety_domain_add", $_SESSION['permissionArr'])) {
                                    // 新建
                                    echo '<button type="button" id="newBut" class="btn table-toolbar-btn">
                                        <i class="viconfont vicon-biaogetianjia mr4"></i> <span>' . $LANG['UI_PUBLIC_ADDNEW'] . '</span></button>';
                                }
                                if (in_array("p_safety_domain_edit", $_SESSION['permissionArr'])) {
                                    // 修改
                                    echo '<button type="button" id="editBut" class="btn table-toolbar-btn">
                                        <i class="viconfont vicon-xiugai mr4"></i> <span>' . $LANG['UI_PUBLIC_MODIFY'] . '</span></button>';
                                }
                                ?>
                            </div>
                        </div>

                        <div class="rightTool">
                            <div class="vin_btnToolbar">
                            </div>
                        </div>
                    </div>
                    <table id="domain_table">
                    </table>
                </div>
                <div class="alert alert-block alert-info fade in mt-10" id="marktips">
                    <button type="button" class="close" data-dismiss="alert"></button>
                    <ul class="alert-ul">
                        <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                        <li>
                            <?php echo $LANG['UI_DOMAIN_SERVER_TIPS'] ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/platform/users/domain_server.js"></script>
<!-- END PAGE LEVEL PLUGINS -->