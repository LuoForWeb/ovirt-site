<style>
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
    #modaldivedit{
        z-index: 10049 !important;
    }
    .drawer-backdrop{
        z-index: 10048 !important;
    }
</style>
<div class="table-toolbar-wrapper vin_toolbar" id="storage_manager_toolbar">
    <div class="table-toolbar-wrapper__left">
        <?php
        if (in_array("p_storage_manager_delete", $_SESSION['permissionArr'])) {
            // 删除
            echo '<div class="customBtn1">
                <button type="button" id="delete" class="b-btn brr2 mr12 table-toolbar-btn" style="cursor:not-allowed;background-color:#F4F4F5">
                    <i class="icon-gray-delete"></i>
                </button>
            </div>';
        }
        echo '<div class="search input-group mr12">
        <input type="search" id="searchInputVal" maxlength="128" class="searchinput customSearch" autocomplete="off"
            style="padding-right:32px" maxlength="64" type="text"
            placeholder="' . $LANG['UI_SEARCH_AS_STORAGE_NAME'] . '">
        <div class="position0" style="width:auto;height:34px">
            <button class="b-btn task_alarm_tableclear clear hide position0" id="searchbtnclear"><i
                    class="icon-close-small"></i></button>
        </div>
        <div class="search-btn positionL0" style="width:auto;height:34px;">
            <button class="b-btn search-btn" id="searchbtn"><i class="icon-search"></i></button>
        </div>
        </div>';
        if (in_array("p_storage_manager_add", $_SESSION['permissionArr'])) {
            // 新建
            echo '<div class="btn-group">
                    <button type="button" id="add" class="btn table-toolbar-btn">
                    <i class="viconfont vicon-biaogetianjia"></i> ' . $LANG['UI_PUBLIC_ADDNEW'] . '</button></div>';
        }
        if (in_array("p_storage_manager_edit", $_SESSION['permissionArr'])) {
            // 修改
            echo '<div class="btn-group">
                <button type="button" id="edit" class="btn table-toolbar-btn">
                    <i class="viconfont vicon-xiugai"></i> ' . $LANG['UI_PUBLIC_MODIFY'] . '
                </button>
            </div>';
        }
        if (in_array("p_storage_manager_data", $_SESSION['permissionArr'])) {
            // 导入数据管理
            echo '<div class="btn-group">
                <button type="button" id="importdata" class="btn table-toolbar-btn">
                    <i class="viconfont vicon-vmdata" style="font-weight: bold;"></i>' . $LANG['UI_PALTFORM_STORAGE_DATA'] . '
                </button>
                </div>';
        }
        /*if (in_array("p_storage_manager_timepoint_config", $_SESSION['permissionArr'])) {
            // 自动导入时间点配置
            echo '<div class="btn-group">
                <button type="button" id="autoimporttimepointconf" class="btn table-toolbar-btn">
                    <i class="viconfont vicon-a-Afferentchuanru"></i>' . $LANG['UI_PALTFORM_STORAGE_TIMEPOINT_CONFIG'] . '
                </button>
            </div>';
        }*/
        if (in_array("p_storage_manager_sync", $_SESSION['permissionArr'])) {
            // 手动同步
            echo '<div class="btn-group">
                <button type="button" id="sync" class="btn table-toolbar-btn">
                    <i class="viconfont vicon-a-Afferentchuanru"></i>' . $LANG['WEB_PLATFORM_DES_SYNC'] . '
                </button>
            </div>';
        }

        ?>
    </div>

    <div class="table-toolbar-wrapper__right">
        <div class="page-right">
            <!-- <input id="searchInput" type="search" maxlength="128"
                class="searchinput table-group-action-input form-control input-inline input"
                placeholder="<?php echo $LANG['UI_SEARCH_AS_STORAGE_NAME'] ?>" aria-controls="example">
            <input id="searchbtn" type="button" class="btn btn-search"
                value="<?php echo $LANG['UI_PUBLIC_SEARCH'] ?>"> -->
            <button type="button" id="searchAll" class="btn btn-primary adv_btn brr2 p-lr8">
                <i class="adv-search"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?>
            </button>
        </div>
    </div>
</div>

<div id="searchDiv" class="search-content searchDiv display-none"><?php echo $LANG['UI_PUBLIC_SEARCH_CONDITION'] ?>
    <span class="searchContent"></span>
    <span class="clearSearch"><?php echo $LANG['UI_SEARCH_CLEAR'] ?></span>
</div>

<div class="table-container storage-manager-table-container">
    <table class="table" id="storagetable"></table>
</div>



<!-- BEGIN MODAL DELETE-->
<div id="modaldiv" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first"
     data-backdrop="static">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title"><i class="viconfont vicon-ge_delete"></i>
            <?php echo $LANG['UI_STORAGE_DELETE'] ?></h4>
    </div>
    <div class="modal-body">
        <div class="portlet-body" id="childrendiv">
            <div class="alert alert-warning">
                <button type="button" class="close" data-dismiss="alert"></button>
                <ul class="alert-ul">
                    <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                    <li>
                        <i class="fa fa-info-circle "></i>
                        <?php echo $LANG['UI_STORAGE_DELETE_TIP_TITLE'] ?>
                        <?php echo $LANG['UI_STORAGE_DELETE_TIP_CONTENT'] ?>
                    </li>
                </ul>
            </div>
        </div>
        <div class="row static-info">
            <div class="col-md-4 name textalignr">
                <?php echo $LANG['UI_STORAGE_DELETE_POINT_COUNT'] ?>:
            </div>
            <div class="col-md-8 value" id="timepointcount">
            </div>
        </div>
        <div class="row static-info">
            <div class="col-md-4 name textalignr">
                <?php echo $LANG['UI_STORAGE_DELETE_BACKUP_SIZE'] ?>:
            </div>
            <div class="col-md-8 value" id="timepointsize">
            </div>
        </div>
        <div class="row static-info">
            <div class="col-md-4 name textalignr">
                <?php echo $LANG['UI_STORAGE_DELETE_TASK_COUNT'] ?>:
            </div>
            <div class="col-md-8 value" id="taskcount">
            </div>
        </div>
        <div class="row static-info">
            <div class="col-md-4 name textalignr">
                <?php echo $LANG['UI_JOB_RNAME'] ?>:
            </div>
            <div class="col-md-8 value" id="taskname" style="max-height:150px;overflow-y:scroll;">
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" data-dismiss="modal"
                class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
        <button type="button" class="btn btn-primary" id="submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>
<!-- END MODAL -->

<!-- BEGIN SEARCH MODAL -->
<div id="searchmodal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first"
     data-backdrop="static">
    <input id="vcenteruuid" class="display-none"></input>
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title"><i class="fa fa-search"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?>
        </h4>
    </div>
    <div class="modal-body">
        <div class="portlet-body">

            <div class="list-option">
                <div class="row">
                    <!-- 存储别名 -->
                    <label
                            class="control-label col-md-2 vmbackdata-padding_en"><?php echo $LANG['UI_STORAGE_NAME'] ?>
                        ：
                    </label>
                    <div class="col-md-4">
                        <input style="width:235px; height:34px;" id="nickName" type="text" maxlength="64"
                               class="table-group-action-input form-control input-inline input input-sm"
                               aria-controls="example">
                    </div>

                    <!-- 存储状态 -->
                    <label
                            class="control-label col-md-2 vmbackdata-padding_en"><?php echo $LANG['UI_PUBLIC_TYPE'] ?>
                        ：
                    </label>
                    <div class="col-md-4">
                        <select class="form-control " id="storageType">
                            <option value="0"><?php echo $LANG['UI_PUBLIC_ALL'] ?></option>
                            <option value="3"><?php echo $LANG['UI_STORAGE_TYPE3'] ?></option>
                            <option value="1"><?php echo $LANG['UI_STORAGE_TYPE1'] ?></option>
                            <option value="11"><?php echo $LANG['UI_STORAGE_TYPE11'] ?></option>
                            <option value="2"><?php echo $LANG['UI_STORAGE_TYPE2'] ?></option>
                            <option value="4"><?php echo $LANG['UI_STORAGE_TYPE4'] ?></option>
                            <option value="5">iSCSI</option>
                            <option value="6"><?php echo $LANG['UI_STORAGE_TYPE6'] ?></option>
                            <option value="7"><?php echo $LANG['UI_STORAGE_TYPE7'] ?></option>
                            <?php
                                if (!empty($_SESSION['authfun']['copy'])) {
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
                            <?php if ($CONF['SYSTEM_INFO']['arch_type'] != 'arm' && !empty($_SESSION['authfun']['ddBoost'])) {
                                // arm的不显示
                                echo ' <option value="16">DELL Data Domain Boost</option>';
                            } ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="list-option">
                <div class="row">

                    <!-- 所在节点 -->
                    <label
                            class="control-label col-md-2 vmbackdata-padding_en"><?php echo $LANG['UI_STORAGE_IN_NODE'] ?>
                        ：
                    </label>
                    <div class="col-md-4">
                        <select class="form-control " id="nodeSelect">
                        </select>
                    </div>

                    <!-- 存储状态 -->
                    <label
                            class="control-label col-md-2 vmbackdata-padding_en"><?php echo $LANG['UI_STORAGE_STATUS'] ?>
                        ：
                    </label>
                    <div class="col-md-4">
                        <select id="storageStatus" class="form-control ">
                            <option value="0"><?php echo $LANG['UI_PUBLIC_ALL'] ?></option>
                            <option value="1"><?php echo $LANG['WEB_PLATFORM_DES_NORMAL'] ?></option>
                            <option value="2"><?php echo $LANG['WEB_STORAGE_STATUS_CREATING'] ?></option>
                            <option value="3"><?php echo $LANG['WEB_AGENT_STATUS_OFFLINE'] ?></option>
                            <option value="4"><?php echo $LANG['WEB_STORAGE_STATUS_UNMOUNT'] ?></option>
                        </select>
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
                id="serach_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>

<!-- END SEARCH MODAL -->

<!-- start import modal -->
<div id="importModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first"
     data-backdrop="static">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title"><i
                    class="viconfont vicon-a-Afferentchuanru"></i><?php echo $LANG['UI_PALTFORM_STORAGE_TIMEPOINT_CONFIG'] ?>
    </div>
    <div class="modal-body">
        <div class="portlet-body">
            <!-- <div class="list-option">
                <div class="row">
                    <label class="control-label col-md-5">存储设备自动导入开关</label>
                    <div class="col-md-3">
                        <input type="checkbox" id="timepointimportswitch" checked  class="make-switch" data-on-color="primary" data-off-color="info"
                        data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                        data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                    </div>
                    <div class="col-md-2 mt5">
                        <a class="popovers" data-container="body" data-trigger="hover"
                        data-placement="right" data-content="开启后配置存储设备自动导入时间点时间">
                        <i class="viconfont vicon-tishi"></i>
                        </a>
                    </div>
                </div>
            </div> -->
            <div class="list-option importtimediv">
                <div class="row">
                    <!-- 虚拟化中心 -->
                    <label
                            class="control-label col-md-5"><?php echo $LANG['UI_PALTFORM_STORAGE_TIMEPOINT_INTERVAL_CONFIG'] ?>
                    </label>
                    <div class="col-md-7" style="display:inline-flex">
                        <div id="autoimport" style="width: 140px;">
                            <div class="input-group spinner-group" style="width:140px;">
                                <input type="text" id="autoimportValue" style="text-align: center;"
                                       class="spinner-input form-control" maxlength="3"
                                       onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}"
                                       onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'0')}else{this.value=this.value.replace(/\D/g,'')}">
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
            <!-- 提示信息暂且不要 -->
            <!-- <div class="list-option">
                 <div class="row">
                     <div class="col-md-12">
                        <div class="alert alert-block alert-info fade in" id="marktips">
                            <button type="button" class="close" data-dismiss="alert"></button>
                            <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                            <ol class="alert-ol">
                                <li>
                                    <?php echo $LANG['UI_VCENTER_REFRESH_SET_HITE1']; ?>
                                </li>
                                <li>
                                    <?php echo $LANG['UI_VCENTER_REFRESH_SET_HITE2']; ?>
                                </li>
                            </ol>
                        </div>
                    </div>
                 </div>
            </div> -->
        </div>
    </div>

    <!-- modal-footer -->
    <div class="modal-footer">
        <button type="button" data-dismiss="modal"
                class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
        <button type="button" class="btn btn-primary"
                id="importsubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>
<!-- end refresh modal -->

<div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true"
     id="modaldivedit" style="width: 800px">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header ">
            <h4 class="drawer-title" id="drawer-1-title" style="vertical-align: top;padding-left:0;font-weight:400;font-size:16px;">
                <i class="viconfont vicon-ge_modify" style="padding-top: 3px"></i>
                <span style="vertical-align: top"><?php echo $LANG['UI_STORAGE_EDIT'];?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i class="viconfont vicon-guanbi"></i></span>
            </h4>
        </div>
        <!-- BEGIN FORM-->
        <div class="drawer-body form-horizontal">
            <div class="portlet-body" style="padding: 0 !important;">
                <div class="form-group">
                    <label class="col-md-3 control-label"><span class="required">
                    * </span><?php echo $LANG['UI_STORAGE_NAME'] ?></label>
                    <div class="col-md-8">
                        <input type="text" class="form-control display-none" maxlength="128" id="storageuuid">
                        <input type="text" class="form-control" maxlength="64" id="storagename">
                        <span class="help-block">
                    <?php echo $LANG['UI_STORAGE_EDIT_NAME_TIPS'] ?> </span>
                    </div>
                </div>
                <div class="form-group display-none" id="node2Div">
                    <label class="control-label col-md-3">
                        <span class="required">* </span>
                        <?php echo $LANG['UI_NODE_IPADDR'] ?>
                    </label>
                    <div class="col-md-8" id="muti-node">
                        <div class="input-icon">
                            <select id="nodeselect2" name="nodeselect2"
                                    class="bootstrap-mutiple-select selectpicker show-tick" multiple data-live-search="true"
                                    data-actions-box="true">
                            </select>
                            <div><span class="help-block ">
                        <?php echo $LANG['UI_STORAGE_ADD_NODE_TIPS'] ?>
                            <span id="share_amount_in_use" class="display-none" style="color:red;">(<?php echo $LANG['UI_STORAGE_ADD_NODE_TIPS2'] ?>)</span>
                    </span></div>
                        </div>
                    </div>
                </div>

                <div class="form-group noticeCheckDiv">
                    <label
                            class="control-label col-md-3 form-group-label"><?php echo $LANG['UI_STORAGE_ALERT'] ?></label>
                    <div class="col-md-8 form-group-content">
                        <input type="checkbox" id="noticeswitch" checked class="make-switch" data-size="small"
                               data-on-color="primary" data-off-color="info"
                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                    </div>
                </div>

                <div class="storagewarningdiv">
                    <div class="form-group warnningdiv">
                        <label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_ALERT_TYPE'] ?>
                        </label>
                        <div class="col-md-8">
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
                                    <input type="text" id="warningpercent" style="text-align: left;"
                                           class="spinner-input form-control" maxlength="3"
                                           onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}"
                                           onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'0')}else{this.value=this.value.replace(/\D/g,'')}">
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
                            <div style="font-size: 14px;margin: 12px;">
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
                                    <input type="text" id="warningsize" style="text-align: left;"
                                           class="spinner-input form-control" maxlength="8"
                                           onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}"
                                           onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'0')}else{this.value=this.value.replace(/\D/g,'')}">
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
                            <div style="font-size: 14px;margin: 12px;">
                                GB
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group wormCheckDiv">
                    <label
                            class="control-label col-md-3 form-group-label"><?php echo $LANG['UI_STORAGE_WORM'] ?></label>
                    <div class="col-md-3 form-group-content">
                        <input type="checkbox" id="wormswitch" checked class="make-switch" data-size="small"
                               data-on-color="primary" data-off-color="info"
                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                    </div>
                    <div class="col-md-2 mt5">
                        <a class="popovers" data-container="body" data-trigger="hover" data-placement="right"
                           data-content="<?php echo $LANG['UI_STORAGE_WORM_TIPS'] ?>">
                            <i class="viconfont vicon-tishi" style="margin-left: -100px;"></i>
                        </a>
                    </div>
                </div>

                <div class="storagewormdiv">
                    <div class="form-group wormdiv">
                        <label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_WORM_SPACE'] ?>
                        </label>
                        <div class="col-md-8">
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

                    <div class="form-group wormdiv" id='worm_percentdiv'>
                        <label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_WORM_SPACE_LIMIT'] ?>
                        </label>
                        <div class="col-md-4" style="display:inline-flex;">
                            <div id="worm_spinnerpercent">
                                <div class="input-group spinner-group">
                                    <input type="text" id="wormgpercent" style="text-align: left;"
                                           class="spinner-input form-control" maxlength="3"
                                           onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}"
                                           onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'0')}else{this.value=this.value.replace(/\D/g,'')}">
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
                            <div style="font-size: 14px;margin: 12px;">
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
                                    <input type="text" id="wormsize" style="text-align: left;"
                                           class="spinner-input form-control" maxlength="8"
                                           onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}"
                                           onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'0')}else{this.value=this.value.replace(/\D/g,'')}">
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
                            <div style="font-size: 14px;margin: 12px;">
                                GB
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group yidiCheckDiv display-none">
                    <label class="control-label col-md-3 form-group-label"><?php echo $LANG['WEB_USERS_EDIT_PASS'] ?></label>
                    <div class="col-md-8 form-group-content">
                        <input type="checkbox" id="yidiswitch" class="make-switch" data-size="small"
                               data-on-color="primary" data-off-color="info"
                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                    </div>
                </div>
                <div class="yidimessagediv display-none">
                    <div class="form-group">
                        <label class="control-label col-md-3">
                            <?php echo $LANG['UI_LOGIN_USERNAME'] ?>
                            <span class="required">*</span>
                        </label>
                        <div class="col-md-8">
                            <div class="input-icon right">
                                <i class="fa"></i>
                                <input style="display:none"><!-- for disable autocomplete on chrome -->
                                <input type="text" maxlength="128" class="form-control" name="username" id="remote_username" />
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
                        <div class="col-md-8">
                            <div class="input-icon right">
                                <i class="fa"></i>
                                <input style="display:none"><!-- for disable autocomplete on chrome -->
                                <input type="password" autocomplete="off" maxlength="128" class="form-control"
                                       name="password" id="remote_password"
                                       oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')" />
                                <div><span class="help-block ">
                                <?php echo $LANG['UI_STORAGE_REMOTE_ADMIN_PASSWORD'] ?>
                            </span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group cifsCheckDiv display-none">
                    <label class="control-label col-md-3 form-group-label"><?php echo $LANG['WEB_USERS_EDIT_PASS'] ?></label>
                    <div class="col-md-8 form-group-content">
                        <input type="checkbox" id="cifsswitch" class="make-switch" data-size="small"
                               data-on-color="primary" data-off-color="info"
                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                    </div>
                </div>
                <div class="cifsmessagediv display-none">
                    <div class="form-group">
                        <label class="control-label col-md-3">
                            <span class="required">*</span>
                            <?php echo $LANG['UI_LOGIN_USERNAME'] ?>
                        </label>
                        <div class="col-md-8">
                            <div class="input-icon right">
                                <i class="fa"></i>
                                <input style="display:none"><!-- for disable autocomplete on chrome -->
                                <input type="text" maxlength="128" class="form-control" name="username2" id="cifs_username" />
                                <div><span class="help-block ">
                                <?php echo $LANG['UI_STORAGE_CIFS_USER'] ?>
                            </span></div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3">
                            <span class="required">*</span>
                            <?php echo $LANG['UI_LOGIN_PASSWORD'] ?>
                        </label>
                        <div class="col-md-8">
                            <div class="input-icon right">
                                <i class="fa"></i>
                                <input style="display:none"><!-- for disable autocomplete on chrome -->
                                <input type="password" autocomplete="off" maxlength="128" class="form-control"
                                       name="password2" id="cifs_password"
                                       oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')" />
                                <div><span class="help-block ">
                                <?php echo $LANG['UI_STORAGE_CIFS_PASS'] ?>
                            </span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group dddbCheckDiv display-none">
                    <label class="control-label col-md-3 form-group-label"><?php echo $LANG['WEB_USERS_EDIT_INFO'] ?></label>
                    <div class="col-md-8 form-group-content">
                        <input type="checkbox" id="dddbswitch" class="make-switch" data-size="small"
                               data-on-color="primary" data-off-color="info"
                               data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                    </div>
                </div>
                <div class="dddbmessagediv display-none">
                    <div class="form-group">
                        <label class="control-label col-md-3">
                            <span class="required">* </span>
                            <?php echo $LANG['UI_LOGIN_USERNAME'] ?>
                        </label>
                        <div class="col-md-8">
                            <div class="input-icon right">
                                <i class="fa"></i>
                                <input style="display:none"><!-- for disable autocomplete on chrome -->
                                <input type="text" maxlength="128" class="form-control" name="username16"/>
                                <div><span class="help-block ">
                                                 <?php echo $LANG['UI_STORAGE_DDDB_USERNAME_TIPS']?>
                                            </span></div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3">
                            <?php echo $LANG['UI_LOGIN_PASSWORD'] ?>
                        </label>
                        <div class="col-md-8">
                            <div class="input-icon right">
                                <i class="fa"></i>
                                <input style="display:none"><!-- for disable autocomplete on chrome -->
                                <input type="password" autocomplete="off" maxlength="128" class="form-control" name="password16" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')" />
                                <div><span class="help-block ">
                                                <?php echo $LANG['UI_STORAGE_DDDB_PAWSSWORD_TIPS']?>
                                            </span></div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="cloudwarningdiv display-none">
                    <!-- <div class="form-group warnningdiv">
                <label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_ALERT_TYPE'] ?>
                </label>
                <div class="col-md-8">
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
                                    <input type="text" id="limitsize" style="text-align: left;"
                                           onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control"
                                           maxlength="8">
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
                            <div style="font-size: 14px;margin: 12px;">
                                TB
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 存储用途 -->
                <div class="form-group display-none" id="usemodeDiv">
                    <label
                            class="control-label col-md-3 form-group-label"><?php echo $LANG['UI_STORAGE_USED_FOR'] ?></label>
                    <div class="col-md-6 form-group-content">
                        <div class="input-group form-group-boxes" id="useMode">
                            <label style="padding-right: 20px;"><input type="checkbox" id="backupCheck"
                                                                       data-checkbox="icheckbox_square-blue" data-mode="1"
                                                                       class="icheck"><?php echo $LANG['UI_PLATFORM_BACKUP'] ?></label>
                            <label style="padding-right: 20px;<?php if (!in_array('copy', $_SESSION['authfun'])) {
                                echo 'display:none';
                            } ?>" ><input type="checkbox" id="copyCheck"
                                          data-checkbox="icheckbox_square-blue" data-mode="2"
                                          class="icheck"><?php echo $LANG['WEB_PLATFORM_DES_COPY'] ?></label>
                            <label <?php if (!in_array('archive', $_SESSION['authfun'])) {
                                echo 'display:none';
                            } ?>><input type="checkbox" id="archiveCheck" data-checkbox="icheckbox_square-blue"
                                        data-mode="3" class="icheck"><?php echo $LANG['UI_PLATFORM_ARCHIVE'] ?></label>
                        </div>
                    </div>
                </div>
                <!--
        <div id="autoimportflagDiv">

            <div class="form-group">
                <label
                    class="control-label col-md-3 form-group-label"><?php echo $LANG['UI_STORAGE_CBR_AUTO_SCAN'] ?></label>
                <div>
                    <div class="col-md-3">
                        <input type="checkbox" id="timepointimportswitch" class="make-switch"
                            data-on-color="primary" data-off-color="info"
                            data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                            data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                    </div>
                    <div class="col-md-2 mt5">
                        <a class="popovers" data-container="body" data-trigger="hover" data-placement="right"
                            data-content="<?php echo $LANG['UI_STORAGE_CBR_AUTO_SCAN_TIME_POINT_TIPS'] ?>">
                            <i class="viconfont vicon-tishi"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label
                    class="control-label col-md-3 form-group-label"><?php echo $LANG['UI_STORAGE_CBR_AUTO_ASSIGN'] ?></label>
                <div>
                    <div class="col-md-3">
                        <input type="checkbox" id="timepointallocateswitch" <?php if ($_SESSION['isThreePowers']) {
                    echo 'disabled';
                } ?> class="make-switch"
                            data-on-color="primary" data-off-color="info"
                            data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                            data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                    </div>
                    <div class="col-md-2 mt5">
                        <a class="popovers" data-container="body" data-trigger="hover" data-placement="right"
                            data-content="<?php echo $LANG['UI_STORAGE_CBR_AUTO_ASSIGN_USER_TIPS'] ?>">
                            <i class="viconfont vicon-tishi"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        -->
                <div class="hwdatascanDiv display-none">
                    <div class="form-group">
                        <label
                                class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_CBR_DATA_SCAN_SWITCH'] ?></label>
                        <div class="col-md-3">
                            <input type="checkbox" id="datascanswitch" checked class="make-switch"
                                   data-on-color="primary" data-off-color="info"
                                   data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                   data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                        </div>
                        <div class="col-md-2 mt5">
                            <a class="popovers" data-container="body" data-trigger="hover" data-placement="right"
                               data-content="<?php echo $LANG['UI_STORAGE_CBR_DATA_SCAN_SWITCH_TIPS'] ?>">
                                <i class="viconfont vicon-tishi"></i>
                            </a>
                        </div>
                    </div>
                    <div class="form-group datascandiv">
                        <label class="control-label col-md-3"><?php echo $LANG['UI_STORAGE_CBR_SCAN_INTERVAL'] ?>
                        </label>
                        <div class="col-md-8" style="display:inline-flex">
                            <div id="cbrscantime" style="width: 140px;">
                                <div class="input-group  spinner-group " style="width:140px;">
                                    <input type="text" id="cbrscantimevalue" style="text-align: center;"
                                           class="spinner-input form-control" maxlength="3"
                                           onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}"
                                           onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'0')}else{this.value=this.value.replace(/\D/g,'')}">
                                    <div class="spinner-buttons input-group-btn  spinner-group-btn">
                                        <button type="button" class="btn spinner-up default ">
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
            </div>
        </div>
        <div class="drawer-footer">
            <div id="bottom-btn">
                <button type="button" id="editsubmit" class="btn green-haze btn-confirm"><?php echo $LANG['UI_PUBLIC_YES']?></button>
                <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-default" style="margin-right:0;"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
            </div>
        </div>
    </div>
</div>
<!-- END MODAL -->

<!-- END PAGE CONTENT-->
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./scripts/platform/storage/storage_manager.js"></script>
<!-- END PAGE LEVEL PLUGINS -->