<?php include_once '../../../tpl/permission.php'; ?>

<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<?php session_start();
session_commit(); ?>
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="virtual_lab_manager" href="./content/platform/dataverification/virtual_lab_manager.php">
            <?php echo $LANG['UI_VIRTUAL_LAB_MANAGER_LIST'];?>
        </a>
    </li>
    <span>></span>
    <span class="curent"><?php if(empty($_GET['uuid'])) {echo $LANG['UI_PUBLIC_ADDNEW'];} else{ echo $LANG['UI_PUBLIC_MODIFY'];}?></span>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row row-manager" >
    <div class="col-md-12 col-manager" >
        <input id="lab_uuid" value="<?php echo $_GET['uuid'];?>" class="display-none"></input>
        <div class="portlet box blue-hoki" id="labContent">
            <div class="portlet-title">
                <div class="caption">
                    <i class="levelchild viconfont vicon-virtual_lab_manager"></i><?php if(empty($_GET['uuid'])) {echo $LANG['UI_VIRTUAL_LAB_ADD'];} else{ echo $LANG['UI_VIRTUAL_LAB_EDIT'];}?>
                </div>
            </div>
            <div class="portlet-body form">
                <form action="#" class="form-horizontal" id="submit_form" method="POST">
                    <div class="form-wizard">
                        <div class="form-body">
                            <ul class="nav nav-pills steps">
                                <li>
                                    <a href="#tab1" data-toggle="tab" class="step">
                                        <span class="number">1 </span>
                                        <span class="desc">
											<?php echo $LANG['UI_VIRTUAL_LAB_INFO'] ?>
											<i class="fa fa-check"></i>
										</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#tab2" data-toggle="tab" class="step">
                                        <span class="number">2 </span>
                                        <span class="desc">
											<?php echo $LANG['UI_JOB_TARGET_HOST'] ?>
											<i class="fa fa-check"></i>
										</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#tab3" data-toggle="tab" class="step active">
                                        <span class="number">3 </span>
                                        <span class="desc">
											<?php echo $LANG['UI_DRILLS_ISOLATED_NETWORK'] ?>
											<i class="fa fa-check"></i>
										</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#tab4" data-toggle="tab" class="step">
                                        <span class="number">4 </span>
                                        <span class="desc">
											<?php echo $LANG['UI_BACKUP_CONFIRM_CONFIG'] ?>
											<i class="fa fa-check"></i>
										</span>
                                    </a>
                                </li>
                            </ul>
                            <!--<div id="bar" class="progress progress-striped" role="progressbar">
                                <div class="progress-bar progress-bar-success">
                                </div>
                            </div>-->
                            <div class="tab-content bakuptab overflowy-auto" style="height: calc(100% - 45px);">
                                <div class="tab-pane active" id="tab1">
                                    <div class="row">
                                        <div class="form-group">
                                            <label class="control-label col-md-4 hosttypelable"><?php echo $LANG['UI_VIRTUAL_LAB_SELECT_BACKUP_HOST_TYPE']?></label>
                                            <div class="col-md-4">
                                                <select class="form-control select2me " name="data_source_type" id="data_source_type">
                                                    <?php
                                                    if (!empty($_SESSION['authfun'])) {
                                                        if ($_SESSION['authfun']['dataVerificationDR']) {
                                                            echo '<option value="2">'.$LANG['UI_VIRTUAL_LAB_EMBEDDED_VM'].'</option>';
                                                        }
                                                        if ( $_SESSION['authfun']['dataVerificationVM'] && $CONF['SYSTEM_INFO']['vendor'] != "sangfor") {
                                                            echo '<option value="1">' . $LANG['UI_VIRTUAL_LAB_THIRD_PARTY_VIRTUALIZATION'] . '</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-4 lablabel">
                                                <span class="required" aria-required="true">* </span>
                                                <?php echo $LANG['UI_VIRTUAL_LAB_NAME'] ?>
                                            </label>
                                            <div class="col-md-4">
                                                <div class="input-icon right">
                                                    <i class="fa"></i>
                                                    <input type="text" maxlength="24" class="form-control" name="labname" id="labname">
                                                    <div><span class="help-block ">
															<?php echo $LANG['UI_VIRTUAL_LAB_NAME_TIPS'] ?>
														</span></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group display-hide">
                                            <label class="control-label col-md-4 proxylabel">
                                                <span class="required" aria-required="true">* </span>
                                                <?php echo $LANG['UI_VERIFY_PROXY'] ?>
                                            </label>
                                            <div class="col-md-4">
                                                <div class="input-icon right">
                                                    <i class="fa"></i>
                                                    <input type="text" maxlength="80" class="form-control" name="proxyname" id="proxyname">
                                                    <div><span class="help-block ">
															<?php echo $LANG['UI_VIRTUAL_LAB_PROXY_TIPS'] ?>
														</span></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group display-hide">
                                            <label class="control-label col-md-4 poollabel"><?php echo $LANG['UI_VIRTUAL_LAB_POOL'] ?>
                                            </label>
                                            <div class="col-md-4">
                                                <p class="pt7" id="resourcepool"></p>
                                            </div>
                                        </div>
                                        <div class="form-group display-hide">
                                            <label class="control-label col-md-4 folderlabel"><?php echo $LANG['UI_VIRTUAL_LAB_FOLDER'] ?>
                                            </label>
                                            <div class="col-md-4">
                                                <p class="pt7" id="folder"></p>
                                            </div>
                                        </div>
                                        <div class="form-group display-hide">
                                            <label class="control-label col-md-4 switchlabel"><?php echo $LANG['UI_VIRTUAL_LAB_VM_SWITCH'] ?>
                                            </label>
                                            <div class="col-md-4">
                                                <p class="pt7" id="virtualswitch"></p>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="tab-pane" id="tab2">
                                    <div class="row tab-pane__row">
                                        <div class="col-md-12">

                                            <div class="form-group host_tree_div data-resource1 display-hide" id="selectHost">
                                                <label class="control-label col-md-4">
                                                    <span class="required">* </span>
                                                    <span id="source_title"><?php echo $LANG['UI_RECOVERY_SELECT_HOST'] ?></span>
                                                </label>
                                                <div class="col-md-4 tree_div2">
                                                    <ul id="host_tree" class="ztree bd1de5"></ul>
                                                    <div><span class="help-block" id="source_desc">
															<?php echo $LANG['UI_VIRTUAL_LAB_SELECT_HOST_TIPS'] ?>
														</span></div>
                                                </div>
                                            </div>
                                            <div class="form-group display-hide" id="nohosttips">
                                                <label class="control-label col-md-4">
                                                    <span class="required">* </span>
                                                    <?php echo $LANG['UI_RECOVERY_SELECT_HOST'] ?>
                                                </label>
                                                <div class="col-md-6 alert alert-block alert-info fade in">
                                                    <button type="button" class="close" data-dismiss="alert"></button>
                                                    <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                                    <ol class="alert-ol">
                                                        <li>
                                                            <?php echo $LANG['UI_RECOVERY_NO_HOST'] ?>
                                                        </li>
                                                        <li>
                                                            <?php
                                                            //if(empty($_SESSION['tenantuuid'])){
                                                            echo '<p><a id="toaddvcenter"><small>' . $LANG['UI_RECOVERY_ADD_VCENTER_TIPS'] . '</small></a></p>';
                                                            //}
                                                            ?>
                                                        </li>
                                                    </ol>
                                                </div>
                                            </div>

                                            <div class="form-group data-resource2 ">
                                                <label class="control-label col-md-4 nodelabel">
                                                    <span class="required">* </span><?php echo $LANG['UI_VERIFY_SELECT_NODE']?></label>
                                                <div class="col-md-6">
                                                    <select class="form-control select2me width364" name="data_vm_type" id="nodeSelect">
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 proxySettingsDiv ">
                                            <div class="form-group data-resource1 display-hide">
                                                <label class="control-label col-md-4"><?php echo $LANG['UI_VIRTUAL_LAB_SET_STORAGE_AND_NETWORK'] ?>:
                                                </label>
                                            </div>
                                            <div class="form-group proxyDiv display-hide">
                                                <label class="control-label col-md-4"><?php echo $LANG['UI_VERIFY_PROXY'] ?>
                                                </label>
                                                <div class="col-md-4">
                                                    <p class="pt7" id="proxydes"></p>
                                                </div>
                                            </div>
                                            <div class="form-group data-resource1 display-hide">
                                                <label class="control-label col-md-4 storagelabel">
                                                    <span class="required" aria-required="true">* </span>
                                                    <?php echo $LANG['UI_NODE_POOL_NODE_LIST'] ?>
                                                </label>
                                                <div class="col-md-6">
                                                    <select class="form-control select2me width364 inline-block" name="hoststorage" id="hoststorage">
                                                    </select>
                                                    <a class="popovers ml12" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VIRTUAL_LAB_SET_STORAGE_TIPS'] ?>">
                                                        <i class="viconfont vicon-tishi"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="form-group data-resource1 display-hide">
                                                <label class="control-label col-md-4 proxynetworklabel">
                                                    <span class="required" aria-required="true">* </span>
                                                    <?php echo $LANG['UI_VIRTUAL_LAB_PRODUCT_NETWORK'] ?>
                                                </label>
                                                <div class="col-md-6">
                                                    <select class="form-control select2me width364 inline-block" name="hostnetwork" id="hostnetwork">
                                                    </select>
                                                    <a class="popovers ml12" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VIRTUAL_LAB_SET_NETWOK_TIPS'] ?>">
                                                        <i class="viconfont vicon-tishi"></i>
                                                    </a>
                                                </div>
                                            </div>

                                            <div class="form-group data-resource2">
                                                <label class="control-label col-md-4 storagelabel2">
                                                    <span class="required" aria-required="true">* </span>
                                                    <?php echo $LANG['UI_VIRTUAL_LAB_TARGET_STORAGE'] ?>
                                                </label>
                                                <div class="col-md-6">
                                                    <select class="form-control select2me width364 inline-block" name="hoststorage2" id="hoststorage2">
                                                    </select>
                                                    <a class="popovers ml12" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VIRTUAL_LAB_SET_STORAGE_TIPS'] ?>">
                                                        <i class="viconfont vicon-tishi"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="form-group display-hide">
                                                <label class="control-label col-md-4 proxynetworklabel2">
                                                    <span class="required" aria-required="true">* </span>
                                                    <?php echo $LANG['UI_VIRTUAL_LAB_PRODUCT_NETWORK'] ?>
                                                </label>
                                                <div class="col-md-6">
                                                    <select class="form-control select2me width364 inline-block" name="hostnetwork2" id="hostnetwork2">
                                                    </select>
                                                    <a class="popovers ml12" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VIRTUAL_LAB_SET_NETWOK_TIPS'] ?>">
                                                        <i class="viconfont vicon-tishi"></i>
                                                    </a>
                                                </div>
                                            </div>

                                            <div class="form-group data-resource1 display-hide">
                                                <label class="control-label col-md-4 iplabel">
                                                    <span class="required" aria-required="true">* </span>
                                                    <?php echo $LANG['UI_PUBLIC_IP_ADDRESS'] ?>
                                                </label>
                                                <div class="col-md-6">
                                                    <div class="input-icon right width364">
                                                        <i class="fa"></i>
                                                        <input type="text" maxlength="128" class="form-control" name="ipaddress" id="ipaddress" placeholder="192.168.1.110">
                                                        <div><span class="help-block ">

															</span></div>
                                                    </div>
                                                    <a class="popovers ml12 display-hide" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VIRTUAL_LAB_INPUT_IP_ADDRESS_TIPS']?>">
                                                        <i class="viconfont vicon-tishi"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="form-group data-resource1 display-hide">
                                                <label class="control-label col-md-4 netmasklabel">
                                                    <span class="required" aria-required="true">* </span>
                                                    <?php echo $LANG['UI_SETTINGS_NETMASK'] ?>
                                                </label>
                                                <div class="col-md-6">
                                                    <div class="input-icon right width364">
                                                        <i class="fa"></i>
                                                        <input type="text" maxlength="128" class="form-control" name="subnetmask" id="subnetmask" placeholder="255.255.255.0">
                                                        <div><span class="help-block ">

															</span></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group data-resource1 display-hide">
                                                <label class="control-label col-md-4 gatewaylabel">
                                                    <span class="required" aria-required="true">* </span>
                                                    <?php echo $LANG['UI_SETTINGS_GATEWAY'] ?>
                                                </label>
                                                <div class="col-md-6">
                                                    <div class="input-icon right width364">
                                                        <i class="fa"></i>
                                                        <input type="text" maxlength="128" class="form-control" name="gateway" id="gateway" placeholder="192.168.1.1">
                                                        <div><span class="help-block ">

															</span></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="tab3">
                                    <div class="row tab-pane__row">
                                        <div class="col-md-12 col-steptwo">


                                            <div class="form-group data-resource1 display-hide">
                                                <label class="control-label col-md-3 diynodelabel"><?php echo $LANG['UI_VIRTUAL_LAB_SELECT_PRODUCT_NETWORK'] ?></label>
                                                <div class="col-md-4">
                                                    <select class="form-control select2me selectpicker show-tick ignore" multiple data-live-search="true" data-actions-box="true" id="productnetwork">
                                                    </select>
                                                    <div><span class="help-block ">
															<?php echo $LANG['UI_VIRTUAL_LAB_SELECT_PRODUCT_NETWORK_TIPS'] ?>
														</span></div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-md-offset-3 col-md-9">
                                                    <div class="btn-group">
                                                        <button type="button" id="addNetwork" class="btn btn-sm green-haze data-resource2 mr15">
                                                            <i class="viconfont vicon-ge_add_task"></i>
                                                            <?php echo $LANG['UI_VIRTUAL_LAB_ADD_PRODUCT_NETWORK'] ?>
                                                        </button>
                                                        <button type="button" id="getIsolate" class="btn btn-sm green-haze data-resource1 display-hide">
                                                            <?php echo $LANG['UI_VIRTUAL_LAB_AUTO_ISOLATED_NETWORK'] ?>
                                                        </button>
                                                    </div>
                                                    <a class="popovers ml12 data-resource1 display-hide" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VIRTUAL_LAB_AUTO_ISOLATED_NETWORK_TIPS'] ?>">
                                                        <i class="viconfont vicon-tishi"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-md-offset-3 col-md-6 accordion" id="isolatedDiv">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-md-offset-3 col-md-6">
                                                    <div class="alert alert-block alert-info fade in" id="marktips">
                                                        <button type="button" class="close" data-dismiss="alert"></button>
                                                        <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong>
                                                        </h4>
                                                        <ol class="alert-ol">
                                                            <li>
                                                                <?php echo $LANG['UI_VIRTUAL_LAB_ADD_ISOLATED_NETWORK_TIPS1']; ?>
                                                            </li>
                                                            <li>
                                                                <?php echo $LANG['UI_VIRTUAL_LAB_ADD_ISOLATED_NETWORK_TIPS2']; ?>
                                                            </li>
                                                        </ol>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="tab4">
                                    <div class="tab-pane__body">
                                        <div class="tab-pane__body__form">
                                            <h4 class="form-section"><?php echo $LANG['UI_VIRTUAL_LAB_SET'] ?></h4>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_VIRTUAL_LAB'] ?>:</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static colorgreen virtuallabshow">
                                                    </p>
                                                </div>
                                            </div>
                                            <h4 class="form-section"></h4>
                                            <div class="form-group mb0 mt10 data-resource2">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_NODE_POOL_NODE_LIST'] ?>:</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static nodeshow">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mt10 data-resource2">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_VIRTUAL_LAB_TARGET_STORAGE'] ?>:</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static storageshow">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mt10 hostshowDiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_VIRTUAL_LAB_HOST_INFO'] ?>:</label>
                                                <div class="col-md-9">
                                                     <p class="form-control-static hostshow">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mt10 data-resource1 display-hide">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_VERIFY_PROXY'] ?>:</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static proxyshow">
                                                    </p>
                                                </div>
                                            </div>
                                            <h4 class="form-section"></h4>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_DRILLS_ISOLATED_NETWORK'] ?>:</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static isoladinfoshow">
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions form-actions--create">
                            <div class="row">
                                <div class="col-md-offset-6 col-md-6">
                                    <a href="javascript:;" class="btn default button-previous">
                                        <i class="viconfont vicon-shangyibu" style="margin-right: 2px;"></i> <?php echo $LANG['UI_PUBLIC_PREV_STEP'] ?> </a>
                                    <a href="javascript:;" class="btn green-turquoise button-next next-btn-margin-left" style="margin-left: 10px">
                                        <?php echo $LANG['UI_PUBLIC_NEXT_STEP'] ?> <i class="viconfont vicon-xiayibu" style="margin-left: 2px;"></i>
                                    </a>
                                    <a href="javascript:;" class="btn green-haze button-submit" style="margin-left: 10px">
                                        <?php echo $LANG['UI_PUBLIC_SUBMIT'] ?> <i class="viconfont vicon-xiayibu" style="margin-left: 2px;"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- END PAGE CONTENT-->


<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<?php
if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
    //如果是中文简体|中文繁体,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
        $_SESSION['language'] . '.js" type="text/javascript"></script>';

    echo '<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>';
} else if ($_SESSION['language'] != "en-us" && $_SESSION['language'] != "zh-cn" && $_SESSION['language'] != "zh-tw") {
    //如果不是英文|中文简体|中文繁体,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages_' .
        $_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/additional-methods.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>

<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script src="./scripts/platform/dataverification/add_virtual_lab.js" type="text/javascript"></script>