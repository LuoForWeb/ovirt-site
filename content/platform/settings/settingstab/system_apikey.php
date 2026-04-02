<?php include_once '../../../../tpl/permission.php';?>
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="setting_manager" href="./content/platform/settings/setting_manager.php" >
            <span><?php echo $LANG['UI_PLATFORM_SYSTEM_SET'] ?></span>
        </a>
    </li>
    <span>
        >
    </span>
    <span class="curent"><?php echo $LANG['UI_SETTINGS_APIKEY_MANAGE'] ?></span>
</h3>

<div class="row">
	<div class="col-md-12">
		<!-- BEGIN TAB PORTLET-->
		 <div class="portlet box blue-hoki">
			<div class="portlet-title">
                <div class="caption">
                    <i class="viconfont vicon-apikey"></i><?php echo $LANG['UI_SETTINGS_APIKEY_MANAGE_LIST'] ?>
                </div>
        	</div>
             <div class="portlet-body margin10">

                 <div class="table-container">
                     <div class="vin_toolbar" >
                         <div class="leftTool">
                             <?php
                             //检查屏蔽只读观察者的操作按钮
                             // 根据授权显示
                             if (in_array("p_api_key_delete", $_SESSION['permissionArr'])) {
                                 // 删除
                                 echo '<div style="cursor:not-allowed;"><button class="btn viconfont vicon-a-Deleteshanchu1 b-btn brr2 mr12 exch-forbid-event" id="delete"></button></div>';
                             }
                             ?>
                             <div class="customBtn2">

                                 <?php
                                 //检查屏蔽只读观察者的操作按钮
                                 if (in_array("p_api_key_add", $_SESSION['permissionArr'])) {
                                     // 添加
                                     echo '<button type="button" id="add" class="btn table-toolbar-btn">
									<i class="viconfont vicon-a-Unlockjiesuo-011"></i> ' . $LANG['UI_SETTINGS_APIKEY_CREATE'] . '
								</button>';
                                 }
                                 ?>

                                 <?php
                                 //检查屏蔽只读观察者的操作按钮
                                 if (in_array("p_api_key_enable", $_SESSION['permissionArr'])) {
                                     // 启用
                                     echo '<button type="button" id="unlock" class="btn table-toolbar-btn">
									<i class="viconfont vicon-a-Unlockjiesuo-0111"></i> ' . $LANG['WEB_PLATFORM_ENABLE'] . '
								</button>';
                                 }
                                 ?>

                                 <?php
                                 //检查屏蔽只读观察者的操作按钮
                                 if (in_array("p_api_key_disable", $_SESSION['permissionArr'])) {
                                     // 禁用
                                     echo '<button type="button" id="lock" class="btn table-toolbar-btn">
									<i class="viconfont vicon-a-Unlockjiesuo-011"></i> ' . $LANG['WEB_PLATFORM_DISABLE'] . '
								</button>';
                                 }
                                 ?>

                             </div>
                         </div>

                         <div class="vin_btnToolbar">
                         </div>
                     </div>
                 </div>

                 <div class="table-container">
                     <table id="system_apikey_table"></table>
                     <div class="alert alert-block alert-info fade in" id="marktips">
                         <button type="button" class="close" data-dismiss="alert"></button>
                         <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                         <ol class = "alert-ol">
                             <li>
                                 <?php echo $LANG['UI_SETTINGS_APIKEY_MANAGE_TIPS1'] ?>
                             </li>
                             <li>
                                 <?php echo $LANG['UI_SETTINGS_APIKEY_MANAGE_TIPS2'] ?>
                             </li>
                             <li>
                                 <?php echo $LANG['UI_SETTINGS_APIKEY_MANAGE_TIPS3'] ?>
                             </li>
                         </ol>
                     </div>
                 </div>
             </div>
         </div>

    </div>
</div>

<script type="text/javascript" src="./scripts/platform/settings/settingstab/system_apikey.js"></script>