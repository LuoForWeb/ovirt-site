<?php
include_once '../../../tpl/permission.php';
global $LANG;
?>
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="backup_manager" href="./content/platform/backup/backupresources.php">
            <span><?php echo $LANG['UI_PLATFORM_BACKUP_RESOURCE'] ?></span>
        </a>
    </li>
    <span>
        >
    </span>
    <li>
        <a class="ajaxify" name="backup_manager" href="./content/platform/node/node.php">
            <?php echo $LANG['UI_PALTFORM_NODE'] ?>
        </a>
    </li>
    <span>></span>
    <span class="curent"><?php echo $LANG['UI_PUBLIC_ADDNEW'] ?></span>
</h3>

<!-- END PAGE HEADER-->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<!-- BEGIN PAGE CONTENT-->
<div class="row row-manager">
    <div class="col-md-12 col-manager">
        <!-- BEGIN VALIDATION STATES-->
        <div class="portlet box blue-hoki" id="addcontent">
            <input type="text" value="<?php echo $_GET['node_uuid'] ?>" id="masterNodeUuid" hidden />
            <div class="portlet-title">
                <div class="caption">
                    <i class="viconfont vicon-ge_add_task"></i><?php echo $LANG['UI_PUBLIC_ADDNEW']?>
                </div>
            </div>
            <div class="portlet-body form">
                <!-- BEGIN FORM-->
                <form action="#" id="addnodeform" class="form-horizontal">
                    <div class="form-body">
                        <div class="form-group masternodeform">
                            <label class="control-label col-md-4"><span class="required">* </span><?php echo $LANG['UI_NODE_SERVER_IPADDR']?>
                            </label>
                            <div class="col-md-4">
                                <div class="input-icon right display-none inputipaddr">
                                    <i class="fa"></i>
                                    <input type="text" maxlength="128" id="nodeAddInput" class="form-control" name="ipaddr"/>
                                    <div>
                                        <span class="help-block "><?php echo $LANG['UI_NODE_REMOTE_INPUT_MASTER_NODE_IP']?>
                                            <a id = "switchToSelectAddNodeIp"><?php echo $LANG['UI_NODE_SWITCH_CHILD_NODE'] ?></a>
                                        </span>
                                    </div>
                                </div>
                                <div class="input-icon right selectipaddr">
                                    <i class="fa"></i>
                                    <select id="nodeAddSelect" class=" form-control" name="ipaddrselect">
                                    </select>
                                    <div>
                                        <span class="help-block "><?php echo $LANG['UI_NODE_REMOTE_CHANGE_MASTER_NODE_IP']?>
                                        <a id = "switchToInputAddNodeIp"><?php echo $LANG['UI_NODE_INPUT_CHILD_NODE'] ?></a>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-4"><span class="required">* </span><?php echo $LANG['UI_NODE_SERVER_PORT']?>
                            </label>
                            <div class="col-md-8">
                                <div id="spinnerNum">
                                    <div class="input-group spinner-group" style="width:150px;">
                                        <input type="text" id="spinnerNumInput" onkeyup="value=value.replace(/[^\d]/g,'')" name="port" class="spinner-input form-control" maxlength="5">
                                        <div class="spinner-buttons input-group-btn spinner-group-btn">
                                            <button type="button" class="btn spinner-up default">
                                                <i class="fa fa-angle-up"></i>
                                            </button>
                                            <button type="button" class="btn spinner-down default">
                                                <i class="fa fa-angle-down"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div><span class="help-block "><?php echo $LANG['UI_NODE_SERVER_PORT_TIPS']?></span></div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-4"><span class="required">* </span><?php echo $LANG['UI_NODE_REMOTE_IPADDR']?>
                            </label>
                            <div class="col-md-4">
                                <div class="input-icon right">
                                    <i class="fa"></i>
                                    <input type="text" maxlength="128" id="remoteAddInput" class="form-control" name="remoteip"/>
                                    <div><span class="help-block "><?php echo $LANG['UI_NODE_REMOTE_IPADDR_TIPS']?></span></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4"><span class="required">* </span><?php echo $LANG['UI_NODE_REMOTE_PORT']?>
                            </label>
                            <div class="col-md-8">
                                <div id="remoteNum">
                                    <div class="input-group spinner-group" style="width:150px;">
                                        <input type="text" id="remoteNumInput" onkeyup="value=value.replace(/[^\d]/g,'')" name="remoteport" class="spinner-input form-control" maxlength="5">
                                        <div class="spinner-buttons input-group-btn spinner-group-btn">
                                            <button type="button" class="btn spinner-up default">
                                                <i class="fa fa-angle-up"></i>
                                            </button>
                                            <button type="button" class="btn spinner-down default">
                                                <i class="fa fa-angle-down"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div><span class="help-block "><?php echo $LANG['UI_NODE_REMOTE_PORT_TIPS']?></span></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4">
                                <span class="required">* </span>
                                <?php echo $LANG['UI_NODE_FUNCTION']?>
                            </label>
                            <div class="col-md-8">
                                <div>
                                    <label class="checkbox-inline pl0 pt0" for="nodeFunctionManagement">
                                        <input type="checkbox" class="icheck" id="nodeFunctionManagement" checked value="1" name="node_function" />
                                        <?php echo $LANG['UI_NODE_FUNCTION_MANAGEMENT'] ?>
                                    </label>
                                </div>
                                <div>
                                    <label class="checkbox-inline pl0 pt0" for="nodeFunctionCalculation">
                                        <input type="checkbox" class="icheck" id="nodeFunctionCalculation" checked value="2" name="node_function" />
                                        <?php echo $LANG['UI_NODE_FUNCTION_CALCULATION'] ?>
                                    </label>
                                </div>
                                <!-- <div class="help-block "><?php echo $LANG['UI_NODE_FUNCTION_HELP_BLOCK']?></div> -->
                            </div>
                        </div>
                    </div>
                    <div class="form-actions pt50">
                        <div class="row">
                            <div class="col-md-offset-4 pl24 col-md-6">
                                <button type="button" id="cancelBut" class="btn default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
                                <button type="button" id="addsubmit" class="btn green-haze btn-confirm"><?php echo $LANG['UI_PUBLIC_YES']?></button>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- END FORM-->
            </div>
        </div>
        <!-- END VALIDATION STATES-->
    </div>
</div>
<!-- END PAGE CONTENT-->
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<?php
if($_SESSION['language'] != "en-us"){
    //如果不是英文,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
        $_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script src="./scripts/libs/base64.js" type="text/javascript"></script>
<script src="./scripts/platform/node/node_add.js" type="text/javascript"></script>
