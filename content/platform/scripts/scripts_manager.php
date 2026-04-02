<?php
include_once '../../../tpl/permission.php';
include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php';
?>

<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>

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
    <span class="curent"><?php echo $LANG['WEB_SCRIPT_SCRIPT_LIST'] ?></span>
</h3>
<!-- END PAGE HEADER-->
 
<!-- BEGIN PAGE CONTENT-->
<div class="script_page">
    <div class="row">
        <div class="col-md-12">
          <div class="portlet box blue-hoki" id='kuberneteScriptDiv'>
            <div class="portlet-title">
                <div class="caption">
                    <i class="levelchild viconfont vicon-jiaobenguanli"></i><?php echo $LANG['WEB_SCRIPT_SCRIPT_LIST']; ?>
                </div>

            </div>

            <div class="portlet-body mlr10">
                <div class="table-container" >
                <!-- 表格 -->
                    <div class="vin_toolbar" id = "vin_kubernetes_script_toolbar">
                        <div class="leftTool">
                            <div class="btn-group grey_box_btn  mr12" style="<?php if (!in_array("p_scripts_manager_delete", $_SESSION['permissionArr'])) { echo "display:none;";} ?>" >
                                <button id="deleteSelect" class="btn" title="<?php echo $LANG['WEB_SCRIPT_DELETE']; ?>">
                                    <i class="viconfont vicon-a-Deleteshanchu"></i>
                                </button>
                            </div>
                            <div class="btn-group">
                                <div class="search input-group mr12">
                                    <input class="search_glass_input customSearch scriptCustomSearch" id="searchVal" autocomplete="off" type="text" placeholder="<?php echo $LANG['WEB_SCRIPT_SEARCH']; ?>" style="padding-left: 34px;">
                                    <div class="position0" style="width:auto;height:34px">
                                        <button class="b-btn clear hide position0"><i class="icon-close-small"></i></button>
                                    </div>
                                    <div class="search-btn positionL0" style="width:auto;height:34px;">
                                        <button class="b-btn search-btn"  id="searchSubmit"><i class="icon-search"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="btn-group" style="<?php if (!in_array("p_scripts_manager_add", $_SESSION['permissionArr'])) { echo "display:none;";} ?>" >
                                <button class="btn table-toolbar-btn"  id="addScript" data-toggle="drawer" data-target="#add_user_drawer" aria-haspopup="true" aria-expanded="false">
                                    <i class="viconfont vicon-biaogetianjia mr4"></i>
                                    <span><?php echo $LANG['UI_PUBLIC_ADDNEW']; ?></span>
                                </button>
                            </div>
                        </div>
                        <div class="rightTool">
                            <div class="vin_btnToolbar"></div>
                        </div>
                    </div>
                    <div class="table-container">
                        <table class="table" id="script_table"></table>
                    </div>
                    <!-- 表格 -->
                </div>
            </div>

           </div>
        </div>
    </div>

   
    

































































   



</div>
<!-- modal start -->
<div id="addScriptmodal" class="modal xmodal fade form-horizontal high-search" tabindex="-1"  data-backdrop="static">
    <div class="modal-header " style="display: flex;padding: 10px;">
        <i class="viconfont vicon-danchuangtianjia1" style="padding: 12px 10px;font-size: 20px;"></i>
        <h4 style="font-weight: 400;font-size: 16px;color: #333333;"><?php echo $LANG['WEB_SCRIPT_ADD_NEW']; ?></h4>
    </div>
    <div class="modal-body form_body">
        <div class="portlet-body">
            <form>
                <div class="form_div">
                    <div class="">
                        <!-- 脚本名称 -->
                        <label class="script_form_title"><?php echo $LANG['WEB_SCRIPT_NAME']; ?>
                        </label>
                        <div class="script_form_content">
                            <input style="height:34px;width: 100%;" id="script_name" type="text" maxlength="64" class="table-group-action-input form-control input-inline input input-sm" aria-controls="example">
                        </div>
                    </div>
                </div>

                <div class="form_div">
                    <div class="">
                        <!-- 功能描述 -->
                        <label class="script_form_title"><?php echo $LANG['WEB_SCRIPT_FUNCTION_DESCRIPTION']; ?>
                        </label>
                        <div class="script_form_content">
                            <input style="height:34px;width: 100%;" id="script_description" type="text" maxlength="64" class="table-group-action-input form-control input-inline input input-sm" aria-controls="example">
                        </div>
                    </div>
                </div>

                <div class="form_div">
                    <div class="">
                        <!-- 功能描述 -->
                        <label class="script_form_title"><?php echo $LANG['WEB_SCRIPT_TYPE']; ?>
                        </label>
                        <div class="script_form_content">
                            <select class="form-control select2me" id="script_type" style="width: 100%;">
                                    <option value="0"><?php echo $LANG['UI_PUBLIC_SELECT']; ?></option>
                                    <option value="1">shell<?php echo $LANG['WEB_SCRIPT_SCRIPT']; ?>(.sh)</option>
                                    <option value="2">bat<?php echo $LANG['WEB_SCRIPT_SCRIPT']; ?>(.bat)</option>
                                    <!-- <option value="3">yaml<?php echo $LANG['WEB_SCRIPT_SCRIPT']; ?>(.yaml)</option> -->
                                    <option value="4">python2<?php echo $LANG['WEB_SCRIPT_SCRIPT']; ?>(.py)</option>
                                    <option value="5">python3<?php echo $LANG['WEB_SCRIPT_SCRIPT']; ?>(.py)</option>
                                    <option value="6">SQL<?php echo $LANG['WEB_SCRIPT_SCRIPT']; ?>(.sql)</option>
                                    <option value="7">PowerShell<?php echo $LANG['WEB_SCRIPT_SCRIPT']; ?>(.ps1)</option>
                                    <!-- <option value="8">PowerShell<?php echo $LANG['WEB_SCRIPT_SCRIPT']; ?>(.psm1)</option>
                                    <option value="9">PowerShell<?php echo $LANG['WEB_SCRIPT_SCRIPT']; ?>(.psd1)</option> -->
                            </select>
                        </div>
                    </div>
                </div>

               


                <div class="form_div script_div">
                    <div class="portlet">
                            <div class="portlet-title line" style="border-bottom:unset;">
                                <div class="caption" style="width: 10%;color: #666666;font-weight: 400;font-size: 14px">
                                <?php echo $LANG['WEB_SCRIPT_CONTENT']; ?>
                                </div>
                                <div class="tools">
                                    <a type="file" href="" id="chooseFile" class="hh" data-original-title="<?php echo $LANG['WEB_SCRIPT_LOAD_LOCAL_CODE']; ?>" title="<?php echo $LANG['WEB_SCRIPT_LOAD_LOCAL_CODE']; ?>" >
                                        <i class="viconfont vicon-shangchuan"></i>
                                    </a>
                                    <input type="file" id="hiddenFileInput" accept=".sh,.bat,.yaml,.py" style="display: none;" />
                                    <a href="" class="viconfont vicon-a-apikeydaima" data-original-title="<?php echo $LANG['WEB_SCRIPT_ZOOM']; ?>" title="<?php echo $LANG['WEB_SCRIPT_ZOOM']; ?>">
                                    </a>
                                </div>
                                <div class="">
                                    <!-- 脚本内容 -->
                                    <div id="script_content" class="script_msg" style="height:150px;width: 100%;"></div>
                                </div>
                            </div>
                        </div>
                </div>

            </form>
            <div class="alert alert-block alert-info fade in" id="marktips">
                <button type="button" class="close" data-dismiss="alert"></button>
                <ul class="alert-ul">
                    <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                    <li>
                    <?php echo $LANG['WEB_SCRIPT_WARNING']; ?>
                        <a id="excludeBtn" data-toggle="drawer" data-target="#drawer-1"><?php echo $LANG['WEB_SCRIPT_HELP_CENTER']; ?></a>
                    </li>
                </ul>
            </div>


        </div>
    </div>
    <!-- modal-footer -->
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default" id="clear_script"><?php echo $LANG['UI_PUBLIC_CANCEL']; ?></button>
        <button type="button"class="btn btn-primary" id="current_script_submit"><?php echo $LANG['UI_DRILLS_DETAIL_SUBMIT']; ?></button>
    </div>
</div>
<!-- modal end -->
<!-- modal start -->
<div id="editScriptmodal" class="modal xmodal fade form-horizontal high-search" tabindex="-1"  data-backdrop="static">
    <div class="modal-header " style="display: flex;padding: 10px;">
        <input id="edit_script_uuid" style="display: none;">
        <i class="viconfont vicon-danchuangtianjia1" style="padding: 12px 10px;font-size: 20px;"></i>
        <h4 style="font-weight: 400;font-size: 16px;color: #333333;"><?php echo $LANG['WEB_SCRIPT_EDIT_SCRIPT']; ?></h4>
    </div>
    <div class="modal-body">
        <div class="portlet-body" style="padding: 20px;">
            <form>
                 <div class="form_div">
                    <div class="">
                        <!-- 脚本名称 -->
                        <label class="script_form_title"><?php echo $LANG['WEB_SCRIPT_NAME']; ?>
                        </label>
                        <div class="script_form_content">
                            <input style="height:34px;width: 100%;" id="edit_script_name" type="text" maxlength="64" class="table-group-action-input form-control input-inline input input-sm" aria-controls="example">
                        </div>
                    </div>
                </div>

                 <div class="form_div">
                    <div class="">
                        <!-- 功能描述 -->
                        <label class="script_form_title"><?php echo $LANG['WEB_SCRIPT_FUNCTION_DESCRIPTION']; ?>
                        </label>
                        <div class="script_form_content">
                            <input style="height:34px;width: 100%;" id="edit_script_description" type="text" maxlength="64" class="table-group-action-input form-control input-inline input input-sm" aria-controls="example">
                        </div>
                    </div>
                </div>

                <div class="form_div">
                    <div class="">
                        <!-- 功能描述 -->
                        <label class="script_form_title"><?php echo $LANG['WEB_SCRIPT_TYPE']; ?>
                        </label>
                        <div class="script_form_content">
                            <select class="form-control select2me" id="edit_script_type" style="width: 100%;">
                            <option value="0"><?php echo $LANG['UI_PUBLIC_SELECT']; ?></option>
                                    <option value="1">shell<?php echo $LANG['WEB_SCRIPT_SCRIPT']; ?>(.sh)</option>
                                    <option value="2">bat<?php echo $LANG['WEB_SCRIPT_SCRIPT']; ?>(.bat)</option>
                                    <!-- <option value="3">yaml<?php echo $LANG['WEB_SCRIPT_SCRIPT']; ?>(.yaml)</option> -->
                                    <option value="4">python2<?php echo $LANG['WEB_SCRIPT_SCRIPT']; ?>(.py)</option>
                                    <option value="5">python3<?php echo $LANG['WEB_SCRIPT_SCRIPT']; ?>(.py)</option>
                                    <option value="6">SQL<?php echo $LANG['WEB_SCRIPT_SCRIPT']; ?>(.sql)</option>
                                    <option value="7">PowerShell<?php echo $LANG['WEB_SCRIPT_SCRIPT']; ?>(.ps1)</option>
                                    <!-- <option value="8">PowerShell<?php echo $LANG['WEB_SCRIPT_SCRIPT']; ?>(.psm1)</option>
                                    <option value="9">PowerShell<?php echo $LANG['WEB_SCRIPT_SCRIPT']; ?>(.psd1)</option> -->
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form_div script_div">
                    <div class="portlet">
                        <div class="portlet-title line" style="border-bottom:unset;">
                            <div class="caption" style="width: 10%;color: #666666;font-weight: 400;font-size: 14px">
                            <?php echo $LANG['WEB_SCRIPT_CONTENT']; ?>
                            </div>
                            <div class="tools">
                                <a type="file" href="" id="chooseFileEdit" class="hh" data-original-title="<?php echo $LANG['WEB_SCRIPT_LOAD_LOCAL_CODE']; ?>" title="<?php echo $LANG['WEB_SCRIPT_LOAD_LOCAL_CODE']; ?>" >
                                    <i class="viconfont vicon-shangchuan"></i>
                                </a>
                                <input type="file" id="hiddenFileInputEdit" accept=".sh,.bat,.yaml,.py" style="display: none;" />
                                <a href="" class="viconfont vicon-a-apikeydaima" data-original-title="<?php echo $LANG['WEB_SCRIPT_ZOOM']; ?>" title="<?php echo $LANG['WEB_SCRIPT_ZOOM']; ?>">
                                </a>
                            </div>
                            <div class="">
                                <!-- 脚本内容 -->
                                <div id="edit_script_content"  class="script_msg" style="height:150px;width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </form>
        </div>
            <div class="alert alert-block alert-info fade in" id="marktips">
                <button type="button" class="close" data-dismiss="alert"></button>
                <ul class="alert-ul">
                    <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                    <li>
                    <?php echo $LANG['WEB_SCRIPT_WARNING']; ?>
                        <a id="excludeBtn" data-toggle="drawer" data-target="#drawer-1"><?php echo $LANG['WEB_SCRIPT_HELP_CENTER']; ?></a>
                    </li>
                </ul>
            </div>

    </div>
    <!--  hint -->
    
    <!-- modal-footer -->
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" id="cancel_edit_script" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_CANCEL']; ?></button>
        <button type="button"class="btn btn-primary" id="edit_script_submit"><?php echo $LANG['UI_DRILLS_DETAIL_SUBMIT']; ?></button>
    </div>
</div>
<!-- modal end -->


<!-- drawer开始 -->
<div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title"  id="drawer-1" style="width: 600px;">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header help_header">
            <h4 class="drawer-title help_title" id="drawer-1-title">
                <i class="viconfont vicon-bangzhuzhongxin help_icon_helpcenter"></i><?php echo $LANG['WEB_SCRIPT_HELP_CENTER']; ?>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close help_icon_close"><i class="viconfont vicon-guanbi"></i></span>
            </h4>

        </div>
        <div class="drawer-body help_body">
            <h4><?php echo $LANG['WEB_SCRIPT_CUSTOM_SCRIPT_HELP']; ?></h4>
            <hr>
            <p><?php echo $LANG['WEB_SCRIPT_CUSTOM_SCRIPT_DESCRIPTION']; ?><a class="help_link"><?php echo $LANG['WEB_SCRIPT_OFFICIAL_DOCUMENTATION']; ?></a></p>
            <h5><?php echo $LANG['WEB_SCRIPT_YAML_TEMPLATE']; ?></h5>
            <hr>
            <textarea class="code_msg" disabled>
<?php echo 'cat <<EOF | kubectl apply -f -
apiVersion: v1
kind: Pod
metadata:
  name: pod-rbd-fs-restore
spec:
    containers:
    - name: pod-rbd-fs-restore
      image: busybox
      command: ["/bin/sh"]
      args: ["-c", "while true; do tail -n1 /mnt/time.txt; sleep 5; done"]
      volumeMounts:
      - name: pvc-rbd-fs-restore
        mountPath: /mnt
    volumes:
    - name: pvc-rbd-fs-restore
      persistentVolumeClaim:
        claimName: pvc-rbd-fs-restore
EOF' ?>
            </textarea>
            <h5><?php echo $LANG['WEB_SCRIPT_YAML_PARSING']; ?></h5>
            <hr>
            <p><?php echo $LANG['WEB_SCRIPT_SYNTAX']; ?></p>


        </div>
        <div class="drawer-footer">
            <!--            <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-primary" id="addsubmit">确 定</button>-->
            <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_OFF_TWO']; ?></button>
        </div>
    </div>
</div>
<!-- drawer结束 -->




<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./scripts/libs/md5.js" type="text/javascript"></script>

<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/ace/src-min-noconflict/ace.js"></script>



<script src="./scripts/platform/scripts/scripts_manager.js" type="text/javascript"></script>

