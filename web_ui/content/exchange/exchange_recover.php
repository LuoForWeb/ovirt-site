<?php
include_once '../../tpl/permission.php';
include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php';
?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./css/fs/filetype-small.css"/>
<link rel="stylesheet" type="text/css"
      href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>

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
<link rel="stylesheet" type="text/css" href="./css/platform/component/safe_virus.css"/>
<link rel="stylesheet" type="text/css" href="./scripts/components/timePointDetail/css/timePointDetail.css" />
<!-- END PAGE LEVEL STYLES -->
<?php session_start();
session_commit(); ?>
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="recovery" href="./content/recovery/recoverycenter.php">
            <span><?php echo $LANG['WEB_PLATFORM_DES_RECOVERY'] ?></span>
        </a>
    </li>
    <span>
        >
    </span>
    <span class="curent"><?php echo $LANG['UI_RECOVERY_FOR_M365'] ?></span>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row recover-page">
    <div class="col-md-12" style="height:100%;">
        <div class="portlet box blue-hoki" id="exchange-recover-content">
            <input id="externalPointUuid" value="<?php echo $_GET['point_uuid']; ?>" class="display-none"></input>
            <input id="externalTaskUuid" value="<?php echo $_GET['task_uuid']; ?>" class="display-none"></input>
            <input id="externalItemUuid" value="<?php echo $_GET['item_uuid']; ?>" class="display-none"></input>
            <div class="portlet-title">
                <div class="caption">
                    <i class="levelchild viconfont vicon-huifu1"></i> <?php echo $LANG['UI_MICROSOFT365_CREATE_RECOVERY_JOB'] ?>
                </div>
            </div>
            <div class="portlet-body form">
                <form action="#" class="form-horizontal" id="submit_form" method="POST">
                    <div class="form-wizard">
                        <div class="form-body" style="padding-bottom: 20px;">
                            <ul class="nav nav-pills steps">
                                <li>
                                    <a href="#tab1" data-toggle="tab" class="step">
                                        <span class="number">
                                            1 </span>
                                        <span class="desc">
                                            <?php echo $LANG['UI_RECOVERY_DATA_SOURCE'] ?>
                                            <i class="fa fa-check"></i>
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#tab2" data-toggle="tab" class="step">
                                        <span class="number">
                                            2 </span>
                                        <span class="desc">
                                            <?php echo $LANG['UI_RECOVERY_GOAL'] ?>
                                            <i class="fa fa-check"></i>
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#tab3" data-toggle="tab" class="step active">
                                        <span class="number">
                                            3 </span>
                                        <span class="desc">
                                            <?php echo $LANG['UI_RECOVERY_VM_TYPE'] ?>
                                            <i class="fa fa-check"></i>
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#tab4" data-toggle="tab" class="step">
                                        <span class="number">
                                            4 </span>
                                        <span class="desc">
                                            <i class="fa fa-check"></i> <?php echo $LANG['UI_BACKUP_CONFIRM_CONFIG'] ?> </span>
                                    </a>
                                </li>
                            </ul>
                            <div class="tab-content bakuptab">
                                <!-- 选择备份点 -->
                                <div class="tab-pane active" id="tab1" style="height: 100%;">
                                    <div class="alert alert-danger display-none selecttimepointtip">
                                    </div>
                                    <div class="row tab-pane__row">
                                        <div class="col-md-4" style="height: 100%">
                                            <div class="form-group src-wrap" id="echangetimepointtree">
                                                <div class="src-wrap__title">
                                                    <span><i class="viconfont vicon-exchange mr8"></i><?php echo $LANG['UI_MICROSOFT365_RECOVERY_SOURCE'] ?></span>
                                                </div>
                                                <div class="src-wrap__content" style="overflow: auto;">
                                                    <div class="src-wrap__content__search">
                                                        <select class="bs-select width100p form-control" value=""
                                                                style="height: 34px" data-show-subtext="true"
                                                                id="storageSelect">
                                                        </select>
                                                        <div class="vm_tree_div">
                                                            <select class="bs-select width45p display-hide"
                                                                    data-show-subtext="true" id="c">
                                                                <option data-icon="timevmtype icon-default"
                                                                        value="1"> <?php echo $LANG['UI_RECOVERY_BACKUP_POINT_VM_GROUP'] ?></option>
                                                                <option data-icon="timepointtype icon-default"
                                                                        value="2"> <?php echo $LANG['UI_RECOVERY_BACKUP_POINT_TIME_GROUP'] ?></option>
                                                            </select>
                                                            <div class="input-icon width100p">
                                                                <input type="text" maxlength="128"
                                                                       placeholder="<?php echo $LANG['WEB_OS_SEARCH_NAME'] ?>"
                                                                       class="form-control" id="searchExchange"
                                                                       autocomplete="off" onkeyup="customInputValidate('string', this.value, $(this))"/>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="min-height250">
                                                        <div class="exchange_tree display-none">
                                                            <ul id="exchange_point_tree" style="padding: 12px;"
                                                                class="ztree ztree-fa tree_div"></ul>
                                                        </div>
                                                        <div class="alert alert-block alert-info fade in display-hide"
                                                             id="nopointtips" style="margin: 13px;">
                                                            <button type="button" class="close"
                                                                    data-dismiss="alert"></button>
                                                            <h4 class="alert-heading">
                                                                <strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong>
                                                            </h4>
                                                            <ol class="alert-ol">
                                                                <li>
                                                                    <?php echo $LANG['UI_RECOVERY_NO_VM_TITLE'] ?>
                                                                </li>
                                                                <li>
                                                                    <a id="tobackup"
                                                                       class="alert-link"><?php echo $LANG['UI_MICROSOFT365_TO_ADD_BAK'] ?></a>
                                                                </li>
                                                            </ol>
                                                        </div>
                                                        <div class="alert alert-block alert-info fade in display-hide"
                                                             id="nosearchtips" style="margin: 13px;">
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
                                        <div class="col-md-8 step1tips pd0">
                                            <div class="alert alert-block alert-info fade in" id="nopointtips">
                                                <button type="button" class="close" data-dismiss="alert"></button>
                                                <h4 class="alert-heading">
                                                    <strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                                <ol class="alert-ol">
                                                    <li>
                                                        <?php echo $LANG['UI_MICROSOFT365_RECOVERY_TIPS1'] ?>
                                                    </li>
                                                    <li>
                                                        <?php echo $LANG['UI_MICROSOFT365_RECOVERY_TIPS2'] ?>
                                                    </li>
                                                    <li>
                                                        <?php echo $LANG['UI_MICROSOFT365_RECOVERY_TIPS3'] ?>
                                                    </li>
                                                    <li>
                                                        <?php echo $LANG['UI_MICROSOFT365_RECOVERY_TIPS4'] ?>
                                                    </li>
                                                    <li>
                                                        <?php echo $LANG['UI_MICROSOFT365_RECOVERY_TIPS5'] ?>
                                                    </li>
                                                    <li>
                                                        <?php echo $LANG['UI_MICROSOFT365_RECOVERY_TIPS6'] ?>
                                                    </li>

                                                </ol>
                                            </div>
                                        </div>
                                        <!--                                        搜索结果显示-->
                                        <div class="col-md-8 pd0 display-none seachDiv" style="height: 100%;">
                                            <div class="addTitle">
                                                <span><i class="viconfont vicon-huifu mr8"></i> <?php echo $LANG['UI_MICROSOFT365_RECOVERY_SELECT_OBJECT'] ?></span>
                                            </div>
                                            <div class="addIt-list" style="height: calc(100% - 81px);border-bottom: none;overflow-x: clip;">
                                                <div class="row">
                                                    <div class="col-md-12 table-div search-table">
                                                        <!-- Begin: life time stats grey-cararra -->
                                                        <div class="portlet-body">
                                                            <!-- 顶部工具栏 -->
                                                            <div id="batchExport_search" class="btn-group squreBtn mb5">
                                                                <i class="viconfont vicon-huifu"></i>
                                                            </div>
                                                            <div class="btn-group">
                                                                <div class="btn" style="">
                                                                    <span class="searchContent"></span>
                                                                    <span class="clearSearch"><?php echo $LANG['UI_MICROSOFT365_CLEAR_SEARCH'] ?></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <table id="searchTable"></table>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="loadMore-div-search display-none" style="height: 47px;border: 1px solid #ccccccb5;border-top: none;padding: 10px 0;color: #7e8299;">
                                                <div class="col-md-1 pr0">
                                                    <label class="control-label"><input type="radio" checked value="20" name="page_num_search" class="icheck"><?php echo $LANG['UI_MICROSOFT365_20_ITEMS'] ?></label>
                                                </div>
                                                <div class="col-md-1 pd0">
                                                    <label class="control-label"><input type="radio" value="50" name="page_num_search" class="icheck"><?php echo $LANG['UI_MICROSOFT365_50_ITEMS'] ?></label>
                                                </div>
                                                <div class="col-md-1 pd0">
                                                    <label class="control-label"><input type="radio" value="100" name="page_num_search" class="icheck"><?php echo $LANG['UI_MICROSOFT365_100_ITEMS'] ?></label>
                                                </div>
                                                <div class="col-md-1 pd0">
                                                    <label class="control-label diy-label"><input type="radio" value="0" name="page_num_search" class="icheck"><?php echo $LANG['UI_MICROSOFT365_CUSTOM'] ?></label>
                                                    <input type="num" style="width: 65px;height: 22px; margin-top: 3px;" class="form-control display-none diy-input">
                                                </div>
                                                <div class="col-md-4" style="text-align: center;cursor: pointer;margin: 5px 0;">
                                                    <div class="loadMoreSearchClick"><?php echo $LANG['UI_MICROSOFT365_LOAD_MORE_DATA'] ?></div>
                                                </div>
                                                <div class="col-md-3" style="text-align: right; margin: 5px 0">
                                                    <div class="loadMoreSpeed">
                                                        <!--                                                        已显示<span class="loadNUm"></span>条-->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-8 VMList-div exchange-div display-none">
                                            <div class="addTitle">
                                                <span><i class="viconfont vicon-huifu mr8"></i><?php echo $LANG['UI_MICROSOFT365_RECOVERY_SELECT_OBJECT'] ?></span>
                                            </div>
                                            <div class="addIt-list">
                                                <div class="row">
                                                    <div class="col-md-12 pd0 topToolDiy display-none" style="margin-bottom: 14px;">
                                                        <div class="btn-group col-md-1 pd0 display-none batch-export-div" style="width: 45px;">
                                                            <div id="batchExport" class="squreBtn" style="">
                                                                <i class="viconfont vicon-huifu"></i>
                                                            </div>
                                                        </div>
                                                        <!--全文搜索-->
                                                        <div class="input-group col-md-2 pd0">
                                                            <input id="normalSearchInput" type="text" class="form-control" placeholder="<?php echo $LANG['UI_MICROSOFT365_SEARCH_TEXT'] ?>" aria-describedby="normalSearchBtn">
                                                            <span class="input-group-addon" id="normalSearchBtn"><i class="viconfont vicon-gaojisousuo1"></i></span>
                                                        </div>
                                                        <!--                                                        高级搜索-->
                                                        <div class="btn-group position0">
                                                            <label class="">
                                                                <div class="btn green-turquoise advanceSearch">
                                                                    <i class="viconfont vicon-gaojisousuo1"></i>
                                                                    <?php echo $LANG['WEB_EXCH_BACKUP_POINT_OP_CODE_ADVANCED_SEARCH'] ?>
                                                                </div>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12 pd0 table-div point-table display-none">
                                                        <!-- Begin: life time stats grey-cararra -->
                                                        <div class="portlet-body">
                                                        </div>
                                                        <table id="pointTable"></table>
                                                    </div>
                                                    <div class="col-md-12 pd0 table-div user-table display-none">
                                                        <!-- Begin: life time stats grey-cararra -->
                                                        <div class="portlet-body">

                                                        </div>
                                                        <table id="userTable"></table>
                                                    </div>
                                                    <div class="col-md-12 pd0 table-div rootdir-table display-none">
                                                        <!-- Begin: life time stats grey-cararra -->
                                                        <div class="portlet-body">

                                                        </div>
                                                        <table id="rootDirTable"></table>
                                                    </div>
                                                    <div class="col-md-12 pd0 table-div task-table display-none">
                                                        <!-- Begin: life time stats grey-cararra -->
                                                        <div class="portlet-body">
                                                        </div>
                                                        <table id="taskTable"></table>
                                                    </div>
                                                    <div class="col-md-12 pd0 table-div calendar-table display-none">
                                                        <!-- Begin: life time stats grey-cararra -->
                                                        <div class="portlet-body">

                                                        </div>
                                                        <table id="calendarTable"></table>
                                                    </div>
                                                    <div class="col-md-12 pd0 table-div contacts-table display-none">
                                                        <!-- Begin: life time stats grey-cararra -->
                                                        <div class="portlet-body">
                                                        </div>
                                                        <table id="contactsTable"></table>
                                                    </div>
                                                    <div class="col-md-12 pd0 table-div default-table">
                                                        <!-- Begin: life time stats grey-cararra -->
                                                        <div class="portlet-body">
                                                        </div>
                                                        <table id="defaultTable"></table>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="loadMore-div display-none" style="height: 47px;border: 1px solid #ccccccb5;border-top: none;padding: 10px 0;color: #7e8299;">
                                                <div class="col-md-1 pr0">
                                                    <label class="control-label"><input type="radio" checked value="20" name="page_num" class="icheck"><?php echo $LANG['UI_MICROSOFT365_20_ITEMS'] ?></label>
                                                </div>
                                                <div class="col-md-1 pd0">
                                                    <label class="control-label"><input type="radio" value="50" name="page_num" class="icheck"><?php echo $LANG['UI_MICROSOFT365_50_ITEMS'] ?></label>
                                                </div>
                                                <div class="col-md-1 pd0">
                                                    <label class="control-label"><input type="radio" value="100" name="page_num" class="icheck"><?php echo $LANG['UI_MICROSOFT365_100_ITEMS'] ?></label>
                                                </div>
                                                <div class="col-md-1 pd0">
                                                    <label class="control-label diy-label"><input type="radio" value="0" name="page_num" class="icheck"><?php echo $LANG['UI_MICROSOFT365_CUSTOM'] ?></label>
                                                    <input type="num" style="width: 65px;height: 22px; margin-top: 3px;" class="form-control display-none diy-input">
                                                </div>
                                                <div class="col-md-4" style="text-align: center;cursor: pointer;margin: 5px 0;">
                                                    <div class="loadMoreClick"><?php echo $LANG['UI_MICROSOFT365_LOAD_MORE_DATA'] ?></div>
                                                </div>
                                                <div class="col-md-3" style="text-align: right; margin: 5px 0">
                                                    <div class="loadMoreSpeed">
                                                        <?php echo $LANG['UI_MICROSOFT365_DISPLAYED'] ?><span class="loadPercent"></span>&nbsp;&nbsp;
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- 高级搜索模态框开始 -->
                                            <div id="advanceSearch" class="modal xmodal fade form-horizontal "
                                                 tabindex="-1" data-focus-on="input:first" data-backdrop="static">
                                                <div class="modal-header ">
                                                    <button type="button" class="close" data-dismiss="modal"
                                                            aria-hidden="true"></button>
                                                    <h4 class="modal-title add-title">
                                                        <i class="viconfont vicon-danchuangsousuo mr8"></i><?php echo $LANG['WEB_EXCH_BACKUP_POINT_OP_CODE_ADVANCED_SEARCH'] ?>
                                                        <span class="titleDes"> </span>

                                                    </h4>
                                                </div>
                                                <div class="modal-body" style="padding: 20px;">
                                                    <form action="#" id="verifyForm" class="form-horizontal">
                                                        <div class="form-group simpleDiv">
                                                            <label class="col-md-3 control-label"><?php echo $LANG['UI_MICROSOFT365_CATEGORY'] ?></label>
                                                            <div class="col-md-7">
                                                                <select class="form-control select2me" id="category">
                                                                    <option value="0"><?php echo $LANG['WEB_M365_EMAIL'] ?></option>
                                                                    <option value="1"><?php echo $LANG['UI_MICROSOFT365_EVENT'] ?></option>
                                                                    <option value="2"><?php echo $LANG['WEB_M365_CONTACTS'] ?></option>
                                                                    <option value="3"><?php echo $LANG['WEB_M365_TASK'] ?></option>
                                                                    <option value="4"><?php echo $LANG['UI_MICROSOFT365_ADDRESS'] ?></option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group simpleDiv">
                                                            <label class="col-md-3 control-label"><?php echo $LANG['UI_MICROSOFT365_FOLDER'] ?></label>
                                                            <div class="col-md-7">
                                                                <select class="form-control select2me" id="folder">
                                                                    <option value="0"><?php echo $LANG['UI_MICROSOFT365_ALL_EMAILS'] ?></option>
                                                                    <option value="1"><?php echo $LANG['WEB_M365_INBOX'] ?></option>
                                                                    <option value="2"><?php echo $LANG['WEB_M365_DRAFT'] ?></option>
                                                                    <option value="3"><?php echo $LANG['UI_MICROSOFT365_SENT_EMAILS'] ?></option>
                                                                    <option value="4"><?php echo $LANG['UI_MICROSOFT365_DELETED_EMAILS'] ?></option>
                                                                    <option value="5"><?php echo $LANG['WEB_M365_SPAM'] ?></option>
                                                                    <option value="6"><?php echo $LANG['WEB_M365_FILE'] ?></option>
                                                                    <option value="7"><?php echo $LANG['UI_MICROSOFT365_DIALOGUE_HISTORY'] ?></option>
                                                                    <option value="100"><?php echo $LANG['WEB_M365_CALENDAR'] ?></option>
                                                                    <option value="200"><?php echo $LANG['WEB_M365_CONTACTS'] ?></option>
                                                                    <option value="300"><?php echo $LANG['WEB_M365_TASK'] ?></option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group simpleDiv">
                                                            <label class="col-md-3 control-label"><?php echo $LANG['UI_MICROSOFT365_LOCATION'] ?></label>
                                                            <div class="col-md-7">
                                                                <select class="form-control select2me" id="position">
                                                                    <option value="0"><?php echo $LANG['UI_PUBLIC_ALL'] ?></option>
                                                                    <option value="1"><?php echo $LANG['UI_MICROSOFT365_THEME'] ?></option>
                                                                    <option value="2"><?php echo $LANG['UI_MICROSOFT365_MESSAGE_BODY'] ?></option>
                                                                    <option value="3"><?php echo $LANG['UI_MICROSOFT365_ANNEX'] ?></option>
                                                                    <option value="4"><?php echo $LANG['UI_MICROSOFT365_RECIPIENT'] ?></option>
                                                                    <option value="5"><?php echo $LANG['UI_MICROSOFT365_FROM'] ?></option>
                                                                    <option value="6"><?php echo $LANG['UI_MICROSOFT365_CC'] ?></option>
                                                                    <!--                                                                    <option value="7">开始日期</option>-->
                                                                    <!--                                                                    <option value="8">结束日期</option>-->
                                                                    <!--                                                                    <option value="9">接收时间</option>-->
                                                                    <!--                                                                    <option value="10">截止日期</option>-->

                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group simpleDiv">
                                                            <label class="col-md-3 control-label"><?php echo $LANG['UI_MICROSOFT365_CONDITION'] ?></label>
                                                            <div class="col-md-7">
                                                                <select class="form-control select2me" id="condition">
                                                                    <option value="0"><?php echo $LANG['UI_MICROSOFT365_INCLUDE'] ?></option>
                                                                    <option value="1"><?php echo $LANG['WEB_M365_EXCLUDE'] ?></option>
                                                                    <option value="2"><?php echo $LANG['UI_MICROSOFT365_EQUATION'] ?></option>
                                                                    <option value="3"><?php echo $LANG['UI_MICROSOFT365_BEGIN_OF_WORD'] ?></option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group simpleDiv">
                                                            <label class="col-md-3 control-label"><?php echo $LANG['UI_MICROSOFT365_INCLUDE_ATTACHMENTS'] ?></label>
                                                            <div class="col-md-7">
                                                                <select class="form-control select2me" id="attachment">
                                                                    <option value="0"><?php echo $LANG['UI_MICROSOFT365_NO_LIMIT'] ?></option>
                                                                    <option value="1"><?php echo $LANG['UI_MICROSOFT365_INCLUDE'] ?></option>
                                                                    <option value="2"><?php echo $LANG['UI_MICROSOFT365_NOT_INCLUDE'] ?></option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group simpleDiv">
                                                            <label class="col-md-3 control-label"><?php echo $LANG['UI_MICROSOFT365_KEYWORD'] ?></label>
                                                            <div class="col-md-7">
                                                                <input class="form-control" type="text" id="keywords"/>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" data-dismiss="modal" class="btn btn-default">
                                                        <?php echo $LANG['UI_PUBLIC_CANCEL'] ?>
                                                    </button>
                                                    <button type="button" class="btn green-haze searchSubmit"
                                                            ><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
                                                </div>
                                            </div>
                                            <!-- 高级搜索模态框结束 -->
                                            <!-- 邮件详情模态框开始 -->
                                            <div id="emailModal" class="modal xmodal fade form-horizontal "
                                                 tabindex="-1" data-focus-on="input:first" data-backdrop="static">
                                                <div class="modal-header ">
                                                    <button type="button" class="close" data-dismiss="modal"
                                                            aria-hidden="true"></button>
                                                    <h4 class="modal-title add-title">
                                                        <i class="viconfont vicon-youjian mr8" style="font-size: 18px;vertical-align: middle;"></i>
                                                        <span class="email-title"></span>
                                                    </h4>
                                                </div>
                                                <div class="modal-body" style="padding: 20px 20px 0 20px;">
                                                    <form action="#" id="emailForm" class="form-horizontal">
                                                        <div class="form-group simpleDiv">
                                                            <label class="col-md-1 control-label pd0"><?php echo $LANG['UI_MICROSOFT365_FROM'] ?></label>
                                                            <div class="col-md-9 pd0">
                                                                <input type="text" class="form-control" id="sendFrom" readonly>
                                                            </div>
                                                            <div class="col-md-1">
                                                                <div class="btn-group">
                                                                    <button type="button" class="btn green-haze" id="modify-send">
                                                                        <?php echo $LANG['UI_PUBLIC_MODIFY'] ?>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-1">
                                                                <a class="popovers ml15" data-container="body" data-trigger="hover" style="line-height: 30px;"
                                                                   data-placement="right" data-html="true" data-content="<?php echo $LANG['UI_MICROSOFT365_EMAIL_FROM_TIPS'] ?>">
                                                                    <i class="viconfont vicon-tishi"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                        <div class="form-group simpleDiv">
                                                            <label class="col-md-1 control-label pd0"><?php echo $LANG['UI_MICROSOFT365_SEND_TO'] ?></label>
                                                            <div class="col-md-11 pd0">
                                                                <input type="text" class="form-control" id="sendTo"
                                                                       placeholder="<?php echo $LANG['UI_MICROSOFT365_SEND_TO_TIPS'] ?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-group simpleDiv">
                                                            <label class="col-md-1 control-label pd0"><?php echo $LANG['UI_MICROSOFT365_CC_TO'] ?></label>
                                                            <div class="col-md-11 pd0">
                                                                <input type="text" class="form-control" id="ccTo"
                                                                       placeholder="<?php echo $LANG['UI_MICROSOFT365_SEND_TO_TIPS'] ?>">
                                                            </div>
                                                        </div>
                                                        <div class="exchange-contentBox">
                                                            <div class="form-group simpleDiv">
                                                                <label class="col-md-1 pd0 col-md-2_en"><?php echo $LANG['UI_MICROSOFT365_FROM'] ?></label>
                                                                <div class="col-md-11 pd0 col-md-10_en">
                                                                    <span class="contentName"></span>
                                                                    <!--                                                                    <span class="contetEmail des">(hanmeimei@exch.com.cn)</span>-->
                                                                </div>
                                                            </div>
                                                            <div class="form-group simpleDiv">
                                                                <label class="col-md-1 pd0 col-md-2_en"><?php echo $LANG['UI_PUBLIC_TIME'] ?></label>
                                                                <div class="col-md-11 pd0 col-md-10_en">
                                                                    <span class="contentTime des"></span>
                                                                </div>
                                                            </div>
                                                            <div class="form-group simpleDiv">
                                                                <label class="col-md-1 pd0 col-md-2_en"><?php echo $LANG['UI_MICROSOFT365_RECIPIENT'] ?></label>
                                                                <div class="col-md-11 pd0 col-md-10_en">
                                                                    <span class="receiveName"></span>
                                                                    <!--                                                                    <span class="contetEmail des">(hanmeimei@exch.com.cn)</span>-->
                                                                </div>
                                                            </div>
                                                            <div class="form-group simpleDiv">
                                                                <label class="col-md-1 pd0 col-md-2_en"><?php echo $LANG['UI_MICROSOFT365_MAKE_COPY'] ?></label>
                                                                <div class="col-md-11 pd0 col-md-10_en">
                                                                    <span class="ccName"></span>
                                                                    <!--                                                                    <span class="contetEmail des">(hanmeimei@exch.com.cn)</span>-->
                                                                </div>
                                                            </div>
                                                            <div class="form-group simpleDiv">
                                                                <label class="col-md-1 pd0 col-md-2_en"><?php echo $LANG['UI_MICROSOFT365_ANNEX'] ?></label>
                                                                <div class="col-md-11 pd0 col-md-10_en">
                                                                    <span class="attachmentName"><?php echo $LANG['UI_MICROSOFT365_ANNEX_ONE'] ?></span>
                                                                    <!--                                                                    <span class="contetEmail des">(test.xlsx)</span>-->
                                                                </div>
                                                            </div>
                                                            <div class="text" style="max-height: 300px;overflow: auto;">

                                                            </div>
                                                        </div>
                                                        <div class="alert alert-block alert-info fade in mt20" id="marktips">
                                                            <button type="button" class="close" data-dismiss="alert"></button>
                                                            <ul class="alert-ul">
                                                                <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                                                                <li><?php echo $LANG['UI_MICROSOFT365_SEND_EMAIL_TIPS'] ?></li>
                                                            </ul>
                                                        </div>
                                                    </form>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" data-dismiss="modal" class="btn btn-default">
                                                        <?php echo $LANG['UI_PUBLIC_CANCEL'] ?>
                                                    </button>
                                                    <button type="button" class="btn green-haze exportEmail">
                                                        <?php echo $LANG['UI_PUBLIC_EXPORT'] ?>
                                                    </button>
                                                    <button type="button" class="btn green-haze sendEmail">
                                                        <?php echo $LANG['UI_MICROSOFT365_SEND'] ?>
                                                    </button>
                                                </div>
                                            </div>
                                            <!-- 邮件详情模态框结束 -->
                                            <!-- 联系人详情模态框开始 -->
                                            <div id="contactModal" class="modal xmodal fade form-horizontal "
                                                 tabindex="-1" data-focus-on="input:first" data-backdrop="static">
                                                <div class="modal-header ">
                                                    <button type="button" class="close" data-dismiss="modal"
                                                            aria-hidden="true"></button>
                                                    <h4 class="modal-title add-title">
                                                        <i class="viconfont vicon-youjian mr8" style="font-size: 18px;vertical-align: middle;"></i>
                                                        <span><?php echo $LANG['WEB_M365_CONTACTS'] ?></span>
                                                    </h4>
                                                </div>
                                                <div class="modal-body" style="padding: 20px;min-height: 490px;">
                                                    <form action="#" id="contactForm" class="form-horizontal">
                                                        <div class="col-md-6 pd0">
                                                            <div class="form-group simpleDiv">
                                                                <label class="col-md-2 control-label pd0" style="text-align: left;"><?php echo $LANG['UI_MICROSOFT365_NAME'] ?>：</label>
                                                                <div class="col-md-5 pd0">
                                                                    <span class="contact-name" style="white-space: nowrap;"></span>
                                                                </div>
                                                            </div>
                                                            <div class="form-group simpleDiv">
                                                                <label class="col-md-4 control-label pd0 col-md-5_en" style="text-align: left;"><?php echo $LANG['UI_MICROSOFT365_DEPARTMENT'] ?>：</label>
                                                                <div class="col-md-5 pd0">
                                                                    <span class="contact-position"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 pd0">
                                                            <div class="form-group simpleDiv">
                                                                <label class="col-md-2 control-label pd0 col-md-3_en" style="text-align: left;"><?php echo $LANG['UI_MICROSOFT365_COMPANY'] ?>：</label>
                                                                <div class="col-md-5 pd0">
                                                                    <span class="contact-company"></span>
                                                                </div>
                                                            </div>
                                                            <div class="form-group simpleDiv">
                                                                <label class="col-md-4 control-label pd0" style="text-align: left;"><?php echo $LANG['UI_MICROSOFT365_E_MAIL'] ?>：</label>
                                                                <div class="col-md-5 pd0">
                                                                    <span class="contact-email"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12 pd0">
                                                            <div class="form-group simpleDiv">
                                                                <label class="col-md-4 control-label pd0" style="text-align: left;"><?php echo $LANG['UI_MICROSOFT365_WEB_PAGE_ADDRESS'] ?>：</label>
                                                                <div class="col-md-7 pd0">
                                                                    <span class="contact-address"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 pd0">
                                                            <div class="form-group simpleDiv">
                                                                <label class="col-md-4 control-label pd0" style="text-align: left;"><?php echo $LANG['UI_MICROSOFT365_BUSINESS_PHONE'] ?>：</label>
                                                                <div class="col-md-5 pd0">
                                                                    <span class="contact-shangwu"></span>
                                                                </div>
                                                            </div>
                                                            <div class="form-group simpleDiv">
                                                                <label class="col-md-4 control-label pd0" style="text-align: left;"><?php echo $LANG['UI_MICROSOFT365_RESIDENT_PHONE'] ?>：</label>
                                                                <div class="col-md-5 pd0">
                                                                    <span class="contact-zhuzhai"></span>
                                                                </div>
                                                            </div>
                                                            <div class="form-group simpleDiv">
                                                                <label class="col-md-2 control-label pd0" style="text-align: left;"><?php echo $LANG['UI_MICROSOFT365_ZIP_CODE'] ?>：</label>
                                                                <div class="col-md-5 pd0">
                                                                    <span class="contact-youbian"></span>
                                                                </div>
                                                            </div>
                                                            <div class="form-group simpleDiv">
                                                                <label class="col-md-5 control-label pd0 col-md-7_en" style="text-align: left;"><?php echo $LANG['UI_MICROSOFT365_PROVINCE_CITY_REGION'] ?>：</label>
                                                                <div class="col-md-5 pd0">
                                                                    <span class="contact-sheng"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 pd0">
                                                            <div class="form-group simpleDiv">
                                                                <label class="col-md-2 control-label pd0" style="text-align: left;"><?php echo $LANG['UI_MICROSOFT365_COUNTRY'] ?>：</label>
                                                                <div class="col-md-5 pd0">
                                                                    <span class="contact-country"></span>
                                                                </div>
                                                            </div>
                                                            <div class="form-group simpleDiv">
                                                                <label class="col-md-4 control-label pd0" style="text-align: left;"><?php echo $LANG['UI_MICROSOFT365_MOBILE_PHONE'] ?>：</label>
                                                                <div class="col-md-5 pd0">
                                                                    <span class="contact-phone"></span>
                                                                </div>
                                                            </div>
                                                            <div class="form-group simpleDiv">
                                                                <label class="col-md-3 control-label pd0" style="text-align: left;"><?php echo $LANG['UI_MICROSOFT365_CITY_COUNTY'] ?>：</label>
                                                                <div class="col-md-5 pd0">
                                                                    <span class="contact-xian"></span>
                                                                </div>
                                                            </div>
                                                            <div class="form-group simpleDiv">
                                                                <label class="col-md-2 control-label pd0" style="text-align: left;"><?php echo $LANG['UI_MICROSOFT365_STREET'] ?></label>
                                                                <div class="col-md-5 pd0">
                                                                    <span class="contact-street"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12 pd0"><?php echo $LANG['UI_MICROSOFT365_REMARKS'] ?></div>
                                                        <div class="exchange-contentBox col-md-12 contact-text"></div>
                                                    </form>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" data-dismiss="modal" class="btn btn-default">
                                                        <?php echo $LANG['UI_PUBLIC_CANCEL'] ?>
                                                    </button>
                                                    <button type="button" class="btn green-haze exportEmail">
                                                        <?php echo $LANG['UI_PUBLIC_EXPORT'] ?>
                                                    </button>
                                                </div>
                                            </div>
                                            <!-- 联系人详情模态框结束 -->
                                            <!-- 日历详情模态框开始 -->
                                            <div id="calendarModal" class="modal xmodal fade form-horizontal "
                                                 tabindex="-1" data-focus-on="input:first" data-backdrop="static">
                                                <div class="modal-header ">
                                                    <button type="button" class="close" data-dismiss="modal"
                                                            aria-hidden="true"></button>
                                                    <h4 class="modal-title add-title">
                                                        <i class="viconfont vicon-rili1 mr8" style="font-size: 18px;vertical-align: middle;"></i>
                                                        <span> <?php echo $LANG['UI_MICROSOFT365_CALENDAR_EVENT'] ?></span>
                                                    </h4>
                                                </div>
                                                <div class="modal-body" style="padding: 20px;min-height: 345px;">
                                                    <form action="#" id="calendarForm" class="form-horizontal">
                                                        <div class="col-md-12 pd0">
                                                            <div class="form-group simpleDiv">
                                                                <label class="col-md-1 control-label pd0" style="text-align: left;"><?php echo $LANG['UI_MICROSOFT365_TITLE'] ?>：</label>
                                                                <div class="col-md-10 pd0">
                                                                    <span class="calendar-title"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12 pd0">
                                                            <div class="form-group simpleDiv">
                                                                <label class="col-md-1 control-label pd0" style="text-align: left;"><?php echo $LANG['UI_MICROSOFT365_REGION'] ?>：</label>
                                                                <div class="col-md-10 pd0">
                                                                    <span class="calendar-address"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12 pd0">
                                                            <div class="form-group simpleDiv">
                                                                <label class="col-md-2 control-label pd0" style="text-align: left;"><?php echo $LANG['UI_JOB_START_TIME'] ?>：</label>
                                                                <div class="col-md-10 pd0">
                                                                    <span class="calendar-start"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12 pd0">
                                                            <div class="form-group simpleDiv">
                                                                <label class="col-md-2 control-label pd0" style="text-align: left;"><?php echo $LANG['UI_JOB_OVER_TIME'] ?>：</label>
                                                                <div class="col-md-10 pd0">
                                                                    <span class="calendar-end"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12 pd0">
                                                            <div class="form-group simpleDiv">
                                                                <label class="col-md-1 control-label pd0" style="text-align: left;"><?php echo $LANG['UI_MICROSOFT365_ANNEX'] ?>：</label>
                                                                <div class="col-md-10 pd0">
                                                                    <span class="calendar-attach"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-12 pd0"><?php echo $LANG['UI_MICROSOFT365_TEXT'] ?>：</div>
                                                        <div class="exchange-contentBox col-md-12 calendar-text"></div>
                                                    </form>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" data-dismiss="modal" class="btn btn-default">
                                                        <?php echo $LANG['UI_PUBLIC_CANCEL'] ?>
                                                    </button>
                                                    <button type="button" class="btn green-haze exportEmail">
                                                        <?php echo $LANG['UI_PUBLIC_EXPORT'] ?>
                                                    </button>
                                                </div>
                                            </div>
                                            <!-- 日历详情模态框结束 -->
                                            <!-- 任务详情模态框开始 -->
                                            <div id="taskModal" class="modal xmodal fade form-horizontal "
                                                 tabindex="-1" data-focus-on="input:first" data-backdrop="static">
                                                <div class="modal-header ">
                                                    <button type="button" class="close" data-dismiss="modal"
                                                            aria-hidden="true"></button>
                                                    <h4 class="modal-title add-title">
                                                        <i class="viconfont vicon-renwu mr8" style="font-size: 18px;vertical-align: middle;"></i>
                                                        <span><?php echo $LANG['WEB_M365_TASK'] ?></span>
                                                    </h4>
                                                </div>
                                                <div class="modal-body" style="padding: 20px;min-height: 430px;">
                                                    <form action="#" id="taskForm" class="form-horizontal">
                                                        <div class="col-md-6 pd0">
                                                            <div class="form-group simpleDiv">
                                                                <label class="col-md-2 control-label pd0" style="text-align: left;"><?php echo $LANG['UI_MICROSOFT365_TITLE'] ?>：</label>
                                                                <div class="col-md-5 pd0">
                                                                    <span class="task-title"></span>
                                                                </div>
                                                            </div>
                                                            <div class="form-group simpleDiv">
                                                                <label class="col-md-2 control-label pd0" style="text-align: left;"><?php echo $LANG['UI_PUBLIC_STATUS'] ?>：</label>
                                                                <div class="col-md-5 pd0">
                                                                    <span class="task-status"></span>
                                                                </div>
                                                            </div>
                                                            <div class="form-group simpleDiv">
                                                                <label class="col-md-4 control-label pd0  col-md-3_en" style="text-align: left;"><?php echo $LANG['UI_JOB_START_TIME'] ?>：</label>
                                                                <div class="col-md-6 pd0">
                                                                    <span class="task-start"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 pd0">
                                                            <div class="form-group simpleDiv">
                                                                <label class="col-md-2 control-label pd0" style="text-align: left;"><?php echo $LANG['UI_MICROSOFT365_AUTHOR'] ?>：</label>
                                                                <div class="col-md-5 pd0">
                                                                    <span class="task-author"></span>
                                                                </div>
                                                            </div>
                                                            <div class="form-group simpleDiv">
                                                                <label class="col-md-3 control-label pd0" style="text-align: left;"><?php echo $LANG['UI_MICROSOFT365_IMPORTANCE'] ?>：</label>
                                                                <div class="col-md-5 pd0">
                                                                    <span class="task-import"></span>
                                                                </div>
                                                            </div>
                                                            <div class="form-group simpleDiv">
                                                                <label class="col-md-4 control-label pd0 col-md-3_en" style="text-align: left;"><?php echo $LANG['UI_JOB_OVER_TIME'] ?>： </label>
                                                                <div class="col-md-6 pd0">
                                                                    <span class="task-end"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12 pd0">
                                                            <div class="form-group simpleDiv">
                                                                <label class="col-md-1 control-label pd0" style="text-align: left;"><?php echo $LANG['UI_MICROSOFT365_ANNEX'] ?>：</label>
                                                                <div class="col-md-7 pd0">
                                                                    <span class="task-attach"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12 pd0"><?php echo $LANG['UI_MICROSOFT365_REMARKS'] ?>：</div>
                                                        <div class="exchange-contentBox col-md-12 task-text"></div>
                                                    </form>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" data-dismiss="modal" class="btn btn-default">
                                                        <?php echo $LANG['UI_PUBLIC_CANCEL'] ?>
                                                    </button>
                                                    <!--                                                    <button type="button" class="btn green-haze exportEmail">-->
                                                    <!--                                                        导出-->
                                                    <!--                                                    </button>-->
                                                </div>
                                            </div>
                                            <!-- 任务详情模态框结束 -->
                                        </div>
                                        <div class="totalDataDiv display-none" data-toggle="drawer" data-target="#drawer-1">
                                            <span class="totalNum">0</span>
                                            <i class="viconfont vicon-gouwuche c0FBF98"></i>
                                        </div>
                                        <!-- drawer开始 -->
                                        <div class="drawer slide" data-placement="right" tabindex="-1" role="dialog"
                                             aria-labelledby="drawer-1-title" aria-hidden="true" id="drawer-1">
                                            <div class="drawer-content drawer-content-scrollable" role="document">
                                                <div class="drawer-header">
                                                    <h4 class="drawer-title" id="drawer-1-title">
                                                        <i class="viconfont vicon-beifen mr8"></i><?php echo $LANG['UI_MICROSOFT365_SELECTED_RECOVERY_RESOURCE'] ?>
                                                        <span class="tabledes"> </span>
                                                        <span data-dismiss="drawer" aria-label="Close" class="drawer-close"><i class="viconfont vicon-guanbi"></i></span>
                                                    </h4>
                                                </div>
                                                <div class="drawer-body" style="padding-bottom: 0;">
                                                    <div class="col-md-12 table-div total-table pd0">
                                                        <!-- Begin: life time stats grey-cararra -->
                                                        <div class="btn-group del-parent-div">
                                                            <div id="delete-shopping" class="exch-forbid-event" style="">
                                                                <i class="viconfont vicon-a-Deleteshanchu1" style="margin: 0;"></i>
                                                            </div>
                                                        </div>
                                                        <div class="portlet-body">
                                                        </div>
                                                        <table id="totalTable"></table>
                                                    </div>
                                                </div>
                                                <div class="drawer-footer">
                                                    <button type="button" data-dismiss="drawer" aria-label="Close"
                                                            class="btn btn-default"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- drawer结束 -->
                                    </div>
                                </div>
                                <!-- 恢复目标 -->
                                <div class="tab-pane min-height400" id="tab2" style="height: 100%;">
                                    <div class="row tab-pane__row">
                                        <div class="col-md-12 min-height300" style="height: 100%;overflow:auto">
                                            <div class="form-group organization_tree_div">
                                                <label class="control-label col-md-3">
                                                    <?php echo $LANG['UI_MICROSOFT365_SELECT_ORGANIZATION'] ?>
                                                </label>
                                                <div class="col-md-6 tree_div2">
                                                    <ul class="ztree" id="exchange_tree"></ul>
                                                    <div class="alert alert-block alert-info fade in display-none" id="noOrganization">
                                                        <button type="button" class="close" data-dismiss="alert"></button>
                                                        <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                                        <ol class="alert-ol">
                                                            <li>
                                                                <a id="toAdd" class="alert-link"><?php echo $LANG['UI_MICROSOFT365_SELECT_ORGANIZATION_TIPS1'] ?></a>
                                                            </li>
                                                            <li>
                                                                <?php echo $LANG['UI_MICROSOFT365_SELECT_ORGANIZATION_TIPS2'] ?>                                                    </li>
                                                            <li>
                                                                <?php echo $LANG['UI_MICROSOFT365_SELECT_ORGANIZATION_TIPS3'] ?>
                                                            </li>
                                                        </ol>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group recovery-way-div display-none">
                                                <label class="control-label col-md-3">
                                                    <?php echo $LANG['UI_MICROSOFT365_RECOVERY_METHOD'] ?>
                                                </label>
                                                <div class="col-md-5 tree_div2">
                                                    <select name="recovery-way-div" class="form-control" id="recovery-way-div">
                                                        <option value="0"><?php echo $LANG['WEB_M365_NEW_RECOVERY'] ?></option>
                                                        <option value="1"><?php echo $LANG['WEB_M365_OVERWRITE_RECOVERY'] ?></option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group user-select-div display-none">
                                                <label class="control-label col-md-3">
                                                    <?php echo $LANG['UI_MICROSOFT365_RECOVERY_SPECIFIED_USER'] ?>
                                                </label>
                                                <div class="col-md-4 pr0 select-user">
                                                    <input readonly name="userSelect" value="<?php echo $LANG['WEB_M365_NOT_SPECIFY_USER'] ?>" id="userSelect" type="text" class="form-control">
                                                </div>
                                                <div class="col-md-1 select-user">
                                                    <button type="button" name="btn-modal" id="showUserModal" class="form-control btn green-turquoise"><?php echo $LANG['WEB_M365_RECOVERY_SELECT_USER'] ?></button>
                                                </div>
                                                <a class="popovers ml15" data-container="body" data-trigger="hover"
                                                   style="line-height: 34px;"
                                                   data-placement="right" data-content="<?php echo $LANG['UI_MICROSOFT365_RECOVERY_SPECIFIED_USER_DES'] ?>">
                                                    <i class="viconfont vicon-tishi fa-lg"></i>
                                                </a>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label col-md-3">
                                                </label>
                                                <div class="col-md-6 tree_div2">
                                                    <div class="alert alert-block alert-info fade in" id="step1tips">
                                                        <button type="button" class="close"
                                                                data-dismiss="alert"></button>
                                                        <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                                        <ol class="alert-ol">
                                                            <li><?php echo $LANG['UI_MICROSOFT365_RECOVERY_SPECIFIED_USER_TIPS1'] ?></li>
                                                            <li><?php echo $LANG['UI_MICROSOFT365_RECOVERY_SPECIFIED_USER_TIPS2'] ?></li>
                                                        </ol>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 恢复方式 -->
                                <div class="tab-pane min-height400" id="tab3" style="height: 100%;">
                                    <div class="row" style="height: 100%;padding-bottom: 15px;">
                                        <div class="tabbable-custom col-md-offset-1 col-md-10" style="height: 100%;">
                                            <ul class="nav nav-tabs tab3-nav">
                                                <li class="commonLi active">
                                                    <a href="#tab_common" class="popovers" 
                                                       data-container="body" data-trigger="hover" data-placement="top"
                                                       data-toggle="tab">
                                                        <i class="viconfont vicon-celve1"></i> <?php echo $LANG['UI_BACKUP_COMMON_STRATEGY'] ?>
                                                    </a>
                                                </li>
                                                <li class="transferLi">
                                                    <a href="#tab_transfer" class="popovers" 
                                                       data-container="body" data-trigger="hover" data-placement="top"
                                                       data-toggle="tab">
                                                        <i class="viconfont vicon-a-chuanshu"></i> <?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?>
                                                    </a>
                                                </li>
                                                <li class="safeLi">
                                                    <a href="#tab_safety" class="popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                        <i class="viconfont vicon-anquancelve"></i> <?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?> 
                                                    </a>
                                                </li>
                                                <li class="highLi">
                                                    <a href="#tab_other" class="popovers" 
                                                       data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                        <i class="viconfont vicon-gaojipeizhi"></i> <?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?> </a>
                                                </li>
                                            </ul>
                                            <div class="tab-content min-height300 tab3-content"
                                                 style="height: calc(100% - 45px);overflow-x:hidden;overflow-y: auto;">
                                                <!-- 通用策略 -->
                                                <div class="tab-pane active" id="tab_common">
                                                    <div class="panel-body">
                                                        <!-- 时间策略 -->
                                                        <div class="form-group">
                                                            <div class="col-md-offset-1  col-md-10 pt15 recoveryTimeDiv">
                                                                <div class="accordion strategyOne" >
                                                                    <div class="panel panel-default strategy-panel">
                                                                        <div class="panel-heading">
                                                                            <h4 class="panel-title">
                                                                                <a class="accordion-toggle accordion-toggle-styled popovers" 
                                                                                data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#recoveryTime" aria-expanded="true">
                                                                                <i class="viconfont vicon-shijian2 font-green-seagreen"></i> 
                                                                                <span class="font-green-seagreen"><?php echo $LANG['UI_STRATEGY_TIME'] ?></span>
                                                                                <span class="strategyDes recoveryTimeDes"></span>
                                                                                </a>
                                                                            </h4>
                                                                        </div>
                                                                        <div id="recoveryTime" class="panel-collapse collapse in">
                                                                            <div class="panel-body">
                                                                                <div class="col-md-12">
                                                                                    <div class="form-group">
                                                                                        <label class="control-label col-md-2"><?php echo $LANG['UI_RECOVERY_TYPE'] ?> 
                                                                                        </label>
                                                                                        <div class="col-md-3">
                                                                                            <select class="form-control select2me input-sm" id="recovertype">
                                                                                                <option value="1"><?php echo $LANG['UI_RECOVERY_TYPE_NOW'] ?></option>
                                                                                                <option value="4"><?php echo $LANG['UI_RECOVERY_TYPE_TIMING'] ?></option>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="form-group display-hide setOnceTime">
                                                                                        <div class="onceTime-content">
                                                                                            <label class="control-label col-md-2">
                                                                                                <span class="required">* </span>
                                                                                                <?php echo $LANG['UI_BACKUP_SET_TIME'] ?>
                                                                                            </label>
                                                                                            <div class="onceTime-content-input">
                                                                                                <div class="input-group date form_datetime">
                                                                                                    <input type="text" size="16" readonly id="oncetime" class="form-control input-sm"
                                                                                                        style="width: 160px;">
                                                                                                    <span class="input-group-btn">
                                                                                                        <button class="btn default input-sm" id="resetdate" type="button"><i
                                                                                                                class="fa fa-times"></i></button>
                                                                                                        <button class="btn default date-set input-sm" type="button"><i
                                                                                                                class="viconfont vicon-ge_calendar"></i></button>
                                                                                                    </span>
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="onceTime-content-tips">
                                                                                                <a class="popovers " data-container="body" data-trigger="hover" data-placement="right"
                                                                                                    data-content="<?php echo $LANG['UI_RECOVERY_TYPE_TIMING_TIME'] ?>"
                                                                                                    data-original-title="" title="">
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
                                                                    <?php include_once('../../content/platform/global_strategy/speedJob.php'); ?>
                                                                    <!--       全局限速策略组合的组件配置-->
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>

                                                <div class="tab-pane " id="tab_transfer">
                                                    <div class="panel-body">
                                                        <div class="tabbable-custom pdlr15  col-md-10">
                                                            <!-- 传输加密 -->
                                                            <div class="form-group transport-encrypt-div">
                                                                <label class="control-label form-group-top4-label col-md-3"><?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER'] ?></label>
                                                                <div class="col-md-4">
                                                                    <input type="checkbox" id="transport_encrypt_flag" data-size="small" class="make-switch" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                    <a class="popovers ml15" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php echo $LANG['WEB_OS_TRANSFER_ENCRY_TIPS'] ?>">
                                                                        <i class="viconfont vicon-tishi fa-lg"></i>
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
                                                            <!--选择客户端   -->
                                                            <div class="form-group display-none agentConfigDiv">
                                                                <label class="control-label col-md-3 form-group-top4-label agentConfiglabel"><?php echo $LANG['WEB_M365_AGENT_CONFIG'] ?></label>
                                                                <div class="col-md-4">
                                                                    <input type="checkbox" id="agentConfig" data-size="small" class="make-switch" data-on-color="primary" data-off-color="info" data-on-text="" data-off-text="">
                                                                    <a class="popovers ml15" data-container="body" data-trigger="hover"
                                                                       data-placement="right" data-content="<?php echo $LANG['WEB_M365_AGENT_CONFIG_TIPS'] ?>">
                                                                        <i class="viconfont vicon-tishi fa-lg"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                            <div class="form-group display-none agentListDiv">
                                                                <label class="control-label col-md-3 agentListLabel"><?php echo $LANG['WEB_M365_SPECIFY_AGENT'] ?></label>
                                                                <div class="col-md-4">
                                                                    <select class="form-control select2me" id="agentList">
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <!-- 传输网络-->
                                                            <div class="form-group display-none transfernetworkDiv">
                                                                <label class="control-label col-md-3 transfernetworklabel"><?php echo $LANG['UI_NODE_TRANSFER_NETWORK'] ?></label>
                                                                <div class="col-md-4">
                                                                    <ul class="ztree" id="transferNetworkTree"></ul>
                                                                </div>
                                                                <div class="col-md-2 mt5">
                                                                    <a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_NODE_TRANSFER_NETWORK_TIPS'] ?>">
                                                                        <i class="viconfont vicon-tishi"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                            <?php
                                                            $authfun = $_SESSION['authfun'];
                                                            if ($authfun['multithread']) {
                                                                echo '
                                                                        <!-- 传输线程-->
                                                                        <div class="form-group recoveryThreadNumDiv">
                                                                            <label class="control-label col-md-3 threadnumlabel">' . $LANG['UI_BACKUP_THREAD_NUM'] . '
                                                                            </label>
                                                                            <div class="col-md-4">
                                                                                <div id="recoveryThreadDiv">
                                                                                    <div class="input-group spinner-group">
                                                                                        <input type="text"
                                                                                               id="recoveryThreadNum"
                                                                                               onkeyup="value=value.replace(/[^\d]/g,\'\')"
                                                                                               class="spinner-input form-control input-sm">
                                                                                        <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                                            <button type="button"
                                                                                                    class="btn spinner-up default input-sm">
                                                                                                <i class="fa fa-angle-up"></i>
                                                                                            </button>
                                                                                            <button type="button"
                                                                                                    class="btn spinner-down default input-sm">
                                                                                                <i class="fa fa-angle-down"></i>
                                                                                            </button>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-2 mt5">
                                                                                <a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="' . $LANG['WEB_M365_THREAD_NUM_TIP'] . '" data-original-title="" title="">
                                                                                    <i class="viconfont vicon-tishi fa-lg"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                        ';
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- 安全配置 -->
                                                <div class="tab-pane fs-tab-content__pane" id="tab_safety">
                                                    <div class="panel-body">
                                                        <!-- 备份数据完整性校验 -->
                                                        <div id="completeConfig"></div>
                                                    </div>
                                                </div>
                                                <!-- 高级配置 -->
                                                <div class="tab-pane " id="tab_other">
                                                    <div class="advance-config-wrap">
                                                        <div class="advance-config-wrap__row row m0">
                                                            <div class="col-md-2 col-sm-2 col-xs-2 nav-tab-radios">
                                                                <ul class="nav nav-tabs tabs-left">
                                                                    <li class="active">
                                                                        <a href="#retry_strategy_pane"
                                                                            data-toggle="tab" aria-expanded="true">
                                                                            <?php echo $LANG['UI_STRATEGY_RETRY'] ?>
                                                                        </a>
                                                                    </li>
                                                                    <li class="">
                                                                        <a href="#over_load_strategy_pane"
                                                                            data-toggle="tab" aria-expanded="true">
                                                                            <?php echo $LANG['UI_STRATEGY_OVER_LOAD_LABEL'] ?>
                                                                        </a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            <div class="col-md-10 col-sm-10 col-xs-10 pe-0 advance-config-wrap__row__content">
                                                                <div class="tab-content level1" style="height: 100%;">
                                                                    <div class="tab-pane fade in active" id="retry_strategy_pane">
                                                                        <div class="">
                                                                            <div class="tab-content">
                                                                                <div id="retry_config" class="retry_config_pane">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="tab-pane fade" id="over_load_strategy_pane">
                                                                        <div class="abnormal-handle-form">
                                                                            <!-- 忽略节点资源限制 -->
                                                                            <div class="form-group advancedDiv ignoreResourceLimitDiv">
                                                                                <label for="ignoreResourceLimit" class="control-label col-md-4 ignoreResourceLimitLabel form-group-label">
                                                                                    <?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE'] ?>
                                                                                </label>
                                                                                <div class="col-md-3 form-group-content">
                                                                                    <input type="checkbox" checked id="ignoreResourceLimit" class="make-switch" data-on-color="primary"
                                                                                           data-off-color="info" data-size="small"
                                                                                           data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                                    <a class="popovers ml15" data-container="body" data-trigger="hover"
                                                                                       data-html="true" data-placement="right"
                                                                                       data-content="<?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE_TIPS'] ?>">
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

                                <!-- 确认配置 -->
                                <div class="tab-pane" id="tab4" style="height: 100%;">
                                    <div class="tab-pane__body">
                                        <div class="tab-pane__body__form">
                                            <div class="alert alert-danger display-none jobnametip"></div>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_JOB_RNAME'] ?>
                                                    :</label>
                                                <div class="col-md-9">
                                                    <div class="input-icon right">
                                                        <input type="text" maxlength="64" class="form-control"
                                                               id="jobname"  onkeyup="customInputValidate('string', this.value, $(this))" id="jobname"/>
                                                        <span class="help-block "><?php echo $LANG['UI_JOB_RNAME_TIPS'] ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <h4 class="form-section"></h4>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_FILE_DATA_SRC'] ?>
                                                </label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static  srcdata">
                                                    </p>
                                                </div>
                                            </div>
                                            <h4 class="form-section"></h4>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_FILE_DATA_SRC_TASK'] ?>
                                                </label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static  recovertask">
                                                    </p>
                                                </div>
                                            </div>
                                            <h4 class="form-section"></h4>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_MICROSOFT365_BACKUP_TIME_POINT'] ?>
                                                </label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static  recoverpoint">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_MICROSOFT365_RECOVERY_DATA_LIST'] ?>
                                                </label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static  recoverdatalist">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_MICROSOFT365_RECOVERY_TARGET'] ?>
                                                </label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static  recoverdes">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mt10 recoveruserdiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_MICROSOFT365_TARGET_USER'] ?>
                                                </label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static  recoveruser">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mt10">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_MICROSOFT365_RECOVERY_METHOD'] ?>
                                                </label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static  recoverway">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mt10" id="speedstrategyshowdiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_RECOVERY_TYPE'] ?>
                                                </label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static  reservetypeshow">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mt10" id="speedstrategyshowdiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?>
                                                </label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static  speedlimitshow">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mt10 transferDiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?>
                                                </label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static  transfershow">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 mt10 retryShow"></div>
                                            <div class="form-group mb0 safeDiv">
                                                <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?>:</label>
                                                <div class="col-md-9">
                                                    <p class="form-control-static safeStrategyShow">
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-group mb0 highDiv">
                                                <label
                                                    class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?>:</label>
                                                <div class="col-md-9">
                                                    <div class="form-control-static">
                                                        <div class="ignoreResourceLimitShow "></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions" style="height: 66px;">
                            <div class="row">
                                <div class="col-md-offset-6 col-md-6">
                                    <a href="javascript:;" class="btn default button-previous">
                                        <i class="viconfont vicon-shangyibu"
                                           style="margin-right: 2px;"></i> <?php echo $LANG['UI_PUBLIC_PREV_STEP'] ?>
                                    </a>
                                    <a href="javascript:;" class="btn green-turquoise button-next next-btn-margin-left"
                                       style="margin-left: 10px">
                                        <?php echo $LANG['UI_PUBLIC_NEXT_STEP'] ?> <i class="viconfont vicon-xiayibu"
                                                                                      style="margin-left: 2px;"></i>
                                    </a>
                                    <a href="javascript:;" class="btn green-haze button-submit"
                                       style="margin-left: 10px;">
                                        <?php echo $LANG['UI_PUBLIC_SUBMIT'] ?> <i class="viconfont vicon-xiayibu"
                                                                                   style="margin-left: 2px;"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
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
                        <div class="col-md-6">
                            <select class="form-control select2me inline-block" id="speedModeType" style="width:203px;">
                                <option value="1"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_STRATEGY'] ?></option>
                                <option value="2"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_FOREVER'] ?></option>
                            </select>
                            <a class="popovers ml15" data-container="body" data-trigger="hover" data-placement="right"
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
                                            <input type="text" id="speedSpinnerNumInput" style="text-align: left;"
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
                                <a style="display:block; margin-top:8px;" class="popovers ml15" data-container="body"
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
        <!-- 身份验证模态框开始 -->
        <div id="authModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
            <div class="modal-header ">
                <button type="button" class="close cancleBtn" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title add-title">
                    <i class="viconfont vicon-wancheng mr8"></i><span><?php echo $LANG['UI_MICROSOFT365_AUTHENTY'] ?></span>
                </h4>
            </div>
            <div class="modal-body" style="padding: 20px;max-height: 430px;">
                <form action="#" id="authForm" class="form-horizontal">
                    <div class="server display-none">
                        <div class="form-group managerNameDiv">
                            <label class="control-label col-md-3">
                                <?php echo $LANG['UI_MICROSOFT365_ADMINISTRATOR_ACCOUNT'] ?>
                            </label>
                            <div class="col-md-8">
                                <input type="text" maxlength="128" class="form-control" id="managerName">
                                <span class="serverDes"><?php echo $LANG['UI_MICROSOFT365_ADMINISTRATOR_ACCOUNT_TIP'] ?></span>
                            </div>
                        </div>
                        <div class="form-group managerPwdDiv">
                            <label class="control-label col-md-3">
                                <?php echo $LANG['UI_MICROSOFT365_ADMINISTRATOR_PASSWORD'] ?>
                            </label>
                            <div class="col-md-8">
                                <input type="password" maxlength="128" class="form-control" id="managerPwd">
                                <span class="serverDes"><?php echo $LANG['UI_MICROSOFT365_ADMINISTRATOR_PASSWORD_TIP'] ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="online display-none" id="addVerify">
                        <div class="verify-content">
                            <div class="verify-box">
                                <div><?php echo $LANG['UI_MICROSOFT365_LOGIN_ACCOUNT_TIP'] ?></div>
                                <div><?php echo $LANG['UI_MICROSOFT365_COPY_CODE_VERIFY'] ?></div>
                                <input type="text" id="vertifyCode" disabled="disabled">
                                <i class="viconfont vicon-a-Redozhongxin"></i>
                                <div class="copy-btn-group">
                                            <span class="copy">
                                                 <i class="viconfont vicon-fuzhi" style="margin-right: 3px;"></i><?php echo $LANG['UI_MICROSOFT365_COPY'] ?>
                                            </span>
                                    <div class="copy-tips display-none"><?php echo $LANG['UI_MICROSOFT365_COPY_SUCCESS'] ?></div>
                                </div>
                                <a href="https://microsoft.com/devicelogin" class="verifyLink c0FBF98" target="_blank"><?php echo $LANG['UI_MICROSOFT365_COPY_CODE_TIP'] ?></a>
                            </div>
                            <!--验证码请求中-->
                            <div class="vertify-waiting">
                                <i class="viconfont vicon-shalou c0FBF98"></i><?php echo $LANG['UI_MICROSOFT365_WAIT_LOGIN'] ?>
                            </div>
                            <!--验证码请求超时-->
                            <div class="vertify-outtime display-none">
                                <i class="viconfont vicon-shalou c0FBF98"></i><?php echo $LANG['UI_MICROSOFT365_VERIFY_OUTTIME_TIP'] ?>
                            </div>
                            <!--验证码请求成功-->
                            <div class="vertify-success display-none"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-default cancleBtn">
                    <?php echo $LANG['UI_PUBLIC_CANCEL'] ?>
                </button>
                <button type="button" class="btn green-haze authSubmit">
                    <?php echo $LANG['UI_PUBLIC_CONFIRM'] ?>
                </button>
            </div>
        </div>
        <!-- 身份验证模态框结束 -->
        <!-- 恢复用户模态框开始 -->
        <div id="userModal" class="modal xmodal fade form-horizontal "
             tabindex="-1" data-focus-on="input:first" data-backdrop="static">
            <div class="modal-header ">
                <button type="button" class="close" data-dismiss="modal"
                        aria-hidden="true"></button>
                <h4 class="modal-title add-title">
                    <i class="viconfont vicon-lianjie mr8"></i><?php echo $LANG['WEB_M365_RECOVERY_SELECT_USER'] ?>
                    <span class="titleDes"> </span>
                </h4>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <div class="form-group simpleDiv">
                    <input name="search" id="searchUserInput" type="text" class="form-control" placeholder="<?php echo $LANG['WEB_M365_RECOVERY_SEARCH_USER'] ?>">
                </div>
                <div class="form-group simpleDiv">
                    <ul class="ztree " id="userTree"></ul>
                </div>
            </div>
            <div class="modal-footer">
                <button name="cancelBtn" type="button" data-dismiss="modal" class="btn btn-default">
                    <?php echo $LANG['UI_PUBLIC_OFF_TWO'] ?>
                </button>
                <button type="button" class="btn green-haze userSubmit"
                ><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
            </div>
        </div>
        <!-- 恢复用户模态框结束 -->
    </div>
</div>
<!-- END PAGE CONTENT-->


<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./scripts/components/timePointDetail/js/timePointDetail.js" type="text/javascript"></script>
<script src="./scripts/components/timePointDetail/js/virusHistoryTable.js" type="text/javascript"></script>
<script type="text/javascript" src="./assets/global/plugins/clipboard/clipboard.min.js"></script>
<script src="./scripts/backupData/clipboard.js" type="text/javascript"></script>
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
<script type="text/javascript"
        src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.speedStrategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.strategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/calendar/jquery.searchableSelect.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.taskCrowd.js"></script>
<script type="text/javascript" src="./scripts/platform/component/transfer_network.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.GFSStrategy.js"></script>
<script type="text/javascript" src="./scripts/platform/component/retry_strategy.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_complete.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script src="./scripts/exchange/exchange_recover.js" type="text/javascript"></script>
