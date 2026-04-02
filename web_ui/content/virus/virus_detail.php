<?php
include_once '../../tpl/permission.php';
include_once '../../content/platform/public/bs_table.php';
global $LANG;
?>
<link rel="stylesheet" type="text/css"
      href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./css/virus/virus-page.css"/>
<link href="./assets/global/plugins/jquery-file-upload/css/jquery.fileupload.css" rel="stylesheet"/>

<!-- BEGIN PAGE HEADER -->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="backup_manager" href="./content/platform/backup/backupresources.php" >
            <span><?php echo $LANG['UI_PLATFORM_BACKUP_RESOURCE'] ?></span>
        </a>
    </li>
    <span>></span>
    <li>
        <a class="ajaxify" name="backup_manager"
           href="./content/virus/virus.php"><?php echo $LANG['WEB_PLATFORM_VIRUS_MANAGE'] ?></a>
    </li>
    <span>></span>
    <span class="curent"><?php echo $_GET['vendor'] ?></span>
</h3>
<!-- END PAGE HEADER -->

<!-- BEGIN PAGE CONTENT -->
<div class="resource-manager-wrap" id="virusDetail__wrapper">
    <input id="virus_type" value="<?php echo $_GET['virus_type']; ?>" class="display-none"></input>
    <div class="resource-manager-wrap__header">
        <i class="levelchild viconfont vicon-bingdukuguanli"></i>
        <span class="resource-manager-wrap__header__text"><?php echo $_GET['vendor'] ?></span>
    </div>
    <div class="resource-manager-wrap__content">
        <div class="table-toolbar">
            <div class="vin_toolbar" id="virus_toolbar">
                <div class="leftTool">
                    <div class="" style="margin-bottom: 5px;">
                        <div class="btn-group">
                            <!--                                        <button type="button" id="delete" class="dropdown-toggle btn-font btn-title" style="width: 34px; margin-right: 12px;background-color: #F4F4F5">-->
                            <!--                                            <i class="viconfont vicon-a-Deleteshanchu1" style="margin: 0 auto;font-weight: normal;color: black"></i>-->
                            <!--                                        </button>-->
                        </div>
                    </div>
                </div>
                <div class="rightTool">
                    <div class="vin_btnToolbar"></div>
                </div>
            </div>
        </div>
        <div class="table-container reset-td-width">
            <table id="virus_table"></table>
        </div>
    </div>
    <!--更新病毒库-->
    <div class="drawer slide" data-placement="right" tabindex="-1" role="dialog"
         aria-hidden="true" id="drawer-updata">
        <div class="drawer-content drawer-content-scrollable" role="document">
            <div class="drawer-header">
                <h4 class="drawer-title">
                    <i class="viconfont vicon-shangchuan"></i>
                    <span class="text"><?php echo $LANG['WEB_TOOL_VIRUS_OP_OFFLINE_UPDATE_VIRUS_LIB']; ?></span>
                    <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i class="viconfont vicon-guanbi"></i></span>
                </h4>
            </div>
            <div class="add-body">
                <div class="detail-task-right updataFile">
                    <div class="col-md-2 add-text item-add col-md-4_en pt7">
                        <span class="required">* </span>
                        <?php echo $LANG['UI_VIRUS_UPDATA_VIRUS'] ?>
                    </div>
                    <div class="col-md-10 col-md-8_en">
                        <!-- 上传 -->
                        <div class="form-group item-add">
                            <div class="drawer-item-content d-flex">
                                <button id="uploadDriverBtn" class="btn green-haze" type="button">
                                    <i class="viconfont vicon-shangchuan"></i>
                                    <?php echo $LANG['UI_SETTINGS_UPDATE_START_UPLOAD'] ?>
                                </button>
                                <a class="popovers ml12 pt7" data-container="body" data-trigger="hover" data-html="true"
                                   data-placement="right"
                                   data-content="<?php echo $LANG['UI_VIRUS_ADD_UPLOAD_TIPS'] ?>">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                            </div>
                        </div>

                        <!-- 上传病毒 -->
                        <div class="form-group upload-driver-result-div">
                            <label class="drawer-item-label"></label>
                            <div class="drawer-item-content">
                                <div id="uploadDriverResult" class="dropzone"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="detail-task-right">
                    <div class="context-item col-md-2 col-md-4_en"
                         style="text-align: right;color: #666666"><?php echo $LANG['UI_VIRUS_UPDATE_METHOD'] ?>
                    </div>
                    <div class="col-md-10 context-item col-md-8_en">
                        <div class="input-icon right">
                            <select id="updataType" class="form-control" disabled>
                                <option value="0"><?php echo $LANG['WEB_SETTINGS_UPDATE_ONLINE'] ?></option>
                                <option value="1" selected><?php echo $LANG['WEB_SETTINGS_UPDATE_OFFLINE'] ?></option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="detail-task-right">
                    <div class="context-item col-md-2 item-add col-md-4_en"
                         style="text-align: right;color: #666666"><?php echo $LANG['UI_VIRUS_VIRUS_TYPE'] ?>
                    </div>
                    <div class="col-md-10 context-item col-md-8_en">
                        <div class="input-icon right item-add">
                            <select id="virusType" class="form-control" disabled>
                                <option value="1" selected>kav</option>
                                <option value="2" selected>clamav</option>
                            </select>
                        </div>
                    </div>
                </div>
                <!-- clamav 提示 -->
                <div class="tipsWrapper" id="clamavTips">
                    <div class="alert alert-block alert-info fade in" id="marktips">
                        <button type="button" class="close" data-dismiss="alert"></button>
                        <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                        <ol class="alert-ol">
                            <li>
                                <div><?php echo $LANG['UI_VIRUS_OFFLINE_VIRUS_CLAMAV_TIPS1'] ?></div>
                                <div>
                                    <a href="<?php echo $LANG['UI_VIRUS_OFFLINE_VIRUS_CLAMAV_TIPS1_URL1'] ?>" target="_blank"><?php echo $LANG['UI_VIRUS_OFFLINE_VIRUS_CLAMAV_TIPS1_URL1'] ?></a>
                                </div>
                                <div>
                                    <a href="<?php echo $LANG['UI_VIRUS_OFFLINE_VIRUS_CLAMAV_TIPS1_URL2'] ?>" target="_blank"><?php echo $LANG['UI_VIRUS_OFFLINE_VIRUS_CLAMAV_TIPS1_URL2'] ?></a>
                                </div>
                                <div>
                                    <a href="<?php echo $LANG['UI_VIRUS_OFFLINE_VIRUS_CLAMAV_TIPS1_URL3'] ?>" target="_blank"><?php echo $LANG['UI_VIRUS_OFFLINE_VIRUS_CLAMAV_TIPS1_URL3'] ?></a>
                                </div>
                            </li>
                            <li><?php echo $LANG['UI_VIRUS_OFFLINE_VIRUS_CLAMAV_TIPS2'] ?></li>
                        </ol>
                    </div>
                </div>
                <!--  kav 提示 -->
                <div class="tipsWrapper" id="kavTips">
                    <div class="alert alert-block alert-info fade in" id="kavMarkTips">
                        <button type="button" class="close" data-dismiss="alert"></button>
                        <ul class="alert-ul">
                            <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>
                                :</strong>
                            <li>
                                <?php echo $LANG['UI_VIRUS_OFFLINE_VIRUS_KAV_TIPS1'] ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="drawer-footer">
                <button type="button" class="btn btn-primary"
                        id="renew_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
                <button type="button" data-dismiss="drawer" aria-label="Close"
                        class="btn btn-default"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
            </div>
        </div>
    </div>

    <!-- BEGIN UPLOAD LICENSE FILE DRAWER -->
    <div class="drawer slide " data-placement="right" tabindex="-1" role="dialog" id="uploadAuthorizationFileDrawer">
        <div class="drawer-content drawer-content-scrollable" role="document">
            <div class="drawer-header">
                <h4 class="drawer-title">
                    <i class="viconfont vicon-gongnengshouquan icon"></i>
                    <span class="text"><?php echo $LANG['WEB_TOOL_VIRUS_OP_UPLOAD_AUTHORIZATION_FILE'] ?></span>
                    <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i class="viconfont vicon-guanbi"></i></span>
                </h4>
            </div>
            <div class="drawer-body">
                <div class="detail-task-right uploadAuthorizationDiv">
                    <div class="col-md-2 add-text item-add col-md-4_en pt7">
                        <span class="required">* </span>
                        <?php echo $LANG['WEB_TOOL_VIRUS_OP_UPLOAD_AUTHORIZATION_FILE'] ?>
                    </div>
                    <div class="col-md-10 col-md-8_en">
                        <!-- 上传 -->
                        <div class="form-group item-add">
                            <div class="drawer-item-content">
                                <button id="uploadAuthorizationFileBtn" class="btn green-haze" type="button">
                                    <i class="viconfont vicon-shangchuan"></i>
                                    <?php echo $LANG['UI_SETTINGS_UPDATE_START_UPLOAD'] ?>
                                </button>
<!--                                <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true"-->
<!--                                   data-placement="right"-->
<!--                                   data-content="--><?php //echo $LANG['UI_VIRUS_ADD_UPLOAD_TIPS'] ?><!--">-->
<!--                                    <i class="viconfont vicon-tishi"></i>-->
<!--                                </a>-->
                            </div>
                        </div>

                        <!-- 上传授权文件 -->
                        <div class="form-group upload-authorization-file-result-div">
                            <label class="drawer-item-label"></label>
                            <div class="drawer-item-content">
                                <div id="uploadAuthorizationFileResult" class="dropzone"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tipsWrapper">
                    <div class="alert alert-block alert-info fade in" id="uploadAuthorizationFileTips">
                        <button type="button" class="close" data-dismiss="alert"></button>
                        <ul class="alert-ul">
                            <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                            <li>
                                <?php echo $LANG['UI_VIRUS_UNAUTHORIZED'] ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- footer -->
            <div class="drawer-footer">
                <button type="button" class="btn btn-primary" id="uploadAuthorizationFileSubmit">
                    <?php echo $LANG['UI_PUBLIC_YES'] ?>
                </button>
                <button type="button" class="btn btn-default" data-dismiss="drawer" aria-label="Close">
                    <?php echo $LANG['UI_PUBLIC_NO'] ?>
                </button>
            </div>
        </div>
    </div>
    <!-- END UPLOAD LICENSE FILE DRAWER -->
</div>
<!-- END PAGE CONTENT -->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript"
        src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/virus/virus_detail.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
