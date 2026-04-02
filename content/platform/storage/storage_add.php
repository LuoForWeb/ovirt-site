<?php include_once '../../../tpl/permission.php'; ?>
<?php include_once '../../../content/platform/public/bs_table.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>

<style>
    /* .fixed-table-body{height: auto !important;}*/
    .bootstrap-mutiple-select{
        width: 100%!important;
    }

    /* 更改多选下拉的选中的图标的位置 start */
    .bootstrap-select.bootstrap-mutiple-select .dropdown-menu.inner li a {
        position: relative;
        padding-left: 35px !important;
    }

    /* 隐藏原来的勾选图标 */
    .bootstrap-select.bootstrap-mutiple-select .dropdown-menu.inner .glyphicon-ok {
        display: none !important;
    }

    /* 添加左边的勾选标志 */
    .bootstrap-select.bootstrap-mutiple-select .dropdown-menu.inner li.selected a::before {
        content: "✓";
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #0FBF98;
        font-weight: bold;
        font-size: 14px;
    }

    /* 选中状态的背景色 */
    .bootstrap-select.bootstrap-mutiple-select .dropdown-menu.inner li.selected a {
        background-color: rgba(0, 123, 255, 0.1) !important;
    }
    /* 更改多选下拉的选中的图标的位置 end */
</style>
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="storage_manager" href="./content/platform/storage/storagedevice.php">
            <span><?php echo $LANG['UI_PLATFORM_STORAGE'] ?></span>
        </a>
    </li>
    <span>
        >
    </span>
    <li>
        <a class="ajaxify" name="storage_manager" href="./content/platform/storage/storage.php">
            <?php echo $LANG['UI_PLATFORM_STORAGE_BACKUP'] ?>
        </a>
    </li>
    <span>></span>
    <span class="curent"><?php echo $LANG['UI_PUBLIC_ADD_OPERATION'] ?></span>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row row-manager">
    <div class="col-md-12 col-manager">
        <!-- BEGIN VALIDATION STATES-->
        <div class="portlet box blue-hoki" id="addcontent">
            <div class="portlet-title">
                <div class="caption">
                    <i class="viconfont vicon-ge_modify"></i><?php echo $LANG['UI_STORAGE_ADD_IMPUT_INFO'] ?>
                </div>
            </div>
            <div class="portlet-body form">
                <!-- BEGIN FORM-->
                <div class="form-horizontal">
                    <div class="form-body">
                        <form id="addnodeform">
                            <div class="form-group">
                                <label class="control-label col-md-3">
                                    <span class="required">* </span>
                                    <?php echo $LANG['UI_STORAGE_TYPE'] ?>
                                </label>
                                <div class="col-md-4">
                                    <select class="form-control " name="storagetype">
                                        <option value="0"></option>
                                        <option value="3"><?php echo $LANG['UI_STORAGE_TYPE3'] ?></option>
                                        <option value="1"><?php echo $LANG['UI_STORAGE_TYPE1'] ?></option>
                                        <option value="11"><?php echo $LANG['UI_STORAGE_TYPE11'] ?></option>
                                        <option value="2"><?php echo $LANG['UI_STORAGE_TYPE2'] ?></option>
                                        <option value="4"><?php echo $LANG['UI_STORAGE_TYPE4'] ?></option>
                                        <option value="5">iSCSI</option>
                                        <option value="6"><?php echo $LANG['UI_STORAGE_TYPE6'] ?></option>
                                        <option value="7"><?php echo $LANG['UI_STORAGE_TYPE7'] ?></option>
                                        <?php
                                            if (!empty( $_SESSION['authfun']['copy'])) {
                                                echo '<option value="8">' . $LANG['UI_COPY_ALLOPATRIC_BACKUP_SYSTEM'] . '</option>';
                                            }
                                            if (!empty( $_SESSION['authfun']['cloudstorage'])) {
                                                echo '<option value="9">' . $LANG['UI_STORAGE_TYPE9'] . '</option>';
                                            }
                                        ?>

                                        <!--<option value="12">华为CBR存储</option>-->
                                        <?php if (!in_array($CONF['SYSTEM_INFO']['vendor'], ['inspur', 'sangfor'])){
                                            // 浪潮/深信服的不显示
                                            echo ' <option value="101">Huawei OceanProtect(NFS)</option>
                                                    <option value="102">Huawei OceanProtect(CIFS)</option>';
                                        } ?>
                                        <!-- <option value="13"><?php /*echo $LANG['UI_STORAGE_TYPE_FILE'];*/ ?></option>-->

                                        <?php if ($CONF['SYSTEM_INFO']['arch_type'] != 'arm' && !empty($_SESSION['authfun']['ddBoost'])) {
                                            // arm的不显示
                                            echo ' <option value="16">DELL Data Domain Boost</option>';
                                        } ?>
                                    </select>
                                    <div><span class="help-block ">
                                            <?php echo $LANG['UI_STORAGE_TYPE_SELECT_TIPS'] ?>
                                        </span></div>
                                </div>
                            </div>
                            <div class="form-group display-none" id="nodeDiv">
                                <label class="control-label col-md-3">
                                    <span class="required">* </span>
                                    <?php echo $LANG['UI_NODE_IPADDR'] ?>
                                </label>
                                <div class="col-md-4">
                                    <select class="form-control " name="nodeselect">
                                    </select>
                                    <div><span class="help-block ">
                                            <?php echo $LANG['UI_STORAGE_ADD_NODE_TIPS'] ?>
                                        </span></div>
                                </div>
                            </div>

                            <div class="form-group display-none" id="node2Div">
                                <label class="control-label col-md-3">
                                    <span class="required">* </span>
                                    <?php echo $LANG['UI_NODE_IPADDR'] ?>
                                </label>
                                <div class="col-md-4" id="muti-node">
                                    <div class="input-icon">
                                        <select id="nodeselect2" name="nodeselect2"
                                                class="selectpicker show-tick bootstrap-mutiple-select" multiple data-live-search="true"
                                                data-actions-box="true">
                                        </select>
                                        <div><span class="help-block ">
                                                <?php echo $LANG['UI_STORAGE_ADD_NODE_TIPS'] ?>
                                            </span></div>
                                    </div>
                                </div>
                            </div>

                        </form>

                        <div class="form-group display-none wwndiv">
                            <label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_FIBER_CHANNEL'] ?>
                            </label>
                            <div class="col-md-8">
                                <div class="table-container">
                                    <table class="table" id="wwntable">

                                    </table>
                                </div>
                                <div><span class="help-block ">
                                        <?php echo $LANG['UI_STORAGE_FC_TIPS'] ?>
                                    </span></div>
                            </div>
                        </div>

                        <!-- 1-4 STORAGE DIV START -->
                        <div id="morestoragediv" class="display-none asdiv">
                            <form>
                                <div class="form-group">
                                    <label class="control-label col-md-3">
                                        <span class="required">* </span>
                                        <?php echo $LANG['UI_STORAGE_SELECT_RES'] ?>
                                    </label>
                                    <div class="col-md-8">
                                        <div class="table-container">
                                            <table class="table" id="resourcetable">
                                            </table>
                                            <div>
                                                <span class="help-block ">
                                                    <span id="selecttips"><?php echo $LANG['UI_STORAGE_ISCSI_SELECT_TARGET'] ?></span>
                                                    <a class="moreselectparams"><?php echo $LANG['UI_STORAGE_CONFIG_MOUNT_PARAMS'] ?></a>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <!-- 1-4 STORAGE DIV END -->

                        <!-- ISCSI DIV START -->
                        <form id="iscsidiv" class="display-none asdiv">
                            <div class="form-group">
                                <label class="control-label col-md-3">
                                    <span class="required">* </span>
                                    <?php echo $LANG['UI_STORAGE_ISCSI_NAME'] ?>
                                </label>
                                <div class="col-md-4 margin10" id="iscsiname">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3">
                                    <span class="required">* </span>
                                    <?php echo $LANG['UI_STORAGE_ISCSI_HOST'] ?>
                                </label>
                                <div class="col-md-3">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input type="text" maxlength="15" class="form-control" name="iscsiip" placeholder="192.168.1.10" />
                                        <div><span class="help-block ">
                                                <?php echo $LANG['UI_STORAGE_ISCSI_IP_TIPS'] ?>
                                                <?php echo $LANG['UI_STORAGE_ISCSI_IP_TIPS1'] ?><a id="moreiscsi"><?php echo $LANG['UI_STORAGE_ISCSI_IP_TIPS2'] ?></a>
                                            </span></div>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input type="text" maxlength="5" class="form-control" value='3260' name="iscsiport" placeholder="3260" />
                                        <div><span class="help-block ">
                                                <?php echo $LANG['UI_SETTINGS_NOTICE_PORT'] ?>
                                            </span></div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-offset-3 col-md-4">
                                    <button type="button" class="btn btn-default textalignr" id="iscsiscan"><?php echo $LANG['UI_STORAGE_ISCSI_SCAN_TARGET'] ?></button>
                                    <div>
                                        <span class="help-block ">
                                            <a class="moreselectparams"><?php echo $LANG['UI_STORAGE_CONFIG_MOUNT_PARAMS'] ?></a>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group target">
                                <label class="control-label col-md-3">
                                    <span class="required">* </span>
                                    <?php echo 'Target LUN' ?>
                                </label>
                                <div class="col-md-6">
                                    <div class="table-container">
                                        <table class="table table-striped table-bordered table-hover" id="targetluntable">
                                            <ul id="permissionTree" class="ztree bd1de5  tree_div ztree-fa"></ul>
                                        </table>
                                    </div>
                                    <div><span class="help-block ">
                                            <?php echo $LANG['UI_STORAGE_ISCSI_SELECT_TARGET'] ?>
                                        </span></div>
                                </div>
                            </div>

                        </form>
                        <!-- ISCSI DIV END -->

                        <!-- NFS DIV START -->
                        <form id="nfsdiv" class="display-none asdiv">
                            <div class="form-group">
                                <label class="control-label col-md-3">
                                    <span class="required">* </span>
                                    <?php echo $LANG['UI_STORAGE_SHARED_FOLDERS'] ?>
                                </label>
                                <div class="col-md-4">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input style="display:none"><!-- for disable autocomplete on chrome -->
                                        <input type="text" maxlength="1280" class="form-control" name="host" placeholder="192.168.1.10:/path/directory" />
                                        <div><span class="help-block ">
                                                <?php echo $LANG['UI_STORAGE_NFS_TIPS'] ?> <a id="morenfsparams"><?php echo $LANG['UI_STORAGE_CONFIG_MOUNT_PARAMS'] ?></a>
                                            </span></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group nfsConfigDiv display-hide">
                                <label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_MOUNT_PARAMS'] ?>
                                </label>
                                <div class="col-md-4">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input style="display:none"><!-- for disable autocomplete on chrome -->
                                        <input type="text" maxlength="1280" class="form-control" id="nfsConfig" placeholder="vers=x.0,xxx=xxx" />
                                        <div><span class="help-block ">
                                                <?php echo $LANG['UI_STORAGE_CONFIG_MOUNT_PARAMS_TIPS'] ?>
                                            </span></div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <!-- NFS DIV END -->

                        <!-- CIFS DIV START -->
                        <form id="cifsdiv" class="display-none asdiv">
                            <div class="form-group">
                                <label class="control-label col-md-3">
                                    <span class="required">* </span>
                                    <?php echo $LANG['UI_STORAGE_SHARED_FOLDERS'] ?>
                                </label>
                                <div class="col-md-4">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input style="display:none"><!-- for disable autocomplete on chrome -->
                                        <input type="text" maxlength="1280" class="form-control" name="host" placeholder="//192.168.1.10/path/directory" />
                                        <div><span class="help-block ">
                                                <?php echo $LANG['UI_STORAGE_CIFS_TIPS'] ?> <a id="morecifsparams"><?php echo $LANG['UI_STORAGE_CONFIG_MOUNT_PARAMS'] ?></a>
                                            </span></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group cifsConfigDiv display-hide">
                                <label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_MOUNT_PARAMS'] ?>
                                </label>
                                <div class="col-md-4">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input style="display:none"><!-- for disable autocomplete on chrome -->
                                        <input type="text" maxlength="1280" class="form-control" id="cifsConfig" placeholder="vers=x.0,xxx=xxx" />
                                        <div><span class="help-block ">
                                                <?php echo $LANG['UI_STORAGE_CONFIG_MOUNT_PARAMS_TIPS'] ?>
                                            </span></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo $LANG['UI_LOGIN_USERNAME'] ?>
                                </label>
                                <div class="col-md-4">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input style="display:none"><!-- for disable autocomplete on chrome -->
                                        <input type="text" maxlength="128" class="form-control" name="username"/>
                                        <div><span class="help-block ">
                                                <?php echo $LANG['UI_STORAGE_CIFS_USER'] ?>
                                            </span></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo $LANG['UI_LOGIN_PASSWORD'] ?>
                                </label>
                                <div class="col-md-4">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input style="display:none"><!-- for disable autocomplete on chrome -->
                                        <input type="password" autocomplete="off" maxlength="128" class="form-control" name="password" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')" />
                                        <div><span class="help-block ">
                                                <?php echo $LANG['UI_STORAGE_CIFS_PASS'] ?>
                                            </span></div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <!-- CIFS DIV END -->

                        <!-- dddb DIV START -->
                        <form id="dddbdiv" class="display-none asdiv">
                            <div class="form-group">
                                <label class="control-label col-md-3">
                                    <span class="required">* </span>
                                    <?php echo $LANG['UI_STORAGE_DDDB_DOMAIN']?>
                                </label>
                                <div class="col-md-4">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input style="display:none"><!-- for disable autocomplete on chrome -->
                                        <input type="text" maxlength="1280" class="form-control" name="data_domain_system" placeholder="192.168.1.10" />
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3">
                                    <span class="required">* </span>
                                    <?php echo $LANG['UI_STORAGE_DDDB_UNIT']?>
                                </label>
                                <div class="col-md-4">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input style="display:none"><!-- for disable autocomplete on chrome -->
                                        <input type="text" maxlength="1280" class="form-control" name="storage_unit" placeholder="<?php echo $LANG['UI_STORAGE_DDDB_UNIT']?>" />
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3">
                                    <span class="required">* </span>
                                    <?php echo $LANG['UI_LOGIN_USERNAME'] ?>
                                </label>
                                <div class="col-md-4">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input style="display:none"><!-- for disable autocomplete on chrome -->
                                        <input type="text" maxlength="128" class="form-control" name="username"/>
                                        <div><span class="help-block ">
                                                 <?php echo $LANG['UI_STORAGE_DDDB_USERNAME_TIPS']?>
                                            </span></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3">
                                    <span class="required">* </span>
                                    <?php echo $LANG['UI_LOGIN_PASSWORD'] ?>
                                </label>
                                <div class="col-md-4">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input style="display:none"><!-- for disable autocomplete on chrome -->
                                        <input type="password" autocomplete="off" maxlength="128" class="form-control" name="password" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')" />
                                        <div><span class="help-block ">
                                                <?php echo $LANG['UI_STORAGE_DDDB_PAWSSWORD_TIPS']?>
                                            </span></div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <!-- dddb DIV END -->

                        <!-- COPY STORAGE DIV START -->
                        <form id="copydiv" class="display-none asdiv">
                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_IP_OR_DOMAIN'] ?><span class="required">
                                * </span>
                                </label>
                                <div class="col-md-3 form-group" style="margin-left: 0px;">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input id="remoteIp" type="text" maxlength="15" class="form-control" name="remoteip" placeholder="192.168.1.10"/>
                                        <div><span class="help-block "><?php echo $LANG['UI_STORAGE_REMOTE_IP_OR_DOMAIN'] ?>
                                        </span></div>
                                    </div>
                                </div>
                                <div class="col-md-1 form-group"  style="padding-left: 0; margin-left: 15px;">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input style="padding-right: 12px;" type="number" maxlength="5" class="form-control" value="22711" name="remoteport" placeholder="22711" onkeyup="if(value.length >=5) value=value.slice(0, 5)"/>
                                        <div><span class="help-block ">
                                        <?php echo $LANG['UI_SETTINGS_NOTICE_PORT'] ?>
                                        </span></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3">
                                    <?php echo $LANG['UI_LOGIN_USERNAME'] ?>
                                    <span class="required">*</span>
                                </label>
                                <div class="col-md-4">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input style="display:none"><!-- for disable autocomplete on chrome -->
                                        <input type="text" maxlength="128" class="form-control" name="username"/>
                                        <div><span class="help-block ">
                                        <?php echo $LANG['UI_STORAGE_REMOTE_ADMIN_USER_NAME'] ?>
                                        </span></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3">
                                    <?php echo $LANG['UI_LOGIN_PASSWORD'] ?>
                                    <span class="required">*</span>
                                </label>
                                <div class="col-md-4">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input style="display:none"><!-- for disable autocomplete on chrome -->
                                        <input type="password" autocomplete="off" maxlength="128" class="form-control" name="password" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')"/>
                                        <div><span class="help-block ">
                                        <?php echo $LANG['UI_STORAGE_REMOTE_ADMIN_PASSWORD'] ?>
                                        </span></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-offset-3 col-md-4">
                                    <button type="button" id="scanRemoteStorage" class="btn green-haze"><?php echo $LANG['UI_STORAGE_REMOTE_SCAN_STORAGE'] ?></button>
                                    <div>
                                            <span class="help-block ">
                                        <?php echo $LANG['UI_STORAGE_REMOTE_SCAN_STORAGE_LIST_ALL'] ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group storage-list" style="display:none">
                                <div class="table-container">
                                    <label class="control-label col-md-3">
                                        <?php echo $LANG['UI_STORAGE_SELECT_RES'] ?>
                                        <span class="required">*</span>
                                    </label>
                                    <div class="col-md-6">
                                        <table id="remote_storage_table">
                                        </table>
                                        <div>
                                                <span class="help-block ">
                                                <?php echo $LANG['UI_STORAGE_REMOTE_SELECT_REMOTE_STORAGE'] ?>
                                                </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <!-- COPY DIV END -->

                        <!-- CLOUD STORAGE START -->
                        <form id="cloudDiv" class="display-none asdiv">
                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_CLOUD_VENDOR'] ?>
                                </label>
                                <div class="col-md-4">
                                    <select class="form-control " id="vendorSelect" name="vendor">
                                        <option value="1">AWS S3</option>
                                        <option value="2">Azure</option>
                                        <option value="3"><?php echo $LANG['UI_STORAGE_CLOUD_VENDOR_ALI'] ?></option>
                                        <option value="4"><?php echo $LANG['UI_STORAGE_CLOUD_VENDOR_HUAWEI'] ?></option>
                                        <option value="5"><?php echo $LANG['UI_STORAGE_CLOUD_VENDOR_TENCENT'] ?></option>
                                        <option value="6">Ceph S3</option>
                                        <option value="7">Wasabi</option>
                                        <option value="8">MinIO</option>
                                        <?php if ($CONF['SYSTEM_INFO']['enterprise'] != 'enterprise_en') {
                                            //海外版不显示这个vendor
                                            echo '<option value="9">Huawei OceanStor Pacific</option>';
                                        }
                                        ?>
                                        <option value="10"><?php echo $LANG['UI_STORAGE_CLOUD_VENDOR_OTHER'] ?></option>
                                    </select>
                                    <div><span class="help-block ">
                                            <?php echo $LANG['UI_STORAGE_CLOUD_VENDOR_TIPS'] ?>
                                        </span></div>
                                </div>
                            </div>
                            <div class="form-group awsDiv regionDiv">
                                <label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_CLOUD_REGION'] ?>
                                </label>
                                <div class="col-md-4">
                                    <div class="selectRegionDiv">
                                        <select class="form-control " id="regionSelect" name="region">
                                            <option value="cn-north-1"><?php echo $LANG['UI_STORAGE_CLOUD_REGION1'] ?></option>
                                            <option value="cn-northwest-1"><?php echo $LANG['UI_STORAGE_CLOUD_REGION2'] ?></option>
                                            <option value=""><?php echo $LANG['UI_STORAGE_CLOUD_REGION3'] ?></option>
                                        </select>
                                        <div><span class="help-block ">
                                                <?php echo $LANG['UI_STORAGE_CLOUD_REGION_TIPS'] ?>
                                                <a id="diyRegion"><?php echo $LANG['UI_INSTANT_VM_TARGET_DIY1'] ?></a>
                                            </span></div>
                                    </div>
                                    <div class="input-icon display-none inputRegionDiv">
                                        <i class="fa"></i>
                                        <input type="text" maxlength="128" class="form-control" name="regiondiy" />
                                        <span class="help-block inputRegionTips"><?php echo $LANG['UI_STORAGE_CLOUD_REGION_TIPS'] ?>
                                            <a id="selectRegion"><?php echo $LANG['UI_INSTANT_VM_TARGET_DIY2'] ?></a></span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group display-none cephDiv">
                                <label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_CLOUD_REGION'] ?>
                                </label>
                                <div class="col-md-4">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input style="display:none"><!-- for disable autocomplete on chrome -->
                                        <input type="text" maxlength="128" class="form-control" name="regioninput" id="regioninput" placeholder="us-east-1" />
                                        <div><span class="help-block ">
                                                <?php echo $LANG['UI_STORAGE_CLOUD_REGION_CEPH_TIPS'] ?>
                                            </span></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group display-none servernodeDiv">
                                <label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_CLOUD_AWS_SERVER_NODE'] ?>
                                </label>
                                <div class="col-md-4">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input style="display:none"><!-- for disable autocomplete on chrome -->
                                        <input type="text" maxlength="128" class="form-control" name="servernode" placeholder="" />
                                        <div><span class="help-block "><?php echo $LANG['UI_STORAGE_CLOUD_AWS_SERVER_NODE_TIPS'] ?>
                                            </span></div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group display-none servernodeDiv">
                                <label class="control-label col-md-3 form-group-label"><?php echo $LANG['UI_STORAGE_CLOUD_AWS_SSL_CERTIFICATE_VERIFICATION'] ?></label>
                                <div class="col-md-2 form-group-content">
                                    <input type="checkbox" id="sslConnect" checked class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                </div>
                            </div>

                            <div class="form-group awsDiv">
                                <label class="control-label col-md-3">
                                    <span class="required">* </span>
                                    <?php echo $LANG['UI_LOGIN_USERNAME'] ?>
                                </label>
                                <div class="col-md-4">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input style="display:none"><!-- for disable autocomplete on chrome -->
                                        <input type="text" maxlength="128" class="form-control" name="cloudname" placeholder="access key id" onkeyup="this.value=this.value.replace(/[, ]/g,'')" />
                                        <div><span class="help-block ">
                                                <?php echo $LANG['UI_STORAGE_CLOUD_ACCESS_KEY'] ?>
                                            </span></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group awsDiv">
                                <label class="control-label col-md-3">
                                    <span class="required">* </span>
                                    <?php echo $LANG['UI_LOGIN_PASSWORD'] ?>
                                </label>
                                <div class="col-md-4">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input style="display:none"><!-- for disable autocomplete on chrome -->
                                        <input type="password" autocomplete="off" maxlength="128" class="form-control" name="cloudpassword" placeholder="Secret access key" onkeyup="this.value=this.value.replace(/[, ]/g,'')" />
                                        <div><span class="help-block ">
                                                <?php echo $LANG['UI_STORAGE_CLOUD_SECRET_KEY'] ?>
                                            </span></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group awsDiv">
                                <label class="control-label col-md-3">
                                    <span class="required">* </span>
                                    <?php echo $LANG['UI_STORAGE_CLOUD_BUCKET_NAME'] ?>
                                </label>
                                <div class="col-md-2">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input style="display:none"><!-- for disable autocomplete on chrome -->
                                        <input type="text" maxlength="128" class="form-control" name="bucketname" />
                                        <div><span class="help-block ">
                                                <?php echo $LANG['UI_STORAGE_CLOUD_BUCKET_TIPS'] ?>
                                            </span></div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-default textalignr" id="bucketscan"><?php echo $LANG['UI_STORAGE_CLOUD_SCAN_BUCKET'] ?></button>
                                </div>
                            </div>
                            <div class="form-group azureDiv display-none">
                                <label class="control-label col-md-3">
                                    <span class="required">* </span>
                                    <?php echo $LANG['UI_ARCHIVE_CLOUD_CONNEC_STR'] ?>
                                </label>
                                <div class="col-md-4">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input style="display:none"><!-- for disable autocomplete on chrome -->
                                        <input type="text" maxlength="512" class="form-control" name="cloudstring" placeholder="" />
                                        <div><span class="help-block ">
                                            </span></div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group azureDiv display-none">
                                <label class="control-label col-md-3">
                                    <span class="required">* </span>
                                    <?php echo $LANG['UI_ARCHIVE_CLOUD_CONTAIN'] ?>
                                </label>
                                <div class="col-md-2">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input style="display:none"><!-- for disable autocomplete on chrome -->
                                        <input type="text" maxlength="128" class="form-control" name="containername" />
                                        <div><span class="help-block ">
                                            </span></div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn floatr btn-default textalignr" id="containerscan"><?php echo $LANG['UI_ARCHIVE_CLOUD_SCAN_CONTAIN'] ?></button>
                                </div>
                            </div>

                            <div class="form-group folderDiv">
                                <label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_CLOUD_FOLDER'] ?>
                                </label>
                                <div class="col-md-4">
                                    <div class="selectdiv display-none">
                                        <select class="form-control " id="folderSelect">
                                        </select>
                                        <span class="help-block "><?php echo $LANG['UI_STORAGE_CLOUD_SELECT_FOLDER_TIPS'] ?>
                                            <a id="diyTab"><?php echo $LANG['UI_INSTANT_VM_TARGET_DIY'] ?></a></span>
                                    </div>

                                    <div class="input-icon right inputdiv">
                                        <i class="fa"></i>
                                        <input type="text" maxlength="128" class="form-control" name="folderinput" />
                                        <span class="help-block"><?php echo $LANG['UI_STORAGE_CLOUD_INPUT_FOLDER_TIPS'] ?>
                                            <a id="selectTab"><?php echo $LANG['UI_INSTANT_VM_TARGET_DIY2'] ?></a></span>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <!-- CLOUD STORAGE END -->

                        <!-- LOCAL DIR START -->
                        <form id="localdiv" class="display-none asdiv">
                            <div class="form-group">
                                <label class="control-label col-md-3">
                                    <span class="required">* </span>
                                    <?php echo $LANG['UI_STORAGE_LOCAL_DIR_PATH'] ?>
                                </label>
                                <div class="col-md-4">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input style="display:none"><!-- for disable autocomplete on chrome -->
                                        <input id="dirName" type="text" maxlength="1280" class="form-control" name="dir" placeholder="/path/directory" />
                                        <div><span class="help-block ">
                                                <?php echo $LANG['UI_STORAGE_LOCAL_DIR_PATH_TIPS'] ?>
                                            </span></div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <!-- NFS DIV END -->

                        <!-- CLOUDHUAWEI STORAGE START -->
                        <form id="cloudhwDiv" class="display-none asdiv">
                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_CLOUD_VENDOR'] ?>
                                </label>
                                <div class="col-md-4">
                                    <select class="form-control " id="vendorhwSelect" name="vendorhw">
                                        <option value="4"><?php echo $LANG['UI_STORAGE_CLOUD_VENDOR_HUAWEI'] ?></option>
                                    </select>
                                    <div><span class="help-block ">
                                            <?php echo $LANG['UI_STORAGE_CLOUD_VENDOR_TIPS'] ?>
                                        </span></div>
                                </div>
                            </div>
                            <div class="form-group awsDiv regionDiv">
                                <label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_CLOUD_REGION'] ?>
                                </label>
                                <div class="col-md-4">
                                    <div class="selectRegionhwDiv">
                                        <select class="form-control " id="regionhwSelect" name="regionhw">
                                            <option value="cn-north-1"><?php echo $LANG['UI_STORAGE_CLOUD_REGION1'] ?></option>
                                            <option value="cn-northwest-1"><?php echo $LANG['UI_STORAGE_CLOUD_REGION2'] ?></option>
                                            <option value=""><?php echo $LANG['UI_STORAGE_CLOUD_REGION3'] ?></option>
                                        </select>
                                        <div><span class="help-block ">
                                                <?php echo $LANG['UI_STORAGE_CLOUD_REGION_TIPS'] ?>
                                                <a id="diyhwRegion"><?php echo $LANG['UI_INSTANT_VM_TARGET_DIY1'] ?></a>
                                            </span></div>
                                    </div>
                                    <div class="input-icon display-none inputRegionhwDiv">
                                        <i class="fa"></i>
                                        <input type="text" maxlength="128" class="form-control" name="regionhwdiy" />
                                        <span class="help-block inputRegionTips"><?php echo $LANG['UI_STORAGE_CLOUD_REGION_TIPS'] ?>
                                            <a id="selecthwRegion"><?php echo $LANG['UI_INSTANT_VM_TARGET_DIY2'] ?></a></span>
                                    </div>
                                </div>
                            </div>


                            <div class="form-group awsDiv">
                                <label class="control-label col-md-3">
                                    <span class="required">* </span>
                                    <?php echo $LANG['UI_LOGIN_USERNAME'] ?>
                                </label>
                                <div class="col-md-4">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input style="display:none"><!-- for disable autocomplete on chrome -->
                                        <input type="text" maxlength="128" class="form-control" name="cloudname" placeholder="access key id" onkeyup="this.value=this.value.replace(/[, ]/g,'')" />
                                        <div><span class="help-block ">
                                                <?php echo $LANG['UI_STORAGE_CLOUD_ACCESS_KEY'] ?>
                                            </span></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group awsDiv">
                                <label class="control-label col-md-3">
                                    <span class="required">* </span>
                                    <?php echo $LANG['UI_LOGIN_PASSWORD'] ?>
                                </label>
                                <div class="col-md-4">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input style="display:none"><!-- for disable autocomplete on chrome -->
                                        <input type="password" autocomplete="off" maxlength="128" class="form-control" name="cloudpassword" placeholder="Secret access key" onkeyup="this.value=this.value.replace(/[, ]/g,'')" />
                                        <div><span class="help-block ">
                                                <?php echo $LANG['UI_STORAGE_CLOUD_SECRET_KEY'] ?>
                                            </span></div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3">
                                    <span class="required">* </span>
                                    <?php echo $LANG['UI_STORAGE_TYPE_FILE_NAME'] ?>
                                </label>
                                <div class="col-md-4">
                                    <div class="input-icon right ">
                                        <i class="fa"></i>
                                        <input type="text" maxlength="64" class="form-control" name="system_name" />
                                        <div><span class="help-block "><?php echo $LANG['UI_STORAGE_TYPE_FILE_NAME'] ?></span></div>
                                    </div>
                                </div>
                            </div>

                        </form>
                        <!-- CLOUDHUAWEI STORAGE END -->

                        <div class="form-group display-none" id="customParamdiv">
                            <label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_MOUNT_PARAMS'] ?>
                            </label>
                            <div class="col-md-4">
                                <div class="input-icon right">
                                    <i class="fa"></i>
                                    <input style="display:none"><!-- for disable autocomplete on chrome -->
                                    <input type="text" maxlength="1280" value="" class="form-control" id="customConfig" placeholder="" />
                                    <div><span class="help-block ">
                                        <?php echo $LANG['UI_STORAGE_CONFIG_MOUNT_PARAMS_CUSTOM_TIPS'] ?>
                                    </span></div>
                                </div>
                            </div>
                        </div>
                        <!-- 华为CBR START -->
                        <form id="huaweicbrdiv" class="display-none asdiv">
                            <div class="form-group cbraccessid">
                                <label class="control-label col-md-3"><span class="required">* </span><?php echo $LANG['UI_STORAGE_CBR_USERNAME'] ?>
                                </label>
                                <div class="col-md-4">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input style="display:none"><!-- for disable autocomplete on chrome -->
                                        <input type="text" maxlength="128" class="form-control" name="cbraccessid" placeholder="access key id" onkeyup="this.value=this.value.replace(/[, ]/g,'')"/>
                                        <div><span class="help-block ">
                                            <?php echo $LANG['UI_STORAGE_CBR_USERNAME_TIPS'] ?>
                                        </span></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3"><span class="required">* </span><?php echo $LANG['UI_STORAGE_CBR_PASSWORD'] ?>
                                </label>
                                <div class="col-md-3">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input style="display:none"><!-- for disable autocomplete on chrome -->
                                        <input type="password" maxlength="128" class="form-control" name="cbraccesskey" placeholder="Secret access key" onkeyup="this.value=this.value.replace(/[, ]/g,'')"/>
                                        <div><span class="help-block ">
                                            <?php echo $LANG['UI_STORAGE_CBR_PASSWORD_TIPS'] ?>
                                        </span></div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-default textalignr" id="cbrGetArea"><?php echo $LANG['UI_STORAGE_CBR_GET_REGION'] ?></button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3"><span class="required">* </span><?php echo $LANG['UI_STORAGE_CLOUD_REGION'] ?>
                                </label>
                                <div class="col-md-4" id="cbr-muti-region">
                                    <div class="input-icon right">
                                        <select id="hwregion_list" name="hwregionlist" class="selectpicker show-tick bootstrap-mutiple-select" multiple data-live-search="true" data-actions-box="true" data-size="5" style="height:34px;">
                                        </select>
                                        <div><span class="help-block ">
                                            <?php echo $LANG['UI_STORAGE_CBR_GET_REGION_TIPS'] ?>
                                        </span></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_CBR_STORAGE_ID'] ?></label>
                                <div class="col-md-4">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input style="display:none">
                                        <input maxlength="128" class="form-control" name="cbrstorageid" onkeyup="this.value=this.value.replace(/[, ]/g,'')"/>
                                        <div><span class="help-block ">
                                            <?php echo $LANG['UI_STORAGE_CBR_STORAGE_ID_TIPS'] ?>
                                        </span></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3"><span class="required">* </span><?php echo $LANG['UI_STORAGE_CBR_STORAGE_ADMINISTRATOR_NAME'] ?></label>
                                <div class="col-md-4">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input style="display:none">
                                        <input maxlength="128" class="form-control" name="huaweiuserak" placeholder="access key id" onkeyup="this.value=this.value.replace(/[, ]/g,'')"/>
                                        <div><span class="help-block ">
                                            <?php echo $LANG['UI_STORAGE_CBR_STORAGE_ADMINISTRATOR_NAME_TIPS'] ?>
                                        </span></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3"><span class="required">* </span><?php echo $LANG['UI_STORAGE_CBR_STORAGE_ADMINISTRATOR_PASSWORD'] ?></label>
                                <div class="col-md-4">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input style="display:none">
                                        <input type="password" maxlength="128" class="form-control" name="huaweiusersk" placeholder="Secret access key" onkeyup="this.value=this.value.replace(/[, ]/g,'')"/>
                                        <div><span class="help-block ">
                                            <?php echo $LANG['UI_STORAGE_CBR_STORAGE_ADMINISTRATOR_PASSWORD_TIPS'] ?>
                                        </span></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3"><span class="required">* </span><?php echo $LANG['UI_STORAGE_CBR_AUTH_LIST'] ?></label>
                                <div class="col-md-4">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input style="display:none">
                                        <input maxlength="128" class="form-control" name="huaweiusername" placeholder="user1,user2"/>
                                        <div><span class="help-block ">
                                            <?php echo $LANG['UI_STORAGE_CBR_AUTH_LIST_TIPS'] ?>
                                        </span></div>
                                    </div>
                                </div>
                            </div>
                            <div class="hwdatascandiv">
                                <div class="form-group">
                                    <label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_CBR_DATA_SCAN_SWITCH'] ?></label>
                                    <div class="col-md-1">
                                        <input type="checkbox" id="datascanswitch" checked  class="make-switch" data-on-color="primary" data-off-color="info"
                                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                    </div>
                                    <div class="col-md-2 mt5">
                                        <a class="popovers" data-container="body" data-trigger="hover"
                                           data-placement="right" data-content="<?php echo $LANG['UI_STORAGE_CBR_DATA_SCAN_SWITCH_TIPS'] ?>">
                                            <i class="viconfont vicon-tishi"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="form-group datascandiv">
                                    <label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_CBR_SCAN_INTERVAL'] ?>
                                    </label>
                                    <div class="col-md-4" style="display:inline-flex">
                                        <div id="cbrscantime" style="width: 140px;">
                                            <div class="input-group spinner-group" style="width:140px;">
                                                <input type="text" id="cbrscantimevalue" style="text-align: center;" class="spinner-input form-control" maxlength="3" onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}"
                                                       onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'0')}else{this.value=this.value.replace(/\D/g,'')}" >
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
                                        <div style="font-size: 16px;margin: 6px 12px 12px 12px;">
                                            <?php echo $LANG['WEB_UTILS_MINUTE'] ?>
                                        </div><br>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <!-- 华为CBR END -->
                        <div class="form-group">
                            <label class="control-label col-md-3"><span class="required">*</span><?php echo $LANG['UI_STORAGE_NAME'] ?></label>
                            <div class="col-md-4">
                                <div class="input-icon right ">
                                    <i class="fa"></i>
                                    <input type="text" maxlength="64" class="form-control" name="rname" />
                                    <div><span class="help-block "><?php echo $LANG['UI_STORAGE_RNAME'] ?></span></div>
                                </div>
                            </div>
                        </div>

                        <!-- 存储用途 -->
                        <div class="form-group display-none readonlyDiv" id="usemodeDiv">
                            <label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_USED_FOR'] ?></label>
                            <div class="col-md-4">
                                <div class="input-group " id="useMode" style="margin-top: 4px">
                                    <label class="backupDiv display-none" style="padding-right: 20px;"><input type="checkbox" id="backupCheck" data-checkbox="icheckbox_square-blue" data-mode="1" class="icheck"><?php echo $LANG['UI_PLATFORM_BACKUP'] ?></label>
                                    <label class="copyDiv display-none" style="padding-right: 20px;"><input type="checkbox" id="copyCheck" data-checkbox="icheckbox_square-blue" data-mode="2" class="icheck"><?php echo $LANG['WEB_PLATFORM_DES_COPY_AND_ARCHIVE'] ?></label>
                                    <label style="padding-right: 20px;"><input type="checkbox" id="readonlyCheck" data-checkbox="icheckbox_square-blue" data-mode="5" class="icheck"><?php echo $LANG['UI_NAS_MANAGE_ONLY_READ'] ?></label>
                                </div>
                            </div>
                        </div>

                        <!-- 自动扫描 自动分配 -->
                        <!--<div class="form-group">
                            <label class="control-label col-md-3"><?php /*echo $LANG['UI_STORAGE_CBR_AUTO_IMPORT_BACKUP_DATA']*/ ?></label>
                            <div class="col-md-4">
                                <div class="input-group" style="margin-top: 4px">
                                    <span style="padding-right: 20px;">
                                    <label class="backupDiv">
                                        <input type="checkbox" id="autoscan" data-checkbox="icheckbox_square-blue" class="icheck"><?php /*echo $LANG['UI_STORAGE_CBR_AUTO_SCAN']*/ ?></label>
                                        <a class="popovers ml15" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php /*echo $LANG['UI_STORAGE_CBR_AUTO_SCAN_TIPS']*/ ?>" data-original-title="" title="">
                                            <i class="viconfont vicon-tishi"></i>
                                        </a>
                                    </span>
                                    <span>
                                    <label class="backupDiv"><input type="checkbox" id="allocate" <?php /*if ($_SESSION['isThreePowers']) {echo 'disabled';}*/ ?> data-checkbox="icheckbox_square-blue" class="icheck"><?php /*echo $LANG['UI_STORAGE_CBR_AUTO_ASSIGN']*/ ?></label>
                                    <a class="popovers ml15" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php /*echo $LANG['UI_STORAGE_CBR_AUTO_ASSIGN_TIPS']*/ ?>" data-original-title="" title="">
                                            <i class="viconfont vicon-tishi"></i>
                                        </a>
                                    </span>
                                </div>
                            </div>
                        </div>-->


                        <div class="storagewarningdiv">
                            <div class="form-group">
                                <label class="control-label col-md-3 form-group-label"><?php echo $LANG['UI_STORAGE_ALERT'] ?></label>
                                <div class="col-md-2 form-group-content">
                                    <input type="checkbox" id="noticeswitch" checked class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                </div>
                            </div>


                            <div class="form-group warnningdiv">
                                <label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_ALERT_TYPE'] ?>
                                </label>
                                <div class="col-md-4">
                                    <select class="form-control " name="noticetype">
                                        <option value="1"><?php echo $LANG['UI_STORAGE_ALERT_PERCENT'] ?></option>
                                        <option value="2"><?php echo $LANG['UI_STORAGE_ALERT_SIZE'] ?></option>
                                    </select>
                                    <div><span class="help-block ">
                                            <?php echo $LANG['UI_STORAGE_ALERT_TIPS'] ?>
                                        </span></div>
                                </div>
                            </div>

                            <div class="form-group warnningdiv" id='percentdiv'>
                                <label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_ALERT_THRESHOLD'] ?>
                                </label>
                                <div class="col-md-4" style="display:inline-flex;">
                                    <div id="spinnerpercent">
                                        <div class="input-group spinner-group">
                                            <input type="text" id="warningpercent" style="text-align: left;" class="spinner-input form-control" maxlength="3" onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}" onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'0')}else{this.value=this.value.replace(/\D/g,'')}">
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
                                    <div style="font-size: 14px;margin: 6px 12px 12px 12px;">
                                        %
                                    </div>
                                </div>

                            </div>

                            <div class="form-group warnningdiv display-none" id='sizediv'>
                                <label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_ALERT_THRESHOLD'] ?>
                                </label>
                                <div class="col-md-4" style="display:inline-flex;">
                                    <div id="spinnersize">
                                        <div class="input-group spinner-group">
                                            <input type="text" id="warningsize" style="text-align: left;" class="spinner-input form-control" maxlength="8" onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}" onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'0')}else{this.value=this.value.replace(/\D/g,'')}">
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
                                    <div style="font-size: 14px;margin: 12px;margin-top: 7px">
                                        GB
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="cloudwarningdiv display-none">
                            <!-- <div class="form-group warnningdiv">
                                <label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_ALERT_TYPE'] ?>
                                </label>
                                <div class="col-md-4">
                                    <select class="form-control " name="cloudnoticetype">
                                        <option value="1"><?php echo $LANG['UI_STORAGE_CLOUD_CAPACITY_LIMIT'] ?></option>
                                    </select>
                                    <div><span class="help-block ">
                                        <?php echo $LANG['UI_STORAGE_CLOUD_ALERT_TIPS'] ?>
                                    </span></div>
                                </div>
                            </div> -->

                            <div class="form-group warnningdiv" id='cloudsizediv'>
                                <label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_CLOUD_CAPACITY_LIMIT'] ?>
                                </label>
                                <div class="col-md-4" style="display:inline-flex;">
                                    <div id="cloudsize">
                                        <div class="input-group spinner-group">
                                            <input type="text" id="limitsize" style="text-align: left;" class="spinner-input form-control" maxlength="8" onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}" onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'0')}else{this.value=this.value.replace(/\D/g,'')}">
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
                                    <div style="font-size: 14px;margin: 9px 12px 12px 12px;">
                                        TB
                                    </div>
                                    <a class="popovers mt10" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php echo $LANG['UI_ARCHIVE_CLOUD_LIMIT_MAX_STORE'] ?>">
                                        <i class="viconfont vicon-tishi"></i>
                                    </a>
                                </div>
                            </div>
                        </div>


                        <div class="storagewormdiv">
                            <div class="form-group">
                                <label class="control-label col-md-3 form-group-label"><?php echo $LANG['UI_STORAGE_WORM'] ?></label>
                                <div class="col-md-2 form-group-content">
                                    <input type="checkbox" id="wormswitch" checked class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                    <a class="popovers ml15" data-container="body" data-trigger="hover" data-placement="right"
                                       data-content="<?php echo $LANG['UI_STORAGE_WORM_TIPS'] ?>">
                                        <i class="viconfont vicon-tishi"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="form-group wormdiv">
                                <label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_WORM_SPACE'] ?>
                                </label>
                                <div class="col-md-4">
                                    <select class="form-control " name="wormtype">
                                        <option value="1"><?php echo $LANG['UI_STORAGE_ALERT_PERCENT'] ?></option>
                                        <option value="2"><?php echo $LANG['UI_STORAGE_ALERT_SIZE'] ?></option>
                                        <option value="3"><?php echo $LANG['UI_SETTINGS_AUTH_UNLIMITED'] ?></option>
                                    </select>
                                    <div>
                                        <span class="help-block ">
                                            <?php echo $LANG['UI_STORAGE_WORM_SPACE_TIPS'] ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group wormdiv"  id='worm_percentdiv'>
                                <label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_WORM_SPACE_LIMIT'] ?>
                                </label>
                                <div class="col-md-4" style="display:inline-flex;">
                                    <div id="worm_spinnerpercent">
                                        <div class="input-group spinner-group">
                                            <input type="text" id="wormgpercent" style="text-align: left;" class="spinner-input form-control" maxlength="3" onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}" onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'0')}else{this.value=this.value.replace(/\D/g,'')}">
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
                                    <div style="font-size: 14px;margin: 6px 12px 12px 12px;">
                                        %
                                    </div>
                                </div>
                            </div>

                            <div class="form-group wormdiv display-none" id='worm_sizediv'>
                                <label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_WORM_SPACE_LIMIT'] ?>
                                </label>
                                <div class="col-md-4" style="display:inline-flex;">
                                    <div id="worm_spinnersize">
                                        <div class="input-group spinner-group">
                                            <input type="text" id="wormsize" style="text-align: left;" class="spinner-input form-control" maxlength="8" onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}" onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'0')}else{this.value=this.value.replace(/\D/g,'')}">
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
                                    <div style="font-size: 14px;margin: 12px;margin-top: 7px">
                                        GB
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-offset-6 col-md-6">
                                <button type="button" id="cancelBut" class="btn default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
                                <button type="button" id="addsubmit" class="btn green-haze btn-confirm"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END FORM-->
            </div>
        </div>
        <!-- END VALIDATION STATES-->

        <!-- BEGIN MODAL -->
        <div id="modaldiv" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
            <div class="modal-header ">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title"><i class="glyphicon glyphicon-plus"></i> <?php echo $LANG['UI_STORAGE_ADD'] ?></h4>
            </div>
            <div class="modal-body">
                <div class="portlet-body" id="childrendiv">
                    <div class="alert alert-danger" id="hypervisortip">
                    </div>
                    <div class="alert alert-warning" id="childrentip">
                    </div>
                </div>
                <div class="form-group" id="importdiv">
                    <label class="col-md-3 control-label"><?php echo $LANG['UI_STORAGE_IMPORT_BAKCUP_DATA'] ?></label>
                    <div class="col-md-8">
                        <div>
                            <label class="checkbox-inline pl0"><input type="checkbox" style="padding-left: 0;" class="icheck" id="import">
                                <?php echo $LANG['UI_STORAGE_IMPORT_DATA'] ?> </label>
                        </div>
                        <span class="help-block" id="timepointtip">
                        </span>
                    </div>
                </div>

                <div class="form-group display-none" id="copyimportdiv">
                    <label class="col-md-3 control-label"><?php echo $LANG['UI_COPY_DATA_IMPORT'] ?></label>
                    <div class="col-md-8">
                        <div>
                            <label class="checkbox-inline pl0"><input type="checkbox" style="padding-left: 0;" class="icheck" id="copyImport">
                                <?php echo $LANG['UI_STORAGE_IMPORT_DATA'] ?> </label>
                        </div>
                        <span class="help-block" id="copytimepointtip">
                        </span>
                    </div>
                </div>
                <div class="form-group display-none" id="cloudimportdiv">
                    <label class="col-md-3 control-label"><?php echo $LANG['UI_ARCHIVE_DATA_IMPORT'] ?></label>
                    <div class="col-md-8">
                        <div>
                            <label class="checkbox-inline pl0"><input type="checkbox" style="padding-left: 0;" class="icheck" id="cloudImport">
                                <?php echo $LANG['UI_STORAGE_IMPORT_DATA'] ?> </label>
                        </div>
                        <span class="help-block" id="cloudtimepointtip">
                        </span>
                    </div>
                </div>

                <div class="form-group" id="formatdiv">
                    <label class="col-md-3 control-label"><?php echo $LANG['UI_STORAGE_IMPORT_FORMAT_STR'] ?></label>
                    <div class="col-md-8">
                        <div>
                            <label class="checkbox-inline pl0"><input style="padding-left: 0;" type="checkbox" class="icheck" id="format">
                                <?php echo $LANG['UI_STORAGE_IMPORT_FORMAT'] ?> </label>
                        </div>
                        <span class="help-block">
                            <?php echo $LANG['UI_STORAGE_IMPORT_FORMAT_TIPS'] ?> </span>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
                <button type="button" class="btn btn-primary" id="submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
            </div>
        </div>
        <!-- END MODAL -->

        <!-- BEGIN MODAL -->
        <div id="modal-iscsi-chap-div" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
            <div class="modal-header ">
                <h4 class="modal-title"><?php echo $LANG['UI_ISCSI_CHAT_AUTH'] ?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="control-label col-md-3">
                        <?php echo $LANG['UI_LOGIN_USERNAME'] ?>
                        <span class="required">*</span>
                    </label>
                    <div class="col-md-6">
                        <div class="input-icon right">
                            <i class="fa"></i>
                            <input style="display:none"><!-- for disable autocomplete on chrome -->
                            <input type="text" maxlength="128" class="form-control" id="iscsi-chap-username"/>
                            <div><span class="help-block ">
                                        <?php echo $LANG['UI_ISCSI_CHAT_USERNAME_TIPS'] ?>
                                        </span></div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-3">
                        <?php echo $LANG['UI_LOGIN_PASSWORD'] ?>
                        <span class="required">*</span>
                    </label>
                    <div class="col-md-6">
                        <div class="input-icon right">
                            <i class="fa"></i>
                            <input style="display:none"><!-- for disable autocomplete on chrome -->
                            <input type="password" autocomplete="off" maxlength="128" class="form-control" id="iscsi-chap-userpwd" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')"/>
                            <div><span class="help-block ">
                                        <?php echo $LANG['UI_ISCSI_CHAT_PASSWORD_TIPS'] ?>
                                        </span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
                <button type="button" class="btn btn-primary" id="iscsi-chap-submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
            </div>
        </div>
        <!-- END MODAL -->

    </div>
</div>
<!-- END PAGE CONTENT-->
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>

<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/fuelux/js/spinner.min.js"></script>
<script src="./scripts/libs/base64.js" type="text/javascript"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script src="./scripts/platform/storage/storage_add.js" type="text/javascript"></script>

<?php
if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
    //如果是中文简体|中文繁体,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
        $_SESSION['language'] . '.js" type="text/javascript"></script>';
} else if ($_SESSION['language'] != "en-us" && $_SESSION['language'] != "zh-cn" && $_SESSION['language'] != "zh-tw") {
    //如果不是英文|中文简体|中文繁体,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages_' .
        $_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>

