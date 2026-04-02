</div>
<!-- END CONTAINER -->
<!-- BEGIN FOOTER -->
<!-- 
<div class="page-footer">
	<div class="page-footer-inner">
		 <?php
		 echo $CONF['SYSTEM_INFO']['copyright'] . " &copy; " . $CONF['SYSTEM_INFO']['years'] . " " .
		 	$CONF['SYSTEM_INFO']['company'] . " " . $CONF['SYSTEM_INFO']['version'];
		 ?>
	</div>
	<?php
	$display = "display-none";
	if ($softwareType == $CONF['SOFTWARE_VERSION']['FREE_EDITION']) {
		//如果是免费版
		$display = "";
	}
	?>
	<div class="<?php echo $display; ?>" style="float: right; color: #ff1100;">
		  <?php echo $LANG['UI_FOOTER_LISENCE_TIP'] ?>&nbsp;&nbsp;
		   <a target="_black" href="<?php echo $CONF['REMOTE']['website'] ?>"><?php echo $LANG['UI_FOOTER_UPDATE'] ?></a>
	</div>
	

 -->
<div class="">
	<div class="scroll-to-top">
		<i class="icon-arrow-up"></i>
	</div>
</div>
<!-- END FOOTER -->

<!-- BEGIN JAVASCRIPTS(Load javascripts at bottom, this will reduce page load time) -->
<!-- BEGIN CORE PLUGINS -->
<!--[if lt IE 9]>
<script src="./assets/global/plugins/respond.min.js"></script>
<script src="./assets/global/plugins/excanvas.min.js"></script> 
<![endif]-->
<script src="./scripts/conf/config.js" type="text/javascript"></script>
<script src="./lang/<?php echo $_SESSION['language']; ?>.js" type="text/javascript"></script>
<script src="./assets/global/plugins/jquery-migrate.min.js" type="text/javascript"></script>
<!-- IMPORTANT! Load jquery-ui.min.js before bootstrap.min.js to fix bootstrap tooltip conflict with jquery ui tooltip -->
<script src="./assets/global/plugins/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js" type="text/javascript">
</script>
<script src="./assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/jquery.cokie.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/uniform/jquery.uniform.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js" type="text/javascript"></script>
<script src="./scripts/libs/json2.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/fuelux/js/spinner.js" type="text/javascript"></script>
<script src="./assets/global/plugins/bootbox/bootbox.min.js" type="text/javascript"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-modal/js/bootstrap-modalmanager.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-modal/js/bootstrap-modal.js"></script>
<script src="./assets/global/plugins/jquery.history.min.js" type="text/javascript"></script>

<script src="./assets/global/plugins/cryptojs/crypto-js.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/bootstrap-drawer/js/bootstrap-drawer.js" type="text/javascript"></script>
<script src="./assets/global/plugins/clipboard/clipboard.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/dropzone/js/dropzone.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/nouislider/js/nouislider.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/js-pdf/js/jspdf.umd.js" type="text/javascript"></script>
<script src="./assets/global/plugins/exportpdf/html2canvas.js" type="text/javascript"></script>
<script src="./assets/global/plugins/exportpdf/FeiHuaSongTi-normal.js" type="text/javascript"></script>
<script src="./assets/global/plugins/sheetjs/xlsx.full.min.js" type="text/javascript"></script>
<!-- END CORE PLUGINS -->
<script src="./assets/global/plugins/bootstrap-toastr/toastr.min.js"></script>
<script src="./assets/global/scripts/metronic.js" type="text/javascript"></script>
<script src="./assets/admin/layout/scripts/layout.js" type="text/javascript"></script>
<script src="./assets/admin/layout/scripts/quick-sidebar.js" type="text/javascript"></script>
<script src="./scripts/public/init.js" type="text/javascript"></script>
<script src="./scripts/public/ui-toastr.js" type="text/javascript"></script>
<script src="./scripts/public/public.js" type="text/javascript"></script>
<script src="./scripts/public/utils.js" type="text/javascript"></script>
<script src="./scripts/public/initComponents.js" type="text/javascript"></script>
<script src="./scripts/public/route.js" type="text/javascript"></script>
<script src="./assets/global/plugins/jquery-idle-timeout/jquery.idletimeout.js" type="text/javascript"></script>
<script src="./assets/global/plugins/jquery-idle-timeout/jquery.idletimer.js" type="text/javascript"></script>
<script src="./scripts/platform/ui-idletimeout.js"></script>
<script src="./scripts/public/utils/input_validate.js" type="text/javascript"></script>
<script src="./scripts/public/utils/service_module_config.js" type="text/javascript"></script>
<script src="./scripts/platform/component/form_validator.js" type="text/javascript"></script>
<!-- END JAVASCRIPTS -->