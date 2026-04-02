<?php
include_once '../../../tpl/permission.php';
include_once   '../../platform/public/bs_table.php';
?>

<!-- BEGIN PAGE HEADER-->
<!-- BEGIN PAGE LEVEL STYLES -->

<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link rel="stylesheet" type="text/css" href="./css/platform/vm_machine/vm_machine.css" />

<?php
$userAllPermission = $_SESSION['permission'];

$tabNameArr = array('vm_machine_network', 'vm_machine_list', 'vm_machine_operate_log', 'vm_machine_proxy_gateway');
$activeClassArr = array();  //active类
$displayArr = array();      //是否显示

$setFlag = false;
foreach ($tabNameArr as $key => $value){
    if(in_array($value, $userAllPermission)){
        //如果有权限
        $displayArr[$value] = "";
        if(!$setFlag){
            $activeClassArr[$value] = " active ";
            $setFlag = true;
        }else{
            $activeClassArr[$value] = "";
        }
    }else{
        //如果没有权限
        $activeClassArr[$value] = "";
        $displayArr[$value] = "displaynone";
    }
}

?>

<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<!-- END PAGE CONTENT-->
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>

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
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
		<!-- BEGIN TAB PORTLET-->
		<div class="portlet box blue-hoki" id='authdiv'>
			<div class="portlet-title">
				<ul class="nav nav-tabs floatl">
					<li class="<?php echo $activeClassArr['vm_machine_network']?> <?php echo $displayArr['vm_machine_network']?>">
						<a href="#vmMachineNetworkdiv" data-toggle="tab">
                            <i class="levelchild viconfont vicon-vm_overview"></i>
                            <?php echo $LANG['UI_VM_MACHINE_NETWORK']?> </a>
					</li>
                    <li class="<?php echo $activeClassArr['vm_machine_list']?> <?php echo $displayArr['vm_machine_list']?>">
                        <a href="#vmMachinediv" data-toggle="tab">
                            <i class="levelchild viconfont vicon-zhuji1"></i>
                            <?php
                            if ( $CONF['SYSTEM_INFO']['vendor'] == $CONF['VENDOR_LIST']['gmp']){
                                echo $LANG['WEB_PLATFORM_INDUSTRY_REPORT_MACHINE'];
                            }else{
                                echo $LANG['UI_VM_MACHINE_LIST'];
                            }
                            ?> </a>
                    </li>
                    <?php
                    echo '<li class=" '.$activeClassArr['vm_machine_proxy_gateway']. $displayArr['vm_machine_proxy_gateway'] . '">
						<a href="#vmPrxyGatewaydiv" data-toggle="tab">
						<i class="levelchild viconfont vicon-tasklog"></i>
						 '.$LANG['UI_DRILLS_AGENT_GATEWAY'].'</a></li>';
                    ?>
					<?php
                        echo '<li class=" '.$activeClassArr['vm_machine_operate_log']. $displayArr['vm_machine_operate_log'] . '">
						<a href="#vmMachineLogdiv" data-toggle="tab">
						<i class="levelchild viconfont vicon-tasklog"></i>
						 '.$LANG['UI_VM_MACHINE_LOG'].'</a></li>';
					?>

				</ul>
			</div>
			<div class="portlet-body" style="padding-top: 0px;">
				<div class="tab-content row margin10">

                    <div class="tab-pane <?php echo $activeClassArr['vm_machine_network']?>" id="vmMachineNetworkdiv">
                        <?php
                        if (in_array('vm_machine_network', $_SESSION['permission'])) {
                            include_once './network.php';
                        }
                        ?>
                    </div>

                    <div class="tab-pane <?php echo $activeClassArr['vm_machine_list']?>" id="vmMachinediv">
                        <?php
                        if (in_array('vm_machine_list', $_SESSION['permission'])) {
                            include_once './vm_list.php';
                        }
                        ?>
					</div>

					<div class="tab-pane <?php echo $activeClassArr['vm_machine_operate_log']?>" id="vmMachineLogdiv">
                        <?php
                        if (in_array('vm_machine_operate_log', $_SESSION['permission'])) {
                            include_once './vm_log.php';
                        }
                        ?>
					</div>

                    <div class="tab-pane <?php echo $activeClassArr['vm_machine_proxy_gateway']?>" id="vmPrxyGatewaydiv">
                        <?php
                        if (in_array('vm_machine_proxy_gateway', $_SESSION['permission'])) {
                            include_once './proxy_gateway.php';
                        }
                        ?>
                    </div>

				</div>
			</div>
		</div>
		<!-- END TAB PORTLET-->
	</div>
</div>
