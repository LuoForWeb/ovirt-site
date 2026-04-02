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
<link rel="stylesheet" type="text/css" href="./css/vm/virtualiaztionIcon.css"/>
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
        <span class="display-none" id="servertime"></span>
        <div class="portlet box blue-hoki backup-page" id="cbrbackupcontent">
            <div class="portlet-title">
                <div class="caption">
                    <i class="levelchild viconfont vicon-xinjianbeifen1"></i><?php echo $LANG['UI_BACKUP_NEW_JOB'] ?>
                </div>
            </div>
            <div class="portlet-body form">
                <div action="#" class="form-horizontal" id="submit_form" method="POST">
                    <div class="form-wizard">
                        <div class="form-body">
                            <div class="form-body-content" >
                                <ul class="nav nav-pills steps">
                                    <li>
                                        <a href="#tab1" data-toggle="tab" class="step">
                                        <span class="number">
                                        1 </span>
                                        <span class="desc">
                                        <?php echo $LANG['UI_CBR_SYNC_SOURCE'] ?> <i class="fa fa-check"></i></span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#tab2" data-toggle="tab" class="step">
                                        <span class="number">
                                        2 </span>
                                        <span class="desc">
                                        <?php echo $LANG['UI_CBR_SYNC_TARGET'] ?> <i class="fa fa-check"></i></span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#tab3" data-toggle="tab" class="step active">
                                        <span class="number">
                                        3 </span>
                                        <span class="desc">
                                        <?php echo $LANG['UI_CBR_SYNC_STRATEGY'] ?><i class="fa fa-check"></i></span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#tab4" data-toggle="tab" class="step">
                                        <span class="number">
                                        4 </span>
                                        <span class="desc">
                                       <?php echo $LANG['UI_BACKUP_CONFIRM_CONFIG'] ?> <i class="fa fa-check"></i> </span>
                                        </a>
                                    </li>
                                </ul>
                                <div class="tab-content bakuptab">
                                    <div class="tab-pane active" id="tab1">
                                        <div class="alert alert-danger display-none selectvmtip">
                                        </div>
                                        <div class="row tab-pane__row">
                                            <div class="col-md-4 tab-pane__row__source">
                                                <div class="form-group src-wrap">
                                                    <div class="src-wrap__title">
                                                            <i class="viconfont vicon-tongbulidu" style="margin-right: 8px"></i>
                                                            <span> <?php echo $LANG['UI_CBR_SYNC_GRANULARITY_TIPS'] ?> </span>
                                                    </div>
                                                    <div class="src-wrap__content">
                                                        <div class="src-wrap__content__search cbr_tree_div">
                                                            <select class="bs-select width100p" data-show-subtext="true" id="asyncshowtype">
                                                                <option data-icon="cbrmpicon icon-default" value="1"><?php echo $LANG['UI_CBR_SYNC_BY_REPOSITORT'] ?></option>
                                                                <option data-icon="cbrvmicon icon-default" value="2"><?php echo $LANG['UI_CBR_SYNC_BY_RESOURCE'] ?></option>
                                                                <option data-icon="cbrtpicon icon-default" value="3"><?php echo $LANG['UI_CBR_SYNC_BY_TIMEPOINT'] ?></option>
                                                            </select>
                                                            <div class="vm_tree_div">
                                                                <div class="input-icon width100p">
                                                                    <input type="text" maxlength="128" placeholder="<?php echo $LANG['UI_COPY_SEARCH_AS_KEYWORD'] ?>" class="form-control display-none" id="searchcbr"/>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="src-wrap__content__ztree" >
                                                            <div class="three_tree" >
                                                                <ul id="cbr_tree_mp" class="ztree bd1de5  tree_div"></ul>
                                                                <ul id="cbr_tree_vm" class="ztree bd1de5  tree_div display-hide" ></ul>
                                                                <ul id="cbr_tree_tp" class="ztree bd1de5  tree_div display-hide" ></ul>
                                                            </div>
                                                            <div class="alert alert-block alert-info fade in display-hide"  id="nodatatips">
                                                                <button type="button" class="close" data-dismiss="alert"></button>
                                                                <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                                                <ol class = "alert-ol">
                                                                    <li>
                                                                    <?php echo $LANG['UI_CBR_SYNC_NOT_FIND_STORAGE_TIPS'] ?>
                                                                    </li>
                                                                    <li>
                                                                        <a id="toaddcbr"><?php echo $LANG['UI_CBR_SYNC_ADD_CBR_STORAGE_FIRST'] ?></a>
                                                                    </li>
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
                                               <div class="addTitle" style="justify-content:flex-start" >
                                                    <i class="icon-action-redo" style="margin-right: 8px"></i>
                                                    <span><?php echo $LANG['UI_CBR_SYNC_SELECTED_REPOSITORT'] ?></span>
                                               </div>

                                                <div class="addIt-list">
                                                <ul class="feeds addVMList" id="addMPList">
                                                </ul>

                                                <ul class="feeds addVMList" id="addVMList">
                                                </ul>

                                                <ul class="feeds addVMList" id="addTPList">
                                                </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="tab2">
                                        <div class="row">
                                            <div class="col-md-10">
                                                <div class="form-group diynodediv">
                                                    <label class="control-label col-md-3 diynodelabel"><?php echo $LANG['UI_COPY_BACK_TARGET_NODE'] ?></label>
                                                    <div class="col-md-9">
                                                        <select class="form-control select2me" id="selectnode">
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group diystoragediv">
                                                    <label class="control-label col-md-3 diystoragelabel"><?php echo $LANG['UI_COPY_BACK_TARGET_STORAGE'] ?></label>
                                                    <div class="col-md-9">
                                                        <select class="form-control select2me" id="selectstorage">
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-md-offset-3 col-md-9">
                                                        <div class="alert alert-info">
                                                            <button type="button" class="close" data-dismiss="alert"></button>
                                                            <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                                            <ol class = "alert-ol">
                                                                <li>
                                                                    <?php echo $LANG['UI_BACKUP_DES_NODE_TIPS'] ?>
                                                                </li>
                                                                <li>
                                                                    <?php echo $LANG['UI_BACKUP_DES_STORAGE_TIPS'] ?>
                                                                </li>
                                                            </ol>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="tab3">
                                        <div class="row row-stepthree">
                                            <div class="tabbable-custom col-md-offset-1 col-md-10">
                                                <ul class="nav nav-tabs ">
                                                    <li class="commonLi active">
                                                        <a href="#tab_common" class="popovers" data-content="<?php echo $LANG['UI_BACKUP_COMMON_STRATEGY_DES'] ?>"
                                                                       data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                        <i class="viconfont vicon-celve1 "></i> <?php echo $LANG['UI_BACKUP_COMMON_STRATEGY'] ?> </a>
                                                    </li>
                                                    <li class="transferLi" >
                                                        <a href="#tab_transfer" class="popovers" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_TIPS'] ?>"
                                                                       data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                        <i class="viconfont vicon-a-chuanshu"></i> <?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?> </a>
                                                    </li>
                                                    <li class="highLi" style="display:none">
                                                        <a href="#tab_other" class="popovers" data-content="<?php echo $LANG['UI_BACKUP_HIGH_CONFIG_DES'] ?>"
                                                                       data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                        <i class="viconfont vicon-gaojipeizhi"></i> <?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?> </a>
                                                    </li>
                                                </ul>
                                                <div class="tab-content">
                                                    <div class="tab-pane active" id="tab_common">
                                                         <div class="panel-body">
                                                            <div class="form-group">
                                                                <div class="col-md-offset-2 col-md-9 accordion strategyOne" >
                                                                    <div class="panel panel-default strategy-panel">
                                                                        <div class="panel-heading">
                                                                            <h4 class="panel-title">
                                                                                <a class="accordion-toggle accordion-toggle-styled popovers"
                                                                                data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#backupTime" aria-expanded="true">
                                                                                <i class="viconfont vicon-shijian font-green-seagreen"></i>
                                                                                <span class="font-green-seagreen"><?php echo $LANG['UI_STRATEGY_TIME'] ?></span>
                                                                                <span class="strategyDes backupTimeDes"></span>
                                                                                </a>
                                                                            </h4>
                                                                        </div>
                                                                        <div id="backupTime" class="panel-collapse collapse in">
                                                                            <div class="panel-body">
                                                                                <div class="col-md-12">
                                                                                    <div class="form-group">
                                                                                        <label class="control-label col-md-2 form-group-label"><?php echo $LANG['UI_PUBLIC_TASK_CROWD'] ?>
                                                                                        </label>
                                                                                        <div class="col-md-8">
                                                                                            <div class="taskCrowd mb15" id="backupCrowd"></div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="form-group">
                                                                                        <label class="control-label col-md-2 form-group-label"><?php echo $LANG['UI_BACKUP_TYPE'] ?>
                                                                                        </label>
                                                                                        <div class="col-md-4 form-group-content">
                                                                                            <select class="form-control select2me" id="backuptype" >
                                                                                                <option value="strategy"><?php echo $LANG['UI_CBR_SYNC_BY_STRATEGY'] ?></option>
                                                                                                <option value="oncetime"><?php echo $LANG['UI_CBR_SYNC_ONCE_TIME'] ?></option>
                                                                                            </select>

                                                                                        </div>
                                                                                    </div>

                                                                                    <div class="form-group setStrategy">
                                                                                        <label class="control-label col-md-2 form-group-label">
                                                                                        <span class="required">* </span>
                                                                                            <?php echo $LANG['UI_BACKUP_SET_STRATEGY'] ?>
                                                                                        </label>
                                                                                        <div class="col-md-10 form-group-content"  style="display:none">
                                                                                            <div class="input-group form-group-boxes" id="strategymode">
                                                                                                <label style="padding-right: 20px;"><input type="checkbox" id="fullBackup" data-checkbox="icheckbox_square-blue" data-mode="1" class="icheck" checked><?php echo $LANG['UI_BACKUP_FULL'] ?></label>
                                                                                                <label style="padding-right: 20px;"><input type="checkbox" id="incrBackup" data-checkbox="icheckbox_square-blue" data-mode="2" class="icheck"><?php echo $LANG['UI_BACKUP_INCREMENT'] ?></label>
                                                                                                <label class="otherDiv" style="padding-right: 20px;"><input type="checkbox" id="diffBackup" data-checkbox="icheckbox_square-blue" data-mode="3" class="icheck"><?php echo $LANG['UI_BACKUP_DIFFERENCE'] ?></label>
                                                                                                <label style="padding-right: 20px;"><input type="checkbox" id="pincrBackup" data-checkbox="icheckbox_square-blue" data-mode="9" class="icheck"><?php echo $LANG['UI_BACKUP_PERMANENT_INCREMENT'] ?></label>
                                                                                                <a class="popovers otherDiv" data-container="body" data-trigger="hover" data-placement="right" data-html="true"
                                                                                                data-content="<?php
                                                                                                echo $LANG['UI_STRATEGY_FULL_TOOLTIPS'] . "<br><br>" . $LANG['UI_STRATEGY_INCR_TOOLTIPS'] .
                                                                                                    "<br><br>" . $LANG['UI_STRATEGY_DIFF_TOOLTIPS'] . "<br><br>" . $LANG['UI_STRATEGY_PINCR_TOOLTIPS'] . "<br><br>" . $LANG['UI_STRATEGY_THREE_TOOLTIPS'];
                                                                                                ?>"><i class="viconfont vicon-tishi"></i>
                                                                                                </a>
                                                                                                <a class="popovers icsDiv display-none" data-container="body" data-trigger="hover" data-placement="right" data-html="true"
                                                                                                data-content="<?php
                                                                                                echo $LANG['UI_STRATEGY_FULL_TOOLTIPS'] . "<br><br>" . $LANG['UI_STRATEGY_INCR_TOOLTIPS'] .
                                                                                                    "<br><br>" . $LANG['UI_STRATEGY_PINCR_TOOLTIPS'] . "<br><br>" . $LANG['UI_STRATEGY_THREE_TOOLTIPS'];
                                                                                                ?>"><i class="viconfont vicon-tishi"></i>
                                                                                                </a>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-md-10">
                                                                                            <div class="portlet">
                                                                                                <div class="portlet-body">
                                                                                                    <div class="panel-group accordion" id="backupTimestrategy">
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>

                                                                                    <div class="form-group display-hide setOnceTime">
                                                                                        <label class="control-label col-md-2">
                                                                                        <span class="required">* </span>
                                                                                        <?php echo $LANG['UI_BACKUP_SET_TIME'] ?>
                                                                                        </label>
                                                                                        <div class="col-md-6">
                                                                                            <div class="input-group date form_datetime">
                                                                                                <input type="text" size="16" readonly id="oncetime" class="form-control input-sm">
                                                                                                <span class="input-group-btn">
                                                                                                <button class="btn default" id="resetdate" type="button"><i class="fa fa-times"></i></button>
                                                                                                <button class="btn default date-set" type="button"><i class="viconfont vicon-ge_calendar"></i></button>
                                                                                                </span>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-md-2 mt5">
                                                                                            <a class="popovers " data-container="body" data-trigger="hover"
                                                                                            data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_AS_ONCETIME_TIPS'] ?>">
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
                                                            <div class="form-group storageDiv">
                                                                <div class="col-md-offset-2 col-md-9 accordion strategyOne" >
                                                                    <div class="panel panel-default strategy-panel">
                                                                        <div class="panel-heading">
                                                                            <h4 class="panel-title">
                                                                                <a class="accordion-toggle accordion-toggle-styled collapsed popovers"
                                                                                data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#store" aria-expanded="true">
                                                                                <i class="viconfont vicon-cunchu font-green-seagreen"></i>
                                                                                <span class="font-green-seagreen"><?php echo $LANG['UI_BACKUP_STORAGE_STRATEGY'] ?></span>
                                                                                <span class="strategyDes storeDes"></span>
                                                                                </a>
                                                                            </h4>
                                                                        </div>
                                                                        <div id="store" class="panel-collapse collapse ">
                                                                            <div class="panel-body">
                                                                                <div class="col-md-12">
                                                                                    <div class="form-group deduplicationdiv">
                                                                                        <label class="control-label col-md-4 deduplicationlabel form-group-top4-label"><?php echo $LANG['UI_BACKUP_DEDUPLICATION'] ?></label>
                                                                                        <div class="col-md-4">
                                                                                            <input type="checkbox" id="deduplicationcheck"   class="make-switch" data-on-color="primary" data-off-color="info" data-size="small"
                                                                                            data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                            data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">

                                                                                        </div>
                                                                                        <div class="col-md-2 mt5">
                                                                                            <a class="popovers " data-container="body" data-trigger="hover"
                                                                                            data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_DEDUPLICATION_TIPS'] ?>">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                            </a>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="form-group">
                                                                                        <label class="control-label col-md-4 compresslabel form-group-top4-label"><?php echo $LANG['UI_BACKUP_COMPRESS_TRANSFER'] ?></label>
                                                                                        <div class="col-md-4">
                                                                                            <input type="checkbox" id="compressCheck" checked class="make-switch" data-on-color="primary" data-off-color="info" data-size="small"
                                                                                            data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                            data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                        </div>
                                                                                        <div class="col-md-2 mt5">
                                                                                            <a class="popovers" data-container="body" data-trigger="hover"
                                                                                            data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_COMPRESS_TIPS'] ?>">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                            </a>
                                                                                        </div>
                                                                                    </div>

                                                                                    <!-- 压缩等级选择 -->
                                                                                    <div class="form-group CompressGradeDiv">
                                                                                        <label class="control-label col-md-4 compressgradelabel form-group-top4-label"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE'] ?></label>
                                                                                        <div class="col-md-4">
                                                                                            <select class="form-control select2me input-sm" id="compressGrade">
                                                                                                <option value="1"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_FAST'] ?></option>
                                                                                                <option value="2"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_NORMAL'] ?></option>
                                                                                                <option value="3"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_BETTER'] ?></option>
                                                                                                <option value="4"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_BEST'] ?></option>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>

                                                                                    <div class="form-group encryptStorageDiv">
                                                                                        <label class="control-label col-md-4 encryptStoragelabel form-group-top4-label"><?php echo $LANG['UI_BACKUP_DATA_ENCRYPT'] ?></label>
                                                                                        <div class="col-md-4">
                                                                                            <input type="checkbox" id="encryptStorageCheck"  class="make-switch" data-on-color="primary" data-off-color="info" data-size="small"
                                                                                            data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                            data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                        </div>
                                                                                        <div class="col-md-2 mt5">
                                                                                            <a class="popovers" data-container="body" data-trigger="hover"
                                                                                            data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_DATE_ENCRY_TIPS'] ?>">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                            </a>
                                                                                        </div>
                                                                                    </div>
                                                                                    <!-- 存储加密 -->
                                                                                    <div class="form-group storage-encrypt-div display-none">
                                                                                        <label class="control-label col-md-4 storage-encrypt-label"><?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?></label>
                                                                                        <div class="col-md-4">
                                                                                            <select class="form-control select2me input-sm" id="storageEncryptMethod">
                                                                                            <?php
                                                                                            
                                                                                            foreach ($CONF['STORE_ENCRYPT_METHOD'] as $index => $encryptMethod) {
                                                                                                echo '<option value="' . $index . '">' . $encryptMethod . '</option>';
                                                                                            }
                                                                                            ?>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>

                                                                                    <div class="form-group display-none passwordModeDiv">
                                                                                        <label class="control-label col-md-4 passwordAutolabel form-group-top4-label"><?php echo $LANG['UI_BACKUP_PASSOWRD_AUTO'] ?></label>
                                                                                        <div class="col-md-4">
                                                                                            <input type="checkbox" id="passwordAutocheck"  class="make-switch" checked data-on-color="primary" data-off-color="info" data-size="small"
                                                                                            data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                            data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                        </div>
                                                                                        <div class="col-md-2 mt5">
                                                                                            <a class="popovers" data-container="body" data-trigger="hover" 
                                                                                            data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_PASSOWRD_AUTO_TIPS'] ?>">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                            </a>
                                                                                        </div>
                                                                                    </div>

                                                                                    <div class="form-group display-none passwordDiv">
                                                                                        <label class="control-label col-md-4 passwordlabel"><?php echo $LANG['UI_BACKUP_PASSWORD'] ?></label>
                                                                                        <div class="col-md-4">
                                                                                            <input type="password" autocomplete="off" maxlength="128" class="form-control input-sm" id="password" placeholder="" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')"/>
                                                                                        </div>
                                                                                    </div>

                                                                                    <div class="form-group display-none passwordDiv">
                                                                                        <label class="control-label col-md-4 repasswordlabel"><?php echo $LANG['UI_BACKUP_PASSWORD_CONFIRM'] ?></label>
                                                                                        <div class="col-md-4">
                                                                                            <input type="password" autocomplete="off" maxlength="128" class="form-control input-sm" id="repassword" placeholder="" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')"/>
                                                                                        </div>
                                                                                        <div class="col-md-4 display-none passwordTips" style="padding: 10px 0 0 0;color: #F3565D;"><?php echo $LANG['UI_BACKUP_PASSWORD_CONFIRM_ERROR'] ?></div>
                                                                                    </div>

                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                            <div class="form-group">
                                                                <div class="col-md-offset-2 col-md-9 accordion strategyOne" >
                                                                    <div class="panel panel-default strategy-panel">
                                                                        <div class="panel-heading">
                                                                            <h4 class="panel-title">
                                                                                <a class="accordion-toggle accordion-toggle-styled collapsed popovers"
                                                                                data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#reserve" aria-expanded="true">
                                                                                <i class="viconfont  vicon-baoliucelve font-green-seagreen"></i>
                                                                                <span class="font-green-seagreen"><?php echo $LANG['UI_BACKUP_RESERVE_STRATEGY'] ?></span>
                                                                                <span class="strategyDes reserveDes"></span>
                                                                                </a>
                                                                            </h4>
                                                                        </div>
                                                                        <div id="reserve" class="panel-collapse collapse">
                                                                            <div class="panel-body">
                                                                                <div class="col-md-12">
                                                                                    <div class="alert alert-danger display-none setreservtip"></div>
                                                                                    <div class="form-group reserveModeDiv">
                                                                                        <label class="control-label col-md-4">
                                                                                            <?php echo $LANG['UI_GLOBAL_STRATEGY_RESERVE_MODE'] ?>
                                                                                        </label>
                                                                                        <div class="col-md-4">
                                                                                            <select class="form-control select2me input-sm" id="reserveMode">
                                                                                                <option value="1"><?php echo $LANG['UI_GLOBAL_STRATEGY_RESERVE_MODE_POINT'] ?></option>
                                                                                                <option value="2"><?php echo $LANG['UI_GLOBAL_STRATEGY_RESERVE_MODE_CHAIN'] ?></option>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="form-group">
                                                                                        <label class="control-label col-md-4">
                                                                                        <?php echo $LANG['UI_BACKUP_DATA_RESERVE_TYPE'] ?>
                                                                                        </label>
                                                                                        <div class="col-md-4">
                                                                                            <select class="form-control select2me" id="reservetype">
                                                                                                <option value="1"><?php echo $LANG['UI_BACKUP_NUM'] ?></option>
                                                                                                <option value="2"><?php echo $LANG['UI_BACKUP_DAY'] ?></option>
                                                                                            </select>
                                                                                        </div>
                                                                                        <div class="col-md-2 mt5">
                                                                                            <a class="popovers" data-container="body" data-trigger="hover" data-html="true"
                                                                                            data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_RESERVER_TIPS'] ?>">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                            </a>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="form-group reserveNum">
                                                                                        <label class="control-label col-md-4"><?php echo $LANG['UI_BACKUP_RESERVE_NUM'] ?>
                                                                                        </label>
                                                                                        <div class="col-md-4">
                                                                                            <div id="spinnerNum">
                                                                                                <div class="input-group spinner-group">
                                                                                                    <input type="text" id="spinnerNumInput" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" maxlength="3">
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
                                                                                    </div>
                                                                                    <div class="form-group reserveDay display-hide">
                                                                                        <label class="control-label col-md-4"><?php echo $LANG['UI_BACKUP_RESERVE_DAY'] ?>
                                                                                        </label>
                                                                                        <div class="col-md-4">
                                                                                            <div id="spinnerDay">
                                                                                                <div class="input-group spinner-group">
                                                                                                    <input type="text" id="spinnerDayInput" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control vmbackup-input-sm_en input-sm " maxlength="3" >
                                                                                                    <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                                        <button type="button" class="btn spinner-up default vmbackup-input-sm_en input-sm">
                                                                                                        <i class="fa fa-angle-up"></i>
                                                                                                        </button>
                                                                                                        <button type="button" class="btn spinner-down default vmbackup-input-sm_en input-sm">
                                                                                                        <i class="fa fa-angle-down"></i>
                                                                                                        </button>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <!-- ************************* -->
                                                                                    <div class="form-group">
                                                                                        <label class="control-label col-md-4 GFSLable form-group-top4-label"><?php echo $LANG['WEB_VM_GFS_RESERVE_STRATEGY'] ?>:</label>
                                                                                        <div class="col-md-4">
                                                                                            <input type="checkbox" id="GFSflag"   class="make-switch" data-on-color="primary" data-off-color="info" data-size="small"
                                                                                            data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                            data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                        </div>
                                                                                        <div class="col-md-2">
                                                                                            <a class="popovers " data-container="body" data-trigger="hover"
                                                                                            data-placement="right" data-html="true" data-content="<?php echo $LANG['WEB_VM_GFS_RESERVE_STRATEGY_TIPS'] ?>">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                            </a>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div id="GFSDiv" class="display-none"></div>
                                                                                    <!-- ************************* -->


                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                            <div class="form-group">
                                                                <div class="col-md-offset-2 col-md-9 accordion strategyOne" >
                                                                    <div class="panel panel-default strategy-panel">
                                                                        <div class="panel-heading">
                                                                            <h4 class="panel-title">
                                                                                <a class="accordion-toggle accordion-toggle-styled collapsed popovers"
                                                                                data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#backupHigh" aria-expanded="true">
                                                                                <i class="viconfont vicon-gaoji1 font-green-seagreen"></i>
                                                                                <span class="font-green-seagreen"><?php echo $LANG['UI_GLOBAL_STRATEGY_HIGH'] ?></span>
                                                                                <span class="strategyDes backupHighDes"></span>
                                                                                </a>
                                                                            </h4>
                                                                        </div>
                                                                        <div id="backupHigh" class="panel-collapse collapse ">
                                                                            <div class="panel-body">
                                                                                <div class="col-md-12">
                                                                                    <div class="form-group threadDiv">
                                                                                        <label class="control-label col-md-4 threadnumlabel form-group-label"><?php echo $LANG['UI_BACKUP_THREAD_NUM'] ?>
                                                                                        </label>
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
                                                                                    <!-- 新增需要同步的时间点个数 -->
                                                                                    <div class="form-group syncTimeDiv">
                                                                                        <label class="control-label col-md-4 syncnumlabel form-group-label"><?php echo $LANG['UI_CBR_SYNC_TIME_POINT'] ?>
                                                                                        </label>
                                                                                        <div class="col-md-4 form-group-content">
                                                                                            <div class="backupSyncTimeDiv">
                                                                                                <div class="input-group spinner-group" >
                                                                                                    <input type="text" id="backupSyncTimeNum" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" >
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
                                                                                            data-placement="right" data-content="<?php echo $LANG['UI_CBR_SYNC_TIME_POINT_TIPS'] ?>">
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                            </a>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="form-group swapdiv display-none" >
                                                                                        <label class="control-label col-md-4  swaplabel form-group-label"><?php echo $LANG['UI_BACKUP_SWAP'] ?></label>
                                                                                        <div class="col-md-4 form-group-content">
                                                                                            <input type="checkbox" id="swapcheck"  class="make-switch" data-size="small" data-on-color="primary" data-off-color="info"
                                                                                            data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                            data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                        </div>
                                                                                        <div class="col-md-1 vmbackup-mt10_en mt5_cn">
                                                                                            <a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right"
                                                                                                data-content="<?php echo $LANG['UI_BACKUP_MODE_HIGH_SWAP_TIPS'] ?>" >
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                            </a>
                                                                                        </div>
                                                                                    </div>

                                                                                    <div class="form-group deletefilediv display-none" >
                                                                                        <label class="control-label col-md-4  deletefilelabel form-group-label"><?php echo $LANG['UI_BACKUP_DELETEFILE'] ?></label>
                                                                                        <div class="col-md-4 form-group-content">
                                                                                            <input type="checkbox" id="deletefilecheck" data-size="small" class="make-switch" data-on-color="primary" data-off-color="info"
                                                                                            data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                            data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                        </div>
                                                                                        <div class="col-md-1 vmbackup-mt10_en mt5_cn">
                                                                                            <a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right"
                                                                                                data-content="<?php echo $LANG['UI_BACKUP_MODE_HIGH_DELFILE_TIPS'] ?>" >
                                                                                            <i class="viconfont vicon-tishi"></i>
                                                                                            </a>
                                                                                        </div>
                                                                                    </div>

                                                                                    <div class="form-group gapdiv display-none">
                                                                                        <label class="control-label col-md-4  gaplabel form-group-label"><?php echo $LANG['UI_BACKUP_GAP'] ?></label>
                                                                                        <div class="col-md-4 form-group-content">
                                                                                            <input type="checkbox" id="gapcheck" data-size="small" class="make-switch" data-on-color="primary" data-off-color="info"
                                                                                            data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                            data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                        </div>
                                                                                        <div class="col-md-1 vmbackup-mt10_en mt5_cn">
                                                                                            <a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right"
                                                                                                data-content="<?php echo $LANG['UI_BACKUP_MODE_HIGH_GAP_TIPS'] ?>" >
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
                                                            <!-- 新增校验策略模块 -->
                                                            <div class="form-group">
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
                                                                                        </div>
                                                                                        <div class="col-md-2 vmbackup-mt10_en mt5_cn">
                                                                                            <a class="popovers " data-container="body" data-trigger="hover"
                                                                                            data-placement="right" data-html="true" data-content="<?php echo $LANG['UI_CBR_SYNC_INTEGRALITY_CHECK_TIPS'] ?>">
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
                                                    <div class="tab-pane " id="tab_transfer">
                                                         <div class="panel-body">
                                                            <div class="tabbable-custom  col-md-10" >
                                                                <div class="form-group transportdiv">
                                                                    <label class="control-label col-md-3 transferlabel"> <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE'] ?></label>
                                                                    <div class="col-md-4">
                                                                        <select class="form-control select2me" id="transport_mode">
                                                                            <option value="nbd"><?php echo $LANG['UI_BACKUP_TRANSPORT_NBD'] ?></option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-2 vmbackup-mt10_en mt5_cn">
                                                                        <a class="popovers" data-container="body" data-trigger="hover" data-html="true"
                                                                        data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_TRANSPORT_ICS_VVDK_SELECT_TIPS'] ?>">
                                                                        <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>    
                                                                <div class="form-group encrypttransferdiv display-none">
                                                                    <label class="control-label col-md-3 encrypttransferlabel form-group-label"><?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER'] ?></label>
                                                                    <div class="col-md-4 form-group-content">
                                                                        <input type="checkbox" id="encrypttransfer"  class="make-switch" data-size="small"  data-on-color="primary" data-off-color="info"
                                                                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                    </div>
                                                                    <div class="col-md-2 mt5">
                                                                        <a class="popovers" data-container="body" data-trigger="hover" data-html="true"
                                                                           data-placement="right" data-content="<?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER_TIPS'] ?>">
                                                                            <i class="viconfont vicon-tishi"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>    
                                                                <!-- 传输加密算法 -->
                                                                <div class="form-group transfer-encrypt-method-form display-none">
                                                                    <label class="control-label transfer-encrypt-method-label col-md-3"><?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?></label>
                                                                    <div class="col-md-4">
                                                                        <select class="form-control select2me input-sm" id="transferEncryptMethod">
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
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane " id="tab_other">
                                                         <div class="panel-body">
                                                            <div class="tabbable-custom pdlr15  col-md-10" >
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
                                                <div class="alert alert-danger display-none jobnametip">
                                                </div>
                                                <div class="form-group mb0 mt10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_JOB_RNAME'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <div class="input-icon right">
                                                            <i class="fa"></i>
                                                            <input type="text" maxlength="64" class="form-control" id="jobname"/>
                                                            <span class="help-block "><?php echo $LANG['UI_JOB_RNAME_TIPS'] ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <h4 class="form-section"></h4>
                                                <div class="form-group mb0 mt10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_CBR_SYNC_SOURCE'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static colorblack vmshow">
                                                        </p>
                                                    </div>
                                                </div>
                                                <h4 class="form-section"></h4>
                                                <div class="form-group mb0 mt10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_COPY_BACK_TARGET_STORAGE'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static colorblack storeinfoshow">
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_COPY_TARGET_NODE'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static colorblack nodeinfoshow">
                                                        </p>
                                                    </div>
                                                </div>
                                                <h4 class="form-section"></h4>
                                                <div class="form-group mb0 mt10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_CBR_SYNC_METHOD'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static colorblack backuptypeshow">
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_STRATEGY_TIME'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static colorblack backuptypeinfoshow">
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10 speedlimitDiv">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static colorblack speedlimitshow">
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10" id="storagemodeshowdiv">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_STORAGE_STRATEGY'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static colorblack storageinfoshow">
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_RESERVE_STRATEGY'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static colorblack reservetypeshow">
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10" id="backupmodeshowdiv">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <div class="form-control-static colorblack threadnumshow threadDiv">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10" id="verifymodeshowdiv">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_CBR_SYNC_VERIFY_STRATEGY'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <div class="form-control-static colorblack verifymodeshow">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group mb0 mt10" id="transportmodeshowdiv">
                                                    <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?>:</label>
                                                    <div class="col-md-9">
                                                        <p class="form-control-static colorblack transportinfoshow">
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions">
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

                    <div class="form-group ">
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
                                            <input type="text" id="speedSpinnerNumInput" style="text-align: center;" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control" onkeyup="value=value.replace(/[^\d]/g,'')">
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
                                <div class="">
                                    <select id="unit" style="width: 80px; margin-left: 10px;height: 33px;text-align:center;border: 1px solid #E6E6E6;">
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
<!-- END PAGE LEVEL PLUGINS -->
<script src="./scripts/cbr/cbrbackup.js" type="text/javascript"></script>
