<?php include_once '../../tpl/permission.php';?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
		<a class="ajaxify" name="infrastructure" href="./content/platform/resource/infrastructure.php">
			<span><?php echo $LANG['UI_PLATFORM_INFRASTRUCTURE'] ?></span>
		</a>
	</li>
	<span>></span>
    <li>
        <a class="ajaxify" name="infrastructure" href="./content/hadoop/hadoop_cluster.php" >
            <span><?php echo $LANG['WEB_HADOOP_CLUSTER_MANAGE']?></span>
        </a>
    </li>
    <span>></span>
    <span class="curent"><?php echo $LANG['WEB_HADOOP_EDIT_CLUSTER']?></span>
</h3>
<input id="clusteruuid" value="<?php echo $_GET['clusteruuid'];?>" class="display-none"></input>
<div class="row row-manager">
	<div class="col-md-12 col-manager" id="EditClusterDiv">
		<!-- BEGIN TAB PORTLET-->
		<div class="portlet box blue-hoki" id=''>
			<div class="portlet-title">
				<div class="caption" >
					<i class="viconfont vicon-a-Editbianji1 mt2"></i><?php echo $LANG['WEB_HADOOP_EDIT_CLUSTER']?>
				</div>
        </div>
        <div class="portlet-body form">
            <div id="clusterform" class="form-horizontal add_hadoop_form" >
                <div class="form-body">
                    <div class="form-group">
                        <label class="control-label col-md-2">
                            <span class="required">* </span><?php echo $LANG['UI_RECOVERY_STORAGE_NAME']?>
                        </label>
                        <div class="col-md-10 pl0">
                            <div class="col-md-9">
                                <div class="input-icon right">
                                    <i class="fa"></i>
                                    <input type="text" size="16"  id="clustername" class="form-control" name="clustername" onkeyup="customInputValidate('string', this.value, $(this))">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group appliancediv">
                        <label class="control-label col-md-2 appliancelabel form-group-label"><?php echo $LANG['UI_HADOOP_PROXY']?></label>
                        <div class="col-md-10 pl0">
                            <div class="col-md-9">
                                <div style="display: flex;">
                                    <input type="checkbox" id="appliancecheck"  class="make-switch" data-on-color="primary" data-off-color="info" data-size="small"
                                            data-on-text="<?php echo $LANG['UI_PUBLIC_ON']?>"
                                            data-off-text="<?php echo $LANG['UI_PUBLIC_OFF']?>">
                                    <div class="col-md-2 mt5">
                                        <a class="popovers" data-container="body" data-trigger="hover" data-html="true"
                                            data-placement="right" data-content="<?php echo $LANG['UI_HADOOP_APPLIANCE_DES']?>">
                                            <i class="viconfont vicon-tishi"></i>
                                        </a>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                    <div class="form-group display-hide applianceselectdiv">
                        <label class="control-label col-md-2 applianceselectlabel form-group-label"><?php echo $LANG['UI_HADOOP_SELECT_PROXY']?></label>
                        <div class="col-md-10 content pl0">
                            <div class="col-md-9">
                                <select class="form-control select2me" id="applianceSelect">
                                </select>
                            </div>
                        </div>
                    </div>
                    <!-- 验证方式 -->
                    <div class="form-group">
                        <label class="control-label col-md-2"><?php echo $LANG['UI_VERIFY_TYPE']?>
                        </label>
                        <div class="col-md-10 pl0">
                            <div class="col-md-9">
                                <select class="form-control select2me" id="verification">
                                    <option value="1">Simple</option>
                                    <option value="2">Kerberos</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <label class="control-label col-md-2">NameNode
                    </label> 
                    <div class="col-md-10 pl0">
                        <form class="form-group addnodeform display-none row">
                            <!-- <div class="col-md-2 firstlabel"></div> -->
                            <div class="col-md-9">
                                <div class="accordion strategyOne">
                                    <div class="panel panel-default strategy-panel">
                                        <div class="panel-heading">
                                            <h4 class="panel-title">
                                                <a class="accordion-toggle accordion-toggle-styled collapsed popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".strategyOne" href="#nodeinfo" aria-expanded="true" data-original-title="" title="">
                                                    <span><?php echo $LANG['WEB_HADOOP_ADD_NAMENODE']?></span>
                                                    <span class="strategyDes higeDes"></span>
                                                </a>
                                            </h4>
                                        </div>
                                        <div id="nodeinfo" class="panel-collapse collapse ">
                                            <div class="panel-body">
                                                <div class="col-md-12">
                                                    <!-- kerberos验证方式 -->
                                                    <div class="kerberos  display-none ">
                                                        <!-- 配置文件方式 -->
                                                        <div class="profiletype display-none">
                                                            <!-- core-site.xml文件 -->
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3">
                                                                    <span class="required">* </span>
                                                                    <?php echo $LANG['WEB_HADOOP_CORE_SITE_FILE']?>
                                                                </label>
                                                                <div class="col-md-7 notchoosefile" style="display: inline-block;">
                                                                    <input type="file" id="getcoreFile" accept="xml" style="display: none;">
                                                                    <input type="button" id="getcoreFileBut" value="<?php echo $LANG['UIS_SETTINGS_UPLOAD_SELECT_FILE']?>" class="chooseUpFile">
                                                                    <span class="help-block" style="display: inline;">（<?php echo $LANG['WEB_HADOOP_NO_SELECT_FILE']?>）</span>
                                                                </div>
                                                                <!-- 已经选择好文件 -->
                                                                <div class="col-md-7 display-none choosedfile" >
                                                                    <div class="hadoop_file_choose" >
                                                                        <sapn class="title"></sapn>
                                                                            <i class=" viconfont vicon-a-Close-oneguanbi deletefile" style="float:right"></i>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- hdfs-site.xml文件 -->
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3">
                                                                    <span class="required">* </span>
                                                                    <?php echo $LANG['WEB_HADOOP_HDFS_SITE_FILE']?>
                                                                </label>
                                                                <div class="col-md-7 notchoosefile" style="display: inline-block;">
                                                                    <input type="file" id="gethdfsFile" accept="xml" style="display: none;">
                                                                    <input type="button" id="gethdfsFileBut" value="<?php echo $LANG['UIS_SETTINGS_UPLOAD_SELECT_FILE']?>" class="chooseUpFile">
                                                                    <span class="help-block" style="display: inline;">（<?php echo $LANG['WEB_HADOOP_NO_SELECT_FILE']?>）</span>
                                                                </div>
                                                                <!-- 已经选择好文件 -->
                                                                <div class="col-md-7 display-none choosedfile" >
                                                                    <div class="hadoop_file_choose" >
                                                                        <sapn class="title"></sapn>
                                                                            <i class=" viconfont vicon-a-Close-oneguanbi deletefile" style="float:right"></i>
                                                                    </div>
                                                                </div>

                                                            </div>

                                                        </div>
                                                        <!-- 手动添加方式 -->
                                                        <div class="manualaddtype">
                                                            <!-- Realm名称 -->
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3">
                                                                    <span class="required">* </span>
                                                                    <?php echo $LANG['WEB_HADOOP_REALM_NAME']?>
                                                                </label>
                                                                <div class="col-md-6">
                                                                    <div class="input-icon right">
                                                                        <i class="fa"></i>
                                                                        <input type="text"  maxlength="128" class="form-control" name="realmname" id="realmname" onkeyup="this.value=this.value.replace(/[, ]/g,'')" >
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- Realm KDC 服务器 -->
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3">
                                                                    <span class="required">* </span>
                                                                    <?php echo $LANG['WEB_HADOOP_REALM_KDC_SERVER']?>
                                                                </label>
                                                                <div class="col-md-6">
                                                                    <div class="input-icon right">
                                                                        <i class="fa"></i>
                                                                        <input type="text"  maxlength="128"  placeholder="192.168.1.168" class="form-control" name="kdcserver" id="kdcserver" onkeyup="this.value=this.value.replace(/[, ]/g,'')" >
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- Realm 管理服务器 -->
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3">
                                                                    <span class="required">* </span>
                                                                    <?php echo $LANG['WEB_HADOOP_REALM_MANAGE_SERVER']?>
                                                                </label>
                                                                <div class="col-md-6">
                                                                    <div class="input-icon right">
                                                                        <i class="fa"></i>
                                                                        <input type="text"  maxlength="128"  placeholder="192.168.1.168" class="form-control" name="manageserver" id="manageserver" onkeyup="this.value=this.value.replace(/[, ]/g,'')" >
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- REST API Principal -->
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3">
                                                                    <span class="required">* </span>
                                                                    WebHDFS API Principal
                                                                </label>
                                                                <div class="col-md-6">
                                                                    <div class="input-icon right">
                                                                        <i class="fa"></i>
                                                                        <input type="text"  maxlength="128" class="form-control" name="restprincipal" id="restprincipal" onkeyup="this.value=this.value.replace(/[, ]/g,'')" >
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- UDP Preference Limit -->
                                                            <div class="form-group">
                                                                <label class="control-label col-md-3">
                                                                    <span class="required">* </span>
                                                                    UDP Preference Limit
                                                                </label>
                                                                <div class="col-md-6">
                                                                    <div class="input-icon right">
                                                                        <i class="fa"></i>
                                                                        <input type="text"  maxlength="128" class="form-control" name="udpprincipal" id="udpprincipal" onkeyup="this.value=this.value.replace(/[^\d]/g,'')" placeholder="1">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="control-label col-md-3">
                                                                <span class="required">* </span>
                                                                <?php echo $LANG['WEB_HADOOP_KRB5_KEYTAB_FILE']?>
                                                            </label>
                                                            <div class="col-md-7 notchoosefile" style="display: inline-block;">
                                                                <input type="file" id="getkrb5File" accept="keytab" style="display: none;" >
                                                                <input type="button" id="getkrb5FileBut" value="<?php echo $LANG['UIS_SETTINGS_UPLOAD_SELECT_FILE']?>" class="chooseUpFile">
                                                                <span class="help-block" style="display: inline;">（<?php echo $LANG['WEB_HADOOP_NO_SELECT_FILE']?>）</span>
                                                            </div>
                                                            <!-- 已经选择好文件 -->
                                                            <div class="col-md-7 display-none choosedfile" >
                                                                <div class="hadoop_file_choose" >
                                                                    <sapn class="title"></sapn>
                                                                        <i class=" viconfont vicon-a-Close-oneguanbi deletefile" style="float:right"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                
                                                    <!-- 主机 -->
                                                    <div class="form-group">
                                                        <label class="control-label col-md-3">
                                                            <span class="required">* </span>
                                                            <?php echo $LANG['WEB_PLATFORM_DES_HOST']?>
                                                        </label>
                                                        <div class="col-md-6">
                                                            <div class="input-icon right">
                                                                <i class="fa"></i>
                                                                <input type="text"  maxlength="128"  placeholder="192.168.1.168" class="form-control" name="hostip" onkeyup="this.value=this.value.replace(/[, ]/g,'')" id="hostip">
                                                                <div><span class="help-block"><?php echo $LANG['UI_HADOOP_SET_HOST_TIP']?></div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3" style="padding-left: 0;padding-top: 5px">
                                                            <label>
                                                                <input type="checkbox" id="sslConnect" data-checkbox="icheckbox_square-blue"
                                                                    class="icheck">
                                                                <?php echo $LANG['UI_STORAGE_CLOUD_AWS_SSL_CERTIFICATE_VERIFICATION'] ?>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <!-- 用户 -->
                                                    <div class="form-group">
                                                        <label class="control-label col-md-3">
                                                            <span class="required">* </span>
                                                            <?php echo $LANG['WEB_USERS_USER']?>
                                                        </label>
                                                        <div class="col-md-6">
                                                            <div class="input-icon right">
                                                                <i class="fa"></i>
                                                                <input type="text"  maxlength="128"  placeholder="hdfs" class="form-control" name="username" id="username" onkeyup="this.value=this.value.replace(/[, ]/g,'')" >
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- RESTAPI端口 -->
                                                    <div class="form-group">
                                                        <label class="control-label col-md-3">
                                                            <span class="required">* </span>
                                                            <?php echo $LANG['WEB_HADOOP_REST_API_PORT']?>
                                                        </label>
                                                        <div class="col-md-6">
                                                            <div class="input-icon right">
                                                                <i class="fa"></i>
                                                                <input type="text"  maxlength="128"  placeholder="50070" class="form-control" name="restapi" id="restapi" oninput="this.value=this.value.replace(/[^\d]/g,'')"  >
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- 删除按钮 -->
                            <div class="col-md-1">
                                <div class="btn_operation_vicon">
                                    <a><i class="viconfont vicon-a-Deleteshanchu1 deleteNode"></i></a>
                                </div>
                            </div>
                        </form>
                    </div>

                    
                    <!-- 添加按钮 -->
                    <div class="form-group row">
                        <div class="control-label col-md-2"></div>
                        <div class="col-md-2">
                            <div class="btn-group">
                                <button type="button" id="addNode" class="btn btn-sm green-haze">
                                <i class="viconfont vicon-ge_add_task"></i>  <?php echo $LANG['WEB_HADOOP_ADD_NAMENODE']?>
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- 帮助中心 -->
                    <div class="form_div row">
                        <div class="control-label col-md-2"></div>
                        <div class="col-md-10">
                            <div class="col-md-9 pl0" >
                                <div class="alert alert-block alert-info fade in">
                                    <button type="button" class="close" data-dismiss="alert"></button>
                                    <ul class="alert-ul">
                                        <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS']?>:</strong>
                                        <li>
                                        <?php echo $LANG['WEB_HADOOP_NAMENODE_CONFIG_TIPS1']?>
                                            <a id="excludeBtn" data-toggle="drawer" data-target="#drawer-1"><?php echo $LANG['WEB_HADOOP_HELP_CENTER']?></a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                   
                </div>
                <div class="form-actions">
                    <div class="row">
                        <div class="col-md-offset-3 col-md-4">
                            <button type="button" id="clutercancel" class="btn default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
                            <button type="button" id="clustersubmit" class="btn green-haze btn-confirm"><?php echo $LANG['UI_PUBLIC_YES']?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- drawer开始 -->
<div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true" id="drawer-1" style="width: 600px;">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header help_header">
            <h4 class="drawer-title help_title" id="drawer-1-title">
                <i class="viconfont vicon-bangzhuzhongxin help_icon_helpcenter"></i><?php echo $LANG['WEB_HADOOP_HELP_CENTER']?>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close help_icon_close"><i class="viconfont vicon-guanbi"></i></span>
            </h4>

        </div>
        <div class="drawer-body help_body">
            <h4><?php echo $LANG['UI_HADOOP_ADD_CLUSTER']?></h4>
            <hr>
            <p><?php echo $LANG['UI_HADOOP_ADD_CLUSTER_METHOD_TIPS']?></p>
            <h5>1.<?php echo $LANG['UI_HADOOP_ADD_CLUSTER_SIMPLE_VERIFY']?></h5>
            <hr>
            <p><?php echo $LANG['UI_HADOOP_ADD_CLUSTER_SIMPLE_VERIFY_TIPS']?></p>
            <h5>2.<?php echo $LANG['UI_HADOOP_ADD_CLUSTER_KERBEROS_VERIFY']?></h5>
            <hr>
            <p><?php echo $LANG['UI_HADOOP_ADD_CLUSTER_KERBEROS_VERIFY_TIPS']?></p>
            <h5>（1）<?php echo $LANG['WEB_HADOOP_REST_API_PORT']?></h5>
            <p><?php echo $LANG['WEB_HADOOP_REST_API_PORT_TIPS']?></p>
            <h5>（2）<?php echo $LANG['WEB_HADOOP_REALM_NAME']?> </h5>
            <p><?php echo $LANG['WEB_HADOOP_REALM_TIPS']?></p>
            <h5>（3）<?php echo $LANG['WEB_HADOOP_REALM_KDC_SERVER']?></h5>
            <p><?php echo $LANG['WEB_HADOOP_REALM_KDC_SERVER_TIPS']?></p>
            <h5>（4）<?php echo $LANG['WEB_HADOOP_REALM_MANAGE_SERVER']?></h5>
            <p><?php echo $LANG['WEB_HADOOP_REALM_MANAGE_SERVER_TIPS']?></p>
            <h5>（5）WebHDFS API Principal</h5>
            <p><?php echo $LANG['WEB_HADOOP_API_PRINCIPAL_TIPS']?></p>
            <h5>（6）UDP Preference Limit</h5>
            <p><?php echo $LANG['WEB_HADOOP_UDP_PREFERENCE_LIMIT_TIPS']?></p>
            <h5>（7）<?php echo $LANG['WEB_HADOOP_KRB5_KEYTAB_FILE']?> </h5>
            <p><?php echo $LANG['WEB_HADOOP_KEYTAB_FILE_TIPS']?></p>

        </div>
        <div class="drawer-footer">
            <!--            <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-primary" id="addsubmit">确 定</button>-->
            <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_CLOSE']?></button>
        </div>
    </div>
</div>

<!-- drawer结束 -->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<?php
if ($_SESSION['language'] != "en-us") {
	//如果不是英文,加载语言包
	echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
		$_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script src="./scripts/hadoop/edit_cluster.js" type="text/javascript">

