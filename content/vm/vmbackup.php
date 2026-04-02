<?php include_once '../../tpl/permission.php'; ?>

<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/simple-line-icons/simple-line-icons.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./scripts/plugins/calendar/jquery.searchableSelect.css"/>
<link rel="stylesheet" type="text/css" href="./css/vm/virtualizationIcon.css"/>
<link rel="stylesheet" type="text/css" href="./css/platform/component/resource_pool.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/component/safe_virus.css"/>
<link rel="stylesheet" type="text/css" href="./css/platform/component/resource_pool.css" />
<?php session_start();
session_commit(); ?>
<!-- BEGIN PAGE HEADER-->
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row" style="height:100%;">
    <div class="col-md-12" style="height:100%;">
        <input id="s_vmuuid" value="<?php echo $_GET['vmuuid']; ?>" class="display-none"></input>
        <input id="s_vcenteruuid" value="<?php echo $_GET['vcenteruuid']; ?>" class="display-none"></input>
        <input id="s_showtype" value="<?php echo $_GET['showtype']; ?>" class="display-none"></input>
        <input id="s_hypervisor" value="<?php echo $_GET['hypervisor']; ?>" class="display-none"></input>
        <input type="hidden" id="subModuleType" value="<?php echo $_GET['sub_module_type'] ?? 1 ?>">
        <span class="display-none" id="servertime"></span>
        <div class="portlet box blue-hoki backup-page" id="vmbackupcontent">
            <div class="portlet-title">
                <div class="caption">
                    <i class="levelchild viconfont vicon-xinjianbeifen1"></i><?php echo $_GET['sub_module_type'] == 2 ? $LANG['UI_BACKUP_NEW_PRIVATE_CLOUD_BACKUP_JOB'] : $LANG['UI_BACKUP_NEW_VM_BACKUP_JOB'] ?>
                </div>
            </div>
            <div class="portlet-body form">
                <div action="#" class="form-horizontal" id="submit_form" method="POST">
                    <div class="form-wizard">
                        <div class="form-body">
                            <div class="form-body-content">
                                <ul class="nav nav-pills steps">
                                    <li>
                                        <a href="#tab1" data-toggle="tab" class="step">
                                            <span class="number">1 </span>
                                            <span class="desc">
                                                <?php echo $LANG['UI_BACKUP_SOURCE'] ?>
                                                <i class="fa fa-check"></i>
                                            </span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#tab2" data-toggle="tab" class="step">
                                            <span class="number">2 </span>
                                            <span class="desc">
                                                <?php echo $LANG['UI_BACKUP_DATA_DES'] ?>
                                                <i class="fa fa-check"></i>
                                            </span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#tab3" data-toggle="tab" class="step active">
                                            <span class="number">3 </span>
                                            <span class="desc">
                                                <?php echo $LANG['UI_JOB_STRATEGY_INFO'] ?>
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
                                <div class="tab-content bakuptab">
                                    <!-- 备份源 -->
                                    <div class="tab-pane active" id="tab1">
                                        <div class="alert alert-danger display-none selectvmtip">
                                        </div>
                                        <div class="row tab-pane__row">
                                            <div class="col-md-4 tab-pane__row__source">
                                                <!-- 请选择备份源 -->
                                                <div class="form-group src-wrap">
                                                    <div class="src-wrap__title">
                                                        <i class="icon-action-redo" style="margin-right: 8px"></i>
                                                        <span><?php echo $LANG['UI_BACKUP_SELECT_RESOURCE'] ?></span>
                                                    </div>
                                                    <div class="src-wrap__content">
                                                        <div class="src-wrap__content__search">
                                                            <select class="bs-select width100p" data-show-subtext="true" id="hypervisortype" style="display: none">
                                                            </select>
                                                            <select class="bs-select width100p mt15" data-show-subtext="true" id="vmshowtype" style="display: none">
                                                                <option data-icon="hostcluster icon-default" value="1"><?php echo $LANG['UI_VCENTER_SHOW_HOST_CLUSTER'] ?></option>
                                                                <option data-icon="vmtmp icon-default" value="2"><?php echo $LANG['UI_VCENTER_SHOW_VM_TEMPLATE'] ?></option>
                                                                <option data-icon="hostvm icon-default" value="3"><?php echo $LANG['UI_VCENTER_SHOW_HOST_VM'] ?></option>
                                                            </select>
                                                            <div class="vm_tree_div display-none">
                                                                <div class="search input-group input-icon width100p">
                                                                    <input id="searchvm" class="currentSearch customSearch"
                                                                           style="width: 100%!important;padding-right:30px" autocomplete="off" type="text"
                                                                           maxlength="128" placeholder="<?php echo $LANG['UI_COPY_SEARCH_AS_KEYWORD']?>"
                                                                           onkeyup="customInputValidate('string', this.value, $(this))">
                                                                    <div class="position0" style="width:auto;height:34px">
                                                                        <button class="b-btn clear hide position0" id="searchvm_clear_search">
                                                                            <i class="icon-close-small"></i>
                                                                        </button>
                                                                    </div>
                                                                    <div class="search-btn positionL0" style="width:auto;height:34px;">
                                                                        <button id="searchvm_btn" class="b-btn search-btn">
                                                                            <i class="icon-search"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="src-wrap__content__ztree">
                                                            <div class="three_tree">
                                                                <ul id="vm_tree" class="ztree bd1de5  tree_div display-hide"></ul>
                                                                <ul id="vm_tree_hc" class="ztree bd1de5  tree_div " ></ul>
                                                                <ul id="vm_tree_vt" class="ztree bd1de5  tree_div display-hide" ></ul>
                                                            </div>
                                                            <div class="alert alert-block alert-info fade in display-hide"  id="nodatatips">
                                                                <button type="button" class="close" data-dismiss="alert"></button>
                                                                <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                                                <ol class = "alert-ol">
                                                                    <li>
                                                                        <?php echo $_GET['sub_module_type'] == 2 ? $LANG['UI_BACKUP_AWS_NO_INSTANCE'] : $LANG['UI_BACKUP_NO_VM_DATA_TITLE'] ?>
                                                                    </li>
                                                                    <li>
                                                                        <?php if ($_SESSION['isThreePowers'] && 5 == $_SESSION['userLevel']) {
                                                                            // 三权模式且是operator提示需分配资源
                                                                            echo $LANG['UI_BACKUP_NO_VM_DATA_TIPS3'];
                                                                        } else { ?>
                                                                                <a id="toaddvcenter"><?php echo $_GET['sub_module_type'] == 2 ? $LANG['UI_BACKUP_NO_PRIVATE_CLOUD_VM_TIPS'] : $LANG['UI_BACKUP_NO_VM_DATA_TIPS1'] ?></a>
                                                                        <?php } ?>
                                                                    </li>
                                                                    <?php if ($_GET['sub_module_type'] == 1) { ?>
                                                                        <li>
                                                                            <?php echo $LANG['UI_BACKUP_NO_VM_DATA_TIPS2'] ?>
                                                                        </li>
                                                                    <?php } ?>
                                                                </ol>
                                                            </div>
                                                            <div class="alert alert-block alert-info fade in display-hide"  id="nosearchtips">
                                                                <button type="button" class="close" data-dismiss="alert"></button>
                                                                <ul class="alert-ul">
                                                                    <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                                                                    <li>
                                                                        <strong><?php echo $LANG['UI_DATA_NOSEARCH_TIPS'] ?></strong>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-8 VMList-div">
                                                <!-- 已选择备份源 -->
                                                <div class="addTitle">
                                                    <span>
                                                        <i class="viconfont vicon-ge_vm" style="margin-right: 8px;"></i>
                                                        <span><?php echo $LANG['UI_BACKUP_ADD_OBJECT'] ?></span>
                                                    </span>

                                                    <!-- 租户隐藏自动添加备份 -->
                                                    <?php if (empty($_SESSION['tenantuuid'])) { ?>
                                                            <div>
                                                            <span class="vm-filter-span">
                                                                <a href="javascript:void(0)" id="vmFilterBtn">
                                                                    <?php echo $_GET['sub_module_type'] == 2 ? $LANG['UI_BACKUP_INSTANCE_FILTER'] : $LANG['UI_BACKUP_VM_FILTER'] ?>
                                                                </a>
                                                            </span>
                                                                <span class="ml10">
                                                                <span><?php echo $LANG['UI_BACKUP_AUTO_JOIN_BACKUP'] ?></span>
                                                                <span>
                                                                    <input type="checkbox" id="autoJoinFlag"   class="make-switch" data-on-color="primary" data-off-color="info" data-size="small"
                                                                           data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                </span>
                                                            </span>
                                                            </div>
                                                    <?php } ?>
                                                </div>

                                                <div class="addIt-list">
                                                    <div class="filter-items" style="display:none;margin-bottom: 12px;">

                                                    </div>


                                                    <ul class="feeds addVMList" id="addHCList">
                                                    </ul>

                                                    <ul class="feeds addVMList" id="addVTList">
                                                    </ul>

                                                    <ul class="feeds addVMList" id="addHVList">
                                                    </ul>

                                                    <!-- 租户隐藏提示 -->
                                                    <?php if (empty($_SESSION['tenantuuid'])) { ?>
                                                            <div class="alert alert-info">
                                                                <button type="button" class="close" data-dismiss="alert"></button>
                                                                <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                                                <?php if ($_GET['sub_module_type'] == 2) { ?>
                                                                        <ol class="alert-ol">
                                                                            <li><?php echo $LANG['UI_BACKUP_PRIVATE_CLOUD_STEP1_TIPS1'] ?></li>
                                                                            <li><?php echo $LANG['UI_BACKUP_PRIVATE_CLOUD_STEP1_TIPS5'] ?></li>
                                                                            <li><?php echo $LANG['UI_BACKUP_PRIVATE_CLOUD_STEP1_TIPS2'] ?></li>
                                                                            <li><?php echo $LANG['UI_BACKUP_PRIVATE_CLOUD_STEP1_TIPS3'] ?></li>
                                                                            <li><?php echo $LANG['UI_BACKUP_PRIVATE_CLOUD_STEP1_TIPS4'] ?></li>
                                                                        </ol>
                                                                <?php } else { ?>
                                                                        <ol class="alert-ol">
                                                                            <li><?php echo $LANG['UI_BACKUP_VM_STEP1_TIPS1'] ?></li>
                                                                            <li><?php echo $LANG['UI_BACKUP_VM_STEP1_TIPS5'] ?></li>
                                                                            <li><?php echo $LANG['UI_BACKUP_VM_STEP1_TIPS2'] ?></li>
                                                                            <li><?php echo $LANG['UI_BACKUP_VM_STEP1_TIPS3'] ?></li>
                                                                            <li><?php echo $LANG['UI_BACKUP_VM_STEP1_TIPS4'] ?></li>
                                                                        </ol>
                                                                <?php } ?>
                                                            </div>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- 备份目的地 -->
                                    <div class="tab-pane" id="tab2">
                                        <div class="row" style="height: 100%; overflow-y: auto; padding-top: 20px">
                                            <div class="col-md-9" id="backupTarget"></div>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="tab3">
                                        <div class="row row-stepthree">
                                            <div class="tabbable-custom col-md-offset-1 col-md-10">
                                                <ul class="nav nav-tabs">
                                                    <li class="commonLi active">
                                                        <a href="#tab_common" data-toggle="tab">
                                                            <i class="viconfont vicon-celve1"></i> <?php echo $LANG['UI_BACKUP_COMMON_STRATEGY'] ?> </a>
                                                    </li>
                                                    <li class="transferLi">
                                                        <a href="#tab_transfer" data-toggle="tab">
                                                            <i class="viconfont vicon-a-chuanshu"></i> <?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?> </a>
                                                    </li>
                                                    <li class="safeLi">
                                                        <a href="#tab_safe" data-toggle="tab">
                                                            <i class="viconfont vicon-anquancelve"></i> <?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?> </a>
                                                    </li>
                                                    <li class="highLi">
                                                        <a href="#tab_other" data-toggle="tab">
                                                            <i class="viconfont vicon-gaojipeizhi"></i> <?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?> </a>
                                                    </li>
                                                </ul>
                                                <div class="tab-content">
                                                    <!-- 通用策略 -->
                                                    <div class="tab-pane active" id="tab_common">
                                                        <div class="panel-body">
                                                            <div class="form-group">
                                                                <div class="control-label col-md-2"><?php echo $LANG['UI_BACKUP_SELECT_STRATEGY'] ?></div>
                                                                <div class="col-md-4">
                                                                    <select class="form-control select2me inline-block" id="strategySelect">
                                                                    </select>
                                                                    <a class="popovers ml15" data-container="body" data-trigger="hover"
                                                                       data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_SELECT_STRATEGY_TIPS'] ?>">
                                                                        <i class="viconfont vicon-tishi"></i>
                                                                    </a>
                                                                </div>
                                                            </div>

                                                            <!-- 通用策略-时间策略 -->
                                                            <div class="form-group">
                                                                <div class="col-md-offset-2 col-md-9 ">
                                                                    <?php include('../platform/strategy/timeStrategy.php'); ?>
                                                                </div>
                                                            </div>
                                                            <!-- 通用策略-限速策略 -->
                                                            <div class="form-group speedlimitDiv">
                                                                <div class="col-md-offset-2 col-md-9 accordion strategyOne" >
                                                                    <div class="panel panel-default strategy-panel">
                                                                        <div class="panel-heading">
                                                                            <h4 class="panel-title">
                                                                                <a class="accordion-toggle accordion-toggle-styled collapsed popovers"
                                                                                   data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#speed" aria-expanded="true">
                                                                                    <i class="viconfont vicon-xiansucelve1 font-green-seagreen"></i>
                                                                                    <span class="font-green-seagreen"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?></span>
                                                                                    <span class="strategyDes speedlimitDes"></span>
                                                                                </a>
                                                                            </h4>
                                                                        </div>
                                                                        <?php include_once('../../content/platform/global_strategy/speedJob.php'); ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group storeDiv">
                                                                <div class="col-md-offset-2 col-md-9 ">
                                                                    <?php include('../platform/strategy/storeStrategy.php') ?>
                                                                </div>
                                                            </div>
                                                            <!-- 通用策略-保留策略 -->
                                                            <div class="form-group" id="reserveShowDiv">
                                                                <div class="col-md-offset-2 col-md-9 ">
                                                                    <?php include('../platform/strategy/reserveStrategy.php') ?>
                                                                </div>
                                                            </div>
                                                            <!-- 新增校验策略模块 -->
                                                            <div class="form-group verifyDiv">
                                                                <div class="col-md-offset-2 col-md-9 accordion strategyOne" >
                                                                    <div class="panel panel-default strategy-panel">
                                                                        <div class="panel-heading">
                                                                            <h4 class="panel-title">
                                                                                <a class="accordion-toggle accordion-toggle-styled collapsed popovers"
                                                                                   data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#backupVerify" aria-expanded="true">
                                                                                    <i class="viconfont vicon-a-Check-onexiaoyan font-green-seagreen"></i>
                                                                                    <span class="font-green-seagreen"><?php echo $LANG['UI_CBR_SYNC_VERIFY_STRATEGY'] ?></span>
                                                                                    <span class="strategyDes backupVerifyDes"></span>
                                                                                </a>
                                                                            </h4>
                                                                        </div>
                                                                        <div id="backupVerify" class="panel-collapse collapse ">
                                                                            <div class="panel-body">
                                                                                <div class="col-md-12">
                                                                                    <div class="form-group">
                                                                                        <label class="control-label col-md-4 verifyLable form-group-label"><?php echo $LANG['UI_CBR_SYNC_INTEGRALITY_CHECK'] ?></label>
                                                                                        <div class="col-md-4 form-group-content">
                                                                                            <input type="checkbox" id="verifyflag"   class="make-switch" data-on-color="primary" data-off-color="info" data-size="small"
                                                                                                   data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                                   data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                            <a class="popovers ml15" data-container="body" data-trigger="hover"
                                                                                               data-placement="right" data-html="true" data-content="<?php echo $LANG['UI_CBR_SYNC_CHECK_METHOD_TIPS'] ?>">
                                                                                                <i class="viconfont vicon-tishi"></i>
                                                                                            </a>
                                                                                        </div>

                                                                                    </div>
                                                                                    <div class="form-group verifytypeDiv display-none">
                                                                                        <label class="control-label col-md-4 verifyTypeLable">
                                                                                            <?php echo $LANG['UI_CBR_SYNC_CHECK_TYPE'] ?>
                                                                                        </label>
                                                                                        <div class="col-md-4">
                                                                                            <select class="form-control select2me input-sm" id="verifytype">
                                                                                                <option value="1">MD5</option>
                                                                                                <option value="2">CRC</option>
                                                                                            </select>
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
                                                    <!-- 传输策略 -->
                                                    <div class="tab-pane" id="tab_transfer">
                                                        <div class="panel-body">
                                                            <div class="tabbable-custom " >
                                                                <div class="form-group transportdiv">
                                                                    <label class="control-label col-md-3 transferlabel"> <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE'] ?></label>
                                                                    <div class="col-md-4">
                                                                        <select class="form-control select2me" id="transport_mode">
                                                                            <option value="nbd"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD'] ?></option>
                                                                            <option value="nbdssl"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBDSSL'] ?></option>
                                                                            <option value="san"><?php echo $LANG['UI_BACKUP_TRANSPORT_SAN'] ?></option>
                                                                            <option value="hotadd"><?php echo $LANG['UI_BACKUP_TRANSPORT_HOT'] ?></option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-2 vmbackup-mt10_en mt5_cn">
                                                                        <a class="popovers transmode-tips" data-container="body" data-trigger="hover" data-html="true"
                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_SELECT_TIPS'] ?>">
                                                                            <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                        <a class="popovers transmode-no-lanfree-tips display-none" data-container="body" data-trigger="hover" data-html="true"
                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_SELECT_NO_LANFREE_TIPS'] ?>">
                                                                            <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>

                                                                <!-- VMware传输模式 -->
                                                                <div class="form-group vmwaretransportdiv display-none">
                                                                    <label class="control-label col-md-3 transferlabel form-group-label"> <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE'] ?></label>
                                                                    <div class="col-md-4 form-group-content">
                                                                        <select class="form-control select2me" id="vmwaretransport_mode">
                                                                            <option value="nbd"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD_VMWARE'] ?></option>
                                                                            <option value="nbdssl"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBDSSL_VMWARE'] ?></option>
                                                                            <option value="san"><?php echo $LANG['UI_BACKUP_TRANSPORT_SAN'] ?></option>
                                                                            <option value="hotadd"><?php echo $LANG['UI_BACKUP_TRANSPORT_HOT_VMWARE'] ?></option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-2 vmbackup-mt10_en mt5_cn">
                                                                        <a class="popovers transmode-tips" data-container="body" data-trigger="hover" data-html="true"
                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_VMWARE_SELECT_TIPS'] ?>">
                                                                            <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                        <a class="popovers transmode-no-lanfree-tips display-none" data-container="body" data-trigger="hover" data-html="true"
                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_VMWARE_SELECT_NO_LANFREE_TIPS'] ?>">
                                                                            <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>

                                                                <!-- ICS VVDK传输模式 -->
                                                                <div class="form-group icsvvdktransportdiv display-none">
                                                                    <label class="control-label col-md-3 transferlabel form-group-label"> <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE'] ?></label>
                                                                    <div class="col-md-4 form-group-content">
                                                                        <select class="form-control select2me" id="icsvvdktransport_mode">
                                                                            <option value="1"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD'] ?></option>
                                                                            <option value="3"><?php echo $LANG['UI_PLATFORM_APPLIANCE'] ?></option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-2 vmbackup-mt10_en mt5_cn">
                                                                        <a class="popovers" data-container="body" data-trigger="hover" data-html="true"
                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_ICS_VVDK_SELECT_TIPS'] . '<br><br>' . $LANG['UI_BACKUP_TRANSPORT_SELECT_NBD_TIPS'] ?>">
                                                                            <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>

                                                                <!-- 华为KVM传输模式 -->
                                                                <div class="form-group huaweikvmtransportdiv display-none">
                                                                    <label class="control-label col-md-3 huaweikvmtransferlabel form-group-label"> <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE'] ?></label>
                                                                    <div class="col-md-4 form-group-content">
                                                                        <select class="form-control select2me" id="huaweikvmtransport_mode">
                                                                            <option value="1"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD'] ?></option>
                                                                            <option value="5"><?php echo $LANG['UI_BACKUP_TRANSPORT_ADAPTIVE'] ?></option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-2 vmbackup-mt10_en mt5_cn">
                                                                        <a class="popovers" data-container="body" data-trigger="hover" data-html="true"
                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_HUAWEI_KVM_SELECT_TIPS'] ?>">
                                                                            <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>

                                                                <!-- 华为传输模式 -->
                                                                <div class="form-group huaweitransportdiv display-none">
                                                                    <label class="control-label col-md-3 huaweitransferlabel form-group-label"> <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE'] ?></label>
                                                                    <div class="col-md-4 form-group-content">
                                                                        <select class="form-control select2me" id="huaweitransport_mode">
                                                                            <option value="nbd"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD'] ?></option>
                                                                            <option value="nbdssl"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBDSSL'] ?></option>
                                                                            <option value="san"><?php echo $LANG['UI_BACKUP_TRANSPORT_SAN'] ?></option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-2 vmbackup-mt10_en mt5_cn">
                                                                        <a class="popovers transmode-tips" data-container="body" data-trigger="hover" data-html="true"
                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_HUAWEI_SELECT_TIPS'] ?>">
                                                                            <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                        <a class="popovers transmode-no-lanfree-tips display-none" data-container="body" data-trigger="hover" data-html="true"
                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_HUAWEI_SELECT_NO_LANFREE_TIPS'] ?>">
                                                                            <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>

                                                                <!-- XenServer传输模式 -->
                                                                <div class="form-group xentransdiv display-none">
                                                                    <label class="control-label col-md-3 xentranslabel form-group-label"> <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE'] ?></label>
                                                                    <div class="col-md-4 form-group-content">
                                                                        <select class="form-control select2me" id="xentransmode">
                                                                            <option value="1"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD'] ?></option>
                                                                            <option value="2"><?php echo $LANG['UI_BACKUP_TRANSPORT_SAN'] ?></option>
                                                                            <!-- <option value="3"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD_XENSERVER'] ?></option>
                                                                            <option value="4"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD_SAN_XENSERVER'] ?></option> -->
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-2 vmbackup-mt10_en mt5_cn">
                                                                        <a class="popovers transmode-tips" data-container="body" data-trigger="hover" data-html="true"
                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_XENSERVER_SELECT_TIPS2'] ?>">
                                                                            <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                        <a class="popovers transmode-no-lanfree-tips display-none" data-container="body" data-trigger="hover" data-html="true"
                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_XENSERVER_SELECT_NO_LANFREE_TIPS2'] ?>">
                                                                            <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>

                                                                <!-- 类XenServer传输模式 -->
                                                                <div class="form-group leixentransdiv display-none">
                                                                    <label class="control-label col-md-3 leixentranslabel form-group-label"> <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE'] ?></label>
                                                                    <div class="col-md-4 form-group-content">
                                                                        <select class="form-control select2me" id="leixentransmode">
                                                                            <option value="1"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD'] ?></option>
                                                                            <option value="2"><?php echo $LANG['UI_BACKUP_TRANSPORT_SAN'] ?></option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-2 vmbackup-mt10_en mt5_cn">
                                                                        <a class="popovers transmode-tips" data-container="body" data-trigger="hover" data-html="true"
                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_LEIXEN_SELECT_TIPS'] ?>">
                                                                            <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                        <a class="popovers transmode-no-lanfree-tips display-none" data-container="body" data-trigger="hover" data-html="true"
                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_LEIXEN_SELECT_NO_LANFREE_TIPS'] ?>">
                                                                            <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>

                                                                <!-- SmartX传输模式 -->
                                                                <div class="form-group smartxtransdiv display-none">
                                                                    <label class="control-label col-md-3 smartxtranslabel form-group-label"> <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE'] ?></label>
                                                                    <div class="col-md-4 form-group-content">
                                                                        <select class="form-control select2me" id="smartxtransmode">
                                                                            <option value="1"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD'] ?></option>
                                                                            <option value="2"><?php echo $LANG['UI_BACKUP_TRANSPORT_SAN'] ?></option>
                                                                            <option value="3"><?php echo $LANG['UI_PLATFORM_APPLIANCE'] ?></option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-2 vmbackup-mt10_en mt5_cn">
                                                                        <a class="popovers transmode-tips" data-container="body" data-trigger="hover" data-html="true"
                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_SMARTX_SELECT_TIPS'] ?>">
                                                                            <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                        <a class="popovers transmode-no-lanfree-tips display-none" data-container="body" data-trigger="hover" data-html="true"
                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_SMARTX_SELECT_NO_LANFREE_TIPS'] ?>">
                                                                            <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>

                                                                <!-- SangforVVDK传输模式 -->
                                                                <div class="form-group sangforvvdktransdiv display-none">
                                                                    <label class="control-label col-md-3 sangforvvdktranslabel form-group-label"> <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE'] ?></label>
                                                                    <div class="col-md-4 form-group-content">
                                                                        <select class="form-control select2me" id="sangforvvdktransmode">
                                                                            <option value="1"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD'] ?></option>
                                                                            <option value="3"><?php echo $LANG['UI_PLATFORM_APPLIANCE'] ?></option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-2 vmbackup-mt10_en mt5_cn">
                                                                        <a class="popovers" data-container="body" data-trigger="hover" data-html="true"
                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_SCP_SELECT_TIPS'] ?>">
                                                                            <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>

                                                                <!-- oVirt/RHV传输模式 -->
                                                                <div class="form-group redhattransdiv display-none">
                                                                    <label class="control-label col-md-3 redhattranslabel form-group-label"> <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE'] ?></label>
                                                                    <div class="col-md-4 form-group-content">
                                                                        <select class="form-control select2me" id="redhattransmode">
                                                                            <option value="1"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD'] ?></option>
                                                                            <option value="2"><?php echo $LANG['UI_BACKUP_TRANSPORT_SAN'] ?></option>
                                                                            <option value="5">ImageIO</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-2 mt5">
                                                                        <a class="popovers transmode-tips" data-container="body" data-trigger="hover" data-html="true"
                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_RHV_SELECT_TIPS'] ?>">
                                                                            <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                        <a class="popovers transmode-no-lanfree-tips display-none" data-container="body" data-trigger="hover" data-html="true"
                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_RHV_SELECT_NO_LANFREE_TIPS'] ?>">
                                                                            <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>

                                                                <!-- openstack传输模式 -->
                                                                <div class="form-group openstacktransdiv display-none">
                                                                    <label class="control-label col-md-3 openstacktranslabel form-group-label"> <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE'] ?></label>
                                                                    <div class="col-md-4 form-group-content">
                                                                        <select class="form-control select2me" id="openstacktransmode">
                                                                            <option value="1"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD'] ?></option>
                                                                            <option value="4"><?php echo $LANG['UI_BACKUP_TRANSPORT_SAN'] ?></option>
                                                                            <!-- 海外版屏蔽backup proxy -->
                                                                            <?php
                                                                            if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
                                                                                echo '<option value="3">' . $LANG['UI_PLATFORM_APPLIANCE'] . '</option>';
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-2 mt5">
                                                                        <a class="popovers transmode-tips" data-container="body" data-trigger="hover" data-html="true"
                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_OPENSTACK_SELECT_TIPS'] ?>">
                                                                            <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                        <a class="popovers transmode-no-lanfree-tips display-none" data-container="body" data-trigger="hover" data-html="true"
                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_OPENSTACK_SELECT_NO_LANFREE_TIPS'] ?>">
                                                                            <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>

                                                                <!-- vmware传输压缩 -->
                                                                <div class="form-group transferCompressSwitchDiv display-none">
                                                                    <label class="control-label col-md-3 form-group-label transferCompressSwitchLabel"> <?php echo $LANG['UI_BACKUP_TRANSFER_COMPRESS'] ?></label>
                                                                    <div class="col-md-4 form-group-content">
                                                                        <input type="checkbox" id="transferCompressSwitch"
                                                                               class="make-switch"
                                                                               data-on-color="primary"
                                                                               data-off-color="info" data-size="small"
                                                                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                        <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true"
                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_TRANSFER_COMPRESS_TIPS'] ?>">
                                                                            <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group transferCompressDiv display-none">
                                                                    <label class="control-label col-md-3 form-group-label transferCompressLabel"> <?php echo $LANG['UI_BACKUP_TRANSFER_COMPRESS_METHOD'] ?></label>
                                                                    <div class="col-md-4 form-group-content">
                                                                        <select class="form-control select2me" id="transferCompress">
                                                                            <option value="fastlz">fastlz</option>
                                                                            <option value="zlib">zlib</option>
                                                                            <option value="skipz">skipz</option>
                                                                            <option value="unzip" class="display-none"><?php echo $LANG['UI_BACKUP_TRANSFER_COMPRESS_NOT_USE'] ?></option>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <!-- 传输线程 -->
                                                                <div class="form-group threadDiv">
                                                                    <label class="control-label col-md-3 threadnumlabel form-group-label"><?php echo $LANG['UI_BACKUP_THREAD_NUM'] ?></label>
                                                                    <div class="col-md-4 form-group-content">
                                                                        <div class="backupThreadDiv">
                                                                            <div class="input-group spinner-group" >
                                                                                <input type="text" id="backupThreadNum" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" >
                                                                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                    <button type="button" class="btn spinner-up default input-sm">
                                                                                        <i class="fa fa-angle-up"></i>
                                                                                    </button>
                                                                                    <button type="button" class="btn spinner-down default input-sm">
                                                                                        <i class="fa fa-angle-down"></i>
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-2 vmbackup-mt10_en mt5_cn">
                                                                        <a class="popovers" data-container="body" data-trigger="hover" data-html="true"
                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_THREAD_NUM_TIPS'] ?>">
                                                                            <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>

                                                                <div class="form-group appliancediv display-none">
                                                                    <label class="control-label col-md-3 appliancelabel form-group-label"><?php echo $LANG['UI_PLATFORM_APPLIANCE'] ?></label>
                                                                    <div class="col-md-4 form-group-content">
                                                                        <input type="checkbox" id="appliancecheck"  class="make-switch" data-on-color="primary" data-off-color="info" data-size="small"
                                                                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                        <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true"
                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_APPLIANCE_DES'] ?>">
                                                                            <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>

                                                                <div class="form-group display-hide applianceselectdiv">
                                                                    <label class="control-label col-md-3 applianceselectlabel form-group-label"><?php echo $LANG['UI_APPLIANCE_SELECT'] ?></label>
                                                                    <div class="col-md-4 content">
                                                                        <ul class="ztree" id="transferAgentTree"></ul>
                                                                    </div>
                                                                </div>

                                                                <!-- 节点传输ip -->
                                                                <div class="form-group display-none systemIpdiv">
                                                                    <label class="control-label col-md-3 form-group-label"> <?php echo $LANG['UI_BACKUP_NODE_TRANSFER_IP'] ?></label>
                                                                    <div class="col-md-9 accordion strategyOne" >
                                                                        <div class="panel panel-default strategy-panel">
                                                                            <div class="panel-heading">
                                                                                <h4 class="panel-title">
                                                                                    <a class="accordion-toggle accordion-toggle-styled popovers"
                                                                                       data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#nodeTransferIp" aria-expanded="true">
                                                                                        <i class="viconfont vicon-a-chuanshu font-green-seagreen"></i>
                                                                                        <span class="font-green-seagreen"><?php echo $LANG['UI_BACKUP_NODE_TRANSFER_IP'] ?></span>
                                                                                    </a>
                                                                                </h4>
                                                                            </div>
                                                                            <div id="nodeTransferIp" class="panel-collapse collapse in">
                                                                                <div class="panel-body">
                                                                                    <div class="col-md-12 systemIpdivContainer">

                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="form-group display-hide encrypttransferdiv">
                                                                    <label class="control-label col-md-3 encrypttransferlabel form-group-label"><?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER'] ?></label>
                                                                    <div class="col-md-4 form-group-content">
                                                                        <input type="checkbox" id="encrypttransfer"  class="make-switch" data-size="small"  data-on-color="primary" data-off-color="info"
                                                                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                        <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true"
                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER_TIPS'] ?>">
                                                                            <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                                <!-- 传输加密算法 -->
                                                                <div class="form-group transfer-encrypt-method-form display-none">
                                                                    <label class="control-label transfer-encrypt-method-label col-md-3"><?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?></label>
                                                                    <div class="col-md-4">
                                                                        <select class="form-control select2me" id="transferEncryptMethod">
                                                                            <?php
                                                                            
                                                                            foreach ($CONF['TRANSPORT_ENCRYPT_METHOD'] as $index => $encryptMethod) {
                                                                                if ($_SESSION['language'] != "zh-cn" && $_SESSION['language'] != "zh-tw" && $index == 2) {
                                                                                } else {
                                                                                    echo '<option value="' . $index . '">' . $encryptMethod . '</option>';
                                                                                }
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group display-none ipSegmentDiv">
                                                                    <label class="control-label col-md-3 ipsegmentlabel form-group-label"><?php echo $LANG['UI_VM_DATA_TRANSFER_NET_SEGMENT'] ?>
                                                                    </label>
                                                                    <div class="col-md-4 form-group-content">
                                                                        <input type="text" maxlength="128" class="form-control" id="ipSegment"/>
                                                                    </div>
                                                                    <div class="col-md-1 mt5">
                                                                        <a class="popovers" data-container="body" data-trigger="hover"
                                                                           data-placement="right" data-content="<?php echo $LANG['UI_VM_DATA_TRANSFER_NET_SEGMENT_TIPS'] ?>">
                                                                            <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>

                                                                <!-- vmware异步传输 -->
                                                                <div class="form-group asyncTransferDiv display-none">
                                                                    <label class="control-label col-md-3 form-group-label asyncTransferLabel"> <?php echo $LANG['UI_BACKUP_ASYNC_TRANSFER'] ?></label>
                                                                    <div class="col-md-4 form-group-content">
                                                                        <input type="checkbox" id="asyncTransfer"
                                                                               class="make-switch"
                                                                               data-on-color="primary"
                                                                               data-off-color="info" data-size="small"
                                                                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                        <a class="popovers ml15" data-container="body" data-trigger="hover"
                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_ASYNC_TRANSFER_TIPS'] ?>">
                                                                            <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>

                                                                <!-- 并行传输分组 -->
                                                                <div class="form-group parallelTransferDiv">
                                                                    <label class="control-label col-md-3 form-group-label asyncTransferLabel"> <?php echo $LANG['UI_BACKUP_PARALLEL_TRANSFER'] ?></label>
                                                                    <div class="col-md-9 accordion strategyOne" >
                                                                        <div class="panel panel-default strategy-panel">
                                                                            <div class="panel-heading">
                                                                                <h4 class="panel-title">
                                                                                    <a class="accordion-toggle accordion-toggle-styled popovers"
                                                                                       data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#parallelTransfer" aria-expanded="true">
                                                                                        <i class="viconfont vicon-a-chuanshu font-green-seagreen"></i>
                                                                                        <span class="font-green-seagreen"><?php echo $LANG['UI_BACKUP_PARALLEL_TRANSFER'] ?></span>
                                                                                    </a>
                                                                                </h4>
                                                                            </div>
                                                                            <div id="parallelTransfer" class="panel-collapse collapse in">
                                                                                <div class="panel-body">
                                                                                    <div class="col-md-12">
                                                                                        <!-- 并行传输虚拟机个数，华为kvm使用 -->
                                                                                        <div class="form-group ptVmDiv display-none">
                                                                                            <label class="control-label col-md-4 ptVmLabel form-group-label"><?php echo $LANG['UI_BACKUP_PARALLEL_TRANSFER_VM_COUNT'] ?></label>
                                                                                            <div class="col-md-4 form-group-content">
                                                                                                <div class="ptVmInputDiv">
                                                                                                    <div class="input-group spinner-group" >
                                                                                                        <input type="text" id="ptVmCount" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" >
                                                                                                        <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                                            <button type="button" class="btn spinner-up default input-sm">
                                                                                                                <i class="fa fa-angle-up"></i>
                                                                                                            </button>
                                                                                                            <button type="button" class="btn spinner-down default input-sm">
                                                                                                                <i class="fa fa-angle-down"></i>
                                                                                                            </button>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="col-md-2 vmbackup-mt10_en mt5_cn">
                                                                                                <a class="popovers" data-container="body" data-trigger="hover" data-html="true"
                                                                                                   data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_PARALLEL_TRANSFER_VM_COUNT_TIPS'] ?>">
                                                                                                    <i class="viconfont vicon-tishi"></i>
                                                                                                </a>
                                                                                            </div>
                                                                                        </div>
                                                                                        <!-- 并行传输磁盘个数，华为kvm使用 -->
                                                                                        <div class="form-group ptDiskDiv display-none">
                                                                                            <label class="control-label col-md-4 ptDiskLabel form-group-label"><?php echo $LANG['UI_BACKUP_SINGLE_VM_PARALLEL_DISK_TRANSFER_COUNT'] ?></label>
                                                                                            <div class="col-md-4 form-group-content">
                                                                                                <div class="ptDiskInputDiv">
                                                                                                    <div class="input-group spinner-group" >
                                                                                                        <input type="text" id="ptDiskCount" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" >
                                                                                                        <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                                            <button type="button" class="btn spinner-up default input-sm">
                                                                                                                <i class="fa fa-angle-up"></i>
                                                                                                            </button>
                                                                                                            <button type="button" class="btn spinner-down default input-sm">
                                                                                                                <i class="fa fa-angle-down"></i>
                                                                                                            </button>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="col-md-2 vmbackup-mt10_en mt5_cn">
                                                                                                <a class="popovers" data-container="body" data-trigger="hover" data-html="true"
                                                                                                   data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_SINGLE_VM_PARALLEL_DISK_TRANSFER_COUNT_TIPS'] ?>">
                                                                                                    <i class="viconfont vicon-tishi"></i>
                                                                                                </a>
                                                                                            </div>
                                                                                        </div>
                                                                                        <!-- 磁盘并行传输分段个数，华为kvm使用 -->
                                                                                        <div class="form-group ptSegmentDiv display-none">
                                                                                            <label class="control-label col-md-4 ptSegmentLabel form-group-label"><?php echo $LANG['UI_BACKUP_VM_SINGLE_DISK_PARALLEL_TRANSFER_COUNT'] ?></label>
                                                                                            <div class="col-md-4 form-group-content">
                                                                                                <div class="ptSegmentInputDiv">
                                                                                                    <div class="input-group spinner-group" >
                                                                                                        <input type="text" id="ptSegmentCount" onkeyup="value=value.replace(/[^(1-8)]/g,'')" class="spinner-input form-control input-sm" >
                                                                                                        <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                                            <button type="button" class="btn spinner-up default input-sm">
                                                                                                                <i class="fa fa-angle-up"></i>
                                                                                                            </button>
                                                                                                            <button type="button" class="btn spinner-down default input-sm">
                                                                                                                <i class="fa fa-angle-down"></i>
                                                                                                            </button>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="col-md-2 vmbackup-mt10_en mt5_cn">
                                                                                                <a class="popovers" data-container="body" data-trigger="hover" data-html="true"
                                                                                                   data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_VM_SINGLE_DISK_PARALLEL_TRANSFER_COUNT_TIPS'] ?>">
                                                                                                    <i class="viconfont vicon-tishi"></i>
                                                                                                </a>
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
                                                    </div>
                                                    <!-- 安全策略 -->
                                                    <div class="tab-pane" id="tab_safe">
                                                        <div class="panel-body">
                                                            <!-- WORM防护 插件 -->
                                                            <div id="wormConfig"></div>
                                                            <!-- 病毒检测 插件 -->
                                                            <div id="virusConfig"></div>
                                                            <!-- 备份数据完整性校验 插件 -->
                                                            <div id="completeConfig"></div>
                                                        </div>
                                                    </div>
                                                    <!-- 高级配置 -->
                                                    <div class="tab-pane " id="tab_other">
                                                        <div class="advance-config-wrap">
                                                            <div class="advance-config-wrap__row row m0" >
                                                                <div class="col-md-2 col-sm-2 col-xs-2 nav-tab-radios" style="align-items: flex-start;">
                                                                    <ul class="nav nav-tabs tabs-left">
                                                                        <li class="active" id="li_high_snapshot">
                                                                            <a href="#tab_high_snapshot" data-toggle="tab" aria-expanded="true"><?php echo $LANG['UI_BACKUP_FILE_SHOOTSNAP'] ?></a>
                                                                        </li>
                                                                        <li id="li_high_inc">
                                                                            <a href="#tab_high_inc" data-toggle="tab" aria-expanded="false"><?php echo $LANG['WEB_PLATFORM_DES_INCRIMENT'] ?></a>
                                                                        </li>
                                                                        <li id="li_high_vcbt">
                                                                            <a href="#tab_high_vcbt" data-toggle="tab" aria-expanded="false"><?php echo $LANG['UI_BACKUP_VCBT'] ?></a>
                                                                        </li>
                                                                        <li>
                                                                            <a href="#retry_config" data-toggle="tab" aria-expanded="false"><?php echo $LANG['UI_STRATEGY_RETRY'] ?></a>
                                                                        </li>
                                                                        <li id="li_high_storage">
                                                                            <a href="#tab_high_backupdata" data-toggle="tab" aria-expanded="false"><?php echo $LANG['UI_PALTFORM_STORAGE'] ?></a>
                                                                        </li>
                                                                        <li>
                                                                            <a href="#tab_overload_protect" data-toggle="tab" aria-expanded="false"><?php echo $LANG['UI_STRATEGY_OVER_LOAD_LABEL'] ?></a>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                                <div class="col-md-10 col-sm-10 col-xs-10 advance-config-wrap__row__content">
                                                                    <div class="tab-content">
                                                                        <!-- 快照 -->
                                                                        <div class="tab-pane active" id="tab_high_snapshot">
                                                                            <div class="col-md-12 advanced-detail-conf-district pt40">
                                                                                <!-- 快照模式 -->
                                                                                <div class="form-group SnapshotDiv">
                                                                                    <label class="control-label col-md-3 snapshotTypelabel form-group-label">
                                                                                        <?php echo $LANG['UI_BACKUP_SNAPSHOT_MODE'] ?>
                                                                                    </label>
                                                                                    <div class="col-md-4 form-group-content">
                                                                                        <select class="form-control" id="snapshottype">
                                                                                            <option value="1"><?php echo $LANG['UI_BACKUP_SNAPSHOT_MODE_SERIAL'] ?></option>
                                                                                            <option value="2"><?php echo $LANG['UI_BACKUP_SNAPSHOT_MODE_PARALLEL'] ?></option>
                                                                                        </select>
                                                                                    </div>
                                                                                    <div class="col-md-2 vmbackup-mt10_en mt5_cn">
                                                                                        <a class="popovers"
                                                                                           data-container="body"
                                                                                           data-trigger="hover"
                                                                                           data-html="true"
                                                                                           data-placement="right"
                                                                                           data-content="<?php echo $_GET['sub_module_type'] == 2 ? $LANG['UI_BACKUP_MODE_PRIVATE_CLOUD_HIGH_SNAPSHOT_TIPS'] : $LANG['UI_BACKUP_MODE_HIGH_SNAPSHOT_TIPS'] ?>">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                                <!-- 提前创建快照 -->
                                                                                <div class="form-group preSnapshotDiv">
                                                                                    <label class="control-label col-md-3  preSnapshotlabel form-group-label"><?php echo $LANG['UI_BACKUP_PRE_CREATE_SNAPSHOT'] ?></label>
                                                                                    <div class="col-md-2 form-group-content col-md-3_en">
                                                                                        <input type="checkbox" id="preSnapshotcheck"  class="make-switch" data-on-color="primary" data-off-color="info" data-size="small"
                                                                                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                        <a class="popovers ml15"
                                                                                           data-container="body"
                                                                                           data-trigger="hover"
                                                                                           data-html="true"
                                                                                           data-placement="right"
                                                                                           data-content="<?php echo $_GET['sub_module_type'] == 2 ? $LANG['UI_BACKUP_PRIVATE_CLOUD_PRE_CREATE_SNAPSHOT_TIPS'] : $LANG['UI_BACKUP_PRE_CREATE_SNAPSHOT_TIPS'] ?>">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                                <!-- 静默快照 -->
                                                                                <div class="form-group snapshotdiv">
                                                                                    <label class="control-label col-md-3 silentsnapshotlabel form-group-label"><?php echo $LANG['UI_BACKUP_MODE_HIGH_SILENTSNAPSHOT'] ?></label>
                                                                                    <div class="col-md-2 form-group-content col-md-3_en">
                                                                                        <input type="checkbox" id="silentsnapshotcheck"  class="make-switch" data-on-color="primary" data-off-color="info" data-size="small"
                                                                                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                        <div class="ml15 silentDiv" style="display: inline-block">
                                                                                            <a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right"
                                                                                               data-content="<?php echo $LANG['UI_BACKUP_MODE_HIGH_SILENTSNAPSHOT_TIPS'] ?>" >
                                                                                                <i class="viconfont vicon-tishi"></i>
                                                                                            </a>
                                                                                        </div>
                                                                                        <div class="ml15 display-none uniformDiv" style="display: inline-block">
                                                                                            <a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right"
                                                                                               data-content="<?php echo $LANG['UI_BACKUP_MODE_HIGH_HUAWEI_KVM_SILENTSNAPSHOT_TIPS'] ?>" >
                                                                                                <i class="viconfont vicon-tishi"></i>
                                                                                            </a>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <!-- 创建存储快照 -->
                                                                                <div class="form-group storageSnapshotDiv display-none">
                                                                                    <label class="control-label col-md-3  storageSnapshotLabel form-group-label"><?php echo $LANG['UI_BACKUP_CREATE_STORAGE_SNAPSHOT'] ?></label>
                                                                                    <div class="col-md-4 form-group-content">
                                                                                        <input type="checkbox" id="storageSnapshotCheck"  class="make-switch" data-on-color="primary" data-off-color="info" data-size="small"
                                                                                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                    </div>
                                                                                </div>
                                                                                <!-- 快照删除速度 -->
                                                                                <div class="form-group snapshotDelSpeedDiv display-none">
                                                                                    <label class="control-label col-md-3  snapshotDelSpeedLabel form-group-label"><?php echo $LANG['UI_BACKUP_SNAPSHOT_DELETE_SPEED'] ?></label>
                                                                                    <div class="col-md-4 form-group-content">
                                                                                        <div class="snapshotDelSpeedInputDiv">
                                                                                            <div class="input-group spinner-group" >
                                                                                                <input type="text" min="1" id="snapshotDelSpeed" style="text-align: left;" class="spinner-input form-control" oninput="value=value.replace(/[^\d]/g,'')">
                                                                                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                                    <button type="button" class="btn spinner-up default">
                                                                                                        <i class="fa fa-angle-up"></i>
                                                                                                    </button>
                                                                                                    <button type="button" class="btn spinner-down default">
                                                                                                        <i class="fa fa-angle-down"></i>
                                                                                                    </button>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-md-2 vmbackup-mt10_en mt5_cn">MB/s</div>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <!-- 增量 -->
                                                                        <div class="tab-pane" id="tab_high_inc">
                                                                            <div class="col-md-12 advanced-detail-conf-district pt40">
                                                                                <!-- 增量模式 -->
                                                                                <div class="form-group" id="incmodeldiv">
                                                                                    <label class="control-label col-md-3 incmodellabel form-group-label">
                                                                                        <?php echo $LANG['UI_BACKUP_INCMODE'] ?>
                                                                                    </label>
                                                                                    <div class="col-md-4 form-group-content" style="height: auto">
                                                                                        <select class="form-control" id="incmodel">
                                                                                            <option value="2"><?php echo $LANG['UI_BACKUP_INCMODE_HIGH_SPEED'] ?></option>
                                                                                            <option value="3"><?php echo $LANG['UI_BACKUP_INCMODE_CBT'] ?></option>
                                                                                            <option value="1"><?php echo $LANG['UI_BACKUP_INCMODE_GENNERAL'] ?></option>
                                                                                        </select>
                                                                                    </div>
                                                                                    <div class="col-md-2 incmodel-tips-div vmbackup-mt10_en mt5_cn">
                                                                                        <a class="popovers incmodeltips display-none" data-container="body" data-trigger="hover" data-html="true"
                                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_INC_MODE_TIPS'] ?>">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                        <a class="popovers incmodelctbtips display-none" data-container="body" data-trigger="hover" data-html="true"
                                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_INC_MODE_CTB_TIPS'] ?>">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                        <a class="popovers incmodelhighctbtips display-none" data-container="body" data-trigger="hover" data-html="true"
                                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_INC_MODE_HIGH_SPEED_CBT_TIPS'] ?>">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                        <a class="popovers incmodelnormalctbtips display-none" data-container="body" data-trigger="hover" data-html="true"
                                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_INC_MODE_NORMAL_CBT_TIPS'] ?>">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                        <a class="popovers incmodelnormalhightips display-none" data-container="body" data-trigger="hover" data-html="true"
                                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_INC_MODE_TIPS_EN'] ?>">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                        <a class="popovers h3ccvdincmodeltips display-none" data-container="body" data-trigger="hover" data-html="true"
                                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_INC_MODE_H3CCVD_TIPS'] ?>">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                        <a class="popovers hypervincmodeltips display-none" data-container="body" data-trigger="hover" data-html="true"
                                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_INC_MODE_HYPERV_TIPS'] ?>">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                                <!-- 重置cbt -->
                                                                                <div class="form-group resetcbtdiv">
                                                                                    <label class="control-label col-md-3 resetcbtlabel"><?php echo $LANG['UI_BACKUP_RESET_CBT'] ?></label>
                                                                                    <div class="col-md-4">
                                                                                        <select class="form-control" id="resetcbtLevel">
                                                                                            <option value="3"><?php echo $LANG['UI_BACKUP_CBT_FULL_RESET'] ?></option>
                                                                                            <option value="2" selected><?php echo $LANG['UI_BACKUP_CBT_ERROR_RESET'] ?></option>
                                                                                            <option value="1"><?php echo $LANG['UI_BACKUP_CBT_NO_RESET'] ?></option>
                                                                                        </select>
                                                                                    </div>
                                                                                    <div class="col-md-1 vmbackup-mt10_en mt5_cn">
                                                                                        <a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right"
                                                                                           data-content="<?php echo $LANG['UI_BACKUP_MODE_HIGH_RESET_CBT_TIPS'] ?>" >
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                                <!-- h3c cvd 高速磁盘cbt -->
                                                                                <div class="form-group highdiskcbtdiv">
                                                                                    <label class="control-label col-md-3 highdiskcbtlabel form-group-label"><?php echo $LANG['UI_BACKUP_HIGHSPEED_DISK_CBT'] ?></label>
                                                                                    <div class="col-md-4 form-group-content">
                                                                                        <input type="checkbox" id="highdiskcbtcheck"  class="make-switch" data-on-color="primary" data-off-color="info" data-size="small"
                                                                                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                        <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right"
                                                                                           data-content="<?php echo $LANG['UI_BACKUP_HIGHSPEED_DISK_CBT_TIPS'] ?>" >
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <!-- 深度有效数据 -->
                                                                        <div class="tab-pane" id="tab_high_vcbt">
                                                                            <div class="col-md-12 advanced-detail-conf-district pt40">
                                                                                <!-- 深度有效数据提取 -->
                                                                                <div class="form-group parsefsDiv">
                                                                                    <label class="control-label col-md-3 parsefslabel form-group-label"><?php echo $LANG['UI_BACKUP_VCBT'] ?></label>
                                                                                    <div class="col-md-2 form-group-content col-md-3_en">
                                                                                        <input type="checkbox" id="parsefscheck"  class="make-switch" data-on-color="primary" data-off-color="info" data-size="small"
                                                                                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                        <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right"
                                                                                           data-content="<?php echo $LANG['UI_BACKUP_MODE_HIGH_VCBT_TIPS'] ?>" >
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                                <!-- 排除交换文件块 -->
                                                                                <div class="form-group swapdiv display-none" >
                                                                                    <label class="control-label col-md-3  swaplabel form-group-label"><?php echo $LANG['UI_BACKUP_SWAP'] ?></label>
                                                                                    <div class="col-md-2 form-group-content col-md-3_en">
                                                                                        <input type="checkbox" id="swapcheck"  class="make-switch" data-size="small" data-on-color="primary" data-off-color="info"
                                                                                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                        <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right"
                                                                                           data-content="<?php echo $_GET['sub_module_type'] == 2 ? $LANG['UI_BACKUP_MODE_PRIVATE_CLOUD_HIGH_SWAP_TIPS'] : $LANG['UI_BACKUP_MODE_HIGH_SWAP_TIPS'] ?>" >
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                                <!-- 排除删除文件块 -->
                                                                                <div class="form-group deletefilediv display-none" >
                                                                                    <label class="control-label col-md-3  deletefilelabel form-group-label"><?php echo $LANG['UI_BACKUP_DELETEFILE'] ?></label>
                                                                                    <div class="col-md-2 form-group-content col-md-3_en">
                                                                                        <input type="checkbox" id="deletefilecheck" data-size="small" class="make-switch" data-on-color="primary" data-off-color="info"
                                                                                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                        <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right"
                                                                                           data-content="<?php echo $LANG['UI_BACKUP_MODE_HIGH_DELFILE_TIPS'] ?>" >
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                                <!-- 排除分区间隙 -->
                                                                                <div class="form-group gapdiv display-none">
                                                                                    <label class="control-label col-md-3  gaplabel form-group-label"><?php echo $LANG['UI_BACKUP_GAP'] ?></label>
                                                                                    <div class="col-md-2 form-group-content col-md-3_en">
                                                                                        <input type="checkbox" id="gapcheck" data-size="small" class="make-switch" data-on-color="primary" data-off-color="info"
                                                                                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                        <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right"
                                                                                           data-content="<?php echo $LANG['UI_BACKUP_MODE_HIGH_GAP_TIPS'] ?>" >
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <!-- 重试 -->
                                                                        <div id="retry_config" class="tab-pane retry_config_pane">
                                                                        </div>

                                                                        <!-- 备份数据 -->
                                                                        <div class="tab-pane" id="tab_high_backupdata">
                                                                            <div class="col-md-12 advanced-detail-conf-district pt40">
                                                                                <!-- 数据块大小 -->
                                                                                <div class="form-group blocksizediv">
                                                                                    <label class="control-label col-md-3 blocksizelabel form-group-label"><?php echo $LANG['UI_BACKUP_BLOCK_SIZE'] ?></label>
                                                                                    <div class="col-md-4 form-group-content">
                                                                                        <select class="form-control select2me" id="blocksize">
                                                                                            <option value="64">64 KB</option>
                                                                                            <option value="128">128 KB</option>
                                                                                            <option value="256">256 KB</option>
                                                                                            <option value="512">512 KB</option>
                                                                                            <option value="1024" selected = "selected">1024 KB</option>
                                                                                            <option value="2048">2048 KB</option>
                                                                                        </select>
                                                                                    </div>
                                                                                    <div class="col-md-1 vmbackup-mt10_en mt5_cn">
                                                                                        <a class="popovers" data-container="body" data-trigger="hover"
                                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_BLOCK_SIZE_TIPS'] ?> ">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                                <!-- 数据文件大小 -->
                                                                                <div class="form-group datacontainersizediv">
                                                                                    <label class="control-label col-md-3 datacontainersizelabel form-group-label"><?php echo $LANG['UI_BACKUP_DATA_CONTAINER_SIZE'] ?></label>
                                                                                    <div class="col-md-4 form-group-content">
                                                                                        <select class="form-control select2me" id="data_container_size">
                                                                                            <option value="1073741824">1 GB</option>
                                                                                            <option value="2147483648">2 GB</option>
                                                                                            <option value="3221225472">3 GB</option>
                                                                                            <option value="4294967296">4 GB</option>
                                                                                        </select>
                                                                                    </div>
                                                                                    <div class="col-md-1 vmbackup-mt10_en mt5_cn">
                                                                                        <a class="popovers" data-container="body" data-trigger="hover"
                                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_DATA_CONTAINER_SIZE_TIPS'] ?> ">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                                <!-- 合并冗余数据比例 -->
                                                                                <div class="form-group mergemodediv">
                                                                                    <label class="control-label col-md-3 mergemodelabel form-group-label"><?php echo $LANG['UI_BACKUP_MERGE_REDUNDANT_DATA_PROPORTION'] ?></label>
                                                                                    <div class="col-md-4 form-group-content">
                                                                                        <select class="form-control select2me" id="redundant_data_proportion">
                                                                                            <option value="10">10%</option>
                                                                                            <option value="30">30%</option>
                                                                                            <option value="50" selected>50%</option>
                                                                                            <option value="70">70%</option>
                                                                                            <option value="90">90%</option>
                                                                                        </select>
                                                                                    </div>
                                                                                    <div class="col-md-1 vmbackup-mt10_en mt5_cn">
                                                                                        <a class="popovers" data-container="body" data-trigger="hover"
                                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_MERGE_REDUNDANT_DATA_PROPORTION_TIPS'] ?> ">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                                <!-- 有效数据备份 -->
                                                                                <div class="form-group display-hide">
                                                                                    <label class="control-label col-md-3 validlabel form-group-label"><?php echo $LANG['UI_BACKUP_VALID_DATA'] ?></label>
                                                                                    <div class="col-md-4 form-group-content">
                                                                                        <input type="checkbox" id="validcheck" checked  class="make-switch" data-on-color="primary" data-off-color="info" data-size="small"
                                                                                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <!-- 过载保护 -->
                                                                        <div class="tab-pane" id="tab_overload_protect">
                                                                            <div class="col-md-12 advanced-detail-conf-district pt40">
                                                                                <!-- 忽略节点资源限制 -->
                                                                                <div class="form-group">
                                                                                    <label class="control-label col-md-3 ignoreResourceLimitLabel form-group-label"><?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE'] ?></label>
                                                                                    <div class="col-md-2 form-group-content col-md-3_en">
                                                                                        <input type="checkbox" id="ignore_resource_limit"  class="make-switch" data-on-color="primary" data-off-color="info" data-size="small"
                                                                                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                        <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right"
                                                                                           data-content="<?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE_TIPS'] ?>" >
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                                <!-- 任务最大并发数 + 任务禁止运行时间段-->
                                                                                <div class="form-group node-limit-form">
                                                                                    <div class="table-container col-md-offset-3 pl15">
                                                                                        <table class="table table-hover table-borderless" id="nodeLimitTable">
                                                                                        </table>
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
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="tab4">
                                        <div class="tab-pane__body">
                                            <div class="tab-pane__body__form">
                                                <div class="alert alert-danger display-none jobnametip"></div>
                                                <div class="form-group mb0 mt10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_JOB_RNAME'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <div class="input-icon right">
                                                            <i class="fa"></i>
                                                            <input type="text" maxlength="64"  onkeyup="customInputValidate('string', this.value, $(this))" class="form-control" id="jobname"/>
                                                            <span class="help-block "><?php echo $LANG['UI_JOB_RNAME_TIPS'] ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <h4 class="form-section"></h4>
                                                <div class="form-group mb0 mt10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_SOURCE'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static vmshow">
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10 display-hide">
                                                    <label class="control-label col-md-3"><?php echo $_GET['sub_module_type'] == 2 ? $LANG['UI_BACKUP_EXCLUDED_INSTANCE'] : $LANG['UI_BACKUP_EXCLUDED_VM'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static excludevmshow">
                                                        </p>
                                                    </div>
                                                </div>
                                                <h4 class="form-section"></h4>
                                                <div class="form-group mb0 mt10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_COPY_BACK_TARGET_STORAGE'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static storeinfoshow">
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10" id="nodeinfoshow">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_COPY_BACK_TARGET_NODE'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static nodeinfoshow">
                                                        </p>
                                                    </div>
                                                </div>
                                                <h4 class="form-section"></h4>
                                                <div class="form-group mb0 mt10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TYPE'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static backuptypeshow">
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10 backupTypeInfoDiv">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_STRATEGY_TIME'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static backuptypeinfoshow">
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_STORAGE_STRATEGY'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static storageinfoshow">
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10 showdiv" id="transportmodeshowdiv">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <div class="form-control-static transportinfoshow"></div>
                                                        <br>
                                                        <div class="form-control-static threadnumshow threadDiv"></div>
                                                        <div class="form-control-static ptVmShow ptVmDiv"></div>
                                                        <div class="form-control-static ptDiskShow ptDiskDiv"></div>
                                                        <div class="form-control-static ptSegmentShow ptSegmentDiv"></div>
                                                        <div class="form-control-static applianceshow"></div>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10 reserveDiv">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_RESERVE_STRATEGY'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static reservetypeshow">
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10 safeDiv">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static safeStrategyShow">
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10 showdiv" id="backupmodeshowdiv">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?>:</label>
                                                    <div class="col-md-6">
                                                        <div class="form-control-static snapshotmodeshow">
                                                        </div>
                                                        <div class="form-control-static presnapshotshow">
                                                        </div>
                                                        <!--                                                        <div class="form-control-static backupmodeshow1">-->
                                                        <!--                                                        </div>-->
                                                        <!-- 静默快照 -->
                                                        <div class="form-control-static backupmodeshow2">
                                                        </div>
                                                        <!--                                                        <div class="form-control-static cbtmodeshow">-->
                                                        <!--                                                        </div>-->
                                                        <div class="form-control-static incmodeshow">
                                                        </div>
                                                        <div class="form-control-static resetcbtshow">
                                                        </div>
                                                        <div class="form-control-static storagesnapshotshow">
                                                        </div>
                                                        <div class="form-control-static snapshotdelspeedshow">
                                                        </div>
                                                        <div class="form-control-static highdiskcbtshow">
                                                        </div>
                                                        <div class="form-control-static parsefsmodeshow">
                                                        </div>
                                                        <div class="form-control-static swapmodeshow">
                                                        </div>
                                                        <div class="form-control-static deletefilemodeshow">
                                                        </div>
                                                        <div class="form-control-static gapmodeshow">
                                                        </div>
                                                        <div class="form-control-static blocksizeshow">
                                                        </div>
                                                        <div class="form-control-static datacontainersizeshow">
                                                        </div>
                                                        <div class="form-control-static mergemodeshow">
                                                        </div>
                                                        <div class="form-control-static ignoreresourcelimitshow">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10 retryShow">
                                                </div>
                                                <div class="form-group mb0 mt10 speedlimitDiv">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static speedlimitshow">
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 display-none" id="verifymodeshowdiv">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_CBR_SYNC_VERIFY_STRATEGY'] ?>:</label>
                                                    <div class="col-md-6">
                                                        <div class="form-control-static colorblack verifymodeshow">
                                                        </div>
                                                    </div>
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
                                        <i class="viconfont vicon-shangyibu"></i> <?php echo $LANG['UI_PUBLIC_PREV_STEP'] ?>
                                    </a>
                                    <a href="javascript:;" class="btn green-turquoise button-next next-btn-margin-left">
                                        <?php echo $LANG['UI_PUBLIC_NEXT_STEP'] ?> <i class="viconfont vicon-xiayibu"></i>
                                    </a>
                                    <a href="javascript:;" class="btn green-haze button-submit">
                                        <?php echo $LANG['UI_PUBLIC_SUBMIT'] ?> <i class="viconfont vicon-xiayibu"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- BEGIN RATE LIMIT MODAL -->
        <div id="speedlimitModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
            <div class="modal-header ">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title"><i class="iconfont icon-xiansucelve"></i> <?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="portlet-body">

                    <div class="form-group">
                        <label class="control-label col-md-2 form-group-label">  <?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_TYPE'] ?>
                        </label>
                        <div class="col-md-6">
                            <select class="form-control select2me inline-block" id="speedModeType"  style="width:203px;">
                                <option value="1"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_STRATEGY'] ?></option>
                                <option value="2"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_FOREVER'] ?></option>
                            </select>
                            <a class="popovers ml15" data-container="body" data-trigger="hover"
                               data-placement="right" data-content="<?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_TYPE_TIPS'] ?>">
                                <i class="viconfont vicon-tishi"></i>
                            </a>
                        </div>
                    </div>

                    <div class="form-group setSpeedStrategy">
                        <label class="control-label col-md-2 form-group-label"> <?php echo $LANG['UI_BACKUP_SET_STRATEGY'] ?>
                        </label>
                        <div class="col-md-10">
                            <div class="portlet">
                                <div class="portlet-body">
                                    <div class="panel-group accordion" id="speedstrategy">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-2"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_VALUE'] ?>
                        </label>
                        <div class="col-md-6">
                            <div id="rateDiv" style="display:inline-flex;">
                                <div class="input-icon" >
                                    <div id="speedSpinnerNum" >
                                        <div class="input-group spinner-group" >
                                            <input type="text" id="speedSpinnerNumInput" style="text-align: left;" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control" onkeyup="value=value.replace(/[^\d]/g,'')">
                                            <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                <button type="button" class="btn spinner-up default">
                                                    <i class="fa fa-angle-up"></i>
                                                </button>
                                                <button type="button" class="btn spinner-down default">
                                                    <i class="fa fa-angle-down"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="">
                                    <select id="unit" style="width: 80px; margin-left: 10px;height: 33px;text-align:center; border: 1px solid #E6E6E6;">
                                        <option value="1">KB/s</option>
                                        <option selected value="2">MB/s</option>
                                        <option value="3">GB/s</option>
                                    </select>
                                </div>
                                <a style="display:block; margin-top:8px;" class="popovers ml15" data-container="body" data-trigger="hover"
                                   data-placement="right" data-content="<?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_VALUE_TIPS'] ?>">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- modal-footer -->
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
                <button type="button"class="btn btn-primary" id="speed_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
            </div>
        </div>
        <!-- END RATE LIMIT MODAL -->

        <!-- BEGIN VM FILTER MODAL -->
        <div id="vmFilterModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
            <div class="modal-header ">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title"><i class="viconfont vicon-ge_filter"></i>
                    <?php echo $_GET['sub_module_type'] == 2 ? $LANG['UI_BACKUP_INSTANCE_FILTER'] : $LANG['UI_BACKUP_VM_FILTER'] ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="portlet-body" style="height:60%;display: flex;flex-direction: column;justify-content: space-around">
                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <button type="button" class="close" data-dismiss="alert"></button>
                                <ul class = "alert-ul">
                                    <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>: </strong>
                                    <li><?php echo $_GET['sub_module_type'] == 2 ? $LANG['UI_BACKUP_INSTANCE_NAME_PREFIX_MATCH_TIPS'] : $LANG['UI_BACKUP_VM_NAME_PREFIX_MATCH_TIPS'] ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 form-group-label">
                            <?php echo $LANG['UI_BACKUP_FILTER_WAY'] ?>:
                        </label>
                        <div class="col-md-6">
                            <select class="form-control select2me" id="vmMatchType">
                                <option value="3"><?php echo $LANG['UI_BACKUP_VM_NAME_KEYWORD_MATCH'] ?></option>
                                <option value="2"><?php echo $LANG['UI_BACKUP_VM_NAME_KEYWORD_FILTER'] ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 form-group-label">
                            <?php echo $LANG['UI_BACKUP_VM_NAME_PREFIX_OR_KEYWORD'] ?>:
                        </label>
                        <div class="filter-input-container col-md-8" style="padding: 0">
                            <div class="form-group filter-input">
                                <div class="col-md-9">
                                    <input class="form-control inline-block" id="vmMatchWildcard" name="vm_prefix">
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-default delPrefixBtn" style="width: 50px;">
                                        <i class="viconfont vicon-ge_delete"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-light-primary ml10" id="addPrefixBtn" style="margin-left: -60px!important;padding: 6px 14px;min-width: 50px!important;">
                                <i class="viconfont vicon-zhediekuanganniu1"></i>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
                <button type="button" class="btn btn-primary" id="vm_filter_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
            </div>
        </div>
        <!-- END VM FILTER MODAL -->
    </div>
</div>
<!-- END PAGE CONTENT-->


<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/additional-methods.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>

<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<?php
if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
    //如果不是英文,加载语言包
    echo '<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>';
}
?>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js" ></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.speedStrategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.strategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/calendar/jquery.searchableSelect.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.taskCrowd.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.GFSStrategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.backupStrategy.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_worm.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_virus_detection.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_complete.js"></script>
<script type="text/javascript" src="./scripts/platform/component/retry_strategy.js"></script>
<script type="text/javascript" src="./scripts/platform/component/transfer_agent.js"></script>
<script type="text/javascript" src="./scripts/platform/component/backup_target.js"></script>
<script type="text/javascript" src="./scripts/plugins/path-tree-selector.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script src="./scripts/vm/vmbackup.js" type="text/javascript"></script>
