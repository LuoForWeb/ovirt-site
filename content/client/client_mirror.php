<?php
include_once '../../tpl/permission.php';
include_once '../../content/platform/public/bs_table.php';
global $LANG;
?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./css/mirror/mirror.css" />
<link href="./assets/global/plugins/jquery-file-upload/css/jquery.fileupload.css" rel="stylesheet"/>
<link rel="stylesheet" type="text/css" href="./css/platform/component/network_config.css" />

<!-- BEGIN PAGE CONTENT -->
<div class="table-toolbar">
    <div class="vin_toolbar mb-0" id="mirror_toolbar">
        <div class="leftTool"></div>
        <div class="rightTool">
            <div class="vin_btnToolbar"></div>
        </div>
    </div>
</div>

<div class="table-container client-mirror-table-container">
    <table id="mirror_table"></table>
</div>
<!-- END PAGE CONTENT -->

<!-- BEGIN MIRROR DRAWER -->
<!--添加-->
<div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true" id="drawer-add">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header">
            <h4 class="drawer-title">
                <i class="viconfont vicon-danchuangtianjia1"></i>
                <span class="text"><?php echo $LANG['UI_CLIENT_MIRRIOR']; ?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i class="viconfont vicon-guanbi"></i></span>
            </h4>
        </div>
        <div class="add-body">
            <div class="title-head">
                <div class="head-point"></div>
                <div class="head-text"><?php echo $LANG['UI_PLATFORM_SYSTEM_SET']; ?></div>
            </div>
            <div class="system-setting">
                <div class="detail-left">
                    <div class="col-md-3 label-text">
                        <?php echo $LANG['UI_CLIENT_SYSTEM_TYPE_MIRROR']; ?>
                    </div>
                    <div class="col-md-9">
                        <div class="radio-group" id="radio_group_3">
                            <label class="radio-group__item with-svg me-20 active" value="1">
                                <img src="./img/platform/linux.svg" value="1">
                            </label>
                            <label class="radio-group__item with-svg me-20" value="2">
                                <img src="./img/platform/windows.svg" value="2">
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 系统架构 -->
                <div class="detail-left margin-top-10">
                    <div class="col-md-3 label-text">
                        <?php echo $LANG['UI_CLIENT_MIRROR_OS_ARCH']; ?>
                    </div>
                    <div class="col-md-9">
                        <div class="input-icon right">
                            <select id="mirrorOsArch" class="form-control select-with">
                                <option value="9">x86_64</option>
                                <option value="12">aarch64</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="detail-left marTop">
                    <div class="col-md-3 label-text">
                        <?php echo $LANG['UI_CLIENT_MIRROR_TYPE']; ?>
                    </div>
                    <div class="col-md-9">
                        <div class="input-icon right">
                            <select id="coverType" class="form-control select-with">
                                <option value="2" selected><?php echo $LANG['UI_CLIENT_COVER_MIRROR']; ?></option>
                                <option value="1"><?php echo $LANG['UI_CLIENT_DRIVER_MIRROR']; ?></option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="detail-left marTop">
                    <div class="col-md-3 label-text">
                        <?php echo $LANG['UI_CLIENT_TIME_MIRROR']; ?>
                    </div>
                    <div class="col-md-4" style="display: inline-flex">
                        <div id="mirrorRetentionTimeSpinner">
                            <div class="input-group spinner-group">
                                <input id="second_input"  class="spinner-input form-control"
                                       onkeyup="value=value.replace(/\D/g,'')" />
                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                    <button type="button" class="input-sm btn spinner-up default">
                                        <i class="fa fa-angle-up"></i>
                                    </button>
                                    <button type="button" class="input-sm btn spinner-down default">
                                        <i class="fa fa-angle-down"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <span style="line-height: 28px;margin-left: 8px; width: 40px"><?php echo $LANG['WEB_UTILS_MINUTE']; ?></span>
                    </div>
                </div>
            </div>
            <div class="title-head systemCover">
                <div class="head-point"></div>
                <div class="head-text"><?php echo $LANG['UI_CLIENT_BACKUP_SERVER']; ?></div>
            </div>
            <div class="backup-setting systemCover">
                <div class="detail-left margin-top-10">
                    <div class="col-md-3 label-text">
                        <?php echo $LANG['UI_CLIENT_EMAIL_TYPE']; ?>
                    </div>
                    <div class="col-md-9">
                        <div class="input-icon right">
                            <select id="proxy_type" class="form-control select-with">
                                <option value='2' selected><?php echo $LANG['UI_CLIENT_EMAIL_SERVER']; ?></option>
                                <option value='1'><?php echo $LANG['UI_CLIENT_SERVER_PROXY']; ?></option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="detail-left marTop server-port">
                    <div class="col-md-3 label-text">
                        <?php echo $LANG['UI_CLIENT_SERVER_PORT']; ?>
                    </div>
                    <div class="col-md-9">
                        <div class="input-icon right">
                            <i class="fa"></i>
                            <input id="proxy_server" type="text" maxlength="128" class="form-control select-with" name="reportName" value="22710"/>
                        </div>
                    </div>
                </div>
                <div class="detail-left marTop client-port">
                    <div class="col-md-3 label-text">
                        <?php echo $LANG['UI_CLIENT_PROXY_PORT']; ?>
                    </div>
                    <div class="col-md-9">
                        <div class="input-icon right">
                            <i class="fa"></i>
                            <input id="server_proxy" type="text" maxlength="128" class="form-control select-with" name="reportName" value="23100"/>
                        </div>
                    </div>
                </div>
                <div class="detail-left marTop server-port server-ip">
                    <div class="col-md-3 label-text">
                        <?php echo $LANG['UI_CLIENT_SERVER_ADDRESS']; ?>
                    </div>
                    <div class="col-md-9">
                        <div class="input-icon right">
                            <select id="system_type" class="form-control select-with">
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="title-head systemCover">
                <div class="head-point"></div>
                <div class="head-text"><?php echo $LANG['UI_CLIENT_MIRROR_SETTING']; ?></div>
            </div>
            <div id="ipConfig" class="systemCover" style="width: 100%; margin-top: 12px;background: #FAFAFA;padding: 20px"></div>
        </div>

        <div class="drawer-footer">
            <button type="button" class="btn btn-primary" id="submit_add"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
            <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
        </div>
    </div>
</div>
<!-- END MIRROR DRAWER -->


<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/platform/component/network_config.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>

<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/client/client_mirror.js"></script>
<script type="text/javascript" src="./scripts/public/initComponents.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
