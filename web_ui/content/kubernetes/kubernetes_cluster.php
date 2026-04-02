<?php include_once '../../tpl/permission.php'; ?>
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>

<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="infrastructure" href="./content/platform/resource/infrastructure.php">
            <span><?php echo $LANG['UI_PLATFORM_INFRASTRUCTURE'] ?></span>
        </a>
    </li>
    <span>
        >
    </span>
    <span class="curent"><?php echo $LANG['WEB_K8S_CLUSTER_PROTECT'] ?></span>
</h3>
<!-- END PAGE HEADER-->

<!-- BEGIN PAGE CONTENT-->
<div class="resource-manager-wrap" id="ClusterManagerDiv">
    <div class="resource-manager-wrap__header">
        <span>
            <i class="levelchild viconfont vicon-jiqun me-10"></i>
            <span class="resource-manager-wrap__header__text"><?php echo $LANG['WEB_KUBE_CLUSTER_KUBERNETES_CLUSTER_PROTECT'] ?></span>
        </span>
    </div>
    <div class="resource-manager-wrap__content">
        <div class="table-toolbar" id="kubernetes_table_toolbar">
            <div class="vin_toolbar">
				<div class="leftTool"></div>
				<div class="rightTool">
                    <div class="btn-group k8s_refresh_div">
                        <label>
                            <button type="button" id="refreshSelect" class="btn btn-box" title="<?php echo $LANG['UI_PUBLIC_TOOLS_RELOAD'] ?>">
                                <i class="viconfont vicon-biaogeshuaxin"></i>
                            </button>
                        </label>
                    </div>
					<div class="vin_btnToolbar"></div>
				</div>
			</div>

        </div>

        <div class="table-container kubernetes-table-container">
			<table id="cluster_table"></table>
		</div>

        <div class="alert alert-block alert-info fade in h-100px m0" id="kubernetes_cluster_tip">
            <button id="kubernetes_cluster_tip_close" type="button" class="close" data-dismiss="alert"></button>
            <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
            <ol class="alert-ol">
                <li>
                    <?php echo $LANG['WEB_KUBE_CLUSTER_ADD_NEW_CLUSTER'] ?>
                </li>
                <li>
                    <?php echo $LANG['WEB_KUBE_CLUSTER_REFRESH_CLUSTER_DATA'] ?>
                </li>
            </ol>
        </div>
    </div>
</div>
<!-- modal start -->
<div id="addClustermodal" class="modal xmodal fade form-horizontal high-search" tabindex="-1"  data-backdrop="static">
    <div class="modal-header " style="display: flex;padding: 10px;">
        <i class="viconfont vicon-danchuangtianjia1" style="padding: 12px 10px;font-size: 20px;"></i>
        <h4 style="font-weight: 400;font-size: 16px;color: #333333;"><?php echo $LANG['WEB_KUBE_CLUSTER_ADD_KUBERNETES_CLUSTER']; ?></h4>
    </div>
    <div class="modal-body form_body">
        <div class="portlet-body">
            <form id="form_add_cluster">
                <div class="form_div">
                    <div class="">
                        <!-- 脚本名称 -->
                        <label class="col-md-4 form_title"><?php echo $LANG['WEB_KUBE_CLUSTER_ADD_METHOD']; ?>
                        </label>
                        <div class="col-md-7" style="display: inline-block;">
                            <select class="form-control " id="add_style">
                                <option value="BY_MANUAL"><?php echo $LANG['WEB_KUBE_CLUSTER_MANUAL_ADD']; ?> </option>
                                <option value="BY_SSH"><?php echo $LANG['WEB_KUBE_CLUSTER_ADD_BY_SSH']; ?>  </option>
                                <option value="BY_CONFIG"><?php echo $LANG['WEB_KUBE_CLUSTER_ADD_BY_CONFIG']; ?> </option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="manual_div">
                    <div class="form_div">
                       <div class="">
                            <!-- 功能描述 -->
                            
                            <label class="col-md-4 form_title"><span class="required">* </span><?php echo $LANG['WEB_KUBE_IP_OR_DOMAIN']; ?>
                            </label>
                            <div class="input-icon right right_8 col-md-7">
                                <i class="fa"></i>
                                <input name="manual_ip_verify" placeholder="<?php echo $LANG['WEB_KUBE_ENTER_IP_OR_DOMAIN']; ?>" style="height:34px;width: 100%;" id="manual_ip" type="text" maxlength="64" class="table-group-action-input form-control input-inline input input-sm" aria-controls="example">
                            </div>
                        </div>
                    </div>

                    <div class="form_div">
                       <div class="">
                            <!-- 功能描述 -->
                            <label class="col-md-4 form_title"><span class="required">* </span><?php echo $LANG['WEB_KUBE_CLUSTER_CLUSTER_NAME']; ?>
                            </label>
                            <div class="input-icon right right_8  col-md-7" style="display: inline-block;">
                                <i class="fa"></i>
                                <input name="manual_nickname_verify" placeholder="<?php echo $LANG['WEB_KUBE_CLUSTER_ENTER_CLUSTER_NAME']; ?>" style="height:34px;width: 100%;" id="manual_nickname" type="text" maxlength="64" class="table-group-action-input form-control input-inline input input-sm" aria-controls="example">
                            </div>
                        </div>
                    </div>

                    <div class="form_div">
                       <div class="">
                            <!-- 功能描述 -->
                            <label class="col-md-4 form_title"><span class="required">* </span><?php echo $LANG['WEB_KUBE_CLUSTER_CLIENT_LISTEN_PORT']; ?>
                            </label>
                            <div class="input-icon right right_8 col-md-7" style="display: inline-block;">
                                <i class="fa"></i>
                                <input name="manual_port_verify" placeholder="<?php echo $LANG['WEB_KUBE_CLUSTER_ENTER_CLIENT_PORT']; ?>" style="height:34px;width: 100%;" id="manual_port" type="text" maxlength="64" class="table-group-action-input form-control input-inline input input-sm" aria-controls="example">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="SSH_div"  style="display: none;">
                    <div class="form_div">
                        <div class="">
                            <!-- 功能描述 -->
                            <label class="col-md-4 form_title"><span class="required">* </span><?php echo $LANG['WEB_KUBE_IP_OR_DOMAIN']; ?>
                            </label>
                            <div class="input-icon right right_8 col-md-7" style="display: inline-block;">
                            <i class="fa"></i>
                                <input name="ssh_ip_verify" placeholder="<?php echo $LANG['WEB_KUBE_ENTER_IP_OR_DOMAIN']; ?>" style="height:34px;width: 100%;" id="ssh_ip" type="text" maxlength="64" class="table-group-action-input form-control input-inline input input-sm" aria-controls="example">
                            </div>
                        </div>
                    </div>
                    <div class="form_div">
                       <div class="">
                            <!-- 功能描述 -->
                            <label class="col-md-4 form_title"><span class="required">* </span><?php echo $LANG['WEB_KUBE_CLUSTER_SSH_PORT']; ?>
                            </label>
                            <div class="input-icon right right_8 col-md-7" style="display: inline-block;">
                            <i class="fa"></i>
                                <input name="ssh_port_verify" placeholder="<?php echo $LANG['WEB_KUBE_CLUSTER_ENTER_SSH_PORT']; ?>" style="height:34px;width: 100%;" id="ssh_port" type="text" maxlength="64" class="table-group-action-input form-control input-inline input input-sm" aria-controls="example">
                            </div>
                        </div>
                    </div>

                    <div class="form_div">
                       <div class="">
                            <!-- 功能描述 -->
                            <label class="col-md-4 form_title"><span class="required">* </span><?php echo $LANG['WEB_KUBE_CLUSTER_USERNAME']; ?>
                            </label>
                            <div class="input-icon right right_8 col-md-7" style="display: inline-block;">
                            <i class="fa"></i>
                                <input name="ssh_name_verify" placeholder="<?php echo $LANG['WEB_KUBE_CLUSTER_ENTER_USERNAME']; ?>" style="height:34px;width: 100%;" id="ssh_name" type="text" maxlength="64" class="table-group-action-input form-control input-inline input input-sm" aria-controls="example">
                            </div>
                        </div>
                    </div>

                    <div class="form_div">
                       <div class="">
                            <!-- 功能描述 -->
                            <label class="col-md-4 form_title"><?php echo $LANG['WEB_KUBE_CLUSTER_PASSWORD']; ?>
                            </label>
                            <div class="input-icon right right_8 col-md-7" style="display: inline-block;">
                            <i class="fa"></i>
                                <input name="ssh_password_verify" placeholder="<?php echo $LANG['WEB_KUBE_CLUSTER_ENTER_PASSWORD']; ?>" style="height:34px;width: 100%;" id="ssh_password" type="password" maxlength="64" class="table-group-action-input form-control input-inline input input-sm" aria-controls="example">
                            </div>
                        </div>
                    </div>

                    <div class="form_div">
                       <div class="">
                            <!-- 功能描述 -->
                            <label class="col-md-4 form_title"><?php echo $LANG['WEB_KUBE_CLUSTER_ADVANCED_CONFIGURATION']; ?>
                            </label>
                            <div class="col-md-3" style="display: inline-block;">
                                <input type="checkbox" id="ssh_high" class="make-switch"
                                        data-on-color="primary" data-off-color="info" data-size="small"
                                        data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                        data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                            </div>
                        </div>
                    </div>

                    <div class="resourcessh_div" style="display: none;">

                        <div class="form_div">
                           <div class="">
                                <!-- 功能描述 -->
                                <label class="col-md-4 form_title"><?php echo $LANG['WEB_KUBE_CLUSTER_PROXY_NODE_RESOURCE_LIMIT']; ?>
                                </label>
                                <div class="col-md-3" style="display: inline-block;">
                                    <div id = "initSpinnersshcpu">
                                        <div class="input-group spinner-group" >
                                            <input type="text" id="ssh_cpu" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" >
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
                                <div class="col-md-3" style="color: #666666;font-weight: 400;font-size: 14px;text-align: left;line-height: 34px;padding-left: 0;"><?php echo $LANG['WEB_KUBE_CLUSTER_CPU_CORES']; ?></div>
                            </div>
                        </div>

                        <div class="form_div">
                           <div class="">
                                <!-- 功能描述 -->
                                <label class="col-md-4 form_title">
                                </label>
                                <div class="col-md-3" style="display: inline-block;">
                                    <div id = "initSpinnersshram">
                                        <div class="input-group spinner-group" >
                                            <input type="text" id="ssh_ram" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" >
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
                                <div class="col-md-3" style="color: #666666;font-weight: 400;font-size: 14px;text-align: left;line-height: 34px;padding-left: 0;"><?php echo $LANG['WEB_KUBE_CLUSTER_MEMORY_GB']; ?></div>
                            </div>
                        </div>

                    </div>

                    <div class="form_div">
                        <div class="">
                            <!-- 功能描述 -->
                            <label class="col-md-4 form_title"><?php echo $LANG['WEB_KUBE_CLUSTER_NETWORK_MODE']; ?>
                            </label>
                            <div class="col-md-7" style="display: inline-block;">
                                <select class="form-control " id="ssh_network">
                                    <option value="1"><?php echo $LANG['WEB_KUBE_CLUSTER_SERVER_CONNECT_CLIENT']; ?>  </option>
                                    <option value="2"><?php echo $LANG['WEB_KUBE_CLUSTER_CLIENT_CONNECT_SERVER']; ?> </option>

                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form_div ssh_address_div"  style="display: none;">
                        <div class="">
                            <!-- 功能描述 -->
                            <label class="col-md-4 form_title"><?php echo $LANG['WEB_KUBE_CLUSTER_ADDRESS']; ?>
                            </label>
                            <div class="col-md-7" style="display: inline-block;">
                                <select class="form-control " id="ssh_address">

                                </select>
                            </div>
                        </div>
                    </div>


                </div>

            <div class="Config_div"  style="display: none;">
                <div class="form_div">
                    <div class="">
                        <!-- 功能描述 -->
                        <label class="col-md-4 form_title"><?php echo $LANG['WEB_KUBE_CLUSTER_KUBECONFIG_FILE']; ?>
                        </label>
                        <div class="col-md-7" style="display: inline-block;">
                            <input type="file" id="getFile" accept="txt/yaml" style="display: none;">
                            <input type="button" id="getFileBut" value="<?php echo $LANG['WEB_KUBE_CLUSTER_UPLOAD_FILE']; ?>" class="butUpFile">
                        </div>
                    </div>
                </div>

                <div class="form_div">
                    <div class="">
                        <!-- 功能描述 -->
                        <label class="col-md-4 form_title"><span class="required">* </span><?php echo $LANG['WEB_KUBE_CLUSTER_FILE_CONTENT']; ?>
                        </label>
                        <div class="col-md-7" style="display: inline-block;">
                            <textarea placeholder="<?php echo $LANG['WEB_KUBE_CLUSTER_COPY_CONTENT']; ?>" id="config_content" class="code_msg"></textarea>
                        </div>
                    </div>
                </div>

                <div class="form_div">
                    <div class="">
                        <!-- 功能描述 -->
                        <label class="col-md-4 form_title"><?php echo $LANG['WEB_KUBE_CLUSTER_ADVANCED_CONFIGURATION']; ?>
                        </label>
                        <div class="col-md-7" style="display: inline-block;">
                            <input type="checkbox" id="config_high" class="make-switch"
                                data-on-color="primary" data-off-color="info" data-size="small"
                                data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                        </div>
                    </div>
                </div>

                <div class="resourceConfig_div" style="display: none;">

                    <div class="form_div">
                        <div class="">
                            <!-- 功能描述 -->
                            <label class="col-md-4 form_title"><?php echo $LANG['WEB_KUBE_CLUSTER_PROXY_NODE_RESOURCE_CONFIGURATION']; ?>
                            </label>
                            <div class="col-md-3" style="display: inline-block;">
                                <div id = "initSpinnerconfigcpu">
                                    <div class="input-group spinner-group" >
                                        <input type="text" id="config_cpu" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" >
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
                            <div class="col-md-3" style="color: #666666;font-weight: 400;font-size: 14px;text-align: left;line-height: 34px;padding-left: 0;"><?php echo $LANG['WEB_KUBE_CLUSTER_CPU_CORES']; ?></div>
                        </div>
                    </div>

                    <div class="form_div">
                        <div class="">
                            <!-- 功能描述 -->
                            <label class="col-md-4 form_title">
                            </label>
                            <div class="col-md-3" style="display: inline-block;">
                                <div id = "initSpinnerconfigram">
                                    <div class="input-group spinner-group" >
                                        <input type="text" id="config_ram" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control input-sm" >
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
                            <div class="col-md-3" style="color: #666666;font-weight: 400;font-size: 14px;text-align: left;line-height: 34px;padding-left: 0;"><?php echo $LANG['WEB_KUBE_CLUSTER_MEMORY_GB']; ?></div>
                        </div>
                    </div>

                </div>



                <div class="form_div">
                    <div class="">
                        <!-- 功能描述 -->
                        <label class="col-md-4 form_title"><?php echo $LANG['WEB_KUBE_CLUSTER_NETWORK_MODE']; ?>
                        </label>
                        <div class="col-md-7" style="display: inline-block;">
                            <select class="form-control " id="config_network">
                                <option value="1"><?php echo $LANG['WEB_KUBE_CLUSTER_SERVER_CONNECT_CLIENT']; ?>  </option>
                                <option value="2"><?php echo $LANG['WEB_KUBE_CLUSTER_CLIENT_CONNECT_SERVER']; ?> </option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form_div config_address_div" style="display: none;">
                    <div class="">
                        <!-- 功能描述 -->
                        <label class="col-md-4 form_title"><?php echo $LANG['WEB_KUBE_CLUSTER_ADDRESS']; ?>
                        </label>
                        <div class="col-md-7" style="display: inline-block;">
                            <select class="form-control " id="config_address">

                            </select>
                        </div>
                    </div>
                </div>



            </div>







            </form>


        </div>

    </div>
    <!--  hint -->
    <div class="form_div">
        <div class="col-md-12">
            <div class="alert alert-block alert-info fade in">
                <button type="button" class="close" data-dismiss="alert"></button>
                <ul class="alert-ul">
                    <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                    <li>
                    <?php echo $LANG['WEB_KUBE_CLUSTER_ADD_CLUSTER_TIPS']; ?>
                        <a id="excludeBtn" data-toggle="drawer" data-target="#drawer-1"><?php echo $LANG['WEB_KUBE_CLUSTER_HELP_CENTER']; ?></a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- modal-footer -->
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
        <button type="button"class="btn btn-primary" id="current_cluster_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>
<!-- modal end -->


<!-- modal start -->
<div id="editClustermodal" class="modal xmodal fade form-horizontal high-search" tabindex="-1"  data-backdrop="static">
    <div class="modal-header " style="display: flex;padding: 10px;">
        <i class="viconfont vicon-a-Editbianji" style="padding: 12px 10px;font-size: 20px;"></i>
        <h4 style="font-weight: 400;font-size: 16px;color: #333333;"><?php echo $LANG['WEB_KUBE_EDIT_KUBERNETES_CLUSTER'] ?></h4>
    </div>
    <div class="modal-body form_body">
        <div class="portlet-body">
            <form>
                <div class="form_div">
                    <div class="">
                        <!-- 集群名称 -->
                        <label class="col-md-4 form_title"><?php echo $LANG['WEB_KUBE_CLUSTER_NAME'] ?>
                        </label>
                        <div class="col-md-7" style="display: inline-block;">
                        <input placeholder="<?php echo $LANG['WEB_KUBE_CLUSTER_NAME'] ?>" style="height:34px;width: 100%;" id="editClusterName" maxlength="64" class="table-group-action-input form-control input-inline input input-sm" aria-controls="example">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- modal-footer -->
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
        <button type="button"class="btn btn-primary" id="edit_cluster_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>
<!-- modal end -->



<!-- drawer开始 -->
<div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true" id="drawer-1" style="width: 600px;">
    <?php if ($_SESSION['language'] == "en-us") {
        include_once './kubernetes_cluster_explain_en-us.php';
    } else {
        include_once './kubernetes_cluster_explain_zh-cn.php';
    } ?>
</div>
<!-- drawer结束 -->




<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<?php
if ($_SESSION['language'] != "en-us") {
	//如果不是英文,加载语言包
	echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
		$_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script src="./scripts/kubernetes/kubernetes_cluster.js" type="text/javascript">
