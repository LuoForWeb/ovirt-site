<?php
include_once '../../tpl/permission.php';
include_once '../../content/platform/public/bs_table.php';
global $LANG;
?>

<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css"
      href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
<link rel="stylesheet" type="text/css"
      href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./scripts/plugins/calendar/jquery.searchableSelect.css"/>
<link rel="stylesheet" type="text/css" href="./css/dbprotect/dbbackup.css"/>
<link rel="stylesheet" type="text/css" href="./css/platform/component/style.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/component/resource_pool.css"/>
<link rel="stylesheet" type="text/css" href="./css/platform/component/safe_virus.css"/>
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
        <div class="portlet box blue-hoki backup-page" id="dbbackupcontent">
            <div class="portlet-title">
                <div class="caption">
                    <i class="levelchild viconfont vicon-xinjianbeifen1"></i><?php echo $LANG['UI_DB_ADD_NEW_BACKUP_JOB'] ?>
                </div>
            </div>
            <div class="portlet-body form">
                <div action="#" class="form-horizontal" id="submit_form" method="POST" style="height:100%;">
                    <div class="form-wizard" style="height:100%;">
                        <div class="form-body">
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
                                <div class="tab-pane active" id="tab1">
                                    <div class="row tab-pane__row">
                                        <div class="col-md-4 tab-pane__row__source">
                                            <div class="form-group src-wrap">
                                                <div class="src-wrap__title">
                                                    <span>
                                                        <i class="levelchild viconfont vicon-ge_backup_host"></i> <?php echo $LANG['UI_BACKUP_SELECT_DB_BAK_HOST'] ?>
                                                    </span>
                                                </div>
                                                <div class="src-wrap__content">
                                                    <div class="src-wrap__content__search">
                                                        <select class="bs-select width100p form-control"
                                                                data-show-subtext="true" id="dbtype">
                                                        </select>
                                                        <div class="vm_tree_div">
                                                            <div class="input-icon width100p searchDiv display-none">
                                                                <input type="text" maxlength="128"
                                                                       placeholder="<?php echo $LANG['UI_COPY_SEARCH_AS_KEYWORD'] ?>"
                                                                       class="form-control" id="searchAgent"
                                                                       onkeyup="customInputValidate('string', this.value, $(this))"/>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="src-wrap__content__itree">
                                                        <div class="vcenter-tree">
                                                            <ul id="agent_tree" class="ztree tree_div"></ul>
                                                        </div>
                                                        <div class="alert alert-block alert-info fade in display-hide"
                                                             id="noagent">
                                                            <button type="button" class="close"
                                                                    data-dismiss="alert"></button>
                                                            <h4 class="alert-heading">
                                                                <strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong>
                                                            </h4>
                                                            <ol class="alert-ol">
                                                                <li>
                                                                    <?php echo $LANG['UI_BACKUP_NO_VALID_DB_HOST_TIPS'] ?>
                                                                </li>
                                                                <li>
                                                                    <a id="toaddAgent"><?php echo $LANG['UI_BACKUP_NO_VALID_DB_HOST_TIPS1'] ?></a>
                                                                </li>
                                                                <!--																<li>-->
                                                                <!--																	--><?php //echo $LANG['UI_BACKUP_NO_VALID_DB_HOST_TIPS2'] ?>
                                                                <!--																</li>-->
                                                            </ol>
                                                        </div>
                                                        <div class="alert alert-block alert-info fade in display-hide"
                                                             id="nosearchtips">
                                                            <button type="button" class="close"
                                                                    data-dismiss="alert"></button>
                                                            <ul class="alert-ul">
                                                                <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>
                                                                    :</strong>
                                                                <li>
                                                                    <?php echo $LANG['UI_DATA_NOSEARCH_TIPS'] ?>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-8 notipsDiv">
                                            <div class="alert alert-block alert-info fade in" id="step1tips">
                                                <button type="button" class="close" data-dismiss="alert"></button>
                                                <h4 class="alert-heading">
                                                    <strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                                <ol class="alert-ol">
                                                    <li><?php echo $LANG['UI_DB_BACKUP_TIPS1'] ?></li>
                                                    <li><?php echo $LANG['UI_DB_BACKUP_TIPS2'] ?></li>
                                                    <li><?php echo $LANG['UI_DB_BACKUP_TIPS3'] ?></li>
                                                </ol>
                                            </div>
                                            <!-- 没有可用数据库提示 -->
                                            <div class="alert alert-block alert-info fade in display-hide"
                                                 id="noDBTips">
                                                <button type="button" class="close" data-dismiss="alert"></button>
                                                <h4 class="alert-heading">
                                                    <strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                                <ol class="alert-ol">
                                                    <li><?php echo $LANG['UI_DB_NO_DB_TIPS1'] ?></li>
                                                    <li><?php echo $LANG['UI_DB_NO_DB_TIPS2'] ?></li>
                                                </ol>
                                            </div>
                                        </div>
                                        <div class="col-md-8 display-hide VMList-div" id="dbtreediv">
                                            <div class="addTitle">
                                                <span>
                                                    <i class="levelchild viconfont vicon-db_protect"></i> <?php echo $LANG['UI_DB_BACKUP_SELECT_SRC_TIPS'] ?>
                                                </span>
                                            </div>
                                            <div class="addIt-list" id="allDbTree"></div>
                                        </div>
                                    </div>
                                </div><!--//tab1-->

                                <!-- 备份目的地	 -->
                                <div class="tab-pane" id="tab2">
                                    <div class="row">
                                        <div class="col-md-9" id="backupTarget"></div>
                                    </div>
                                </div><!--//tab2-->

                                <div class="tab-pane" id="tab3">
                                    <div class="alert alert-info display-none hypervTips">
                                    </div>
                                    <div class="row row-stepthree">
                                        <div class="nav-tabs-wrapper col-md-offset-1 col-md-10">
                                            <ul class="nav nav-tabs nav-line-tabs">
                                                <li class="commonLi nav-item active">
                                                    <a href="#tab_common" class="nav-link" data-toggle="tab">
                                                        <i class="viconfont vicon-celve1"></i> <?php echo $LANG['UI_BACKUP_COMMON_STRATEGY'] ?>
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="#tab_transfer" class="nav-link" data-toggle="tab">
                                                        <i class="viconfont vicon-a-chuanshu"></i> <?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?>
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="#tab_script" class="nav-link" data-toggle="tab">
                                                        <i class="viconfont vicon-ziyuanxiangqing"></i> <?php echo $LANG['UI_PUBLIC_SCRIPT_CONFIGURE'] ?>
                                                    </a>
                                                </li>
                                                <li class="safeStrategyLi nav-item">
                                                    <a href="#tab_safe_strategy" class="nav-link" data-toggle="tab">
                                                        <i class="viconfont vicon-anquancelve"></i> <?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?>
                                                    </a>
                                                </li>
                                                <li class="highLi nav-item">
                                                    <a href="#tab_other" class="nav-link" data-toggle="tab">
                                                        <i class="viconfont vicon-gaojipeizhi"></i> <?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?>
                                                    </a>
                                                </li>
                                            </ul>
                                            <div class="tab-content hover-scroll-y">
                                                <div class="tab-pane active" id="tab_common">
                                                    <div class="panel-body">
                                                        <!-- 选择策略 -->
                                                        <div class="form-group">
                                                            <div class="control-label col-md-2"><?php echo $LANG['UI_BACKUP_SELECT_STRATEGY'] ?></div>
                                                            <div class="col-md-4 flex-items-center">
                                                                <select class="form-control select2me inline-block"
                                                                        id="strategySelect">
                                                                </select>
                                                                <a class="popovers ml12" data-container="body"
                                                                   data-trigger="hover"
                                                                   data-placement="right"
                                                                   data-content="<?php echo $LANG['UI_BACKUP_SELECT_STRATEGY_TIPS'] ?>">
                                                                    <i class="viconfont vicon-tishi"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                        <!-- 提示信息 -->
                                                        <div class="form-group display-hide beforeTimeStrategyTipsDiv">
                                                            <div class="col-md-offset-1 col-md-10  ">
                                                                <div class="alert alert-block alert-danger fade in"
                                                                     style="margin: 0" id="beforeTimeStrategyTipsAlert">
                                                                    <i class="fa fa-warning"></i>
                                                                    <span id="beforeTimeStrategyTips"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- 时间策略 -->
                                                        <div class="form-group">
                                                            <div class="col-md-offset-1 col-md-10  ">
                                                                <?php include('../platform/strategy/timeStrategy.php'); ?>
                                                            </div>
                                                        </div>
                                                        <!-- 限速策略 -->
                                                        <div class="form-group speedlimitDiv">
                                                            <div class="col-md-offset-1 col-md-10 accordion strategyOne">
                                                                <div class="panel panel-default strategy-panel">
                                                                    <div class="panel-heading">
                                                                        <h4 class="panel-title">
                                                                            <a class="accordion-toggle accordion-toggle-styled collapsed popovers"
                                                                               data-container="body"
                                                                               data-trigger="hover" data-placement="top"
                                                                               data-toggle="collapse"
                                                                               data-parent=".strategyOne" href="#speed"
                                                                               aria-expanded="true">
                                                                                <i class="viconfont vicon-xiansucelve1 font-green-seagreen"></i>
                                                                                <span class="font-green-seagreen"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?></span>
                                                                                <span class="strategyDes speedlimitDes"></span>
                                                                            </a>
                                                                        </h4>
                                                                    </div>
                                                                    <!--<div id="speed" class="panel-collapse collapse ">
                                                                        <div class="panel-body">
                                                                            <div class="col-md-12">
                                                                                <button type="button" id="addSpeedlimit" class="btn btn-sm green-haze">
                                                                                    <i class="viconfont vicon-ge_add_task"></i><?php /*echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_ADD'] */ ?>
                                                                                </button>
                                                                                <ul id="speedList" style="padding-left: 0;">

                                                                                </ul>
                                                                            </div>
                                                                        </div>
                                                                    </div>-->
                                                                    <?php include_once('../../content/platform/global_strategy/speedJob.php'); ?>
                                                                    <!--       全局限速策略组合的组件配置-->
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- 存储策略 -->
                                                        <div class="form-group">
                                                            <div class="col-md-offset-1 col-md-10  ">
                                                                <?php include('../platform/strategy/storeStrategy.php') ?>
                                                            </div>
                                                        </div>
                                                        <!-- 保留策略 -->
                                                        <div class="form-group" id="reserveShowDiv">
                                                            <div class="col-md-offset-1 col-md-10  ">
                                                                <?php include('../platform/strategy/reserveStrategy.php') ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div><!--//tab_common-->
                                                <!-- 传输策略 -->
                                                <div class="tab-pane " id="tab_transfer">
                                                    <div class="panel-body">
                                                        <div class="tabbable-custom col-md-10">
                                                            <div class="form-group encrypttransferdiv">
                                                                <label class="control-label col-md-3 encrypttransferlabel form-group-label"><?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER'] ?></label>
                                                                <div class="col-md-4 form-group-content flex-items-center">
                                                                    <input type="checkbox" id="encrypttransfer"
                                                                           class="make-switch" data-on-color="primary"
                                                                           data-off-color="info" data-size="small"
                                                                           data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                    <a class="popovers ml12 mb-5" data-container="body"
                                                                       data-trigger="hover" data-html="true"
                                                                       data-placement="right"
                                                                       data-content="<?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER_TIPS'] ?>">
                                                                        <i class="viconfont vicon-tishi"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                            <!-- 传输加密算法 -->
                                                            <div class="form-group transfer-encrypt-method-form display-none">
                                                                <label class="control-label transfer-encrypt-method-label col-md-3"><?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?></label>
                                                                <div class="col-md-6">
                                                                    <select class="form-control select2me"
                                                                            id="transferEncryptMethod">
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

                                                            <div class="form-group">
                                                                <label class="control-label col-md-3 transferlabel"> <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE'] ?></label>
                                                                <div class="col-md-6">
                                                                    <select class="form-control select2me"
                                                                            id="transport_mode">
                                                                        <option value="1"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD'] ?></option>
                                                                        <!-- <option value="2"><?php echo $LANG['UI_BACKUP_TRANSPORT_SAN'] ?></option> -->
                                                                    </select>
                                                                    <a class="popovers popover-abs-position" data-container="body"
                                                                       data-trigger="hover" data-html="true"
                                                                       data-placement="right"
                                                                       data-content="<?php echo $LANG['UI_DB_TRANSPORT_TIPS'] ?>">
                                                                        <i class="viconfont vicon-tishi"></i>
                                                                    </a>
                                                                </div>
                                                            </div>

                                                            <div class="form-group display-none transfernetworkDiv">
                                                                <label class="control-label col-md-3 transfernetworklabel"><?php echo $LANG['UI_NODE_TRANSFER_NETWORK'] ?></label>
                                                                <div class="col-md-6">
                                                                    <ul class="ztree" id="transferNetworkTree"></ul>
                                                                    <a class="popovers popover-abs-position" data-container="body"
                                                                       data-trigger="hover" data-html="true"
                                                                       data-placement="right"
                                                                       data-content="<?php echo $LANG['UI_NODE_TRANSFER_NETWORK_TIPS'] ?>">
                                                                        <i class="viconfont vicon-tishi"></i>
                                                                    </a>
                                                                </div>
                                                            </div>

                                                            <!-- oracle通道数 1~32 -->
                                                            <div class="form-group oracleChannelDiv display-none">
                                                                <label class="control-label col-md-3  oracleChannelLabel">
                                                                    <?php echo $LANG['UI_DB_ORACLE_CHANNEL_NUM'] ?>
                                                                </label>
                                                                <div class="col-md-6">
                                                                    <div class="oracleChannelSpinner">
                                                                        <div class="input-group spinner-group">
                                                                            <input id="oracleChannelNum"
                                                                                   onkeyup="value=value.replace(/[^\d]/g,'')"
                                                                                   class="spinner-input form-control">
                                                                            <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                <button type="button"
                                                                                        class="btn spinner-up default">
                                                                                    <i class="fa fa-angle-up"></i>
                                                                                </button>
                                                                                <button type="button"
                                                                                        class="btn spinner-down default">
                                                                                    <i class="fa fa-angle-down"></i>
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <a class="popovers popover-abs-position" data-container="body"
                                                                       data-trigger="hover"
                                                                       data-html="true" data-placement="right"
                                                                       data-content="<?php echo $LANG['UI_DB_ORACLE_CHANNEL_NUM_TIPS2'] ?>">
                                                                        <i class="viconfont vicon-tishi"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                            <!-- SAP HANA - 通道数 -->
                                                            <div class="form-group advanced channelCountDiv display-hide">
                                                                <label class="control-label col-md-3 channelCountLabel">
                                                                    <?php echo $LANG['UI_DB_SAPHANA_CHANNEL_NUM'] ?>
                                                                </label>
                                                                <div class="col-md-6">
                                                                    <div id="channelCountSpinner">
                                                                        <div class="input-group spinner-group">
                                                                            <input id="channelCount"
                                                                                   class="spinner-input form-control"
                                                                                   onkeyup="value=value.replace(/[^\d]/g,'')">
                                                                            <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                <button type="button"
                                                                                        class="btn spinner-up default">
                                                                                    <i class="fa fa-angle-up"></i>
                                                                                </button>
                                                                                <button type="button"
                                                                                        class="btn spinner-down default">
                                                                                    <i class="fa fa-angle-down"></i>
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <a class="popovers popover-abs-position" data-container="body"
                                                                       data-trigger="hover" data-html="true"
                                                                       data-placement="right"
                                                                       data-content="<?php echo $LANG['UI_DB_SAPHANA_CHANNEL_NUM_TIPS'] ?>">
                                                                        <i class="viconfont vicon-tishi"></i>
                                                                    </a>
                                                                </div>
                                                            </div><!--//channelCountDiv-->

                                                            <!-- MongoDB传输线程 1~16 -->
                                                            <div class="form-group mongodbThreadDiv display-none">
                                                                <label class="control-label col-md-3 mongodbthreadnumlabel">
                                                                    <?php echo $LANG['UI_BACKUP_THREAD_NUM'] ?>
                                                                </label>
                                                                <div class="col-md-6">
                                                                    <div class="mongodbBackupThreadDiv">
                                                                        <div class="input-group spinner-group">
                                                                            <input id="mongodbBackupThreadNum"
                                                                                   onkeyup="value=value.replace(/[^\d]/g,'')"
                                                                                   class="spinner-input form-control">
                                                                            <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                <button type="button"
                                                                                        class="btn spinner-up default">
                                                                                    <i class="fa fa-angle-up"></i>
                                                                                </button>
                                                                                <button type="button"
                                                                                        class="btn spinner-down default">
                                                                                    <i class="fa fa-angle-down"></i>
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <a class="popovers popover-abs-position" data-container="body"
                                                                       data-trigger="hover" data-html="true"
                                                                       data-placement="right"
                                                                       data-content="<?php echo $LANG['UI_DB_MONGODB_THREAD_NUM_TIPS'] ?>">
                                                                        <i class="viconfont vicon-tishi"></i>
                                                                    </a>
                                                                </div>
                                                            </div><!-- //END mongodbThreadDiv -->
                                                        </div>
                                                    </div>
                                                </div><!--//tab_transfer-->
                                                <!-- 脚本配置 -->
                                                <div class="tab-pane " id="tab_script">
                                                    <div class="panel-body">
                                                        <!-- 脚本配置 -->
                                                        <div class="form-group scriptConfigSwitchDiv">
                                                            <label class="control-label col-md-3 scriptConfigSwitchLabel">
                                                                <?php echo $LANG['UI_PUBLIC_SCRIPT_CONFIGURE'] ?>
                                                            </label>
                                                            <div class="col-md-4 flex-items-center mt-4">
                                                                <input type="checkbox" id="scriptConfigSwitch"
                                                                       class="make-switch" data-on-color="primary"
                                                                       data-off-color="info" data-size="small">
                                                                <a class="popovers ml12" data-container="body"
                                                                   data-trigger="hover"
                                                                   data-html="true" data-placement="right"
                                                                   data-content="<?php echo $LANG['UI_DB_BACKUP_SCRIPT_TIPS'] ?>">
                                                                    <i class="viconfont vicon-tishi"></i>
                                                                </a>
                                                            </div>
                                                        </div><!--//END scriptConfigSwitchDiv-->
                                                        <div class="form-group scriptConfigDiv">
                                                            <label class="control-label col-md-3">
                                                            </label>
                                                            <div class="col-md-9">
                                                                <div id="scriptConfig"></div>
                                                            </div>
                                                        </div><!--//END scriptConfigDiv-->
                                                    </div>
                                                </div><!--//tab_script-->
                                                <!-- 安全策略 -->
                                                <div class="tab-pane " id="tab_safe_strategy">
                                                    <div class="panel-body">
                                                        <!-- WORM保护 -->
                                                        <div id="wormConfig"></div>
                                                        <!-- 完整性校验 -->
                                                        <div id="integrityCheck"></div>
                                                    </div>
                                                </div><!--//tab_safe_strategy-->
                                                <!-- 高级配置 -->
                                                <div class="tab-pane " id="tab_other">
                                                    <div class="advance-config-wrap">
                                                        <div class="advance-config-wrap__row row m0">
                                                            <!-- 切换页 -->
                                                            <div class="col-md-2 col-sm-3 col-xs-3 nav-tab-radios">
                                                                <ul class="nav nav-tabs tabs-left min-height400"
                                                                    id="tabAdvanceTabUl">
                                                                    <li id="tabAdvancedArchivelogTab">
                                                                        <a href="#tab_advanced_archivelog"
                                                                           data-toggle="tab"
                                                                           aria-expanded="true"><?php echo $LANG['UI_DB_BACKUP_ADVANCED_TAB_ARCHIVELOG'] ?></a>
                                                                    </li>
                                                                    <li id="tabAdvancedWarningTab">
                                                                        <a href="#tab_advanced_warning"
                                                                           data-toggle="tab"
                                                                           aria-expanded="false"><?php echo $LANG['UI_DB_BACKUP_ADVANCED_TAB_WARNING'] ?></a>
                                                                    </li>
                                                                    <li id="tabAdvancedCheckTab">
                                                                        <a href="#tab_advanced_check" data-toggle="tab"
                                                                           aria-expanded="false"><?php echo $LANG['UI_DB_BACKUP_ADVANCED_TAB_CHECK'] ?></a>
                                                                    </li>
                                                                    <li id="tabAdvancedValidateDataTab">
                                                                        <a href="#tab_advanced_validate_data"
                                                                           data-toggle="tab"
                                                                           aria-expanded="false"><?php echo $LANG['UI_DB_BACKUP_ADVANCED_TAB_VALIDATE_DATA'] ?></a>
                                                                    </li>
                                                                    <li id="tabAdvancedSnapshotTab">
                                                                        <a href="#tab_advanced_snapshot"
                                                                           data-toggle="tab"
                                                                           aria-expanded="false"><?php echo $LANG['UI_DB_BACKUP_ADVANCED_TAB_SNAPSHOT'] ?></a>
                                                                    </li>
                                                                    <li id="tabAdvancedIncrementTab">
                                                                        <a href="#tab_advanced_increment"
                                                                           data-toggle="tab"
                                                                           aria-expanded="false"><?php echo $LANG['UI_DB_BACKUP_ADVANCED_TAB_INCREMENT'] ?></a>
                                                                    </li>
                                                                    <li id="tabAdvancedPerformanceTab">
                                                                        <a href="#tab_advanced_performance"
                                                                           data-toggle="tab"
                                                                           aria-expanded="false"><?php echo $LANG['UI_DB_BACKUP_ADVANCED_TAB_PERFORMANCE'] ?></a>
                                                                    </li>
                                                                    <li id="tabAdvancedRetryTab">
                                                                        <a href="#tab_advanced_retry" data-toggle="tab"
                                                                           aria-expanded="false"><?php echo $LANG['UI_STRATEGY_RETRY'] ?></a>
                                                                    </li>
                                                                    <li id="tabAdvancedOverloadTab">
                                                                        <a href="#tab_advanced_overload"
                                                                           data-toggle="tab"
                                                                           aria-expanded="false"><?php echo $LANG['UI_STRATEGY_OVER_LOAD_LABEL'] ?></a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            <div class="col-md-10 col-sm-9 col-xs-9 pe-0 advance-config-wrap__row__content">
                                                                <div class="tab-content"
                                                                     style="height: 100%; overflow: hidden;"
                                                                     id="tabAdvancedContent">
                                                                    <!-- 归档日志 -->
                                                                    <div class="tab-pane active in"
                                                                         id="tab_advanced_archivelog">
                                                                        <div class="abnormal-handle-form">
                                                                            <!-- Oracle日志备份次数 -->
                                                                            <div class="form-group advancedDiv oracleDiv display-none">
                                                                                <label class="control-label col-md-3 form-group-label archivelogBackupTimesCheckLabel"
                                                                                       for="archivelogBackupTimesCheck">
                                                                                    <?php echo $LANG['UI_DB_ORACLE_BACKUP_ARCHIVELOG_TIMES'] ?>
                                                                                </label>
                                                                                <div class="col-md-4 form-group-content flex-items-center">
                                                                                    <input type="checkbox"
                                                                                           id="archivelogBackupTimesCheck"
                                                                                           class="make-switch"
                                                                                           data-on-color="primary"
                                                                                           data-off-color="info"
                                                                                           data-size="small"
                                                                                           data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                    <a class="popovers ml12 mb-5"
                                                                                       data-container="body"
                                                                                       data-trigger="hover"
                                                                                       data-html="true"
                                                                                       data-placement="right"
                                                                                       data-content="<?php echo $LANG['UI_DB_ORACLE_BACKUP_ARCHIVELOG_TIMES_TIPS'] ?>">
                                                                                        <i class="viconfont vicon-tishi"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group advancedDiv oracleDiv display-none archivelogBackupTimesDiv">
                                                                                <label class="control-label col-md-3 form-group-label archivelogBackupTimesLabel"
                                                                                       for="archivelogBackupTimes">
                                                                                    <?php echo $LANG['UI_DB_ORACLE_BACKUP_ARCHIVELOG_TIMES_TITLE'] ?>
                                                                                </label>
                                                                                <div class="col-md-4">
                                                                                    <div id="archivelogBackupTimesSpinner">
                                                                                        <div class="input-group spinner-group">
                                                                                            <input id="archivelogBackupTimes"
                                                                                                   onkeyup="value=value.replace(/[^\d]/g,'')"
                                                                                                   class="spinner-input form-control">
                                                                                            <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                                <button type="button"
                                                                                                        class="btn spinner-up default">
                                                                                                    <i class="fa fa-angle-up"></i>
                                                                                                </button>
                                                                                                <button type="button"
                                                                                                        class="btn spinner-down default">
                                                                                                    <i class="fa fa-angle-down"></i>
                                                                                                </button>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            <!-- Oracle日志备份天数 -->
                                                                            <div class="form-group advancedDiv oracleDiv display-none">
                                                                                <label class="control-label col-md-3 form-group-label archivelogBackupDaysCheckLabel"
                                                                                       for="archivelogBackupDaysCheck">
                                                                                    <?php echo $LANG['UI_DB_ORACLE_BACKUP_ARCHIVELOG_DAYS'] ?>
                                                                                </label>
                                                                                <div class="col-md-4 form-group-content flex-items-center">
                                                                                    <input type="checkbox"
                                                                                           id="archivelogBackupDaysCheck"
                                                                                           class="make-switch"
                                                                                           data-on-color="primary"
                                                                                           data-off-color="info"
                                                                                           data-size="small"
                                                                                           data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                    <a class="popovers ml12 mb-5"
                                                                                       data-container="body"
                                                                                       data-trigger="hover"
                                                                                       data-html="true"
                                                                                       data-placement="right"
                                                                                       data-content="<?php echo $LANG['UI_DB_ORACLE_BACKUP_ARCHIVELOG_DAYS_TIPS'] ?>">
                                                                                        <i class="viconfont vicon-tishi"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group advancedDiv oracleDiv display-none archivelogBackupDaysDiv">
                                                                                <label class="control-label col-md-3 form-group-label archivelogBackupDaysLabel"
                                                                                       for="archivelogBackupDays">
                                                                                    <?php echo $LANG['UI_DB_ORACLE_BACKUP_ARCHIVELOG_DAYS_TITLE'] ?>
                                                                                </label>
                                                                                <div class="col-md-4">
                                                                                    <div id="archivelogBackupDaysSpinner">
                                                                                        <div class="input-group spinner-group">
                                                                                            <input id="archivelogBackupDays"
                                                                                                   onkeyup="value=value.replace(/[^\d]/g,'')"
                                                                                                   class="spinner-input form-control">
                                                                                            <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                                <button type="button"
                                                                                                        class="btn spinner-up default">
                                                                                                    <i class="fa fa-angle-up"></i>
                                                                                                </button>
                                                                                                <button type="button"
                                                                                                        class="btn spinner-down default">
                                                                                                    <i class="fa fa-angle-down"></i>
                                                                                                </button>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            <!-- 删除归档日志 -->
                                                                            <div class="form-group advancedDiv oracleDiv display-none">
                                                                                <label class="control-label col-md-3 delarchiveloglabel form-group-label">
                                                                                    <?php echo $LANG['UI_DB_ORACLE_DELETE_ARCHIVELOG_TITLE'] ?>
                                                                                </label>
                                                                                <div class="col-md-6">
                                                                                    <div style="display: flex; align-items: center; white-space: nowrap">
                                                                                        <label style="display: flex; align-items: center; white-space: nowrap">
                                                                                            <input value="3"
                                                                                                   type="radio"
                                                                                                   name="deleteArchivelog"
                                                                                                   id="deleteArchivelogRadio3"
                                                                                                   checked>
                                                                                            <span id="deleteArchivelogLabel3-1">
                                                                                                <?php echo $LANG['UI_DB_ORACLE_DELETE_ARCHIVELOG_TIPS3_1']; ?>
                                                                                            </span>&nbsp;
                                                                                            <input class="form-control"
                                                                                                   id="archivelogDays"
                                                                                                   type="number" min="1"
                                                                                                   max="9999" value="7"
                                                                                                   style="min-width: 100px"/>
                                                                                            &nbsp;<span
                                                                                                    id="deleteArchivelogLabel3-2">
                                                                                                <?php echo $LANG['UI_DB_ORACLE_DELETE_ARCHIVELOG_TIPS3_2']; ?>
                                                                                            </span>
                                                                                        </label>
                                                                                    </div>
                                                                                    <div>
                                                                                        <label>
                                                                                            <input value="1"
                                                                                                   type="radio"
                                                                                                   name="deleteArchivelog"
                                                                                                   id="deleteArchivelogRadio1">
                                                                                            <span id="deleteArchivelogLabel1">
                                                                                                <?php echo $LANG['UI_DB_ORACLE_DELETE_ARCHIVELOG_TIPS1']; ?>
                                                                                            </span>
                                                                                        </label>
                                                                                    </div>
                                                                                    <div>
                                                                                        <label>
                                                                                            <input value="2"
                                                                                                   type="radio"
                                                                                                   name="deleteArchivelog"
                                                                                                   id="deleteArchivelogRadio2">
                                                                                            <span id="deleteArchivelogLabel2">
                                                                                                <?php echo $LANG['UI_DB_ORACLE_DELETE_ARCHIVELOG_TIPS2']; ?>
                                                                                            </span>
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                            </div><!-- //END: 删除归档日志-->
                                                                            <!-- 检查归档日志文件 -->
                                                                            <div class="form-group oracleDiv oracleCheckArchivelogDiv display-none">
                                                                                <label class="control-label col-md-3 oracleCheckArchivelogLabel"><?php echo $LANG['UI_DB_ORACLE_CHECK_ARCHIVELOG'] ?></label>
                                                                                <div class="col-md-4 flex-items-center">
                                                                                    <input type="checkbox"
                                                                                           id="oracleCheckArchivelog"
                                                                                           class="make-switch"
                                                                                           data-on-color="primary"
                                                                                           data-off-color="info"
                                                                                           data-size="small"
                                                                                           data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                    <a class="popovers ml12"
                                                                                       data-container="body"
                                                                                       data-trigger="hover"
                                                                                       data-html="true"
                                                                                       data-placement="right"
                                                                                       data-content="<?php echo $LANG['UI_BACKUP_CHECK_ARCHIVELOG_TIPS_ORACLE'] ?>">
                                                                                        <i class="viconfont vicon-tishi"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div>
                                                                            <!-- //END: oracleCheckArchivelogDiv-->

                                                                            <!-- DM-删除归档日志 -->
                                                                            <div class="form-group advancedDiv dmDiv display-none">
                                                                                <label class="control-label col-md-3 dmdelarchiveloglabel form-group-label">
                                                                                    <?php echo $LANG['UI_DB_IF_DELETE_ARCHIVE_LOG'] ?>
                                                                                </label>
                                                                                <div class="col-md-4 form-group-content flex-items-center">
                                                                                    <input type="checkbox"
                                                                                           id="deldmarchivelog"
                                                                                           class="make-switch"
                                                                                           data-on-color="primary"
                                                                                           data-off-color="info"
                                                                                           data-size="small"
                                                                                           data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                    <a class="popovers ml12 mb-5"
                                                                                       data-container="body"
                                                                                       data-trigger="hover"
                                                                                       data-html="true"
                                                                                       data-placement="right"
                                                                                       data-content="<?php echo $LANG['UI_DB_DELETE_ARCHIVE_LOG_AFTER_OPEN'] ?>">
                                                                                        <i class="viconfont vicon-tishi"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div><!-- //END: DM-删除归档日志 -->

                                                                            <!-- POSTGRES-删除归档日志配置 -->
                                                                            <div class="form-group advancedDiv postgreDiv display-none">
                                                                                <label class="control-label col-md-3 delpostgreloglabel form-group-label">
                                                                                    <?php echo $LANG['UI_DB_IF_DELETE_ARCHIVE_LOG'] ?>
                                                                                </label>
                                                                                <div class="col-md-4 form-group-content"
                                                                                     style="position: relative;">
                                                                                    <select class="form-control select2me"
                                                                                            id="delpostgrelog">
                                                                                        <option value="1"><?php echo $LANG['UI_DB_DELETE_ARCHIVE_LOG_TYPE1'] ?></option>
                                                                                        <option value="2"><?php echo $LANG['UI_DB_DELETE_ARCHIVE_LOG_TYPE2'] ?></option>
                                                                                        <option value="3"><?php echo $LANG['UI_DB_DELETE_ARCHIVE_LOG_TYPE3'] ?></option>
                                                                                    </select>
                                                                                    <a class="popovers popover-abs-position mt-0"
                                                                                       data-container="body"
                                                                                       data-trigger="hover"
                                                                                       data-html="true"
                                                                                       data-placement="right"
                                                                                       data-content="<?php echo $LANG['UI_DB_DELETE_ARCHIVE_LOG_SELECT_TIPS'] ?>">
                                                                                        <i class="viconfont vicon-tishi"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div>
                                                                            <!--POSTGRES-归档日志告警-->
                                                                            <div class="form-group advancedDiv postgreDiv display-none">
                                                                                <label class="control-label col-md-3 archiveAlarmlabel form-group-label">
                                                                                    <?php echo $LANG['UI_DB_ARCHIVE_STORAGE_ALARM'] ?>
                                                                                </label>
                                                                                <div class="col-md-4 form-group-content flex-items-center">
                                                                                    <input type="checkbox"
                                                                                           id="archiveAlarmCheck"
                                                                                           class="make-switch"
                                                                                           data-on-color="primary"
                                                                                           data-off-color="info"
                                                                                           data-size="small"
                                                                                           data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                    <a class="popovers ml12 mb-5"
                                                                                       data-container="body"
                                                                                       data-trigger="hover"
                                                                                       data-html="true"
                                                                                       data-placement="right"
                                                                                       data-content="<?php echo $LANG['UI_DB_ARCHIVE_STORAGE_ALARM_TIPS'] ?>">
                                                                                        <i class="viconfont vicon-tishi"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div>
                                                                            <!-- POSTGRES-归档日志告警配置 -->
                                                                            <div class="form-group advancedDiv warnningdiv display-none">
                                                                                <label class="control-label col-md-3 noticetype form-group-label">
                                                                                    <?php echo $LANG['UI_STORAGE_ALERT_TYPE'] ?>
                                                                                </label>
                                                                                <div class="col-md-4">
                                                                                    <select class="form-control select2me"
                                                                                            name="noticetype">
                                                                                        <option value="1"><?php echo $LANG['UI_STORAGE_ALERT_PERCENT'] ?></option>
                                                                                        <option value="2"><?php echo $LANG['UI_STORAGE_ALERT_SIZE'] ?></option>
                                                                                    </select>
                                                                                    <div>
                                                                                        <span class="help-block "><?php echo $LANG['UI_STORAGE_ALERT_TIPS'] ?></span>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <!-- POSTGRES-告警阈值百分比 -->
                                                                            <div class="form-group advancedDiv warnningdiv display-none"
                                                                                 id='percentdiv'>
                                                                                <label class="control-label col-md-3 noticevalue">
                                                                                    <?php echo $LANG['UI_STORAGE_ALERT_THRESHOLD'] ?>
                                                                                </label>
                                                                                <div class="col-md-4">
                                                                                    <div id="spinnerpercent">
                                                                                        <div class="input-group spinner-group">
                                                                                            <input id="warningpercent"
                                                                                                   style="text-align: left;"
                                                                                                   class="spinner-input form-control"
                                                                                                   maxlength="2"
                                                                                                   onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}"
                                                                                                   onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'0')}else{this.value=this.value.replace(/\D/g,'')}">
                                                                                            <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                                <button type="button"
                                                                                                        class="btn spinner-up default">
                                                                                                    <i class="fa fa-angle-up"></i>
                                                                                                </button>
                                                                                                <button type="button"
                                                                                                        class="btn spinner-down default">
                                                                                                    <i class="fa fa-angle-down"></i>
                                                                                                </button>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div style="position: absolute; top: 7px; right: -12px; font-size: 14px">
                                                                                        %
                                                                                    </div>
                                                                                    <br>
                                                                                </div>
                                                                            </div>
                                                                            <!-- POSTGRES-告警阈值大小 -->
                                                                            <div class="form-group advancedDiv warnningdiv display-none"
                                                                                 id='sizediv'>
                                                                                <label class="control-label col-md-3 noticevalue">
                                                                                    <?php echo $LANG['UI_STORAGE_ALERT_THRESHOLD'] ?>
                                                                                </label>
                                                                                <div class="col-md-4">
                                                                                    <div id="spinnersize">
                                                                                        <div class="input-group spinner-group">
                                                                                            <input id="warningsize"
                                                                                                   style="text-align: left;"
                                                                                                   class="spinner-input form-control"
                                                                                                   maxlength="8"
                                                                                                   onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}"
                                                                                                   onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'0')}else{this.value=this.value.replace(/\D/g,'')}">
                                                                                            <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                                <button type="button"
                                                                                                        class="btn spinner-up default">
                                                                                                    <i class="fa fa-angle-up"></i>
                                                                                                </button>
                                                                                                <button type="button"
                                                                                                        class="btn spinner-down default">
                                                                                                    <i class="fa fa-angle-down"></i>
                                                                                                </button>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div style="position: absolute; top: 7px; right: -12px; font-size: 14px">
                                                                                        GB
                                                                                    </div>
                                                                                </div>
                                                                            </div><!-- //END: POSTGRES-告警阈值大小 -->
                                                                        </div>
                                                                        <!-- //END: advanced-detail-conf-district -->
                                                                    </div><!-- //END: tab_advanced_archivelog -->

                                                                    <!-- 异常处理 -->
                                                                    <div class="tab-pane fade"
                                                                         id="tab_advanced_warning">
                                                                        <div class="abnormal-handle-form"
                                                                             style="overflow: auto">
                                                                            <div style="height: 100%">
                                                                                <!-- Oracle-跳过不可访问文件 -->
                                                                                <div class="form-group advancedDiv oracleDiv display-none">
                                                                                    <label class="control-label col-md-3 form-group-label skipInaccessibleFileFlagLabel"
                                                                                           for="skipInaccessibleFileFlag">
                                                                                        <?php echo $LANG['UI_DB_ORACLE_BACKUP_SKIP_INACCESSIBLE_FILE'] ?>
                                                                                    </label>
                                                                                    <div class="col-md-4 form-group-content flex-items-center">
                                                                                        <input type="checkbox"
                                                                                               id="skipInaccessibleFileFlag"
                                                                                               class="make-switch"
                                                                                               data-on-color="primary"
                                                                                               data-off-color="info"
                                                                                               data-size="small"
                                                                                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                        <a class="popovers ml12 mb-5"
                                                                                           data-container="body"
                                                                                           data-trigger="hover"
                                                                                           data-html="true"
                                                                                           data-placement="right"
                                                                                           data-content="<?php echo $LANG['UI_DB_ORACLE_BACKUP_SKIP_INACCESSIBLE_FILE_TIPS'] ?>">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                                <!-- Oracle-跳过脱机文件 -->
                                                                                <div class="form-group advancedDiv oracleDiv display-none skipOfflineFileFlagDiv">
                                                                                    <label class="control-label col-md-3 form-group-label skipOfflineFileFlagLabel"
                                                                                           for="skipOfflineFileFlag">
                                                                                        <?php echo $LANG['UI_DB_ORACLE_BACKUP_SKIP_OFFLINE_FILE'] ?>
                                                                                    </label>
                                                                                    <div class="col-md-4 form-group-content flex-items-center">
                                                                                        <input type="checkbox"
                                                                                               id="skipOfflineFileFlag"
                                                                                               class="make-switch"
                                                                                               data-on-color="primary"
                                                                                               data-off-color="info"
                                                                                               data-size="small"
                                                                                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                        <a class="popovers ml12 mb-5"
                                                                                           data-container="body"
                                                                                           data-trigger="hover"
                                                                                           data-html="true"
                                                                                           data-placement="right"
                                                                                           data-content="<?php echo $LANG['UI_DB_ORACLE_BACKUP_SKIP_OFFLINE_FILE_TIPS'] ?>">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                                <!-- Oracle - 跳过数据文件坏块 -->
                                                                                <div class="form-group advancedDiv oracleDiv display-none skipDatafileBadBlockDiv">
                                                                                    <label class="control-label col-md-3 form-group-label skipDatafileBadBlockLabel"
                                                                                           for="skipDatafileBadBlock">
                                                                                        <?php echo $LANG['UI_DB_ORACLE_BACKUP_SKIP_DATAFILE_BAD_BLOCK'] ?>
                                                                                    </label>
                                                                                    <div class="col-md-4">
                                                                                        <select class="form-control select2me"
                                                                                                id="skipDatafileBadBlock"></select>
                                                                                    </div>
                                                                                    <div class="col-md-3 flex-items-center">
                                                                                        <button type="button"
                                                                                                class="btn btn-light-primary"
                                                                                                id="skipDatafileBadBlockBtn"><?php echo $LANG['UI_DB_ORACLE_BACKUP_SKIP_DATAFILE_BAD_BLOCK_ADD_BTN'] ?></button>
                                                                                        <a class="popovers ml12"
                                                                                           data-container="body"
                                                                                           data-trigger="hover"
                                                                                           data-html="true"
                                                                                           data-placement="right"
                                                                                           data-content="<?php echo $LANG['UI_DB_ORACLE_BACKUP_SKIP_DATAFILE_BAD_BLOCK_TIPS'] ?>">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                                <!-- //END skipDatafileBadBlockDiv -->
                                                                                <!-- Oracle - 跳过数据文件坏块-添加后的配置 -->
                                                                                <div class="form-group advancedDiv oracleDiv display-none skipDatafileBadBlockAfterConfigureDiv">
                                                                                    <div class="col-md-3"></div>
                                                                                    <div class="col-md-9">
                                                                                        <div id="skipDatafileBadBlockAfterConfigure"></div>
                                                                                    </div>
                                                                                </div>
                                                                                <!-- //END skipDatafileBadBlockAfterConfigureDiv -->
                                                                            </div>
                                                                        </div>
                                                                        <!-- //END: advanced-detail-conf-district -->
                                                                    </div><!-- //END: tab_advanced_warning -->

                                                                    <!-- 校验 -->
                                                                    <div class="tab-pane fade" id="tab_advanced_check">
                                                                        <div class="abnormal-handle-form">
                                                                            <!-- SQL Server-检查数据库 -->
                                                                            <div class="form-group advancedDiv sqlserverDiv display-none">
                                                                                <label class="control-label col-md-3 checkdblabel form-group-label">
                                                                                    <?php echo $LANG['UI_DB_CHECK_DATABASE'] ?>
                                                                                </label>
                                                                                <div class="col-md-4 form-group-content flex-items-center">
                                                                                    <input type="checkbox" id="checkdb"
                                                                                           checked class="make-switch"
                                                                                           data-on-color="primary"
                                                                                           data-off-color="info"
                                                                                           data-size="small"
                                                                                           data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                    <a class="popovers ml12 mb-5"
                                                                                       data-container="body"
                                                                                       data-trigger="hover"
                                                                                       data-html="true"
                                                                                       data-placement="right"
                                                                                       data-content="<?php echo $LANG['UI_DB_CHECK_DATA_OF_DATABASE'] ?>">
                                                                                        <i class="viconfont vicon-tishi"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div><!-- //END: SQL Server-检查数据库 -->

                                                                            <!-- SQL Server校验 -->
                                                                            <div class="form-group advancedDiv sqlserverDiv display-none">
                                                                                <label class="control-label col-md-3 checksumlabel form-group-label">
                                                                                    <?php echo $LANG['UI_DB_SQLSERVER_CHECK_SUM'] ?>
                                                                                </label>
                                                                                <div class="col-md-4 form-group-content flex-items-center">
                                                                                    <input type="checkbox" id="checksum"
                                                                                           checked class="make-switch"
                                                                                           data-on-color="primary"
                                                                                           data-off-color="info"
                                                                                           data-size="small"
                                                                                           data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                    <a class="popovers ml12 mb-5"
                                                                                       data-container="body"
                                                                                       data-trigger="hover"
                                                                                       data-html="true"
                                                                                       data-placement="right"
                                                                                       data-content="<?php echo $LANG['UI_DB_CHECK_DATABASE_AFTER_OPEN'] ?>">
                                                                                        <i class="viconfont vicon-tishi"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div><!-- //END: SQL Server校验 -->
                                                                        </div>
                                                                        <!-- //END: advanced-detail-conf-district -->
                                                                    </div><!-- //END: tab_advanced_check -->

                                                                    <!-- 有效数据 -->
                                                                    <div class="tab-pane fade"
                                                                         id="tab_advanced_validate_data">
                                                                        <div class="abnormal-handle-form">
                                                                        </div>
                                                                        <!-- //END: advanced-detail-conf-district -->
                                                                    </div><!-- //END: tab_advanced_validate_data -->

                                                                    <!-- 快照 -->
                                                                    <div class="tab-pane fade"
                                                                         id="tab_advanced_snapshot">
                                                                        <div class="abnormal-handle-form">
                                                                        </div>
                                                                        <!-- //END: advanced-detail-conf-district -->
                                                                    </div><!-- //END: tab_advanced_snapshot -->

                                                                    <!-- 增量 -->
                                                                    <div class="tab-pane fade"
                                                                         id="tab_advanced_increment">
                                                                        <div class="abnormal-handle-form">
                                                                        </div>
                                                                        <!-- //END: advanced-detail-conf-district -->
                                                                    </div><!-- //END: tab_advanced_increment -->

                                                                    <!-- 性能优化 -->
                                                                    <div class="tab-pane fade"
                                                                         id="tab_advanced_performance">
                                                                        <div class="abnormal-handle-form">
                                                                            <!-- 数据块大小 -->
                                                                            <div class="form-group advancedDiv blocksizediv display-none">
                                                                                <label class="control-label col-md-3 blocksizelabel form-group-label">
                                                                                    <?php echo $LANG['UI_BACKUP_BLOCK_SIZE'] ?>
                                                                                </label>
                                                                                <div class="col-md-4 form-group-content flex-items-center">
                                                                                    <select class="form-control select2me"
                                                                                            id="blocksize">
                                                                                        <!-- <option value="64">64 KB</option>
                                                                                        <option value="128">128 KB</option>
                                                                                        <option value="256">256 KB</option>
                                                                                        <option value="512">512 KB</option>
                                                                                        <option value="2048">2048 KB</option>-->
                                                                                        <option value="1024"
                                                                                                selected="selected">1024
                                                                                            KB
                                                                                        </option>
                                                                                    </select>
                                                                                    <a class="popovers ml12 mb-5"
                                                                                       data-container="body"
                                                                                       data-trigger="hover"
                                                                                       data-placement="right"
                                                                                       data-content="<?php echo $LANG['UI_BACKUP_BLOCK_SIZE_TIPS'] ?> ">
                                                                                        <i class="viconfont vicon-tishi"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div><!-- //END: 数据块大小 -->

                                                                            <!-- SQL Server压缩 -->
                                                                            <div class="form-group advancedDiv sqlserverDiv display-none">
                                                                                <label class="control-label col-md-3 sqlservercompresslabel form-group-label">
                                                                                    <?php echo $LANG['UI_DB_SQLSERVER_COMPRESS'] ?>
                                                                                </label>
                                                                                <div class="col-md-4 form-group-content flex-items-center">
                                                                                    <input type="checkbox"
                                                                                           id="sqlservercompress"
                                                                                           class="make-switch"
                                                                                           data-on-color="primary"
                                                                                           data-off-color="info"
                                                                                           data-size="small"
                                                                                           data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                    <a class="popovers ml12 mb-5"
                                                                                       data-container="body"
                                                                                       data-trigger="hover"
                                                                                       data-html="true"
                                                                                       data-placement="right"
                                                                                       data-content="<?php echo $LANG['UI_BACKUP_COMPRESS_TIPS_SQLSERVER'] ?>">
                                                                                        <i class="viconfont vicon-tishi"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div><!-- //END: SQL Server压缩 -->

                                                                            <!-- Oracle压缩 -->
                                                                            <div class="form-group advancedDiv oracleDiv display-none">
                                                                                <label class="control-label col-md-3 oraclecompresslabel form-group-label">
                                                                                    <?php echo $LANG['UI_DB_ORACLE_COMPRESS'] ?>
                                                                                </label>
                                                                                <div class="col-md-9">
                                                                                    <input type="checkbox"
                                                                                           id="oraclecompress"
                                                                                           class="make-switch"
                                                                                           data-on-color="primary"
                                                                                           data-off-color="info"
                                                                                           data-size="small"
                                                                                           data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                    <span class="help-block"><?php echo $LANG['UI_BACKUP_COMPRESS_TIPS_ORACLE'] ?></span>
                                                                                </div>
                                                                            </div>
                                                                            <!-- Oracle压缩-分割线 -->
                                                                            <div class="form-group advancedDiv oracleDiv display-none dbSeparatorCls"></div>
                                                                            <!-- 设置filesperset配置 -->
                                                                            <div class="form-group advancedDiv oracleDiv display-none">
                                                                                <label class="control-label col-md-3 form-group-label oracleFilesPersetLabel">
                                                                                    <?php echo $LANG['UI_DB_ORACLE_FILES_PERSET'] ?>
                                                                                </label>
                                                                                <div class="col-md-9">
                                                                                    <input type="checkbox"
                                                                                           id="oracleFilesPerset"
                                                                                           class="make-switch"
                                                                                           data-on-color="primary"
                                                                                           data-off-color="info"
                                                                                           data-size="small"
                                                                                           data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                    <span class="help-block"><?php echo $LANG['UI_BACKUP_FILESPERSET_TIPS_ORACLE'] ?></span>
                                                                                </div>
                                                                            </div>
                                                                            <!-- 数据文件filesperset个数 -->
                                                                            <div class="form-group advancedDiv oracleDiv display-none"
                                                                                 id="oracleFilesPersetDatafileDiv">
                                                                                <label class="control-label col-md-3 form-group-label oracleFilesPersetDatafileLabel">
                                                                                    <?php echo $LANG['UI_DB_ORACLE_FILES_PERSET_DATAFILE'] ?>
                                                                                </label>
                                                                                <div class="col-md-4">
                                                                                    <div id="oracleFilesPersetDatafileSpinner">
                                                                                        <div class="input-group spinner-group">
                                                                                            <input id="oracleFilesPersetDatafile"
                                                                                                   onkeyup="value=value.replace(/[^\d]/g,'')"
                                                                                                   class="spinner-input form-control">
                                                                                            <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                                <button type="button"
                                                                                                        class="btn spinner-up default">
                                                                                                    <i class="fa fa-angle-up"></i>
                                                                                                </button>
                                                                                                <button type="button"
                                                                                                        class="btn spinner-down default">
                                                                                                    <i class="fa fa-angle-down"></i>
                                                                                                </button>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <!-- 归档日志文件filesperset个数 -->
                                                                            <div class="form-group advancedDiv oracleDiv display-none"
                                                                                 id="oracleFilesPersetArchivelogDiv">
                                                                                <label class="control-label col-md-3 form-group-label oracleFilesPersetArchivelogDivLabel"
                                                                                       style="padding-left: 0">
                                                                                    <?php echo $LANG['UI_DB_ORACLE_FILES_PERSET_ARCHIVELOG'] ?>
                                                                                </label>
                                                                                <div class="col-md-4">
                                                                                    <div id="oracleFilesPersetArchivelogSpinner">
                                                                                        <div class="input-group spinner-group">
                                                                                            <input id="oracleFilesPersetArchivelog"
                                                                                                   onkeyup="value=value.replace(/[^\d]/g,'')"
                                                                                                   class="spinner-input form-control">
                                                                                            <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                                <button type="button"
                                                                                                        class="btn spinner-up default">
                                                                                                    <i class="fa fa-angle-up"></i>
                                                                                                </button>
                                                                                                <button type="button"
                                                                                                        class="btn spinner-down default">
                                                                                                    <i class="fa fa-angle-down"></i>
                                                                                                </button>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <!-- Oracle设置filesperset配置-分割线 -->
                                                                            <div class="form-group advancedDiv oracleDiv display-none dbSeparatorCls"></div>

                                                                            <!-- Oracle - BCT配置 -->
                                                                            <div class="form-group advancedDiv oracleDiv display-none enableBctFlagDiv">
                                                                                <label class="control-label col-md-3 form-group-label enableBctFlagLabel"
                                                                                       for="enableBctFlag">
                                                                                    <?php echo $LANG['UI_DB_ORACLE_ENABLE_BCT_FLAG'] ?>
                                                                                </label>
                                                                                <div class="col-md-9">
                                                                                    <input type="checkbox"
                                                                                           id="enableBctFlag"
                                                                                           class="make-switch"
                                                                                           data-on-color="primary"
                                                                                           data-off-color="info"
                                                                                           data-size="small"
                                                                                           data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                    <span class="help-block"><?php echo $LANG['UI_DB_ORACLE_ENABLE_BCT_FLAG_TIPS'] ?></span>
                                                                                </div>
                                                                            </div>
                                                                            <!-- Oracle BCT配置-分割线 -->
                                                                            <div class="form-group advancedDiv oracleDiv display-none enableBctFlagDiv dbSeparatorCls"></div>

                                                                            <!-- Oracle - 多段传输 -->
                                                                            <div class="form-group advancedDiv oracleDiv display-none setSectionSizeFlagDiv">
                                                                                <label class="control-label col-md-3 form-group-label setSectionSizeFlagLabel"
                                                                                       for="setSectionSizeFlag">
                                                                                    <?php echo $LANG['UI_DB_ORACLE_SET_SECTION_SIZE_FLAG'] ?>
                                                                                </label>
                                                                                <div class="col-md-9">
                                                                                    <input type="checkbox"
                                                                                           id="setSectionSizeFlag"
                                                                                           class="make-switch"
                                                                                           data-on-color="primary"
                                                                                           data-off-color="info"
                                                                                           data-size="small"
                                                                                           data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                    <span class="help-block"><?php echo $LANG['UI_DB_ORACLE_SET_SECTION_SIZE_FLAG_TIPS'] ?></span>
                                                                                </div>
                                                                            </div>

                                                                            <!-- Oracle - 分块大小 -->
                                                                            <div class="form-group advancedDiv oracleDiv display-none sectionSizeDiv">
                                                                                <label class="control-label col-md-3 form-group-label sectionSizeLabel"
                                                                                       for="sectionSize">
                                                                                    <?php echo $LANG['UI_DB_ORACLE_SET_SECTION_SIZE'] ?>
                                                                                </label>
                                                                                <div class="col-md-4 form-group-content">
                                                                                    <div class="sectionSizeSpinner">
                                                                                        <div class="input-group spinner-group">
                                                                                            <input id="sectionSize"
                                                                                                   onkeyup="value=value.replace(/[^\d]/g,'')"
                                                                                                   class="spinner-input form-control">
                                                                                            <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                                <button type="button"
                                                                                                        class="btn spinner-up default">
                                                                                                    <i class="fa fa-angle-up"></i>
                                                                                                </button>
                                                                                                <button type="button"
                                                                                                        class="btn spinner-down default">
                                                                                                    <i class="fa fa-angle-down"></i>
                                                                                                </button>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div style="position: absolute; top: 0; right: -12px; font-size: 14px;">
                                                                                        GB
                                                                                    </div>
                                                                                </div>
                                                                            </div><!-- //END: sectionSizeDiv -->
                                                                            <!-- Oracle 多段传输-分割线 -->
                                                                            <div class="form-group advancedDiv oracleDiv display-none setSectionSizeFlagDiv dbSeparatorCls"></div>

                                                                            <!-- Oracle - 自定义RMAN命令 -->
                                                                            <div class="form-group advancedDiv oracleDiv display-none customRmanCmdDiv">
                                                                                <label class="control-label col-md-3 form-group-label customRmanCmdLabel"
                                                                                       for="customRmanCmd">
                                                                                    <?php echo $LANG['UI_DB_ORACLE_CUSTOM_RMAN_CMD'] ?>
                                                                                </label>
                                                                                <div class="col-md-9">
                                                                                    <textarea class="form-control"
                                                                                              id="customRmanCmd"
                                                                                              rows="5"></textarea>
                                                                                    <!-- <input type="text" class="form-control" id="customRmanCmd" /> -->
                                                                                    <span class="help-block "><?php echo $LANG['UI_DB_ORACLE_CUSTOM_RMAN_CMD_TIPS'] ?></span>
                                                                                </div>
                                                                            </div><!-- //END: customRmanCmdDiv -->

                                                                            <!-- MySQL - 线程数 -->
                                                                            <div class="form-group advancedDiv mysqlParallelDiv display-hide">
                                                                                <label class="control-label col-md-3 mysqlParallelLabel form-group-label">
                                                                                    <?php echo $LANG['UI_DB_MYSQL_PARALLEL'] ?>
                                                                                </label>
                                                                                <div class="col-md-4 form-group-content">
                                                                                    <div id="mysqlParallelSpinner">
                                                                                        <div class="input-group spinner-group">
                                                                                            <input id="mysqlParallel"
                                                                                                   onkeyup="value=value.replace(/[^\d]/g,'')"
                                                                                                   class="spinner-input form-control">
                                                                                            <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                                <button type="button"
                                                                                                        class="btn spinner-up default">
                                                                                                    <i class="fa fa-angle-up"></i>
                                                                                                </button>
                                                                                                <button type="button"
                                                                                                        class="btn spinner-down default">
                                                                                                    <i class="fa fa-angle-down"></i>
                                                                                                </button>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div><!-- //END: MySQL - 线程数 -->

                                                                            <!-- MySQL - 源端压缩 -->
                                                                            <div class="form-group advancedDiv mysqlSrcCompressedDiv display-hide">
                                                                                <label class="control-label col-md-3 mysqlSrcCompressedLabel form-group-label">
                                                                                    <?php echo $LANG['UI_DB_MYSQL_SRC_COMPRESSED'] ?>
                                                                                </label>
                                                                                <div class="col-md-4 form-group-content">
                                                                                    <input type="checkbox"
                                                                                           id="mysqlSrcCompressed"
                                                                                           class="make-switch"
                                                                                           data-on-color="primary"
                                                                                           data-off-color="info"
                                                                                           data-size="small">
                                                                                </div>
                                                                            </div><!-- //END: MySQL - 源端压缩 -->

                                                                            <!-- DM压缩 -->
                                                                            <div class="form-group advancedDiv dmDiv display-none">
                                                                                <label class="control-label col-md-3 dmcompresslabel form-group-label">
                                                                                    <?php echo $LANG['UI_DB_DM_COMPRESS'] ?>
                                                                                </label>
                                                                                <div class="col-md-4 form-group-content flex-items-center">
                                                                                    <input type="checkbox"
                                                                                           id="dmcompress"
                                                                                           class="make-switch"
                                                                                           data-on-color="primary"
                                                                                           data-off-color="info"
                                                                                           data-size="small"
                                                                                           data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                    <a class="popovers ml12 mb-5"
                                                                                       data-container="body"
                                                                                       data-trigger="hover"
                                                                                       data-html="true"
                                                                                       data-placement="right"
                                                                                       data-content="<?php echo $LANG['UI_BACKUP_COMPRESS_TIPS'] ?>">
                                                                                        <i class="viconfont vicon-tishi"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div><!-- //END: DM压缩 -->
                                                                            <!-- DM-压缩等级 -->
                                                                            <div class="form-group advancedDiv dmCompressLevelDiv display-none">
                                                                                <label class="control-label col-md-3 dmCompressLevelLabel form-group-label">
                                                                                    <?php echo $LANG['UI_DB_DM_COMPRESS_LEVEL'] ?>
                                                                                </label>
                                                                                <div class="col-md-4">
                                                                                    <div id="dmCompressLevelSpinner">
                                                                                        <div class="input-group spinner-group">
                                                                                            <input id="dmCompressLevel"
                                                                                                   class="spinner-input form-control"
                                                                                                   onkeyup="value=value.replace(/[^\d]/g,'')">
                                                                                            <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                                <button type="button"
                                                                                                        class="btn spinner-up default">
                                                                                                    <i class="fa fa-angle-up"></i>
                                                                                                </button>
                                                                                                <button type="button"
                                                                                                        class="btn spinner-down default">
                                                                                                    <i class="fa fa-angle-down"></i>
                                                                                                </button>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <a class="popovers popover-abs-position"
                                                                                       data-container="body"
                                                                                       data-trigger="hover"
                                                                                       data-html="true"
                                                                                       data-placement="right"
                                                                                       data-content="<?php echo $LANG['UI_DB_DM_COMPRESS_LEVEL_TIPS'] ?>">
                                                                                        <i class="viconfont vicon-tishi"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div><!-- //END: DM-压缩等级 -->

                                                                            <!-- POSTGRES-源端压缩 -->
                                                                            <div class="form-group advancedDiv display-none">
                                                                                <label class="control-label col-md-3 srccompresslabel form-group-label">
                                                                                    <?php echo $LANG['UI_DB_SRC_COMPRESS'] ?>
                                                                                </label>
                                                                                <div class="col-md-4 form-group-content">
                                                                                    <select class="form-control select2me"
                                                                                            id="srccompress">
                                                                                        <option value="0"
                                                                                                selected="selected"><?php echo $LANG['UI_PUBLIC_OFF_TWO'] ?></option>
                                                                                        <option value="1">Gzip</option>
                                                                                        <option value="2">LZMA2</option>
                                                                                    </select>
                                                                                </div>
                                                                            </div><!-- //END: POSTGRES-源端压缩 -->

                                                                            <!-- SAP HANA压缩 -->
                                                                            <div class="form-group advancedDiv sapHanaDiv display-hide">
                                                                                <label class="control-label col-md-3 sapHanaCompressLabel form-group-label">
                                                                                    <?php echo $LANG['UI_DB_SAPHANA_COMPRESS'] ?>
                                                                                </label>
                                                                                <div class="col-md-4 form-group-content flex-items-center">
                                                                                    <input type="checkbox"
                                                                                           id="sapHanaCompress"
                                                                                           class="make-switch"
                                                                                           data-on-color="primary"
                                                                                           data-off-color="info"
                                                                                           data-size="small"
                                                                                           data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                    <a class="popovers ml12 mb-5"
                                                                                       data-container="body"
                                                                                       data-trigger="hover"
                                                                                       data-html="true"
                                                                                       data-placement="right"
                                                                                       data-content="<?php echo $LANG['UI_DB_SAPHANA_COMPRESS_TIPS'] ?>">
                                                                                        <i class="viconfont vicon-tishi"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div><!-- //END: SAP HANA压缩 -->
                                                                            <!-- MongoDB-客户端并行数量 -->
                                                                            <div class="form-group advancedDiv parallelNumDiv display-hide">
                                                                                <label class="control-label col-md-3 parallelNumLabel">
                                                                                    <?php echo $LANG['UI_DB_CLIENT_PARALLEL_NUM'] ?>
                                                                                </label>
                                                                                <div class="col-md-4">
                                                                                    <div id="parallelNumSpinner">
                                                                                        <div class="input-group spinner-group">
                                                                                            <input id="parallelNum"
                                                                                                   class="spinner-input form-control"
                                                                                                   onkeyup="value=value.replace(/[^\d]/g,'')">
                                                                                            <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                                <button type="button"
                                                                                                        class="btn spinner-up default">
                                                                                                    <i class="fa fa-angle-up"></i>
                                                                                                </button>
                                                                                                <button type="button"
                                                                                                        class="btn spinner-down default">
                                                                                                    <i class="fa fa-angle-down"></i>
                                                                                                </button>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <a class="popovers popover-abs-position"
                                                                                       data-container="body"
                                                                                       data-trigger="hover"
                                                                                       data-html="true"
                                                                                       data-placement="right"
                                                                                       data-content="<?php echo $LANG['UI_DB_CLIENT_PARALLEL_NUM_TIPS'] ?>">
                                                                                        <i class="viconfont vicon-tishi"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div><!--//parallelNumDiv-->

                                                                            <!-- TiDB - 压缩算法 -->
                                                                            <div class="form-group advancedDiv tidbDiv display-hide">
                                                                                <label class="control-label col-md-3 tidbCompressMethodLabel form-group-label">
                                                                                    <?php echo $LANG['UI_DB_TIDB_COMPRESS_METHOD'] ?>
                                                                                </label>
                                                                                <div class="col-md-4 form-group-content">
                                                                                    <select class="form-control select2me"
                                                                                            id="tidbCompressMethod">
                                                                                        <option value="1">lz4</option>
                                                                                        <option value="2">snappy
                                                                                        </option>
                                                                                        <option value="3">zstd</option>
                                                                                    </select>
                                                                                    <a class="popovers popover-abs-position mt-0"
                                                                                       data-container="body"
                                                                                       data-trigger="hover"
                                                                                       data-html="true"
                                                                                       data-placement="right"
                                                                                       data-content="<?php echo $LANG['UI_DB_TIDB_COMPRESS_METHOD_TIPS'] ?>">
                                                                                        <i class="viconfont vicon-tishi"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div><!-- //END: TiDB - 压缩算法 -->
                                                                            <!-- TiDB - 压缩等级 -->
                                                                            <div class="form-group advancedDiv tidbDiv tidbCompressLevelDiv display-hide">
                                                                                <label class="control-label col-md-3 tidbCompressLevelLabel form-group-label">
                                                                                    <?php echo $LANG['UI_DB_TIDB_COMPRESS_LEVEL'] ?>
                                                                                </label>
                                                                                <div class="col-md-4 form-group-content">
                                                                                    <div id="tidbCompressLevelSpinner">
                                                                                        <div class="input-group spinner-group">
                                                                                            <input id="tidbCompressLevel"
                                                                                                   onkeyup="value=value.replace(/[^\d]/g,'')"
                                                                                                   class="spinner-input form-control">
                                                                                            <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                                <button type="button"
                                                                                                        class="btn spinner-up default">
                                                                                                    <i class="fa fa-angle-up"></i>
                                                                                                </button>
                                                                                                <button type="button"
                                                                                                        class="btn spinner-down default">
                                                                                                    <i class="fa fa-angle-down"></i>
                                                                                                </button>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <a class="popovers popover-abs-position mt-0"
                                                                                       data-container="body"
                                                                                       data-trigger="hover"
                                                                                       data-html="true"
                                                                                       data-placement="right"
                                                                                       data-content="<?php echo $LANG['UI_DB_TIDB_COMPRESS_LEVEL_TIPS'] ?>">
                                                                                        <i class="viconfont vicon-tishi"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div><!-- //END: TiDB - 压缩等级 -->
                                                                        </div>
                                                                        <!-- //END: advanced-detail-conf-district -->
                                                                    </div><!-- //END: tab_advanced_performance -->

                                                                    <!-- 重试 -->
                                                                    <div class="tab-pane fade"
                                                                         style="margin-left: 20px;"
                                                                         id="tab_advanced_retry">
                                                                    </div><!-- //END: tab_advanced_retry -->

                                                                    <!-- 过载保护 -->
                                                                    <div class="tab-pane fade"
                                                                         id="tab_advanced_overload">
                                                                        <div class="abnormal-handle-form">
                                                                            <!-- 忽略节点资源限制 -->
                                                                            <div class="form-group advancedDiv ignoreResourceLimitDiv">
                                                                                <label for="ignoreResourceLimit"
                                                                                       class="control-label col-md-3 ignoreResourceLimitLabel form-group-label">
                                                                                    <?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE'] ?>
                                                                                </label>
                                                                                <div class="col-md-3 form-group-content flex-items-center">
                                                                                    <input type="checkbox"
                                                                                           id="ignoreResourceLimit"
                                                                                           class="make-switch"
                                                                                           data-on-color="primary"
                                                                                           data-off-color="info"
                                                                                           data-size="small"
                                                                                           data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                    <a class="popovers ml12 mb-5"
                                                                                       data-container="body"
                                                                                       data-trigger="hover"
                                                                                       data-html="true"
                                                                                       data-placement="right"
                                                                                       data-content="<?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE_TIPS'] ?>">
                                                                                        <i class="viconfont vicon-tishi"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div>
                                                                            <!-- 任务最大并发数 + 任务禁止运行时间段-->
                                                                            <div class="form-group node-limit-form">
                                                                                <div class="table-container col-md-offset-3 pl15">
                                                                                    <table class="table table-hover table-borderless"
                                                                                           id="nodeLimitTable">
                                                                                    </table>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <!-- //END: advanced-detail-conf-district -->
                                                                    </div><!-- //END: tab_advanced_overload -->
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div><!--//tab_other-->
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane" id="tab4">
                                    <div class="tab-pane__body">
                                        <div class="tab-pane__body__form">
                                            <!-- 开始 - 任务名 -->
                                            <div class="alert alert-danger display-none jobnametip"></div>
                                            <div class="form-group mb0 mt10 jobNameDiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_JOB_RNAME'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <div class="input-icon right">
                                                        <i class="fa"></i>
                                                        <input type="text" maxlength="64" class="form-control"
                                                               id="jobname"
                                                               onkeyup="customInputValidate('string', this.value, $(this))"/>
                                                        <span class="help-block "><?php echo $LANG['UI_JOB_RNAME_TIPS'] ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- TiDB-完全备份任务名 -->
                                            <div class="form-group mb0 mt10 tidbFullJobNameDiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_DB_TIDB_FULL_JOB_RNAME'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <div class="input-icon right">
                                                        <i class="fa"></i>
                                                        <input type="text" maxlength="64" class="form-control"
                                                               id="tidbFullJobName"
                                                               onkeyup="customInputValidate('string', this.value, $(this))"/>
                                                        <span class="help-block "><?php echo $LANG['UI_DB_TIDB_FULL_JOB_RNAME_TIPS'] ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- TiDB-日志备份任务名 -->
                                            <div class="form-group mb0 mt10 tidbLogJobNameDiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_DB_TIDB_LOG_JOB_RNAME'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <div class="input-icon right">
                                                        <i class="fa"></i>
                                                        <input type="text" maxlength="64" class="form-control"
                                                               id="tidbLogJobName"
                                                               onkeyup="customInputValidate('string', this.value, $(this))"/>
                                                        <span class="help-block "><?php echo $LANG['UI_DB_TIDB_LOG_JOB_RNAME_TIPS'] ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- 结束 - 任务名 -->
                                            <h4 class="form-section"></h4>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_SOURCE'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static dbshow">
                                                    </p>
                                                    <ul class="ztree" id="dbShowTree"
                                                        style="border: 1px solid #E6E6E6"></ul>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_DB_TYPE'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static dbTypeShow">
                                                    </p>
                                                </div>
                                            </div>

                                            <!-- 备份目的地 -->
                                            <h4 class="form-section"></h4>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_COPY_BACK_TARGET_STORAGE'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static  storageInfoShow">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_COPY_BACK_TARGET_NODE'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static nodeInfoShow">
                                                    </p>
                                                </div>
                                            </div>
                                            <h4 class="form-section"></h4>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TYPE'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static backuptypeshow">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mt10 backupTypeInfoDiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_STRATEGY_TIME'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static backuptypeinfoshow">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mt10 speedlimitDiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static speedlimitshow">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_STORAGE_STRATEGY'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static storageinfoshow">
                                                    </p>
                                                </div>
                                            </div>
                                            <!-- 保留策略 -->
                                            <div class="form-group mb0 mt10 reserveDiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_RESERVE_STRATEGY'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static reservetypeshow">
                                                    </p>
                                                </div>
                                            </div>
                                            <!-- 传输策略 -->
                                            <div class="form-group mb0 mt10" id="transportmodeshowdiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static transportinfoshow">
                                                    </p>
                                                    <!--											<p class="form-control-static colorgreen applianceshow">-->
                                                    <!--											</p>-->
                                                </div>
                                            </div>
                                            <!-- 脚本配置 -->
                                            <div class="form-group mb0 mt10" id="scriptConfigShowDiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_PUBLIC_SCRIPT_CONFIGURE'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static scriptConfigShow"></p>
                                                </div>
                                            </div>
                                            <!-- 安全策略 -->
                                            <div class="form-group mb0 mt10" id="safeStrategyShowDiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static safeStrategyShow"></p>
                                                </div>
                                            </div>
                                            <!-- 高级配置 -->
                                            <!-- 归档日志 -->
                                            <div class="form-group mb0 mt10 advancedCategoryShow advancedArchivelogShowDiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_DB_BACKUP_ADVANCED_TAB_ARCHIVELOG'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static advancedArchivelogShow">
                                                    </p>
                                                </div>
                                            </div><!--//END advancedArchivelogShowDiv-->
                                            <!-- 异常处理 -->
                                            <div class="form-group mb0 mt10 advancedCategoryShow advancedWarningShowDiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_DB_BACKUP_ADVANCED_TAB_WARNING'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static advancedWarningShow">
                                                    </p>
                                                </div>
                                            </div><!--//END advancedWarningShowDiv-->
                                            <!-- 校验 -->
                                            <div class="form-group mb0 mt10 advancedCategoryShow advancedCheckShowDiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_DB_BACKUP_ADVANCED_TAB_CHECK'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static advancedCheckShow">
                                                    </p>
                                                </div>
                                            </div><!--//END advancedCheckShowDiv-->
                                            <!-- 有效数据 -->
                                            <div class="form-group mb0 mt10 advancedCategoryShow advancedValidateDataShowDiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_DB_BACKUP_ADVANCED_TAB_VALIDATE_DATA'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static advancedValidateDataShow">
                                                    </p>
                                                </div>
                                            </div><!--//END advancedValidateDataShowDiv-->
                                            <!-- 快照 -->
                                            <div class="form-group mb0 mt10 advancedCategoryShow advancedSnapshotShowDiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_DB_BACKUP_ADVANCED_TAB_SNAPSHOT'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static advancedSnapshotShow">
                                                    </p>
                                                </div>
                                            </div><!--//END advancedSnapshotShowDiv-->
                                            <!-- 增量 -->
                                            <div class="form-group mb0 mt10 advancedCategoryShow advancedIncrementShowDiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_DB_BACKUP_ADVANCED_TAB_INCREMENT'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static advancedIncrementShow">
                                                    </p>
                                                </div>
                                            </div><!--//END advancedIncrementShowDiv-->
                                            <!-- 性能优化 -->
                                            <div class="form-group mb0 mt10 advancedCategoryShow advancedPerformanceShowDiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_DB_BACKUP_ADVANCED_TAB_PERFORMANCE'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static advancedPerformanceShow">
                                                    </p>
                                                </div>
                                            </div><!--//END advancedPerformanceShowDiv-->
                                            <!-- 重试 -->
                                            <div class="form-group mb0 mt10 advancedCategoryShow retryShow">
                                            </div><!--//END retryShow-->
                                            <!-- 过载保护 -->
                                            <div class="form-group mb0 mt10 advancedCategoryShow advancedOverloadShowDiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_STRATEGY_OVER_LOAD_LABEL'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static advancedOverloadShow">
                                                    </p>
                                                </div>
                                            </div><!--//END advancedOverloadShowDiv-->
                                        </div>
                                    </div>
                                </div><!--//tab4-->
                            </div>
                        </div>
                        <div class="form-actions form-actions--create">
                            <div class="row">
                                <div class="col-md-offset-6 col-md-6">
                                    <a href="javascript:;" class="btn default button-previous">
                                        <i class="viconfont vicon-shangyibu"></i> <?php echo $LANG['UI_PUBLIC_PREV_STEP'] ?>
                                    </a>
                                    <a href="javascript:;" class="btn green-turquoise button-next next-btn-margin-left">
                                        <?php echo $LANG['UI_PUBLIC_NEXT_STEP'] ?> <i
                                                class="viconfont vicon-xiayibu"></i>
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
        <div id="speedlimitModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first"
             data-backdrop="static">
            <div class="modal-header ">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title"><i
                            class="iconfont icon-xiansucelve"></i> <?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="portlet-body">

                    <div class="form-group ">
                        <label class="control-label col-md-2"> <?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_TYPE'] ?>
                        </label>
                        <div class="col-md-6 flex-items-center">
                            <select class="form-control select2me inline-block" id="speedModeType" style="width:203px;">
                                <option value="1"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_STRATEGY'] ?></option>
                                <option value="2"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_FOREVER'] ?></option>
                            </select>
                            <a class="popovers ml12" data-container="body" data-trigger="hover" data-placement="right"
                               data-content="<?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_TYPE_TIPS'] ?>">
                                <i class="viconfont vicon-tishi"></i>
                            </a>
                        </div>
                    </div>

                    <div class="form-group setSpeedStrategy">
                        <label class="control-label col-md-2"> <?php echo $LANG['UI_BACKUP_SET_STRATEGY'] ?>
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
                                <div class="input-icon">
                                    <div id="speedSpinnerNum">
                                        <div class="input-group spinner-group">
                                            <input id="speedSpinnerNumInput" style="text-align: left;"
                                                   onkeyup="value=value.replace(/[^\d]/g,'')"
                                                   class="spinner-input form-control">
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
                                    <select id="unit"
                                            style="width: 80px; margin-left: 10px;height: 33px;text-align:center; border: 1px solid #E6E6E6;">
                                        <option value="1">KB/s</option>
                                        <option selected value="2">MB/s</option>
                                        <option value="3">GB/s</option>
                                    </select>
                                </div>
                                <a style="display:block; margin-top:8px;" class="popovers ml12" data-container="body"
                                   data-trigger="hover" data-placement="right"
                                   data-content="<?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_VALUE_TIPS'] ?>">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
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
                        id="speed_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
            </div>
        </div>
        <!-- END RATE LIMIT MODAL -->

        <!-- BEGIN SKIP DATAFILE BAD BLOCK MODAL -->
        <div id="skipDatafileBadBlockModal" class="modal xmodal fade form-horizontal" tabindex="-1"
             data-backdrop="static"
             data-uuid="" data-name="" data-table-space-index="" data-table-space-name="">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title">
                    <i class="viconfont vicon-gaojipeizhi"></i>
                    <span class="text"><?php echo $LANG['UI_DB_BACKUP_ORACLE_CONFIG_SKIP_BAD_BLOCK_NUM'] ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="portlet-body">
                    <div class="table-toolbar">
                        <div class="vin_toolbar mb-0" id="vin_skip_datafile_bad_block_toolbar">
                            <div class="rightTool">
                                <div class="batch-config">
                                    <span class="text"><?php echo $LANG['UI_DB_BACKUP_ORACLE_CONFIG_SKIP_BAD_BLOCK_BATCH'] ?></span>
                                    <input type="number" id="batchBadBlockNum" value="1"
                                           onkeyup="value=value.replace(/[^\d]/g,'')"
                                           class="form-control bad-block-num-input"/>
                                    <button type="button" class="btn btn-light-primary"
                                            id="batchBadBlockApply"><?php echo $LANG['UI_DB_BACKUP_ORACLE_CONFIG_SKIP_BAD_BLOCK_APPLY'] ?></button>
                                </div>
                            </div>
                            <div class="leftTool"></div>
                        </div>
                    </div>

                    <div class="table-container">
                        <table class="table" id="skipDatafileBadBlockTable"></table>
                    </div>
                </div>
            </div>

            <!-- modal-footer -->
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-default">
                    <?php echo $LANG['UI_PUBLIC_NO'] ?>
                </button>
                <button type="button" class="btn btn-primary" id="skipDatafileBadBlockSubmit">
                    <?php echo $LANG['UI_PUBLIC_YES'] ?>
                </button>
            </div>
        </div>
        <!-- END SKIP DATAFILE BAD BLOCK MODAL -->
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

<script type="text/javascript"
        src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript"
        src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<?php
if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
    echo '<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>';
}
?>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/ace/src-min-noconflict/ace.js"></script>
<script type="text/javascript" src="./scripts/public/initComponents.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.speedStrategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.dbStrategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/calendar/jquery.searchableSelect.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.taskCrowd.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.backupStrategy.js"></script>
<script type="text/javascript" src="./scripts/platform/component/vinScript.js"></script>
<script type="text/javascript" src="./scripts/platform/component/backup_target.js"></script>
<script type="text/javascript" src="./scripts/platform/component/transfer_network.js"></script>
<script type="text/javascript" src="./scripts/platform/component/retry_strategy.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_worm.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_complete.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script src="./scripts/dbprotect/dbbackup.js" type="text/javascript"></script>